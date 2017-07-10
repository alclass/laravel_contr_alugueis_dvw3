<?php namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'first_name', 'middle_names', 'last_name', 'cpf',
		'tipo_relacao',
		'email', 'password'
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token'];

	public function full_name( ) {
		$name = "";
		$name = $this->first_name;
		if ( strlen($this->middle_names) > 0 ) {
			$name = $name . ' ' . $this->middle_names;
		}
		$name = $name . ' ' . $this->last_name;
		return $name;
  }
	public function name_first_last()	{
		return $name = $this->first_name . ' ' . $this->last_name;
	}

	public function imoveis( ) {
		return $this->belongsToMany('App\Imovel');
  }



}
