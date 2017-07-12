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
use App\Contract;
use App\Imovel;
use App\User;
use App\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
// use App\Http\Controllers\PaymentController;

// Route::get('/', 'WelcomeController@index');
Route::get('/', function () {
    return view('welcome');
});

Route::get('home', 'HomeController@index');

Route::get('/users', function () {
	$users = User::all();
	$users->load('imoveis');
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
Route::get('/cobranca/{id}/mostrar',
  function($id) {
    $contract = Contract::findOrFail($id);
    return view('cobranca.mostrar', ['contract'=>$contract]);
  }
);

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
