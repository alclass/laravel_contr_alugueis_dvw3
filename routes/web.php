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
use App\Mail\AlugLembreteMensal;
// use Carbon\Carbon;
use Illuminate\Http\Request;
// use App\Http\Controllers\PaymentController;

// Route::get('/', 'WelcomeController@index');
Route::get('/', function () {
    return view('welcome');
});
/*
Route::get('/sistadm/account/login', [
  'as' => 'login',
  'uses' => function() {
    return 'login';
    return view('welcome');
  }
]);
*/

Route::get('/home', 'HomeController@index')->name('home');
//Auth::routes();

Route::get('/sendemail', 'SendEmailController@sendemail_via_httpget')->name('sendemail');
Route::get('/sendemail', 'SendEmailController@sendemail_via_httppost')->name('sendemail');

Route::get('/testgethost', function (\Illuminate\Http\Request $request) {
    $alug_lembr = new AlugLembreteMensal;
    $alug_lembr->set_domain_based_from_email_field($request);
    /*
    $domain_name = $request->getHost();
    $root_domain_name = $request->root();
    // $domain_name = Request::server('HTTP_HOST');
    */
    return '$alug_lembr->instances_from_email = ' . $alug_lembr->instances_from_email;
    // return view('welcome');
});
