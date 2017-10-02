<?php
namespace App\Http\Controllers;

//use App\Models\Immeubles\Imovel;
//use App\Models\Immeubles\CondominioTarifa;
// use App\Http\Requests;
use App\Http\Controllers\Controller;
// use Carbon\Carbon;

use Illuminate\Http\Request;

class dashboardController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function showdashboard() { // $user_id

		$user      = session('user');
		$contracts = $user->contracts; // OBS: ->contracts is a Collection
		//$imoveis   = $user->get_imoveis();

		return view('persons.dashboard', [
			'user'      => $user,
			'contracts' => $contracts,
			//'imoveis'   => $imoveis,
		]);
	} // ends showdashboard()

} // ends class dashboardController extends Controller
