<?php

namespace App\Http\Controllers;

use App\User;

use Auth;
use Illuminate\Http\Request;

class UserController extends Controller {


  public function getSignup() {
    return view('authusers.signup');
  }

  public function postSignup(\Illuminate\Http\Request $request) {
    $this->validate($request, [
      'email'    => 'email|required|unique:users',
      'password' => 'required|min:4',
    ]);

    $user = new User([
      'email' => $request->input('email'),
      'password' => bcrypt($request->input('password')),
    ]);
    $user->save();

    return view('authusers.signin');
  }

  public function getSignin() {
    return view('authusers.signin');
  }

  public function postSignin(\Illuminate\Http\Request $request) {

    $this->validate($request, [
      'email'    => 'email|required',
      'password' => 'required|min:4',
    ]);

    $email    = $request->input('email');
    $password = $request->input('password');

    $email    = 't2@t.com';
    $password = '1234';

    $is_auth_good = Auth::attempt([
      'email' => $request->input('email'),
      // 'password' => bcrypt($request->input('password')),
      'email' => $email,
      'password' => $password,
    ]);
    $aa = [$is_auth_good, $email, $password];

    //return var_dump($aa);
    // if ($is_auth_good) {
    if (true) {
      return redirect()->route('imoveis'); // dashboard
    }

    return redirect()->back();
  }



} // ends class UserController extends Controller
