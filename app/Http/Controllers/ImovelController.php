<?php namespace App\Http\Controllers;

use App\Models\Immeubles\Imovel;
use App\Http\Requests;
use App\Http\Controllers\Controller;
// use Carbon\Carbon;

use Illuminate\Http\Request;

class ImovelController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$imoveis = Imovel::where('is_rentable', 1)->get();
		$imoveis->load('users');
		return view('imoveis.imoveis', ['imoveis' => $imoveis]);
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

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		$imovel = Imovel::findOrFail($id);
		return view('imoveis.imovel', ['imovel' => $imovel]);
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
