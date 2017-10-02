<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class UserController extends Controller {
  /*

  Methods:
    login_via_httpget()
    login_via_httppost()
    logout_via_httpget()
    logout_via_httppost()
    signup_via_httpget()
    signup_via_httppost()
    listUsers()
    showUser()
  */

  public function login_via_httpget() {
    return view('authusers.login');
  }

  public function login_via_httppost(\Illuminate\Http\Request $request) {

    $this->validate($request, [
      'email'    => 'email|required',
      'password' => 'required|min:6',
    ]);

    $email    = $request->input('email');
    $password = $request->input('password');

    $is_auth_good = Auth::attempt([
      'email'    => $email,
      'password' => $password, // bcrypt()
    ]);

    if ($is_auth_good == false) {
      return redirect()->back(); // ->withErrors();
    }
    /*
    $user = User::where('email', $email)->first();
    if ($user == null) {
      $user = User::first();
    }
    $user = Auth::login($user);
    */

    $user = Auth::user();
    // return 'user is ' . var_dump($user);
    session(['user' => $user]);
    return redirect()->route('persons.dashboard'); // dashboard
  }

  public function logout_via_httpget() {
    Auth::logout();
    return redirect()->route('home');
  }

  public function logout_via_httppost(\Illuminate\Http\Request $request) {
    Auth::logout();
    return redirect()->route('home');
  }

  public function signup_via_httpget() {
    return view('authusers.signup');
  }

  public function signup_via_httppost(\Illuminate\Http\Request $request) {

    $this->validate($request, [
      'email'    => 'email|required|unique:users',
      'password' => 'required|min:6',
    ]);

    $user = new User([
      'email'    => $request->input('email'),
      'password' => bcrypt($request->input('password')),
    ]);
    $user->save();

    return redirect()->route('login');
  }

  public function listUsers() {
    $users = User::orderBy('asc')->get();
    return view('users.route',  ['users' => $users]);
  }

  public function showUser($user_id) {
    $user = User::findOrFail($user_id);
    return view('persons.user', ['user' => $user]);
  }

} // ends class UserController extends Controller
