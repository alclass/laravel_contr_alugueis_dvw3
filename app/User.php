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

	public function get_first_n_last_names()	{
		if (strlen($this->last_name) == 0) {
			return $this->first_name;
		}
		return $this->first_name . ' ' . $this->last_name;
	} // ends get_first_n_last_names()

	public function get_full_name( ) {
		if (strlen($this->middle_names) == 0) {
			return $this->get_first_n_last_names();
		}
		return $this->first_name . ' ' . $this->middle_names . ' ' . $this->last_name;
	} // ends get_full_name()

	public function contracts( ) {
		return $this->belongsToMany('App\Models\Immeubles\Contract');
  }  // ends contracts()

} // ends class User extends Model implements AuthenticatableContract, CanResetPasswordContract
