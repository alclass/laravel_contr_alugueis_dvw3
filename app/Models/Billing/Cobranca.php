<?php namespace App\Models\Billing;

use App\Models\Finance\BankAccount;
// use App\Models\Billing\BillingItemForJson;
use App\Models\Immeubles\Contract;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Cobranca extends Model {

	//
  protected $table = 'cobrancas';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'monthyeardateref', 'duedate', 'billingitemsinjson', 'total',
    'n_parcelas', 'are_parcels_monthly', 'parcel_n_days_interval',
    'has_been_paid',
	];

  public function set_billingitemsinjson($billingitemsinjson) {
    $this->billingitemsinjson = billingitemsinjson;
    $this->set_total_after_setting_billingitems();
  }

  private function set_total_after_setting_billingitems() {
    $this->total = $this->billingitemsjson->get_total();
  }

  public function bankaccount() {
    return $this->belongsTo('App\Models\Finance\BankAccount');
  }
  public function contract() {
    return $this->belongsTo('App\Models\Immeubles\Contract');
  }
  public function user() {
    return $this->belongsTo('App\User');
  }
}
