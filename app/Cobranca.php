<?php namespace App;

use App\BankAccount;
use App\Contract;
use App\Imovel;
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
		'mainmonthref', 'duedate',
    'n_parcelas', 'are_parcels_monthly', 'parcel_n_days_interval',
    'has_been_paid',
	];

  public function get_valor_total() {
    $total = 0;
    foreach ($this->billingitems as $billingitem) {
      $total += $billingitem->value;
    }
    return $total;
  }
  public function user() {
    return $this->belongsTo('App\User');
  }
  public function contract() {
    return $this->belongsTo('App\Contract');
  }
  public function bankaccount() {
    return $this->belongsTo('App\BankAccount');
  }
  public function billingitems() {
    return $this->hasMany('App\BillingItem');
  }
}
