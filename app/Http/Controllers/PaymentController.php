<?php namespace App\Http\Controllers;

use App\Imovel;
use App\Payment;
use App\User;

use App\Http\Requests;
use App\Http\Controllers\Controller;

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
		//
		// return 'hi';
		// return var_dump($request);  OBS: this crashed firefox and almost the computer due to RECURSION
		/*
		$payment = null;
		if (empty($request)) {
			// returns right away, don't go further here request has nothing
			return view('registerpayment', ['payment' => $payment]);
		}
		$user = null;
		if ($request->has('user_id')) {
			$user = User::find($request->input('user_id'));
		}
		$imovel = null;
		if ($request->has('imovel_id')) {
			$imovel = Imovel::find($request->has('imovel_id'));
		}
		$amount = null;
		if ($request->has('amount')) {
			$amount = $request->input('amount');
		}
		$deposited_on = null;
		if ($request->has('deposited_on')) {
			$deposited_on = $request->input('deposited_on');
		}
		$bankname = null;
		if ($request->has('bankname')) {
			$bankname = $request->input('bankname');
		}

		if (!empty($user) && !empty($amount) && !empty($deposited_on)) {
			$payment = new Payment;
			$payment->amount       = $amount;
			$payment->deposited_on = $deposited_on;
			$payment->bankname = $bankname;
			$payment->user   = $user;
			$payment->imovel = $imovel;
			$payment->save();
		}
		*/
		// return 'hi';
		$user = User::find(1);
		$imovel = Imovel::find(1);
		// return var_dump($imovel);

		$payment = new Payment;
		$payment->amount       = 100;
		$payment->deposited_on = strtotime('1/1/2017');
		$payment->bankname = 'Itaú';
		$payment->user()->associate($user);
		$payment->imovel()->associate($imovel);

		// return var_dump($payment);

		$payment->save();


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
