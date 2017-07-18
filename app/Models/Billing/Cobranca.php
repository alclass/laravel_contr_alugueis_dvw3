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

  // contract_id is in DB Schema, but connected below with a belongsTo() method

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

  public function get_users() {
    if ($this->contract != null) {
      // the returning users()->get() is a Collection
      return $this->contract->users()->get();
    }
    return null;
  }

  public function createOrFindNextMonthCobranca($n_seq_from_dateref=1) {
    $next_monthyeardateref = $this->monthyeardateref->copy()->addMonths(1);
    if ($this->contract == null) {
      if ($this->contract_id == null) {
        throw new Exception("contract is null when calling createOrFindNextMonthCobranca())", 1);
      }
      // Try to fetch contract from contract_id
      $this->contract = Contract::find($this->contract_id);
      if ($this->contract == null) {
        throw new Exception("contract_id was not db-found when calling createOrFindNextMonthCobranca())", 1);
      }
    }
    return CobrancaGerador
      ::createOrRetrieveCobrancaWithTripleContractRefSeq(
        $this->contract,
        $next_monthyeardateref,
        $n_seq_from_dateref
      );
  }

  public function createOrRetrieveAnyBilligItemsFor($cobrancatipo, $monthyeardateref_of_item, $value) {
    $monthyeardateref_of_item->setTime(0,0,0);
    $billingitems = $this->billingitems
      ->where('cobrancatipo_id',  $cobrancatipo->id)
      ->where('monthyeardateref', $monthyeardateref_of_item)
      ->where('charged_value',    $value)
      ->get();
    if ($billingitems->count()>0) {
      return $billingitems;
    }
    // create one new
    $billingitem = new BillingItem;
    $billingitem->cobrancatipo_id  = $this->cobrancatipo->id;
    $billingitem->monthyeardateref = $monthyeardateref_of_item;
    $billingitem->charged_value    = $value;
    $billingitem->type_ref         = BillingItem::K_REF_TYPE_IS_DATE;
    $billingitem->freq_used_ref    = BillingItem::K_FREQ_USED_IS_MONTHLY;
    $this->$billingitems()->save(billingitem);
    // Re-query it Collection
    $billingitems = $this->billingitems
      ->where('cobrancatipo_id',  $cobrancatipo->id)
      ->where('monthyeardateref', $monthyeardateref_of_item)
      ->where('charged_value',    $value)
      ->get();
    return $billingitems;
  }

  public function createOrRetrieveAnyBillingItemsForCredito($monthyeardateref_of_item, $value) {
    $cobrancatipo = CobrancaTipo
      ::where('char_id', CobrancaTipo::K_4CHAR_CRED)
      ->first();
    if ($cobrancatipo == null) {
      throw new Exception("CobrancaTipo not found in db with corresponding K_CHAR_CRED (= 'CRED')", 1);
    }
    return $this->createOrRetrieveAnyBillingItemsFor($cobrancatipo, $monthyeardateref_of_item, $value);
  }

  public function createOrRetrieveAnyBillingItemsForMora($monthyeardateref_of_item, $value) {
    $cobrancatipo = CobrancaTipo
      ::where('char_id', CobrancaTipo::K_4CHAR_MORA)
      ->first();
    if ($cobrancatipo == null) {
      throw new Exception("CobrancaTipo not found in db with corresponding K_CHAR_MORA (= 'MORA')", 1);
    }
    return $this->createOrRetrieveAnyBillingItemsFor($cobrancatipo, $monthyeardateref_of_item, $value);
  }

  public function createOrRetrieveAnyBillingItemsForMoraOrCredito($monthyeardateref_of_item, $value) {
    if ($value == 0) {
      return null;
    } elseif ($value < 0) {
      $value *= -1;
      return $this->createOrRetrieveAnyBillingItemsForMora($monthyeardateref_of_item, $value);
    }
    return $this->createOrRetrieveAnyBillingItemsForCredito($monthyeardateref_of_item, $value);
  }

  public function __toString() {
    $outstr = "Cobrança\n ";
    $apelido = '';
    if ($this->contract != null) {
      if ($this->contract->imovel != null) {
        $apelido = $this->contract->imovel->apelido;
      }
    }
    $outstr .= 'Contract Imóvel ' . $apelido . '\n';
    // $outstr .= $this->billingitemsinjson;
    return $outstr;
  } // public function __toString()

} // ends class Cobranca extends Model
