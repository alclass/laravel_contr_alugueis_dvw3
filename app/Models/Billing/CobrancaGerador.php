<?php
namespace App\Models\Billing;

// use App\Models\Billing\BillingItemsForJson;
// use App\Models\Billing\BillingItemObjToAssocArray as BItem;
use App\Models\Billing\BillingItem;
use App\Models\Billing\Cobranca;
use App\Models\Billing\CobrancaTipo;
// use App\Models\Billing\RefForBillingItem as Ref;
use App\Models\Finance\BankAccount;
use App\Models\Immeubles\Contract;
use App\Models\Immeubles\CondominioTarifa;
use App\Models\Tributos\IPTUTabela;
use App\Models\Utils\DateFunctions;
use App\User;
use Carbon\Carbon;

class CobrancaGerador {

  private $cobranca         = null;
  private $contract         = null;
  private $monthyeardateref = null;
  private $cobranca_gerada  = false;
  private $cobranca_dbsaved = true;

  public function __construct(
                      $contract,
                      $monthyeardateref = null,
                      $create_new_if_exists=false ) {

    if ($contract == null) {
      $error = 'Contract object is null when instanting a CobrancaGerador object.';
      throw new Exception($error);
    }
    $this->contract = $contract;
    // next method will also treat null $monthyeardateref
    $this->set_cobranca_duedate_based_on_monthyeardateref();

    // instantiate an emtpy Cobranca, if $create_new_if_exists=false, try to fetch an existing one
    $this->cobranca = new Cobranca;
    if ($create_new_if_exists=false) {
      $this->db_fetch_cobranca_to_obj_if_exists();
    }
    $this->cobranca_gerada  = false;
    $this->cobranca_dbsaved = false;

  } // ends __construct()

  public function get_cobranca() {
    return $this->cobranca;
  }

  private function db_fetch_cobranca_to_obj_if_exists() {
    $cobranca = Cobranca::where('contract_id', $contract->id)
      ->where('monthyeardateref', $monthyeardateref)
      ->first();
    if ($cobranca != null) {
      $this->cobranca = $cobranca;
    }
  }

  public function set_monthyeardateref_relative_to_today() {
    $this->monthyeardateref = DateFunctions
      ::find_rent_monthyeardateref_under_convention(
        Carbon::today(),
        $this->contract->pay_day_when_monthly
      );
  }

  public function set_cobranca_duedate_based_on_monthyeardateref() {
    if ($this->monthyeardateref==null) {
      $this->set_monthyeardateref_relative_to_today();
    }
    $this->cobranca_duedate      = $this->monthyeardateref->copy()->addMonths(1);
    $this->cobranca_duedate->day = $this->contract->pay_day_when_monthly;
  }

  private function create_n_add_billingitem_aluguel() {
    // check existing before instantiating a new object
    $billingitem  = new BillingItem;
    $cobrancatipo = CobrancaTipo
      ::where('char_id', CobrancaTipo::K_TEXT_ID_ALUGUEL)
      ->first();
    if ($cobrancatipo == null) {
      return false;
    }
    $billingitem->cobranca_id       = $this->cobranca->id;
    $billingitem->cobrancatipo_id   = $cobrancatipo->id;
    $billingitem->brief_description = $cobrancatipo->brief_description;
    $billingitem->charged_value     = $this->contract->current_rent_value;
    $billingitem->ref_type          = BillingItem::K_REF_TYPE_IS_DATE;
    $billingitem->freq_used_ref     = BillingItem::K_FREQ_USED_IS_MONTHLY;
    $billingitem->monthyeardateref  = $this->monthyeardateref;
    // $billingitem->cobranca->attach($this->cobranca);
    $billingitem->save();
    // $this->cobranca->billingitems->add($billingitem);
    return true;
  }

  private function create_n_add_billingitem_iptu() {

    if ($this->contract->repassar_iptu==false) {
      return false;
    }
      // imovel is protected against null in Constructor (ie, $this->contract->imovel is not null at this point)
    $iptutabela = IPTUTabela
      ::where('imovel_id', $this->contract->imovel->id)
      ->where('ano', $this->monthyeardateref->year)
      ->first();
    if ($iptutabela == null) {
      return null;
    }
    // 1st case: entire IPTU has been fully paid
    if ($iptutabela->ano_quitado == true) {
      return null;
    }

    $billingitem  = new BillingItem;
    $cobrancatipo = CobrancaTipo
      ::where('char_id', CobrancaTipo::K_TEXT_ID_IPTU)
      ->first();
    if ($cobrancatipo == null) {
      return false;
    }
    $billingitem->cobranca_id       = $this->cobranca->id;
    $billingitem->cobrancatipo_id = $cobrancatipo->id;;
    $billingitem->brief_description = $cobrancatipo->brief_description;
    // 2nd case: cota-única anual a ser repassada em Fevereiro, ref. Janeiro
    if ($iptutabela->optado_por_cota_unica == true && $this->monthyeardateref->month == 1) {
      $billingitem->charged_value = $iptutabela->valor_parcela_unica;
      $billingitem->ref_type          = BillingItem::K_REF_TYPE_IS_BOTH_DATE_N_PARCEL;
      $billingitem->freq_used_ref     = BillingItem::K_FREQ_USED_IS_YEARLY;
      $billingitem->n_cota_ref        = 1;
      $billingitem->total_cotas_ref   = 1;
      $billingitem->monthyeardateref  = $this->monthyeardateref;
      $billingitem->save();
      return true;
    }
    // 3rd case: cota-única anual a ser repassada em Fevereiro, ref. Janeiro
    if ($this->monthyeardateref->month == 1 || $this->monthyeardateref->month == 12) {
      // this case is optado por 10x and the first one starts in March ref. February
      // Billing happens from March to December, ref. Feb to Nov
      return null;
    }
    $billingitem->charged_value     = $iptutabela->valor_parcela_10x;
    $billingitem->ref_type          = BillingItem::K_REF_TYPE_IS_BOTH_DATE_N_PARCEL;
    $billingitem->freq_used_ref     = BillingItem::K_FREQ_USED_IS_MONTHLY;
    $billingitem->n_cota_ref        = $this->monthyeardateref->month - 1;
    $billingitem->total_cotas_ref   = 10;  // remove the hardcoded value replacing it with the rules table datum
    $billingitem->monthyeardateref  = $this->monthyeardateref;
    $billingitem->save();
    return true;
  }

