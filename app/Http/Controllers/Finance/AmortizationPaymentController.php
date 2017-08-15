<?php
namespace App\Http\Controllers\Finance;

use App\Models\Persons\Borrower;
use App\Http\Requests;
use App\Http\Controllers\Controller;
// use Carbon\Carbon;
use Illuminate\Http\Request;

class AmortizationPaymentController extends Controller {


	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index() {
	}

	public function show($borrower_id) { // _parcel_evolver_report

		$borrower = Borrower::findOrFail($borrower_id);
		$borrower->set_amortization_parcels_evolver();


		return view('finance.tabelasacprice', [
			'borrower' => $borrower,
		]);


	} // ends show_parcel_evolver_report()

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
	public function show2($id)
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
