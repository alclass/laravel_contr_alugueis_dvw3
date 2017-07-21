<?php namespace App\Http\Controllers;

use App\Models\Billing\Cobranca;
use App\Models\Immeubles\Contract;
use App\Models\Immeubles\Imovel;
use App\Models\Finance\BankAccount;
use App\Models\Finance\MercadoIndice;
use App\Models\Utils\DateFunctions;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use Carbon\Carbon;

class ContractController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$contracts = Contract::where('is_active', 1)->get();
		$authuser = Auth::user();
		return view('contracts.contracts', [
			'contracts' => $contracts,
			'authuser' => $authuser,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)	{

		$contract = Contract::findOrFail($id);
		$authuser = Auth::user();

		return view('contracts.contract', [
			'contract' => $contract,
			'authuser' => $authuser
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */

	 public function store(Illuminate\Http\Request $request) {

		$contract = new Contract;
		$imovel_id = Request::input('imovel_id');
		if ($imovel_id==null) {
			return redirect(route('dashboard'));
		}
		$imovel    = Imovel::findOrFail($imovel_id);
		$contract->imovel_id            = $imovel_id;
		$contract->initial_rent_value   = $request->input('initial_rent_value');
		$contract->current_rent_value   = $contract->initial_rent_value;

		$bankaccount_id           = $request->input('bankaccount_id');
		$contract->bankaccount_id = BankAccount
			::bankaccount_id_or_default_or_null($bankaccount_id);

		$indicador_reajuste           = $request->input('indicador_reajuste');
		$contract->indicador_reajuste = MercadoIndice
			::indice4char_or_default_or_null($indicador_reajuste);

		$contract->pay_day_when_monthly = $request->input('pay_day_when_monthly');
		$contract->percentual_multa     = $request->input('percentual_multa');
		$contract->percentual_juros     = $request->input('percentual_juros');

		$confirm_button = $request->input('confirm_button');
		if ($confirm_button == 'confirmed') {
			return view('contracts.contract', ['contract' => $contract, 'imovel' => $imovel]);
		}
		return view('contracts.contract.checar_entrar', ['contract' => $contract, 'imovel' => $imovel]);
 	} // ends store() // method to create a new Contract object

	public function edit($id)	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id) {
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

	private function dashboard_go($user)	{

		// ATTENTION $contract may be null, don't issue a method without checking
		$contract = $user->contracts->where('is_active', 1)->first();

		$cobrancas_passadas = collect();
		$cobrancas_passadas = $contract->get_ultimas_n_cobrancas_relative_to_ref(null, 4);
		$cobranca_atual     = null;
		$cobranca_atual     = $contract->get_cobranca_atual();
			// $cobranca_proxima = CobrancaGerador::SimularProximaCobranca($cobranca_atual);

		return view('contracts.dashboard', [
			'cobrancas_passadas' => $cobrancas_passadas,
			'cobranca_atual'     => $cobranca_atual,
			'contract' => $contract,
			'user'     => $user
		]);
	} // ends dashboard_go()

	public function dashboard()	{

		$user = Auth::getUser();
		return $this->dashboard_go($user);

	} // ends dashboard()

	public function dashboard_w_userid($user_id)	{
		/*
			The route to this controller method must be removed
			 	for production site (ie, it's only here for development)
		*/

		$user = User::findOrFail($user_id);
		return $this->dashboard_go($user);

	} // ends dashboard_w_userid()


}
