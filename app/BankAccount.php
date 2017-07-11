<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model {

	//
  protected $table = 'bankaccounts';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */

	protected $fillable = [
		'banknumber', 'bankname', 'agency', 'account', 'customer', 'cpf',
  ];

  /*
  public function user() {
    return $this->belongsTo('App\User');
  }
  */

}
