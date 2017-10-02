<?php namespace App\Http\Controllers\Billing;

use App\Models\Billing\Cobranca;
use App\Models\Billing\CobrancaTipo;
use App\Models\Billing\MoraDebito;
use App\Models\Utils\DateFunctions;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RepasseController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()	{

	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create() {
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()	{
		//
	}

	public function showIptuRepasses($imovel_id=null)	{

		$iptus_sob_repasse = [];
		if ($imovel_id==null) {
			$user = Session::get('user');
			$contracts = $user->get_contracts_as_inquilino();
			foreach ($contracts as $contract) {
				$iptu = $contract->get_imovel_if_any();
				$iptus_sob_repasse[] = $iptu;
			}
		}
		else {
			$imovel = Imovel:findOrFail($imovel_id);
			$iptu = $imovel->get_iptu();
			$iptus_sob_repasse = [$iptu];
		}
		return view('encargos/iptus', ['iptus'=>$iptus, 'category_msg'=>'iptus']);
	} // ends showIptuRepasses()

	public function showCondominioRepasses($condominio_id=null)	{
		$condominios = [];
		if ($condominio_id==null) {
			$user = Session::get('user');
			$contracts = $user->get_contracts_as_inquilino();
			foreach ($contracts as $contract) {
				$condominio = $contract->get_condominio_if_any();
				$condominios[] = $condominios;
			}
		}
		else {
			$condominio = condominio:findOrFail($condominio_id);
			$condominios = [$condominio];
		}
		return view('encargos/condominios', ['condominios'=>$condominios, 'category_msg'=>'Condomínio(s)']);
	} // ends showCondominioRepasses()


} // ends class RepasseController extends Controller
