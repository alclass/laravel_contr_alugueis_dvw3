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

Route::get('/signup', [
  'as' => 'authusers.signup',
  'uses' => 'UserController@getSignup',
]);

Route::post('/signup', [
  'as' => 'authusers.signup',
  'uses' => 'UserController@postSignup',
]);

Route::get('/login', [
  'as' => 'login',
  'uses' => 'UserController@getSignin',
]);

Route::get('/signin', [
  'as' => 'authusers.signin',
  'uses' => 'UserController@getSignin',
]);

Route::post('/signin', [
  'as' => 'authusers.signin',
  'uses' => 'UserController@postSignin',
]);

Route::get('/logout', [
  'as' => 'authusers.logout',
  'uses' => 'UserController@getLogout',
]);

Route::post('/logout', [
  'as' => 'authusers.logout',
  'uses' => 'UserController@postLogout',
]);

/*
Route::get('/login', function() {
  return view('home');
})->name('login');

Route::get('/register', function() {
  return view('home');
})->name('register');

Route::post('/login', [
    'as'   => 'login',
    'uses' => 'LoginController@doLogin',
  ]
);
*/
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
// Route::middleware(['auth'])->group(
// ); // ends ::middleware(['auth'])->group(

//Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

// Luiz Madeira financ
Route::get('/lmadeira', 'Finance\LMadeiraController@index')->name('lmadeira');

// simple test
Route::get('/testCarbon', function() {
	//return 'hi';
	$deposited_on = Carbon::createFromFormat('d/m/Y', '1/1/2017');
	// return $deposited_on->toDateTimeString();
	return $deposited_on;
});
