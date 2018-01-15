<?php namespace App\Http\Controllers\Billing;

use App\Models\Billing\Cobranca;
use App\Models\Billing\CobrancaTipo;
use App\Models\Billing\MoraDebito;
use App\Models\Utils\DateFunctions;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CobrancaController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()	{
		//
	}

	public function analyzeverify($cobranca_id)	{

	}

	public function createdynamic($contract=null, $year=null, $month=null) {

	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create() {
		//
	}

	public function showcalcfinanctimecorrection() {
		$moradebitos = MoraDebito::all();
		foreach ($moradebitos as $moradebito) {
			$moradebito->run_time_correction_of_ini_debt_value();
		}
		return view('cobrancas/emmora/exibirmoradebito', [
			'moradebitos'=>$moradebitos, 'category_msg'=>'Atualização Monetária'
		]);

	} // ends showcalcfinanctimecorrection()

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store() {

	}

	/**
	 * generateHistoricoDasCobrancas
	 *
	 * @return Response
	 */
	public function generateHistoricoDasCobrancas($contract_id) {

		if ($contract_id == null) {
			$user = Session::get('user');
			$contracts = $user->get_contracts_as_inquilino();
			foreach ($contracts as $contract) {
				$condominio = $contract->get_condominio_if_any();
				$contracts[] = $condominio;
			}
		}
		if (count($contracts) > 1) {
			return view('cobrancas.historicoDasCobrancasVariosContratos', [
				'contracts' => $contracts,
			]);
		}
		if (count($contracts) == 0) {
			$contract = null;
		}
		else {
			$contract = $contracts[0];
		}
		return view('cobrancas.historicoDasCobrancasContratos', [
			'contract' => $contract,
		]);
	} // ends generateHistoricoDasCobrancas()

	public function emmora()	{
		$today = Carbon::today();
		$cobrancas = Cobranca::where('has_been_paid', 0)
			->where('duedate', '<', $today);
		return view('cobrancas/lista', ['cobrancas'=>$cobrancas, 'category_msg'=>'Abertas']);
	}

	public function listarmorasporcontrato($contract_id)	{
		return 'CobrancaController listarmorasporcontrato';
		$today = Carbon::today();
		/*MoraDebitoCalculator
		find_debitomoras
		*/
		$cobrancas = Cobranca::where('has_been_paid', 0)
			->where('duedate', '<', $today);
		return view('cobrancas/lista', ['cobrancas'=>$cobrancas, 'category_msg'=>'Abertas']);
	}

	public function abrir()	{

	}

	public function onref($year=null, $month=null)	{
		//return 'hi';
		$monthyeardateref = DateFunctions::make_n_get_monthyeardateref_with_year_n_month($year, $month);
		$cobrancas = Cobranca
			::where('monthyeardateref', $monthyeardateref)
			->get();
		$cobrancas->load('contract');
		return view('cobrancas/listarcobrancas', ['cobrancas'=>$cobrancas, 'category_msg'=>'On Ref.']);
	} // ends onref()

	public function onlyrent_onref($year=null, $month=null)	{
		//return 'hi';
		$monthyeardateref = DateFunctions::make_n_get_monthyeardateref_with_year_n_month($year, $month);
		$cobrancas = Cobranca
			::where('monthyeardateref', $monthyeardateref)
			->get();
		$cobrancas->load('contract');
		$cobrancatipo = CobrancaTipo
			::where('char4id', CobrancaTipo::K_4CHAR_ALUG)->first();
		$onlyrent_cobrancas = collect();
		$cobrancas_found = collect();
		$n_entered_if = 0;
		foreach ($cobrancas as $cobranca) {
			foreach ($cobranca->billingitems()->get() as $billingitem) {
				if ($billingitem->cobrancatipo_id == $cobrancatipo->id) {
					$n_entered_if += 1;
					$cobrancas_found->push($cobranca);
					$cobranca_for_display = $cobranca->copy_without_billingitems();
					$cobranca_for_display->billingitems()->save($billingitem->copy());
					$onlyrent_cobrancas->push($cobranca_for_display);
					// there's only one 'rent' item
					break; // out of inner loop
				}
			}
		}
		return view('cobrancas.listarcobrancas', [
			'cobrancas'=>$cobrancas_found,
			'category_msg'=>var_dump([$n_entered_if,$cobrancatipo->id,$cobrancatipo->char4id]) // var_dump($cobrancas) //'On Ref. Only Rents'
		]);
	}

	public function abertas()	{

		// return 'hi';
		$cobrancas = Cobranca
			::where('has_been_paid', false)
			->get();
		$cobrancas->load('contract');
		$today = Carbon::now();
		return view('cobrancas/listarcobrancas',	[
			'cobrancas' => $cobrancas,
			'today' => $today, 'category_msg'=>'Abertas',
		]);
	}

	public function conciliadas()	{
		//
	}


	public function mostrarmesref($contract, $year, $month)	{
		$monthyeardateref = DateFunctions::make_n_get_monthyeardateref_with_year_n_month($year, $month);
		$monthyeardateref = Carbon::createFromDate($year, $month, 1);
		$monthyeardateref->setTime(0,0,0);
		$cobrancas = Cobranca
			::where('contract_id', $contract_id)
			->where('monthyeardateref', $monthyeardateref)
			->get();
		// return var_dump($monthyeardateref->toDayDateTimeString());
		return view('cobrancas.cobranca.mostrar', ['cobrancas'=>$cobrancas]);
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $cobranca_id
	 * @return Response
	 */
	public function show($cobranca_id)	{

		$cobranca = Cobranca::findOrFail($cobranca_id);
		$contract = $cobranca->contract;
		$bankaccount = $contract->bankaccount;
		$imovel = $contract->imovel;
		$user = $contract->users()->first();

		$today = Carbon::today();
		return view('cobrancas.cobranca.mostrar2', [
			'cobranca'=>$cobranca,
			'contract'=>$contract,
			'bankaccount'=>$bankaccount,
			'imovel'=>$imovel,
			// 'user'=>$user,
			'today'=>$today,
		]); // alt.:cobrancas.cobranca.mostrarcobranca
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $contract_id
	 * @param  int  $year
	 * @param  int  $month
	 * @return Response
	 */

	public function showviaref($contract_id, $year, $month)	{
		$cobranca = Cobranca
			::fetch_cobranca_with_triple_contract_id_year_month($contract_id, $year, $month);
		return view('cobrancas.cobranca.mostrarcobranca', ['cobranca'=>$cobranca]);
	}

	/**
	 * Show the edit form for editing the 'cobranca'
	 *
	 * @param  int  $contract_id, int $year, int $month
	 * @return Response
	 */
	 public function edit_via_httpget($contract_id, $year, $month)	{
		 $cobranca = Cobranca
 			::fetch_cobranca_with_triple_contract_id_year_month($contract_id, $year, $month);

		return view('cobrancas.cobranca.editaritensdecobranca', ['cobranca'=>$cobranca]);
 	} // ends edit_via_httppost()

	/**
	 * HTTP-post the edit form for creating/updating the 'cobranca'
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function edit_via_httppost(Request $request)	{

		$cobranca_id     = $request->input('cobranca_id');
		$cobranca = Cobranca::findOrFail($cobranca_id);
		// -------------------------------------
		$monthref = $request->input('monthref');
		$yearref  = $request->input('yearref');
		// -------------------------------------
		$cobrancatipo_id = $request->input('cobrancatipo_id');
		$cobrancatipo  = CobrancaTipo::findOrFail($cobrancatipo_id);
		$monthyeardateref = DateFunctions::make_n_get_monthyeardateref_with_year_n_month($yearref, $monthref);
		// -------------------------------------
		$charged_value   = $request->input('charged_value');
		$ref_type        = $request->input('ref_type');
		$freq_used_ref   = $request->input('freq_used_ref');
		$n_cota_ref      = $request->input('reftype');
		$total_cotas_ref = $request->input('total_cotas_ref');
		// -------------------------------------
		$bi_generator = BillingItemGenerator($cobranca);
		$billingitem = $bi_generator->createIfNeededBillingItemFor(
      $cobrancatipo,
      $charged_value, // $value,
      $ref_type,
      $freq_used_ref,
      $monthyeardateref,
      $n_cota_ref,
      $total_cotas_ref
		);
		$obs = $request->input('obs');
		if ($obs != null) {
			$billingitem->$obs = $obs;
			$billingitem->save();
		}
		return view('cobrancas.cobranca.mostrarcobranca', ['cobranca'=>$cobranca]);

	} // ends edit_via_httppost()

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
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

	public function listarmoras() {

		$moradebitos = MoraDebito::all();

		return view('cobrancas/emmora/listarmoras', ['moradebitos'=>$moradebitos, 'category_msg'=>'Abertas']);

	} // ends moras()



}
