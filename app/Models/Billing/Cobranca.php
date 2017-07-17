<?php
namespace App\Models\Billing;

use App\Models\Finance\BankAccount;
// use App\Models\Billing\BillingItemForJson;
use App\Models\Billing\BillingItem;
use App\Models\Immeubles\Contract;
use App\Models\Tributos\IPTUTabela;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Cobranca extends Model {

	//
  protected $table     = 'cobrancas';
  public $billingitems = null;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */

 protected $dates = [
   'monthyeardateref',
   'duedate',
   //'created_at',
   //'updated_at',
 ];

	protected $fillable = [
		'monthyeardateref', 'duedate', 'n_seq_from_dateref','total', 'n_items',
    'n_parcelas', 'are_parcels_monthly', 'parcel_n_days_interval',
    'has_been_paid',
	];

/*
  public function set_billingitemsinjson($billingitemsinjson) {
    $this->billingitemsinjson = billingitemsinjson;
    $this->set_total_after_setting_billingitems();
  }

  public function convert_from_json_n_set_billingitems() {
    $this->billingitems = new BillingItemsForJson;
    $this->billingitems->fill_in_billingitems_from_json($this->billingitemsinjson);
  }

*/
  public function is_iptu_ano_quitado() {
    if ($this->contract->imovel == null) {
      return false;
    }
    $iptutabela = IPTUTabela
      ::where('imovel_id'  , $this->contract->imovel->id)
      ->where('ano'        , $this->monthyeardateref->year)
      ->first();
    if ($iptutabela != null && $iptutabela->ano_quitado == true) {
      return true;
    }
    return false;
  } // ends is_iptu_ano_quitado()

  public function billingitems() {
    return $this->hasMany('App\Models\Billing\BillingItem');
  }
  public function bankaccount() {
    return $this->belongsTo('App\Models\Finance\BankAccount');
  }
  public function contract() {
    return $this->belongsTo('App\Models\Immeubles\Contract');
  }
  /*
  public function user() {
    return $this->belongsTo('App\User');
  }
  */

  public function createOrFindNextMonthCobranca($n_seq_from_dateref=1) {
    $next_monthyeardateref = $this->monthyeardateref->addMonths(1);
    $cobranca = Cobranca
      ::where('contract_id',        $this->contract->id)
      ->where('monthyeardateref',   $next_monthyeardateref)
      ->where('n_seq_from_dateref', $n_seq_from_dateref)
      ->first();
    if ($cobranca != null) {
      return $cobranca;
    }
    $cobranca = new Cobranca;
    $cobranca->contract_id        = $this->contract->id;
    $cobranca->monthyeardateref   = $next_monthyeardateref;
    $cobranca->duedate            = $next_monthyeardateref->copy()->addMonths(1);
    $cobranca->duedate->day       = $this->contract->pay_day_when_monthly;
    $cobranca->n_seq_from_dateref = $n_seq_from_dateref;
    $cobranca->save();
    return $cobranca;
  }

  public function createOneOrRetrieveAnyBilligItemsFor($cobrancatipo_id, $monthyeardateref_of_item, $value) {
    $monthyeardateref_of_item->setTime(0,0,0);
    $billingitems = $this->billingitems
      ->where('cobrancatipo_id',  $cobrancatipo_id)
      ->where('monthyeardateref', $monthyeardateref_of_item)
      ->where('charged_value',    $value)
      ->get();
    if ($billingitems->count()>0) {
      return $billingitems;
    }
    // create one new
    $billingitem = new BillingItem;
    $billingitem->cobranca_id      = $this->cobranca_id;
    $billingitem->monthyeardateref = $monthyeardateref_of_item;
    $billingitem->charged_value    = $value;
    $billingitem->type_ref         = BillingItem::K_REF_TYPE_IS_DATE;
    $billingitem->freq_used_ref    = BillingItem::K_FREQ_USED_IS_MONTHLY;
    $this->$billingitems->add(billingitem);
    return $billingitem;
  }

  public function createOneOrRetrieveAnyBilligItemsForCredito($monthyeardateref_of_item, $value) {
    $cobrancatipo_id = CobrancaTipo
      ::where('char_id', CobrancaTipo::K_CHARID_FOR_CRED)
      ->first();
    return createOneOrRetrieveAnyBilligItemsFor($cobrancatipo_id, $monthyeardateref_of_item, $value);
  }

  public function createOneOrRetrieveAnyBilligItemsForMora($monthyeardateref_of_item, $value) {
    $cobrancatipo_id = CobrancaTipo
      ::where('char_id', CobrancaTipo::K_4CHAR_FOR_MORA)
      ->first();
    return createOneOrRetrieveAnyBilligItemsFor($cobrancatipo_id, $monthyeardateref_of_item, $value);
  }

  public function createOrFindBilligItemsForDebitoOrCredito($monthyeardateref_of_item, $value) {
    if ($value == 0) {
      return null;
    } elif ($value < 0) {
      $value *= -1;
      return $this->createOneOrRetrieveAnyBilligItemsForMora($monthyeardateref_of_item, $value);
    }
    return $this->createOneOrRetrieveAnyBilligItemsForCredito($monthyeardateref_of_item, $value);
  }

  public function __toString() {
    $outstr = 'Cobrança\n ';
    $apelido = '';
    if ($this->contract != null) {
      if ($this->contract->imovel != null) {
        $apelido = $this->contract->imovel->apelido;
      }
    }
    $outstr .= 'Contract Imóvel ' . $apelido . '\n';
    // $outstr .= $this->billingitemsinjson;
    return $outstr;
  }
} // ends class Cobranca extends Model
