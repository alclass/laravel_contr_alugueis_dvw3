<?php
namespace App\Models\Immeubles;

// use App\Models\Immeubles\Contract;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Tributos\IPTUTabela;

// 2 Collection classes
// use Illuminate\Database\Eloquent\Collection;
// [more generic] use Illuminate\Support\Collection;

class Imovel extends Model {


	public static function fetch_by_apelido($apelido) {
		/*
		if ($p_apelido == null) {
			return null;
		}
		// apelido has mixed case letters, but db will search it case-insensitively
		// $apelido = strtolower($p_apelido);
		*/
		return self
			::where('apelido', $apelido)
			->first();
	}

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

	public function get_active_contract() {
		/*
			A synomym for get_current_rent_contract_if_any()
		*/
		return $this->get_current_rent_contract_if_any();

	} // ends function get_active_contract()

	private function get_private_default_condominiotarifa($monthrefdate) {
		/*
			This method is private because it cannot receive a null $monthrefdate
				ie, callers already adjust $monthrefdate for it being received here

			This method tries TWO options to get a default.
			1) The 1st option (try) is to retrieve an average from db.
				This option will fail if either db is empty or offline.
			2) The 2nd option (try) is to retrieve a pre-set cost group value
				based on the estate's cost group.
				For the time being, these group costs have been hardcoded in
				  class CondominioTarifa.
					However, they may be put in either a db-table or an env file.
		*/

		// default 1st try: pick up the cond's average value in db
		$monthrefdate_12monthsago = $monthrefdate->copy()->addMonths(-12);
		$avg_tarifa = CondominioTarifa
			::where('imovel_id', $this->id)
			->where('monthrefdate', '>', $monthrefdate_12monthsago)
			->avg('tarifa_valor');
		if ($avg_tarifa != null && $avg_tarifa > 0) {
			return $avg_tarifa;
		}

		// default 2nd try: call the default method (which uses the group cost attribute)
		$tarifa_valor = CondominioTarifa::get_default_condominiotarifa_for_group_n($this->condominio_grupo_custo);
		return $tarifa_valor;
	} // ends private get_private_default_condominiotarifa()

	public function get_default_condominiotarifa($p_monthrefdate=null) {
		/*
			This method checks $p_monthrefdate and wraps a non-null $monthrefdate
				from it to the private method get_private_default_condominiotarifa()

			This mentioned downstream private method does TWO tries to get a default.
			To get acquainted to/of these TWO tries, see docstring there.
		*/
		if ($p_monthrefdate == null) {
			$monthrefdate = Carbon::today()->day(1);
		}
		else {
			$monthrefdate = $p_monthrefdate;
		}
		return $this->get_private_default_condominiotarifa($monthrefdate);
	} // ends get_default_condominiotarifa()

	public function get_condominiotarifa_in_refmonth($p_monthrefdate) {
		if ($p_monthrefdate == null) {
			$monthrefdate = Carbon::today()->day(1);
		}
		else {
			$monthrefdate = $p_monthrefdate;
		}
		if ($p_monthrefdate->day != 1) {
			$monthrefdate = $p_monthrefdate->copy()->day(1);
		}

		// 1st try: fetch the value inserted for monthref
		$cond_tarifa = CondominioTarifa
			::where('imovel_id', $this->id)
			->where('monthrefdate', $monthrefdate)
			->first();
		if ($cond_tarifa != null) {
			return $cond_tarifa->tarifa_valor;
		}

		// 2nd try: the value inserted for previous_monthref
		$previous_monthref = $monthrefdate->copy()->addMonths(-1);
		$cond_tarifa = CondominioTarifa
			::where('imovel_id', $this->id)
			->where('monthrefdate', $previous_monthref)
			->first();
		if ($cond_tarifa != null) {
			return $cond_tarifa->tarifa_valor;
		}

		// both 1st and 2nd tries resulted null, try the default method
		// the default method also has 2 tries (1st: it'll try an avg; 2nd: by cost group)
		return $this->get_private_default_condominiotarifa($monthrefdate);
	}

	public function is_condominio_billable() {
		return !$this->renter_pays_cond;
	}

	private function make_default_iptuanoimovel_instance($monthrefdate) {
		return IPTUTabela::make_default_instance_with_imovel_n_ano($this, $monthrefdate->year);
	} // ends make_default_iptuanoimovel_instance()

	public function get_iptuanoimovel_with_refmonth_or_null($monthrefdate) {
		return IPTUTabela
			::where('imovel_id', $this->id)
			->where('ano', $monthrefdate->year)
			->first();
	} // ends get_iptuanoimovel_with_refmonth_or_return_null()

	private function get_iptuanoimovel_of_previous_year_of_refmonth($monthrefdate) {
		$previous_year_monthrefdate = $monthrefdate->copy()->addYear(-1);
		$iptu_ano_imovel = $this->get_iptuanoimovel_with_refmonth_or_null($previous_year_monthrefdate);
		if ($iptu_ano_imovel == null) {
			return null;
		}
		// make a new one without saving it to db
		$res_iptu_ano_imovel = $iptu_ano_imovel->copytoanewyearinstance($monthrefdate->year);
		return $res_iptu_ano_imovel;
	} // ends get_iptuanoimovel_of_previous_year_of_refmonth()

	public function get_iptuanoimovel_with_refmonth_or_default($monthrefdate) {
		$iptu_ano_imovel = $this->get_iptuanoimovel_with_refmonth_or_null($monthrefdate);
		if ($iptu_ano_imovel != null) {
			return $iptu_ano_imovel;
		}
		// 2nd try: check if there's  a previous year record available
		$iptu_ano_imovel = $this->get_iptuanoimovel_of_previous_year_of_refmonth($monthrefdate);
		if ($iptu_ano_imovel != null) {
			return $iptu_ano_imovel;
		}
		// 3rd try: make a default instance of $iptu_ano_imovel
		return $this->make_default_iptuanoimovel_instance($monthrefdate);
	} // ends get_iptuanoimovel_with_refmonth_or_default()

	public function contracts() {
		return $this->hasMany('App\Models\Immeubles\Contract');
  }

	public function iptus() {
		return $this->hasMany('App\Models\Tributos\IPTUTabela');
  }

} // ends class Imovel extends Model


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
