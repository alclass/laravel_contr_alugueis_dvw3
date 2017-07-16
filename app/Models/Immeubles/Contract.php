<?php
namespace App\Models\Immeubles;

use App\Models\Billing\BillingItemsForJson;
use App\Models\Billing\BillingItemObjToAssocArray as BItem;
use App\Models\Billing\Cobranca;
use App\Models\Billing\CobrancaTipo;
use App\Models\Immeubles\CondominioTarifa;
use App\Models\Tributos\IPTUTabela;
use App\Models\Utils\DateFunctions;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Contract extends Model {


  protected $table = 'contracts';

  private $today            = null;
  private $monthyeardateref = null;
  private $monthly_duedate  = null;
  private $cobranca_to_save = null;

  protected $fillable = [
		'initial_rent_value', 'current_rent_value', 'indicador_reajuste',
    'pay_day_when_monthly', 'aplicar_iptu', 'aplicar_condominio',
    'percentual_multa', 'percentual_juros', 'aplicar_corr_monet',
    'signing_date', 'start_date', 'duration_in_months', 'n_days_aditional', 'is_active',
	];

  public function get_end_date() {
    $end_date = $this->start_date->copy()->addMonths($this->duration_in_months);
    if ($this->n_days_aditional > 0) {
      $end_date->addDays($this->n_days_aditional);
    }
    return $end_date;
  }

  public function find_rent_value_next_reajust_date($from_date = null) {
    if ($from_date = null) {
      $this->set_obj_dates_based_on_today();
      $from_date = $this->today();
    }
    $start_date     = $this->start_date->copy();
    $inbetween_date = $from_date;
    $end_date       = $this->get_end_date();
    return DateFunctions::find_next_anniversary_date_with_triple_start_inbetween_end(
      $start_date,
      $inbetween_date,
      $end_date
    );
  }

  private function create_billingitem_aluguel() {
    $billingitem  = new BItem;
    $assoc_array = array();
    $cobrancatipo_id = CobrancaTipo
      ::where('char_id', CobrancaTipo::K_TEXT_ID_ALUGUEL)
      ->first();
    $billingitem->cobrancatipo_id = $cobrancatipo_id;
    // fetch value
    $billingitem->item_value = $this->current_rent_value;
    $is_yearly=false;
    $billingitem->ref_obj = BItem::make_ref_obj_with_date($this->monthyeardateref, $is_yearly);
    return $billingitem;
  }

  private function create_billingitem_iptu($iptu_table) {
    $billingitem  = new BItem;
    $cobrancatipo_id = CobrancaTipo
      ::where('char_id', CobrancaTipo::K_TEXT_ID_IPTU)
      ->first();
    $billingitem->cobrancatipo_id = $cobrancatipo_id;
    // fetch value
    if ($iptu_table->optado_por_cota_unica == true && $this->monthyeardateref->month < 3) {
      $billingitem->item_value = $iptu_table->valor_parcela_unica;
      $n_cota_ref      = 1;
      $total_cotas_ref = 1;
      $is_yearly = true;
      $billingitem->ref_obj = BItem::make_ref_obj_with_parcels($n_cota_ref, $total_cotas_ref, $is_yearly);
    } else {
      if ($this->monthyeardateref->month == 1 || $this->monthyeardateref->month == 12) {
        // this case is optado por 10x and the first one starts in March ref. February
        // Billing happens from March to December, ref. Feb to Nov
        return null;
      }
      $billingitem->item_value = $iptu_table->valor_parcela_10x;
      $n_cota_ref = $this->monthyeardateref->month - 3;
      $total_cotas_ref = 10;
      $billingitem->ref_obj = BItem::make_ref_obj_with_parcels($n_cota_ref, $total_cotas_ref);
    }
    return $billingitem;
  }

  private function create_billingitem_condominio() {
    $billingitem  = new BItem();
    $cobrancatipo_id = CobrancaTipo
      ::where('char_id', CobrancaTipo::K_TEXT_ID_CONDOMINIO)
      ->first();
    $billingitem->cobrancatipo_id = $cobrancatipo_id;
    // fetch value
    $condominio_tarifa = CondominioTarifa
      ::where('imovel_id', $this->imovel->id)
      ->where('monthyeardateref', $this->monthyeardateref)
      ->first();
    $brief_info = null;
    if ($condominio_tarifa == null) {
      $condominio_valor = CondominioTarifa::calcular_media_das_ultimas_tarifas();
      $brief_info = 'Usado Média';
    } else {
      $condominio_valor = $condominio_tarifa->tarifa_valor;
    }
    $billingitem->item_value = $condominio_valor;
    $is_yearly=false;
    $billingitem->ref_obj = BItem::make_ref_obj_with_date($this->monthyeardateref, $is_yearly);
    if ($brief_info != null) {
      $billingitem->ref_obj->brief_info = $brief_info;
    }
    return $billingitem;
  }


  public function set_obj_dates_based_on_today() {
    $this->today = Carbon::today();
    $this->monthyeardateref = DateFunctions::find_rent_monthyeardateref_under_convention($this->today, $this->pay_day_when_monthly);
    $this->monthly_duedate  = DateFunctions::calculate_monthly_duedate_under_convention($this->today, $this->pay_day_when_monthly);
  }

  public function gerar_cobranca_based_on_today() {
    $this->set_obj_dates_based_on_today();
    return $this->gerar_cobranca();
  }

  public function gerar_cobranca_based_on_especified_date($monthyeardateref) {
    $this->set_obj_dates_based_on_today();
    // superpose object's inner $monthyeardateref
    $this->monthyeardateref = $monthyeardateref;
    return $this->gerar_cobranca();
  }

  private function gerar_cobranca() {
    /*
    DO NOT run $this->set_obj_dates_based_on_today() in here!!!
    */

    $billingitems = new BillingItemsForJson;

    // The first item in 'cobrança' is the rent itself
    // [1] Add Aluguel
    $billingitem = $this->create_billingitem_aluguel();
    $billingitems->add($billingitem);
    // [2] Add IPTU if applicable
    if ($this->aplicar_iptu) {
      $iptutabela = IPTUTabela
        ::where('imovel_id', $this->imovel->id)
        ->where('ano', $this->monthyeardateref->year)
        ->first();
      if ($iptutabela == null) {
        return null;
      }
      if ($iptutabela->ano_quitado == true) {
        return null;
      }
      $billingitem = $this->create_billingitem_iptu($iptutabela);
      if ($billingitem != null) {
        $billingitems->add($billingitem);
      }
    }
    // [3] Add Condominio if applicable
    if ($this->aplicar_condominio) {
      $billingitem = $this->create_billingitem_condominio();
      if ($billingitem != null) {
        $billingitems->add($billingitem);
      }
    }

    $cobranca_total = $billingitems->get_total();
    // verify if this bill has already been create_billingitem_condominio
    /*
    $cobranca = Cobranca
      ::where('monthyeardateref', $this->monthyeardateref)
      ->where('duedate',          $this->monthly_duedate)
      ->where('total', $cobranca_total)
      ->get();

      if ($cobranca == null) {
        $cobranca = new Cobranca;
      }

    */
    $cobranca = new Cobranca;
    $cobranca->monthyeardateref = $this->monthyeardateref;
    $cobranca->duedate          = $this->monthly_duedate;
    // $cobranca->set_billingitemsinjson($billingitems->get_json());
    $cobranca->billingitemsinjson = $billingitems->get_json();
    $cobranca->total              = $cobranca_total;
    $cobranca->contract_id        = $this->id;
    $cobranca->bankaccount_id     = $this->bankaccount_id;
    // $cobranca->n_parcelas = 1;

    $cobranca->save();
    $this->cobranca_to_save = $cobranca;
  } // ends gerar_cobranca()

  public function db_save_cobranca_gerada() {
    if ($this->cobranca_to_save != null) {
      $this->cobranca_to_save->save();
    }
  }

  public function imovel() {
    return $this->belongsTo('App\Models\Immeubles\Imovel');
  }

  public function cobrancas() {
    return $this->hasMany('App\Models\Billing\Cobranca');
  }

  public function users() {
    return $this->belongsToMany('App\User');
  }

}
