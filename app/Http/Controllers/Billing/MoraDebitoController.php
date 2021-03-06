<?php namespace App\Http\Controllers\Billing;

use App\Cobranca;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Billing\MoraDebito;
use Carbon\Carbon;

use Illuminate\Http\Request;

class MoraDebitoController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//
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
	public function store()
	{
		//
	}


	public function showcalcfinanctimecorrection()
	{
		$moradebitos = MoraDebito::all();
		foreach ($moradebitos as $moradebito) {
			$moradebito->run_time_correction_of_ini_debt_value();
		}
		return view('cobrancas/emmora/exibirmoradebitos', ['moradebitos'=>$moradebitos, 'category_msg'=>'Abertas']);
		//
	} // ends showcalcfinanctimecorrection()


	public function emmora()	{
		$today = Carbon::today();
		$cobrancas = Cobranca::where('has_been_paid', 0)
			->where('duedate', '<', $today);
		return view('cobrancas/lista', ['cobrancas'=>$cobrancas, 'category_msg'=>'Abertas']);
	}

	public function listarmorasporcontrato($contract_id)	{
		return 'hi contract_id' . $contract_id;
		$today = Carbon::today();
		$mora_calc = MoraDebitoCalculator($contract_id);
		$debitomoras = $mora_calc->find_debitomoras();
		$cobrancas = Cobranca::where('has_been_paid', 0)
			->where('duedate', '<', $today);
		return view('cobrancas/emmora/listarmoras', [
			'cobrancas'    => $cobrancas,
			'debitomoras'  => $debitomoras,
			'category_msg' => 'Abertas']);
	}

	public function abrir()	{
		//
	}


	public function abertas()	{

		//return 'hi';
		$cobrancas = Cobranca::all();
		// where('has_been_paid', 0)->get()->first();
		// $cobranca = Cobranca::where('has_been_paid', 0)->first();
		// $cobrancas = Cobranca::all();
		// $cobranca = new Cobranca;
		$today = Carbon::now();
		// $cobranca->duedate = $today;
		// return 'hi today ' . $today . ' cobrança id ' . $cobranca->id;
		return view('cobrancas/lista', ['cobrancas'=>$cobrancas, 'category_msg'=>'Abertas']);
	}

	public function conciliadas()	{
		//
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
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
