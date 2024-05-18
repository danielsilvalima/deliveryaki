<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
  public function show(User $user, string|int $id)
  {
      if (!$user = User::where('empresa_id', Auth::user()->empresa_id)->first()) {
          return back();
      }

      return view('content.usuario.show')->with([
          'email' => Auth::user()->email,
          'user' => $user
      ]);
  }
}
