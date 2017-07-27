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
	public function index()
	{
		//
	}

	public function analyzeverify($cobranca_id)	{

	}


	public function createdynamic($contract=null, $year=null, $month=null)	{

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
	public function store()
	{
		//
	}


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
		//
	}

	public function onref($year=null, $month=null)	{
		//return 'hi';
		$monthyeardateref = DateFunctions::make_n_get_monthyeardateref_with_year_n_month($year, $month);
		$cobrancas = Cobranca
			::where('monthyeardateref', $monthyeardateref)
			->get();
		$cobrancas->load('contract');
		return view('cobrancas/listarcobrancas', ['cobrancas'=>$cobrancas, 'category_msg'=>'On Ref.']);
	}

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

		//return 'hi';
		$cobrancas = Cobranca
			::where('has_been_paid', false)
			->get();
		$cobrancas->load('contract');
		// where('has_been_paid', 0)->get()->first();
		// $cobranca = Cobranca::where('has_been_paid', 0)->first();
		// $cobrancas = Cobranca::all();
		// $cobranca = new Cobranca;
		// $today = Carbon::now();
		// $cobranca->duedate = $today;
		// return 'hi today ' . $today . ' cobrança id ' . $cobranca->id;
		return view('cobrancas/listarcobrancas', ['cobrancas'=>$cobrancas, 'category_msg'=>'Abertas']);
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
		return view('cobrancas.cobranca.mostrarcobranca', ['cobranca'=>$cobranca]);
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
		$monthyeardateref = DateFunctions::make_n_get_monthyeardateref_with_year_n_month($year, $month);
		$cobranca = Cobranca
			::where('contract_id', $contract_id)
			->where('monthyeardateref', $monthyeardateref)
			->first();
		return view('cobrancas.cobranca.mostrarcobranca', ['cobranca'=>$cobranca]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

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
