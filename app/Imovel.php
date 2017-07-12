<?php namespace App;

use App\Contract;
use Illuminate\Database\Eloquent\Model;

class Imovel extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'imoveis';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'apelido',
		'logradouro', 'tipo_lograd', 'numero', 'complemento', 'cep',
		'tipo_imov',
		'is_rentable', 'm2_no_iptu',
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */

	 public function get_street_address( ) {
		 $line = $this->tipo_lograd;
		 $line = $line . ' ' . $this->logradouro;
		 $line = $line . ', ' . $this->numero;
		 if (!empty($this->complemento)) {
			 $line = $line . ' ' . $this->complemento;
		 }
		 return $line;
	 }

	public function full_address_lines_array( ) {
		$lines = array();
		$line = $this->get_street_address();
		$lines[0] = array($line);
		$line = "";
		$line = $this->cep;
		/*
		$cep_obj = CepObj::fetchCityStateCountry($this->cep);
		$line = $line . ' :: ' . $cep_obj->city . $cep_obj->state . $cep_obj->country;
		*/
		$lines[1] = array($line);
		return $lines;
  } // ends function full_address_lines_array( )

	public function get_current_rent_contract_if_any() {
		$contract = Contract::where('is_active', 1)->first();
		if (!empty($contract)) {
			return $contract;
		}
		return null;
	}

	public function contracts() {
		return $this->hasMany('App\Contract');
  }

} // ends class Imovel extends Model
