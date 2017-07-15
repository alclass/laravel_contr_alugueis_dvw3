<?php
namespace App\Models\Immeubles;

use \App\Models\Billing\BillingItemForJson;
use \App\Models\Billing\BillingItemObjToAssocArray;
use \App\Models\Billing\Cobranca;
use \App\Models\Billing\CobrancaTipo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model {


  protected $table = 'contracts';

  protected $fillable = [
		'initial_rent_value', 'current_rent_value', 'indicador_reajuste',
    'pay_day_when_monthly', 'aplicar_iptu', 'aplicar_condominio',
    'percentual_multa', 'percentual_juros', 'aplicar_corr_monet',
    'signing_date', 'start_date', 'duration_in_months', 'n_days_adicional', 'is_active',
	];

  public function get_next_rent_value_reajust_date() {
    return DateFunctions::get_next_rent_value_reajust_date($this->start_date);
  }

  public function create_billingitem_aluguel() {
    $billingitem  = new BillingItemObjToAssocArray();
    $assoc_array = array();
    $cobrancatipo_id = CobrancaTipo::where('char_id', CobrancaTipo::K_TEXT_ID_ALUGUEL)->first();
    $billingitem->cobrancatipo_id = $cobrancatipo_id;
    // fetch value
    $billingitem->item_value = $this->item_value;
    $billingitem->make_ref_obj();
    return $billingitem;
  }

  public function create_billingitem_iptu() {
    $billingitem  = new BillingItemObjToAssocArray();
    $cobrancatipo_id = CobrancaTipo::where('char_id', CobrancaTipo::K_TEXT_ID_IPTU)->first();
    $billingitem->cobrancatipo_id = $cobrancatipo_id;
    // fetch value
    $billingitem->item_value = $this->item_value;
    $billingitem->make_ref_obj();
    return $billingitem;
  }

  public function create_billingitem_condominio() {
    $billingitem  = new BillingItemObjToAssocArray();
    $cobrancatipo_id = CobrancaTipo::where('char_id', CobrancaTipo::K_TEXT_ID_CONDOMINIO)->first();
    $billingitem->cobrancatipo_id = $cobrancatipo_id;
    // fetch value
    $billingitem->item_value = $this->item_value;
    $billingitem->make_ref_obj();
    return $billingitem;
  }

  public function gerar_cobranca() {

    $today = Carbon::today();
    $monthyeardateref = $today->addMonth(-1);

    // The first item in 'cobrança' is the rent itself
    $billingitems = new BillingItemForJson();

    // Add Aluguel
    $billingitem = $this->create_billingitem_aluguel();
    $billingitems->add($billingitem);
    // Add IPTU if applicable
    if ($this->aplicar_iptu) {
      $billingitem = $this->create_billingitem_iptu();
      $billingitems->add($billingitem);
    }
    // Add Condominio if applicable
    if ($this->aplicar_condominio) {
      $billingitem = $this->create_billingitem_condominio();
      $billingitems->add($billingitem);
    }

    $cobranca = new Cobranca();
    $cobranca->monthyeardateref = 1;
    $cobranca->duedate = 1;
    $cobranca->set_billingitemsinjson($billingitems->get_json());
    $cobranca->total = $billingitems->total;
    $cobranca->contract_id = $this->id;
    $cobranca->bankaccount_id = $this->bankaccount_id;
    // $cobranca->n_parcelas = 1;
    $cobranca->save();

    /*
    // $billingitem->cobrancatipo_id = CobrancaTipo::where('name'=>'aluguel')->get();
    $billingitem->base_value = $this->current_rent_value;
    $billingitem->dateref = $this->monthyeardateref;
    $billingitems->add($billingitem);
    foreach ($this->contractbillingrules() as $contractbillingrule) {
      $billingitem = new BillingItem;
      $billingitem->cobrancatipo_id = $contractbillingrule->cobrancatipo_id;
      $billingitem->monthyeardateref = $monthyeardateref;
      if ($billingitem->cobrancatipo_id == $this->get_cobrancatipo_id('name'=>'condominio')) {

      }
      $tarifa = AdditionTarifa::where('contract_id', $this->id)
        ->where('cobrancatipo_id', $billingitem->cobrancatipo_i)
        ->where('monthyeardateref', $billingitem->monthyeardateref)
        ->get();
      $billingitem->value = $tarifa->value;
      */
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