  private function create_n_add_billingitem_condominio() {

    if ($this->contract->repassar_condominio == false) {
      return false;
    }
    if ($this->contract->imovel == null) {
      $error = '[In CobrancaGerador::create_billingitem_condominio()] Contract object does not have an imovel object attached to it.';
      throw new Exception($error);
    }
    $billingitem  = new BillingItem;
    $cobrancatipo = CobrancaTipo
      ::where('char_id', CobrancaTipo::K_TEXT_ID_CONDOMINIO)
      ->first();
    if ($cobrancatipo == null) {
      return false;
    }
    $billingitem->cobranca_id       = $this->cobranca->id;
    $billingitem->cobrancatipo_id   = $cobrancatipo->id;
    $billingitem->brief_description = $cobrancatipo->brief_description;
    // fetch value
    $condominio_tarifa = CondominioTarifa
      ::where('imovel_id', $this->contract->imovel->id)
      ->where('monthyeardateref', $this->monthyeardateref)
      ->first();
    $brief_info = null;
    if ($condominio_tarifa == null) {
      $condominio_valor = CondominioTarifa::calcular_media_das_ultimas_tarifas();
      $brief_info = 'Usada a média das últimas tarifas';
    } else {
      $condominio_valor = $condominio_tarifa->tarifa_valor;
    }
    $billingitem->charged_value = $condominio_valor;
    $billingitem->ref_type          = BillingItem::K_REF_TYPE_IS_DATE;
    $billingitem->freq_used_ref     = BillingItem::K_FREQ_USED_IS_MONTHLY;
    $billingitem->monthyeardateref  = $this->monthyeardateref;
    if ($brief_info != null) {
      $billingitem->obs = $brief_info;
    }
    $billingitem->save();
    return true;
  }

  public function gerar_cobranca_based_on_today($regerar=false) {
    if ($this->cobranca_gerada == true && $regerar==false) {
      return;
    };
    $this->set_monthyeardateref_relative_to_today();
    $this->set_cobranca_duedate_based_on_monthyeardateref();
    return $this->gerar();
  }

  public function gerar_cobranca_based_on_especified_date($monthyeardateref, $regerar=false) {
    if ($this->cobranca_gerada == true && $regerar==false) {
      return;
    };
    $this->set_cobranca_duedate_based_on_monthyeardateref();
    return $this->gerar();
  }

  private function gerar() {
    /*
    DO NOT run $this->set_obj_dates_based_on_today() in here!!!
    */
    // check cobranca existence
    // consider that two cobranca's with the same (contract_id, monthyeardateref & duedate) are the same
    $this->cobranca = Cobranca
      ::where('contract_id', $this->contract->id)
      ->where('monthyeardateref', $this->monthyeardateref)
      ->where('duedate', $this->cobranca_duedate)
      ->first();
    if ($this->cobranca != null) {
      print ("Cobranca não gerada pois existe uma com os mesmos (contract_id, monthyeardateref & duedate)");
      return false;
    }
    $this->cobranca = new Cobranca;
    $this->cobranca->contract_id          = $this->contract->id;
    $this->cobranca->bankaccount_id = $this->contract->bankaccount_id;
    $this->cobranca->monthyeardateref     = $this->monthyeardateref;
    $this->cobranca->duedate              = $this->cobranca_duedate;
    $this->cobranca->total          = 0;
    $this->cobranca->save();

    // [1] Add Aluguel
    $result = $this->create_n_add_billingitem_aluguel();
    print ('create_n_add_billingitem_aluguel()s result is ' . $result);
    // [2] Add IPTU if applicable
    $result = $this->create_n_add_billingitem_iptu();
    print ('create_n_add_billingitem_iptu()s result is ' . $result);
    // [3] Add Condominio if applicable
    $result = $this->create_n_add_billingitem_condominio();
    print ('create_n_add_billingitem_iptu()s result is ' . $result);

    $this->cobranca->load('billingitems');
    $this->cobranca->total = 0;
    $this->cobranca->n_items = 0;
    foreach ($this->cobranca->billingitems()->get() as $billingitem) {
      $this->cobranca->total += $billingitem->charged_value;
      $this->cobranca->n_items += 1;
    }
    $this->cobranca->save();

    $this->cobranca_gerada = true;
  } // ends gerar_cobranca()

  public function save_cobranca() {
    if ($this->cobranca_dbsaved == true) {
      return null;
    }
    if ($this->cobranca_gerada == true) {
      $this->cobranca->save();
      $this->cobranca_dbsaved = true;
      return true;
    }
    return false;
  }

  public function __toString() {
    $outstr = 'Gerador\n ';
    $outstr .= $this->cobranca->__toString();
    return $outstr;
  }


}
