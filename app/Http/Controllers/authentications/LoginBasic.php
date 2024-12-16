<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\MessageBag;

class LoginBasic extends Controller
{
  public function index()
  {
    return view('content.authentications.auth-login-basic');
  }

  public function login(Request $request)
  {
    /*$request->validate([
      'email' => 'required|regex:/(.+)@(.+)\.(.+)/i|email|max:50',
      'password' => 'required'
    ], [
      'email.required' => __('E-mail é obrigatório'),
      'password.required' => __('Senha é obrigatório'),
    ]);*/

    $validator = Validator::make($request->all(), [
      'email' => 'required',
      'password' => 'required|min:3|max:50',
    ], [
      'email.required' => __('E-mail é obrigatório'),
      'email.email' => __('E-mail inválido'),
      'password.required' => __('Senha é obrigatório'),
    ]);

    if ($validator->fails()) {
      return redirect()->back()
        ->withErrors($validator)
        ->withInput();
    }

    if (!Auth::attempt($request->only('email', 'password'))) {
      //return redirect()->back();
      return redirect()->back()->withErrors(['login' => __('Credenciais inválidas')]);
    }
    return redirect()->route('dashboard-analytics');
  }

  public function logout(Request $request)
  {
    //$request->user()->currentAccessToken()->delete();
    return redirect()->route('auth-login-basic');
  }
}
