<?php namespace App;

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
    'n_parcelas', 'are_parcels_month', 'parcel_n_days_interval',
    'has_been_paid',
	];

  public function get_valor() {
    $total = 0;
    foreach ($this->billingitems as $billingitem) {
      $total = $total + $billingitem->value;
    }
    return $total;
  }

  public function user() {
    return $this->belongsTo('App\User');
  }
  public function imovel() {
    return $this->belongsTo('App\Imovel');
  }
  public function contract() {
    return $this->belongsTo('App\Contract');
  }
  public function bankaccount() {
    return $this->hasOne('App\BankAccount');
  }
  public function billingitems() {
    return $this->hasMany('App\BillingItems');
  }
}
