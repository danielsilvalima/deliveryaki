<?php

namespace App\Http\Controllers\Categoria;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Response;
use App\Services\Empresa\EmpresaService;

class CategoriaController extends Controller
{
  public function index(Categoria $categoria)
  {
    $categorias = Categoria::where('empresa_id', '=', Auth::user()->empresa_id)->get();
    return view('content.categoria.index', [
      'categorias' => $categorias,
      'email' => Auth::user()->email
    ]);
  }

  public function create()
  {
    return view('content.categoria.create')->with(['email' => Auth::user()->email]);
  }

  /*public function store(Request $request, Categoria $categoria)
  {
    try {
      $data = $request->only('descricao', 'status');
      $data['empresa_id'] = Auth::user()->empresa_id;

      if (!$categoria->create($data)) {
        return back();
      }
      return redirect()->route('categoria.index')->with('success', 'CATEGORIA CADASTRADO COM SUCESSO');
    } catch (\Exception $e) {
      return back()->with('error', 'NÃO FOI POSSÍVEL CADASTRAR A CATEGORIA. ' . $e);
    }
  }*/

  public function edit(Request $request, string $id, Categoria $categoria)
  {
    try {
      if (!$categoria = $categoria->find($id)) {
        return back()->with('error', 'CATEGORIA NÃO FOI LOCALIZADA');
      }

      $categoria->update($request->only([
        'descricao',
        'status'
      ]));

      return redirect()->route('categoria.index')->with('success', 'CATEGORIA ATUALIZADO COM SUCESSO');
    } catch (\Exception $e) {
      return back()->with('error', 'CATEGORIA NÃO FOI ATUALIZADA. ' . $e);
    }
  }

  public function show(Categoria $categoria, string|int $id)
  {
    try {
      if (!$categoria = $categoria->where('id', $id)->where('empresa_id', Auth::user()->empresa_id)->first()) {
        return back()->with('error', 'CATEGORIA NÃO FOI LOCALIZADA');
      }

      return view('content.categoria.show', compact(('categoria')))->with(['email' => Auth::user()->email]);
    } catch (\Exception $e) {
      return back()->with('error', 'CATEGORIA NÃO FOI LOCALIZADA.' . $e);
    }
  }

  public function get(Request $request)
  {
    try {
      $empresa_id = $request->input('empresa_id');

      $limit = $request->input('limit', 10);
      $page = $request->input('page', 1);
      $query = Categoria::query();
      $query->where('empresa_id', $empresa_id);

      if (!is_null($request->input('categoria_id'))) {
        $query->where('id', $request->input('categoria_id'));
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
      $data = $request->input('categoria');

      $categoria = Categoria::create($data);

      return response()->json(['message' => 'Categoria cadastrada com sucesso.', 'categoria' => $categoria], Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function update(Request $request, string $id)
  {
    try {
      $data = $request->only(['descricao', 'empresa_id', 'status']);

      $categoria = Categoria::find($id);
      if (!$categoria) {
        return response()->json(['error' => 'Categoria não encontrada.'], Response::HTTP_NOT_FOUND);
      }

      $categoria->fill($data)->save();

      return response()->json(['message' => 'Categoria atualizada com sucesso.'], Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function updateStatus(Request $request, string $id, EmpresaService $empresaService)
  {
    try {
      $empresa_id = $request->input('empresa_id');
      $categoria_id = $request->input('categoria_id');

      $empresa = Empresa::find($empresa_id);
      if (!$empresa) {
        return response()->json(['error' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);
      }
      if ($empresaService->validaDataExpiracao($empresa)) {
        return response()->json(['error' => 'A empresa está expirada e não pode atualizar categorias.'], Response::HTTP_FORBIDDEN);
      }

      if (!$categoria = Categoria::where('id', $categoria_id)->where('empresa_id', $empresa_id)->first()) {
        return response()->json(['error' => 'Produto não encontrado.'], Response::HTTP_NOT_FOUND);
      }

      $categoria->status = $categoria->status === "D" ? "A" : "D";
      $categoria->save();

      return response()->json([
        ['message' => 'Categoria atualizado com sucesso.']
      ], Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }
}
