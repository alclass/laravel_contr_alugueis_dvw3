<?php namespace App\Http\Controllers\Billing;

use App\Models\Billing\BillingItemGenStatic;
use App\Models\Billing\Cobranca;
use App\Models\Billing\CobrancaGerador;
use App\Models\Billing\CobrancaTipo;
use App\Models\Billing\MoraDebito;
use App\Models\Finance\BankAccount;
use App\Models\Immeubles\Contract;
use App\Models\Immeubles\Imovel;
use App\Models\Utils\DateFunctions;
use App\Models\Utils\StringFunctions;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use \Exception;
use \Thowable;
use Illuminate\Http\Request;

class CobrancaController extends Controller {

	const LIMIT_WHILE_LOOPS_TO = 500;

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
		$monthrefdate = DateFunctions::make_n_get_monthrefdate_with_year_n_month($year, $month);
		$cobrancas = Cobranca
			::where('monthrefdate', $monthrefdate)
			->get();
		$cobrancas->load('contract');
		return view('cobrancas/listarcobrancas', ['cobrancas'=>$cobrancas, 'category_msg'=>'On Ref.']);
	} // ends onref()

	public function onlyrent_onref($year=null, $month=null)	{
		//return 'hi';
		$monthrefdate = DateFunctions::make_n_get_monthrefdate_with_year_n_month($year, $month);
		$cobrancas = Cobranca
			::where('monthrefdate', $monthrefdate)
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
			::where('closed', false)
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
		$monthrefdate = DateFunctions::make_n_get_monthrefdate_with_year_n_month($year, $month);
		$monthrefdate = Carbon::createFromDate($year, $month, 1);
		$monthrefdate->setTime(0,0,0);
		$cobrancas = Cobranca
			::where('contract_id', $contract_id)
			->where('monthrefdate', $monthrefdate)
			->get();
		// return var_dump($monthrefdate->toDayDateTimeString());
		return view('cobrancas.cobranca.mostrar', ['cobrancas'=>$cobrancas]);
	}


	public function show_by_year_month_imovelapelido($year, $month, $p_imovelapelido, $monthseqnumber=1) {
		/*
		The 4th param (monthseqnumber) is optional
		*/
		$imovelapelido = strtoupper($p_imovelapelido);
		$imovel = Imovel
			::where('apelido', $imovelapelido)
			->first();
		if ($imovel == null) {
			return 'Imóvel não encontrado.';
		}
		$contract = Contract
			::where('imovel_id', $imovel->id)
			->where('is_active', true)
			->first();
		if ($contract == null) {
			$page_msg = 'Imóvel '. $imovel->apelido . ' não tem contrato ativo.';
			return $page_msg;
		}
		$monthrefdatestr = "$year-$month-01";
		$monthrefdate = new Carbon($monthrefdatestr);
		$cobranca = Cobranca
			::where('monthrefdate',   $monthrefdate)
			->where('monthseqnumber', $monthseqnumber)
			->where('contract_id',    $contract->id)
			->first();
		if ($cobranca == null) {
			return redirect()->route('cobrancasporimovelroute', [$imovel->apelido]);
			/*
			$cobranca = CobrancaGerador::create_or_retrieve_cobranca_with_keys(
				$contract->id,
				$monthrefdate,
				$monthseqnumber
			);
			//$page_msg = 'Cobrança não existe. Dados: Ref.: ' . $year . '/' . $month . '; imóvel ' . $imovel4char . '; contrato: ' . $contract->id . '; dt=' . $monthrefdate;
			//return $page_msg;
			*/
		}
		$bankaccount = $contract->bankaccount;
		$today = Carbon::today();
		// return var_dump($cobranca);
		return view('cobrancas.cobranca.mostrar2', [
			'cobranca'=>$cobranca,
		]); // alt.:cobrancas.cobranca.mostrarcobranca


	} // ends show_by_year_month_imovelapelido()

	public function listar_cobrancas_por_imovel($imovelapelido)	{
		$imovel = Imovel::fetch_by_apelido($imovelapelido);
		if ($imovel == null) {
			return 'Imóvel não encontrado.';
		}
		$contract = Contract
			::where('imovel_id', $imovel->id)
			->where('is_active', true)
			->first();
		if ($contract == null) {
			$page_msg = 'Imóvel '. $imovel->apelido . ' não tem contrato ativo.';
			return $page_msg;
		}

		$cobrancas = Cobranca
			::where('contract_id', $contract->id)
			->orderBy('monthrefdate', 'desc')
			->get();

		$today = Carbon::today();
		return view('cobrancas/listarcobrancas',	[
			'cobrancas' => $cobrancas,
			'today' => $today,
			'category_msg'=>'Cobranças Histórico',
		]);

	} // ends listar_cobrancas_por_imovel()

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
	public function edit_via_httpget(
			$year,
			$month,
			$imovelapelido,
			$monthseqnumber=1,
			$error_msgs = []
		)	{

		$imovel = Imovel::fetch_by_apelido($imovelapelido);
	  if ($imovel == null) {
		  return redirect()->route('/');
		}
		$contract = $imovel->get_active_contract();
		if ($contract == null) {
		  return redirect()->route('/');
		}
		$today = Carbon::today();
		$year = intval($year);
		if ($year < $today->year-6 || $year > $today->year+6) {
			return redirect()->route('/');
		}
		$month = intval($month);
		if ($month < 1 || $month > 12) {
			return redirect()->route('/');
		}
		$monthrefdate = new Carbon("$year-$month-01");
		$cobranca = Cobranca
 		  ::fetch_cobranca_with_imovelapelido_year_month_n_seq($imovelapelido, $year, $month, $monthseqnumber);
		if ($cobranca == null) {
			$cobranca = CobrancaGerador::create_cobranca_with_imovelapelido_year_month_n_seq(
				$imovelapelido,
				$year,
				$month,
				$monthseqnumber);
		}
		if ($cobranca == null) {
			$cobranca = new Cobranca();
			$cobranca->monthrefdate = $monthrefdate;
			$cobranca->duedate = $monthrefdate->copy()->addMonths(1)->day(10);
			$cobranca->monthseqnumber = $monthseqnumber;
			$cobranca->contract_id = $contract->id;
			// throw new Exception('$cobranca == null in controller for cobrança-editar');
		}
		$cobranca->generate_autoinsertable_billingitems();
		$cobranca->set_bankaccountid_from_contract_or_default();
		session()->put('cobranca', $cobranca);

		// return var_dump($aarray);
		return view(
			'cobrancas.cobranca.editarcobranca', [
				'cobranca' => $cobranca,
				'error_msgs' => $error_msgs,
			]);
 	} // ends edit_via_httpget()

	public function show_confirm_cobranca() {

		$cobranca = session()->get('cobranca');
		if ($cobranca == null) {
			return 'Cobranca não existe.';
		}
		return view(
			'cobrancas.cobranca.mostrar2', [
				'cobranca' => $cobranca,
			]);
	} // ends show_confirm_cobranca()

	public function try_recover_editcobranca_from_request_or_errorpage(
			Request $request,
			$error_msgs = []
		)	{
		/*
			In edit_via_httppost(), cobrança was not recovered from session(),
				so try to recover individual fields and redirect to edit_via_httpget()
			If this recovery is not possible, show an error page.

			Recovery intends to recup, ie:
				$year,
				$month,
				$imovelapelido,
				$monthseqnumber=1
		*/
		$year  = $request->input('yearref');
		$month = $request->input('monthref');

		try {
			$monthrefdate = new Carbon("$yearref-$monthref-01");
		} catch (Throwable $t) {
			// Executed only in PHP 7, will not match in PHP 5
			$monthrefdate = DateFunctions::make_n_get_monthrefdate_with_year_n_month();
			$error_msg = 'Mês de referência está faltando.';
			$error_msgs[] = $error_msg;
		}

		$imovelapelido  = $request->input('imovelapelido');
		if (empty($imovelapelido)) {
			$error_msg = 'Imóvel está faltando.';
			$error_msgs[] = $error_msg;
		}
		$monthseqnumber = $request->input('monthseqnumber');
		if (empty($monthseqnumber)) {
			$monthseqnumber = 1;
		}

		return $this->edit_via_httpget(
			$year,
			$month,
			$imovelapelido,
			$monthseqnumber,
			$error_msgs
		);

	} // ends try_recover_cobranca_from_request_or_errorpage()

	public function delete_cobranca(Request $request)	{
		$cobranca = session()->get('cobranca');
		$imovelapelido = null;
		if ($cobranca != null) {
			$imovel = $cobranca->imovel;
			if ($imovel != null) {
				$imovelapelido = $imovel->apelido;
			}
			$cobranca->delete();
		}
		if ($imovelapelido != null) {
			return redirect()->route('cobrancasporimovelroute', [$imovelapelido]);
		}
		return redirect()->route('/');
	} // ends delete_cobranca()

	public function save_cobranca(Request $request)	{

		$cobranca = session()->get('cobranca');
		// $cobranca->save();    // Eloquent is trying to save columns '0' & '1' (of course, it's an error)
		// return 'Saved $cobranca->id = ' . $cobranca->id;
		$cobranca->save_propagating_billingitems();
		return view('cobrancas.cobranca.mostrar2', [
			'cobranca' => $cobranca,
		]);

	} // ends ()


	/**
	 * HTTP-post the edit form for creating/updating the 'cobranca'
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function edit_via_httppost(Request $request)	{
		/*
			This method receives the html-form that contains
				the billing items for a cobrança.
			=> if data components are good, the Eloquent 'cobranca'
				object is saved.
			=> if data components are NOT good, a redirect to the
				edit page should happen carrying the errors array.
		*/

		$cobranca = session()->get('cobranca');
		if ($cobranca == null) {
			return $this->try_recover_editcobranca_from_request_or_errorpage($request);
		}

		$billingitem_n = 0; // it'll start at index 1 below

		$to_vardump = [];

		$cobranca->billingitems()->delete();

		while (true) {
			$billingitem_n += 1;
			// 1) $billingitem_monthrefdate
			$monthrefdatefieldname = 'monthrefdate-' . $billingitem_n . '-fieldname';

			$billingitem_monthrefdatestr = $request->input($monthrefdatefieldname);

			if ($billingitem_monthrefdatestr == null) {
				break;
			}
			$billingitem_monthrefdate = new Carbon($billingitem_monthrefdatestr);

			//$to_vardump[$monthrefdatefieldname] = $billingitem_monthrefdate;

			// 2) $cobrancatipo4char
			$cobrancatipofieldname = 'cobrancatipo4char-' . $billingitem_n . '-fieldname';
			$cobrancatipo4char = $request->input($cobrancatipofieldname);

			//$to_vardump[$cobrancatipofieldname] = $cobrancatipo4char;

			// 3) $charged_value
 			$charged_valuefieldname = 'charged_value-' . $billingitem_n . '-fieldname';
			$charged_valuestr = $request->input($charged_valuefieldname);
			$charged_value = StringFunctions::parseStrToFloat($charged_valuestr);

			//$to_vardump[$charged_valuefieldname] = $charged_value;

			// 4) $additionalinfo
			if (!isset($additionalinfo) || empty($additionalinfo)) {
				$additionalinfo = 'Additional Info not yet set.';
			}
			// 5) $numberpart
			$numberpartfieldname = 'numberpart-' . $billingitem_n . '-fieldname';
			$numberpartstr = $request->input($numberpartfieldname);
			$numberpart = intval($numberpartstr);
			// 6) $totalparts
			$totalpartsfieldname = 'totalparts-' . $billingitem_n . '-fieldname';
			$totalpartsstr = $request->input($totalpartsfieldname);
			$totalparts = intval($totalpartsstr);
			$billingitem = BillingItemGenStatic
				::make_billingitem(
					$cobranca,
					$cobrancatipo4char,
					$charged_value,
					$billingitem_monthrefdate,
					$additionalinfo,
					$numberpart,
					$totalparts
				);

			// $to_vardump[$cobrancatipofieldname] = $billingitem->cobrancatipo->char4id;

			/*
			if ($billingitem != null) {
				$cobranca->billingitems->push($billingitem);
			}
			*/
			// protect against infinite loop
			if ($billingitem_n > self::LIMIT_WHILE_LOOPS_TO) {
				break;
			}

		} // ends while


		// return var_dump($to_vardump);


		// $cobranca->save();
		/* return '(Not saved yet) cobrança with id = '
			. $cobranca->monthrefdate . ' => '
			. $cobranca->totalvalue . ' => '
			. $cobranca;
		// return var_dump($cobranca);
		*/
		session()->put('cobranca', $cobranca);
		return view('cobrancas.cobranca.editconfirm', [
			'cobranca' => $cobranca,
		]);

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
