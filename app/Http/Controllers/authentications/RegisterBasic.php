<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RegisterBasic extends Controller
{

  public function index()
  {
    return view('content.authentications.auth-register-basic');
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'email' => 'required|email|unique:users',
      'cnpj' => 'required|string|unique:empresas',
      'razao_social' => 'required|string|unique:empresas',
      'password' => 'required|min:3|max:50',
    ], [
      'email.required' => __('E-mail é obrigatório'),
      'cnpj.unique' => __('CNPJ já está cadastrado'),
      'cnpj.required' => __('CNPJ é obrigatório'),
      'razao_social.unique' => __('Razão Social já está cadastrado'),
      'razao_social.required' => __('Razão Social é obrigatório'),
      'email.unique' => __('E-mail já está cadastrado'),
      'password.required' => __('Senha é obrigatório'),
    ]);

    if ($validator->fails()) {
      return redirect()->back()
        ->withErrors($validator)
        ->withInput();
    }

    DB::beginTransaction();

    $empresa = $request->only('razao_social', 'cnpj', 'celular');
    $empresa['status'] = 'A';
    if ($empresa_id = Empresa::create($empresa)->id) {

      $usuario = $request->only('email', 'password');
      $usuario['empresa_id'] = $empresa_id;

      if (!User::create($usuario)) {
        DB::rollBack();
      } else {
        DB::commit();
        if (!Auth::attempt($request->only('email', 'password'))) {
          return redirect()->back();
        }

        $request->user()->createToken('invoice');
        return redirect()->route('dashboard-analytics');
      }
    }

    return back();
  }
}
