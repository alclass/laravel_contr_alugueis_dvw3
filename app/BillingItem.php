<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class BillingItem extends Model {

	//
  protected $table = 'billingitems';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'monthref', 'other_ref_if_any', 'value',
  ];

  public function cobranca() {
    return $this->belongsTo('App\Cobranca');
  }

  public function billing_item_type() {
    return $this->hasOne('App\CobrancaTipo');
  }

}
