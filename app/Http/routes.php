<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
use Carbon\Carbon;

use App\Imovel;
use App\User;
use App\Payment;
use Illuminate\Http\Request;
// use App\Http\Controllers\PaymentController;

Route::get('/', 'WelcomeController@index');

Route::get('home', 'HomeController@index');

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);

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
Route::resource('/payments/toregister', 'PaymentController@store');
/*
	'as'   => 'payments.toregister',
	'uses' => 'PaymentController@store'
]);
*/
Route::get('/cobrancas/abertas',     'CobrancaController@abertas');
Route::get('/cobrancas/emmora',      'CobrancaController@emmora');
Route::get('/cobrancas/consiliadas', 'CobrancaController@conciliadas');
Route::resource('/cobrancas/abrir',  'CobrancaController@store');

Route::get('/testCarbon', function() {
	//return 'hi';
	$deposited_on = Carbon::createFromFormat('d/m/Y', '1/1/2017');
	// return $deposited_on->toDateTimeString();
	return $deposited_on;
});
