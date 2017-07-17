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
		'monthyeardateref', 'duedate', 'total', 'n_items',
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

}
