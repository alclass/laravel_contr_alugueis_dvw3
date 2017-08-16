<?php
namespace App\Http\Controllers;

//use App\Models\Immeubles\Imovel;
//use App\Models\Immeubles\CondominioTarifa;
// use App\Http\Requests;
use App\Http\Controllers\Controller;
// use Carbon\Carbon;

use Illuminate\Http\Request;

class UserDashboardController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function showUserDashboard() { // $user_id

		$user      = session('user');
		$contracts = $user->contracts; // OBS: ->contracts is a Collection
		//$imoveis   = $user->get_imoveis();

		return view('persons.userdashboard', [
			'user'      => $user,
			'contracts' => $contracts,
			//'imoveis'   => $imoveis,
		]);
	} // ends showUserDashboard()

} // ends class UserDashboardController extends Controller
