<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model {

	//
  protected $table = 'payments';

  protected $fillable = [
		'amount', 'bankname', 'deposited_on',
	];

  public function user() {
    return $this->belongsTo('App\User');
  }

  public function imovel() {
    return $this->belongsTo('App\Imovel');
  }

}
