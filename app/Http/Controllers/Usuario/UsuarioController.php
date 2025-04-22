<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\User;
use App\Services\Empresa\EmpresaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

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

  public function get(Request $request)
  {
    try {
      $empresa_id = $request->input('empresa_id');

      $limit = $request->input('limit', 10);
      $page = $request->input('page', 1);
      $query = User::query();
      $query->where('empresa_id', $empresa_id);

      if (!is_null($request->input('usuario_id'))) {
        $query->where('id', $request->input('usuario_id'));
      }

      $itensPaginados = $query->paginate($limit, ['*'], 'page', $page);

      return response()->json([
        'current_page' => $itensPaginados->currentPage(),
        'data' => $itensPaginados->items(),
        'total_pages' => $itensPaginados->lastPage(),
        'total' => $itensPaginados->total(),
        'per_page' => $itensPaginados->perPage()
      ], Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function store(Request $request)
  {
    try {
      $data = $request->input('usuario');

      $usuario = User::create($data);

      return response()->json(['message' => 'Usuário cadastrada com sucesso.', 'usuario' => $usuario], Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function update(Request $request, string $id)
  {
    try {
      $data = $request->only(['name', 'email', 'empresa_id', 'status']);

      $usuario = User::find($id);
      if (!$usuario) {
        return response()->json(['error' => 'Usuário não encontrado.'], Response::HTTP_NOT_FOUND);
      }

      $usuario->name = $data['name'];
      $usuario->email = $data['email'];
      $usuario->status = $data['status'];
      $usuario->save();

      return response()->json(['message' => 'Usuário atualizada com sucesso.'], Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function updateStatus(Request $request, string $id, EmpresaService $empresaService)
  {
    try {
      $empresa_id = $request->input('empresa_id');
      $usuario_id = $request->input('usuario_id');

      $empresa = Empresa::find($empresa_id);
      if (!$empresa) {
        return response()->json(['error' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);
      }
      if ($empresaService->validaDataExpiracao($empresa)) {
        return response()->json(['error' => 'A empresa está expirada e não pode atualizar categorias.'], Response::HTTP_FORBIDDEN);
      }

      if (!$usuario = User::where('id', $usuario_id)->where('empresa_id', $empresa_id)->first()) {
        return response()->json(['error' => 'Produto não encontrado.'], Response::HTTP_NOT_FOUND);
      }

      $usuario->status = $usuario->status === "D" ? "A" : "D";
      $usuario->save();

      return response()->json(
        [
          'message' => 'Usuário atualizado com sucesso.'
        ],
        Response::HTTP_OK
      );
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function updatePassword(Request $request, string $id, EmpresaService $empresaService)
  {
    try {
      $empresa_id = $request->input('empresa_id');
      $usuario_id = $request->input('usuario_id');
      $nova_senha = $request->input('nova_senha');
      $senha_atual = $request->input('senha_atual');

      $empresa = Empresa::find($empresa_id);
      if (!$empresa) {
        return response()->json(['error' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);
      }
      if ($empresaService->validaDataExpiracao($empresa)) {
        return response()->json(['error' => 'A empresa está expirada e não pode atualizar categorias.'], Response::HTTP_FORBIDDEN);
      }

      if (!$usuario = User::where('id', $usuario_id)->where('empresa_id', $empresa_id)->first()) {
        return response()->json(['error' => 'Usuário não encontrado.'], Response::HTTP_NOT_FOUND);
      }

      if (!Hash::check($senha_atual, $usuario->password)) {
        return response()->json(['error' => 'Senha atual incorreta.'], Response::HTTP_UNAUTHORIZED);
      }

      if (Hash::check($nova_senha, $usuario->password)) {
        return response()->json(['error' => 'A nova senha deve ser diferente da senha atual.'], Response::HTTP_BAD_REQUEST);
      }

      $usuario->password = $nova_senha;
      $usuario->save();

      return response()->json([
        'message' => 'Senha alterada com sucesso.'
      ], Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }
}
