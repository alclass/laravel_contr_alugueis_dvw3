<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use App\Models\Billing\Cobranca;
use App\Models\Billing\Payment;
use App\Models\Immeubles\CondominioTarifa;
use App\Models\Immeubles\Contract;
use App\Models\Immeubles\Imovel;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
// use App\Http\Controllers\PaymentController;

// Route::get('/', 'WelcomeController@index');
Route::get('/', function () {
    return view('welcome');
});

Route::get('home', 'HomeController@index');

Route::get('/users', function () {
  //return 'hi';
	$users = User::all();
	//$users->load('contracts');
	return view('users', ['users' => $users]);
});

Route::get('/user/{id}', array(
	'as' => 'user.route',
  'uses' =>
	  function ($id) {
			$user = User::findOrFail($id);
			return view('user', ['user' => $user]);
	  }
	)
);

Route::get('/contracts', [
	'as'   => 'contracts',
  'uses' =>
  function () {
    $contracts = Contract::where('is_active', 1)->get();
    return view('contracts.contracts', ['contracts' => $contracts]);
  }
]);

Route::get('/contract', [
	'as'   => 'contract',
  'uses' =>
  function ($id) {
    $contract = Contract::findOrFail($id);
    return view('contracts.contract', ['contract' => $contract]);
  }
]);

Route::get('/imoveis', [
	'as'   => 'imoveis',
  'uses' => 'ImovelController@index'
]);
Route::get('/imovel/{id}', [
	'as'   => 'imovel.show',
  'uses' => 'ImovelController@show'
]);

Route::get('/payments/history', 'PaymentController@index');
Route::post('/payments/toregister', 'PaymentController@store');
/*
	'as'   => 'payments.toregister',
	'uses' => 'PaymentController@store'
]);
*/
Route::get('/cobranca/mostrar/{contract_id}/{year}/{month}',
  function($contract_id, $year, $month) {
    $monthyeardateref = Carbon::createFromDate($year, $month, 1);
    $monthyeardateref->setTime(0,0,0);
    $cobrancas = Cobranca
      ::where('contract_id', $contract_id)
      ->where('monthyeardateref', $monthyeardateref)
      ->get();
    // return var_dump($monthyeardateref->toDayDateTimeString());
    return view('cobrancas.cobranca.mostrar', ['cobrancas'=>$cobrancas]);
  } // ends closure function
); // ends Route::get

Route::get('/condominios/{imovel_id}',
  function($imovel_id) {
    $condominiotarifas = CondominioTarifa
      ::where('imovel_id', $imovel_id)
      ->get();
    $imovel = Imovel::findOrFail($imovel_id);
    // return var_dump($monthyeardateref->toDayDateTimeString());
    return view('imoveis.condominiotarifas', [
      'condominiotarifas'=>$condominiotarifas,
      'imovel'=>$imovel
    ]);
  } // ends closure function
); // ends Route::get


Route::get('/cobrancas/abertas',     'CobrancaController@abertas');
Route::get('/cobrancas/emmora',      'CobrancaController@emmora');
Route::get('/cobrancas/consiliadas', 'CobrancaController@conciliadas');
// Route::resource('/cobrancas/abrir',  'CobrancaController@store');

Route::get('/testCarbon', function() {
	//return 'hi';
	$deposited_on = Carbon::createFromFormat('d/m/Y', '1/1/2017');
	// return $deposited_on->toDateTimeString();
	return $deposited_on;
});
