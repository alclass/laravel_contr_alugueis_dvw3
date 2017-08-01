<?php
namespace App\Models\Persons;

//use App\User;
use Illuminate\Database\Eloquent\Model;

class Person extends Model {

  const K_RELATION4CHAR_BORROWER      = 'BORR';
  const K_RELATION4CHAR_ESTATE_RENTER = 'RENT';

  protected $table = 'persons';
  protected $dates = [
    'birthdate',
  ];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */

	protected $fillable = [
    'user_id_if_applicable',
		'first_name', 'middle_names', 'last_name',
    'cpf', 'birthdate', 'relation',
  ];

  /*
  'carteira_id', 'carteira_emissor', 'carteira_data_emissao',
  'photo_filepath',
  'address_line1', 'address_line2',
  'logradouro_cep', 'logradouro_n',
  */

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

  public function user() {
    return $this->hasOne('App\User', 'user_id_if_applicable');
  } // ends user()

} // ends class Person extends Model
