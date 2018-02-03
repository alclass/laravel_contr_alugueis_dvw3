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

	public function get_iptu_outarray_via_dbdirectapproch() {
		$iptu_outarray = [];
		$iptu_record = IPTUTable
			::where('imovel_id', $this->id)
			->where('ano', $monthrefdate->year)
			->first();
		$totalparts = $iptu_record->total_de_parcelas;			
		$n_months_without_iptu = 12 - $totalparts;
		$iptu_outarray['ano_quitado'] = $iptu_record->ano_quitado;
		$iptu_outarray['optado_por_cota_unica'] = $iptu_record->optado_por_cota_unica;
		$iptu_outarray['partnumber'] = 1;
		if ($iptu_record->optado_por_cota_unica) {
			$iptu_outarray['totalparts'] = 1;
		}
		else {
			$iptu_outarray['totalparts'] = $totalparts;
		}
		if ($monthrefdate->month < $n_months_without_iptu + 1) {
			// time window without IPTU
			$iptu_outarray['valor_repasse'] = 0;
			return $iptu_outarray;
		}
		if ($iptu_record->ano_quitado) { 
			$iptu_outarray['valor_repasse'] = 0;
			return $iptu_outarray;
		}
		if ($iptu_record->optado_por_cota_unica) { 
			if ($monthrefdate->month != 3) {
				$iptu_outarray['valor_repasse'] = 0;
				return $iptu_outarray;
			}
			$iptu_outarray['valor_repasse'] = $iptu_record->valor_parcela_unica;
			return $iptu_outarray;
		}
		$partnumber = $monthrefdate->month - 2;
		$iptu_outarray['partnumber'] = $partnumber;
		if ($partnumber > $totalparts) {
				$iptu_outarray['valor_repasse'] = 0;
		}
		$iptu_outarray['valor_repasse'] = $iptu_record->valor_por_parcela;
		return $iptu_outarray;
	} // ends get_iptu_outarray_via_dbdirectapproch()

	public function get_iptu_value_in_refmonth($monthrefdate) {
		$iptu_record = $this->iptus->where('ano', $monthrefdate->year)->first();
		if ($iptu_record == null) {
			return $this->get_iptu_value_via_dbdirectapproch();
		}
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

	public function iptus() {
		return $this->hasMany('App\Models\Tributos\IPTUTabela');
  }


} // ends class Imovel extends Model
