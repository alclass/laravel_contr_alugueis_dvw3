<?php namespace App\Http\Controllers;

use App\Imovel;
use App\Payment;
use App\User;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
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

		$payment = null;
		$testword = 'testword (should change)';
		if (empty($request)) {
			// returns right away, don't go further here request has nothing
			return view('registerpayment', ['payment' => $payment]);
		}

		$user = null;
		if ($request->has('payer_id')) {
			$user = User::findOrFail($request->input('payer_id'));
			// $testword = $user->name_first_last();
		}
		$imovel = null;
		if ($request->has('imovel_id')) {
			$imovel = Imovel::findOrFail($request->input('imovel_id'));
		}
		$amount = null;
		if ($request->has('amount')) {
			$amount = $request->input('amount');
		}
		$deposited_on = null;
		if ($request->has('deposited_on')) {
			$date_str = $request->input('deposited_on');
			$deposited_on = Carbon::createFromFormat('d/m/Y', $date_str);
		}
		$bankname = null;
		if ($request->has('bankname')) {
			$bankname = $request->input('bankname');
		}

		if (!empty($user) && !empty($amount) && !empty($deposited_on)) {
			$payment = new Payment;
			$payment->amount       = $amount;
			$payment->deposited_on = $deposited_on;
			$payment->bankname     = $bankname;
			$payment->user()->associate($user);
			$payment->imovel()->associate($imovel);
			$payment->save();
		}
		// return 'hi testword -> '. $testword;
		return view('registerpayment', ['payment' => $payment]);
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
