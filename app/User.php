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
		'username', 'first_name', 'middle_names', 'last_name',
		'cpf', 'rg',
		'tipo_relacao',
		'email', 'password',
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token'];

	public function get_full_name( ) {
		$name = "";
		$name = $this->first_name;
		if ( strlen($this->middle_names) > 0 ) {
			$name = $name . ' ' . $this->middle_names;
		}
		$name = $name . ' ' . $this->last_name;
		return $name;
	} // ends function get_full_name()

	public function get_first_n_last_names()	{
		return $name = $this->first_name . ' ' . $this->last_name;
	} // ends function get_first_n_last_names()

	public function contracts( ) {
		return $this->belongsToMany('App\Contract');
  }  // ends function contracts()

} // ends class User
