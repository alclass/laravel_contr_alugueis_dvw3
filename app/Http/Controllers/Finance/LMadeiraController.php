<?php namespace App\Http\Controllers\Finance;

use App\Models\Finance\CorrMonet;
use App\Models\Finance\LMadeiraPagto;
use App\Models\Finance\TimeEvolveParcel;
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

		$loan_ini_date           = new Carbon('2017-04-01');
		$loan_ini_value          = 2000;
		$loan_duration_in_months = 24;

		// The constructor's object will also run its processing (the rows building)
		$time_evolve_loan_obj = new TimeEvolveParcel(
			$loan_ini_date,
			$loan_ini_value,
			$loan_duration_in_months
		);

		return view('finance.tabelasacprice', [
			'time_evolve_loan_obj' => $time_evolve_loan_obj,
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
