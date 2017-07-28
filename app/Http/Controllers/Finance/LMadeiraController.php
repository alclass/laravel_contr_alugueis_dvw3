<?php namespace App\Http\Controllers\Finance;

use App\Models\Finance\CorrMonet;
use App\Models\Finance\LMadeiraPagto;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LMadeiraController extends Controller {


	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index() {

		// $time_evolve_parcels_table = LMadeiraPagto::get_time_evolve_parcels_table();
		$initial_montant = 2000;
		$n_months = 24;
		$interest_rate = 0.01;
		$pmt = FinancialFunctions::calc_monthly_payment_pmt(
			$initial_montant,
			$n_months,
			$interest_rate
		);

		return view('finance.tabelasacprice', [
			'pmt' => 'pmt'
		]);
		/*
		return view('finance.tabelasacprice', [
      'column_keys'   => $column_keys, 'rows' => $rows,
      'loan_ini_date' => $loan_ini_date,
      'msg_or_info'   => $msg_or_info,
    ]);
		*/

	} // ends index()

	public function history()
	{
		return 'hi';
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
	public function store(\Illuminate\Http\Request $request) {

		// return view('registerpayment', ['payment' => $payment]);
		return 'hi';
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

}
