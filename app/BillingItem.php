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
		'monthyeardateref', 'other_ref_if_any', 'value',
  ];

  public function repasse_ou_branco() {
    $item_type = $this->billing_item_type;
    if (!empty($item_type) && $item_type->is_repasse == true) {
      return 'repasse';
    }
    return '';
  }

  public function cobranca() {
    return $this->belongsTo('App\Cobranca');
  }

  public function cobrancatipo() {
    return $this->hasOne('App\CobrancaTipo');
  }

}
