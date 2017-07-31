<?php namespace App\Http\Controllers\Finance;

use App\Models\Persons\Borrower;
use App\Http\Requests;
use App\Http\Controllers\Controller;
// use Carbon\Carbon;
use Illuminate\Http\Request;

class AmortizationParcelController extends Controller {


	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index() {
	}

	public function showevolvetable($borrower_id) {

		$borrower = Borrower::findOrFail($borrower_id);
		$loans_time_evolve_report = $borrower->get_loans_time_evolve_report();
		/*
		$loan_ini_date           = $borrower->get_loan_ini_date(); // new Carbon('2017-04-15');
		$loan_ini_value          = $borrower->get_loan_ini_value(); // 2000;
		$loan_duration_in_months = $borrower->get_loan_duration_in_months(); // 24;

		$time_evolve_loan_obj = new TimeEvolveAmortizationParcel(
			$loan_ini_date,
			$loan_ini_value,
			$loan_duration_in_months
		);
		*/

		return view('finance.tabelasacprice', [
			'loans_time_evolve_report' => $loans_time_evolve_report,
		]);
    /*
      'column_keys'    => $this->column_keys,
      'loan_ini_date'  => $this->loan_ini_date,
      'loan_ini_value' => $this->loan_ini_value,
      'loan_duration_in_months'   => $this->loan_duration_in_months,
      'rows'           => $this->rows,
      'pmt_prestacao_mensal_aprox_until_payment_end' => $this->pmt_prestacao_mensal_aprox_until_payment_end,
      'n_remaining_months_on_pmt' => $this->n_remaining_months_on_pmt,
      'interest_rate_pmt_aprox'   => $this->interest_rate_pmt_aprox,
      'msg_or_info'               => $msg_or_info,
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
