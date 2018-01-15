<?php
namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model {

	//
  protected $table = 'payments';

  protected $fillable = [
		'amount', 'deposit_date', 'bankrefstring',
    // 'is_fully_fit_to_a_bill',
	];

  public function bankaccount() {
    return $this->belongsTo('App\Models\Finance\BankAccount');
  }

  public function user() {
    return $this->belongsTo('App\User');
  }

  public function imovel() {
    return $this->belongsTo('App\Models\Immeubles\Imovel');
  }

}
