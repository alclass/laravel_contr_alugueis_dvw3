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

Route::get('/imoveis', function () {
	$imoveis = Imovel::where('is_rentable', 1)->get();
	$imoveis->load('users');
	//$imoveis = '1';
	// return var_dump($imoveis);
	return view('imoveis', ['imoveis' => $imoveis]);
	// return 'hi';
});

Route::get('/imovel/{id}', array(
	'as' => 'imovel.route',
  'uses' =>
	  function ($id) {
			$imovel = Imovel::findOrFail($id);
			return view('imovel', ['imovel' => $imovel]);
	  }
	)
);

Route::get('/registerpayment2', array(
	'as' => 'registerpayment2.route',
	'uses' =>
	  function () {
			return view('registerpayment');
		}
	)
);

Route::resource('/registerpayment', 'PaymentController@store');
