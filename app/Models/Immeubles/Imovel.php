<?php
namespace App\Models\Immeubles;

// use App\Models\Immeubles\Contract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

// 2 Collection classes
// use Illuminate\Database\Eloquent\Collection;
// [more generic] use Illuminate\Support\Collection;

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
		'apelido', 'predio_nome',
		'logradouro', 'tipo_lograd', 'numero', 'complemento', 'cep',
		'tipo_imov',
		'is_rentable', 'area_edif_iptu_m2	', 'area_terr_iptu_m2',
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */

	 public function get_address_without_complement() {
		 $line = $this->tipo_lograd;
		 $line = $line . ' ' . $this->logradouro;
		 $line = $line . ', ' . $this->numero;
		 return $line;
	 }

	 public function get_street_address( ) {
		 if (!empty($this->complemento)) {
			 $line = $this->get_address_without_complement() . ' ' . $this->complemento;
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
		$contract = $this->contracts->where('is_active', 1)->first();
		if ($contract != null) {
			return $contract;
		}
		return null;
	}

	public function get_condominio_in_refmonth($monthrefdate) {
		return 350.00;
	}

	public function get_iptu_value_in_refmonth($monthrefdate) {
		return 3000.00;
	}

	public function get_iptu_numberpart_in_refmonth($monthrefdate) {
		return $monthrefdate->month - 2;
	}
	public function get_iptu_totalparts_in_refmonth($monthrefdate) {
		return 10;
	}

/*

	public function get_inquilinos_atuais() {
		/*
				TO REVISE!

				Unfortunately, we haven't found a simple mechanism
					to add/merge/combine a Collection.
				Because of that we decided to loop thru all elements and
				  push each to the collect-type.

				The two collection types are:
				+ Illuminate\Database\Eloquent\Collection => new Collection
				+ Illuminate\Support\Collection => collect()

		*/
		/*
		$inquilinos_atuais = new Collection;
		// The following where() is against a Collection, not against a QueryBuilder
		$active_contracts = $this->contracts->where('is_active', true);
		foreach ($active_contracts as $contract) {
			foreach ($contract->users as $user) {
				$inquilinos_atuais->add($user); // if collect-type (collect(), use push() instead of add())
			} // ends inner foreach
		} // ends outer foreach
		// return is typed Collection
		return $inquilinos_atuais;
  } // ends get_inquilinos_atuais()
*/
	public function contracts() {
		return $this->hasMany('App\Models\Immeubles\Contract');
  }

} // ends class Imovel extends Model
