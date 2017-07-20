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

Route::get('/logout', [
  'as' => 'authusers.logout',
  'uses' => 'UserController@postLogout',
]);


Route::get('/dashboard', [
  'as' => 'dashboard',
  'uses' => 'ContractController@dashboard',
])->middleware('auth');

Route::get('/dashboard/{user_id}', [
  'as' => 'dashboard',
  'uses' => 'ContractController@dashboard_w_userid',
])->middleware('auth');




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
/*
Route::get('/contracts', [
	'as'   => 'contracts',
  'uses' =>
  function () {
    $contracts = Contract::where('is_active', 1)->get();
    return view('contracts.contracts', ['contracts' => $contracts]);
  }
]);
*/

Route::middleware(['auth'])->group(
  function () {
    Route::get('/contract/{id}', [
    	'as'   => 'contract',
      'uses' => 'ContractController@show'
    ]);
    Route::get('/contracts', [
    	'as'   => 'contracts',
      'uses' => 'ContractController@index'
    ]);
    Route::post('/contract/cadastrar', [
      'as'   => 'contract.cadastrar',
      'uses' => 'ContractController@store'
    ]);
  }
); //->prefix('sl');

Route::get('/imoveis', [
	'as'   => 'imoveis',
  'uses' => 'ImovelController@index'
]);
Route::get('/imovel/{id}', [
	'as'   => 'imovel.show',
  'uses' => 'ImovelController@show'
]);

Route::get('/payments/history', 'PaymentController@index');
Route::get('/payment/conciliar/{contract_id}/{year}/{month}', [
  'as'   => 'payment.conciliar',
  'uses' => 'PaymentController@conciliar'
]);
// Route::match(array('GET', 'POST') ...
Route::post('/cobranca/editargerar', [
  'as'=>'cobranca.editargerar',
  'uses'=>'PaymentController@editargerar'
]);
//Route::resource('payment', 'PaymentController');

Route::post('/payments/toregister', 'PaymentController@store');
/*
	'as'   => 'payments.toregister',
	'uses' => 'PaymentController@store'
]);
*/
Route::get('/cobranca/mostrar/{contract_id}/{year}/{month}', [
  'as'   => 'cobranca.mostrar',
  'uses' =>
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
]); // ends aarray & Route::get

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

//Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
