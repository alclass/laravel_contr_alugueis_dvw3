<?php namespace App\Http\Controllers\Immeubles;

use App\Models\Immeubles\Imovel;
use App\Models\Immeubles\CondominioTarifa;
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


	public function show_condominium($imovel_id) {

		$condominiotarifas = CondominioTarifa
      ::where('imovel_id', $imovel_id)
      ->get();
    $imovel = Imovel::findOrFail($imovel_id);
    // return var_dump($monthrefdate->toDayDateTimeString());
    return view('imoveis.condominiotarifas', [
      'condominiotarifas'=>$condominiotarifas,
      'imovel'=>$imovel
    ]);

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
		// Notice $contract may be gotten as null here, it's not a problem for the receiving view
    $contract = $imovel->get_current_rent_contract_if_any();
		$next_reajust_date_formatted = 'n/a';
		$next_reajust_date_carbon = $contract->find_rent_value_next_reajust_date();
		if ($next_reajust_date_carbon!=null) {
			$next_reajust_date_formatted = $next_reajust_date_carbon->format('d/M/Y');
		}
		return view('imoveis.imovel', [
			'imovel' => $imovel,
			'contract' => $contract,
			'next_reajust_date_formatted' => $next_reajust_date_formatted,
		]);
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
