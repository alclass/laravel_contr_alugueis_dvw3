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

Route::get('/', 'WelcomeController@index');

Route::get('home', 'HomeController@index');

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);

Route::get('/users', function () {
	// $users = User::all();
	$users = User::all();
	$users->load('imoveis');

	// $users->load('imoveis)';
	//$imoveis->load('users');
	// $users = '1';
	// return var_dump($users);
	return view('users', ['users' => $users]);
	// return 'hi';
});

Route::get('/imoveis', function () {
	$imoveis = Imovel::all();
	$imoveis->load('users');
	//$imoveis = '1';
	// return var_dump($imoveis);
	return view('imoveis', ['imoveis' => $imoveis]);
	// return 'hi';
});
