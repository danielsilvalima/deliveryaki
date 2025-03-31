<?php

namespace App\Http\Controllers\Produto;

use App\Http\Controllers\Controller;
use App\Models\Produto;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseHelper;
use App\Services\Categoria\CategoriaService;
use App\Services\Produto\ProdutoService;
use App\Services\Empresa\EmpresaService;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProdutoController extends Controller
{
  public function index(Produto $produto)
  {
    $produtos = Produto::select(
      'produtos.id',
      'produtos.descricao as produto',
      'categorias.descricao as categoria',
      'produtos.status',
      'produtos.vlr_unitario',
      'produtos.apresentacao'
    )
      ->where('produtos.empresa_id', '=', Auth::user()->empresa_id)->join('categorias', 'produtos.categoria_id', '=', 'categorias.id')->get();

    return view('content.produto.index', [
      'produtos' => $produtos,
      'email' => Auth::user()->email
    ]);
  }

  public function create(CategoriaService $categoriaService)
  {
    try {
      $categorias = $categoriaService->findAllActiveByEmpresaID(Auth::user()->empresa_id);
      return view('content.produto.create')->with([
        'email' => Auth::user()->email,
        'categorias' => $categorias
      ]);
    } catch (\Exception $e) {
      return back()->with('error', 'NÃO FOI POSSÍVEL CADASTRAR O PRODUTO. ' . $e);
    }
  }

  /*public function store(Request $request, Produto $produto)
  {
    try {
      $data = $request->only('descricao', 'status', 'vlr_unitario', 'categoria_id', 'apresentacao');
      $data['empresa_id'] = Auth::user()->empresa_id;

      if (!$produto->create($data)) {
        return back()->with('error', 'NÃO FOI POSSÍVEL CADASTRAR O PRODUTO');
      }
      return redirect()->route('produto.index')->with('success', 'PRODUTO CADASTRADO COM SUCESSO');
    } catch (\Exception $e) {
      return back()->with('error', 'NÃO FOI POSSÍVEL CADASTRAR O PRODUTO. ' . $e);
    }
  }*/

  /*public function edit(Request $request, string $id, Produto $produto, ProdutoService $produtoService, EmpresaService $empresaService)
  {
    try {
      $request->validate([
        'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // Máximo 5MB
      ]);
      if (!$produto = Produto::where('id', '=', $id)->where('empresa_id', '=', Auth::user()->empresa_id)->first()) {
        return back()->with('error', 'NÃO FOI POSSÍVEL LOCALIZAR O PRODUTO');
      }

      $produto = $produtoService->update($request, $produto, $empresaService);

      return redirect()->route('produto.index')->with('success', 'PRODUTO ATUALIZADO COM SUCESSO');
    } catch (\Exception $e) {
      return back()->with('error', 'NÃO FOI POSSÍVEL ATUALIZAR O PRODUTO. ' . $e);
    }
  }*/

  public function show(Produto $produto, string|int $id, CategoriaService $categoriaService)
  {
    if (!$produto = Produto::select('produtos.*', 'categorias.descricao as categorias')->where('produtos.id', $id)->where('produtos.empresa_id', Auth::user()->empresa_id)
      ->join('categorias', 'produtos.categoria_id', '=', 'categorias.id')->first()) {
      return back()->with('error', 'NÃO FOI POSSÍVEL LOCALIZAR O PRODUTO');
    }

    $categorias = $categoriaService->findAllActiveByEmpresaID(Auth::user()->empresa_id);
    return view('content.produto.show')->with([
      'email' => Auth::user()->email,
      'categorias' => $categorias,
      'produto' => $produto
    ]);
  }

  public function modal(string $id, Produto $produto)
  {
    if (!$produto = $produto->where('id', $id)->where('empresa_id', Auth::user()->empresa_id)) {
      return back()->with('error', 'NÃO FOI POSSÍVEL LOCALIZAR O PRODUTO');
    }

    return redirect()->route('produto.index')->with(['produto' => $produto]);
  }

  /*public function delete(string $id, Produto $produto)
  {
    try {
      if (!$produto = $produto->where('id', $id)->where('empresa_id', Auth::user()->empresa_id)) {
        $produtos[] = new Produto();
        return back()->with('error', 'NÃO FOI POSSÍVEL EXCLUIR O PRODUTO');
      }

      $produto->delete();

      return redirect()->route('produto.index')->with('success', 'PRODUTO EXCLUIDO COM SUCESSO');
    } catch (\Exception $e) {
      return back()->with('error', 'NÃO FOI POSSÍVEL EXCLUIR O PRODUTO. ' . $e);
    }
  }*/

  public function deleteLogo(string $id, Produto $produto, ProdutoService $produtoService)
  {
    $produto = Produto::findOrFail($id);

    if ($produto->path) {
      $produtoService->deleteOldFile($produto->id);
      $produto->path = null;
      $produto->save();
      return response()->json(['success' => true, 'message' => 'LOGO REMOVIDO COM SUCESSO, NÃO É NECESSÁRIO SALVAR O CADASTRO']);
    } else {
      return response()->json(['success' => true, 'message' => 'NÃO HÁ LOGO PARA SER REMOVIDO']);
    }
  }

  public function store(Request $request, EmpresaService $empresaService, ProdutoService $produtoService)
  {
    try {
      $empresa_id = $request->empresa_id;
      $data = $request->only('descricao', 'status', 'vlr_unitario', 'categoria_id', 'apresentacao', 'empresa_id');

      $empresa = Empresa::find($empresa_id);
      if (!$empresa) {
        return response()->json(['error' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);
      }
      if ($empresaService->validaDataExpiracao($empresa)) {
        return response()->json(['error' => 'A empresa está expirada e não pode cadastrar produtos.'], Response::HTTP_FORBIDDEN);
      }
      $data['vlr_unitario'] = Str::replace(',', '.', $data['vlr_unitario']);

      $produto = $produtoService->store($data, $request, $empresa);

      return response()->json(['message' => 'Produto cadastrado com sucesso.'], Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function update(Request $request, EmpresaService $empresaService, ProdutoService $produtoService)
  {
    try {
      $empresa_id = $request->empresa_id;
      $produto_id = $request->id;

      $empresa = Empresa::find($empresa_id);
      if (!$empresa) {
        return response()->json(['error' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);
      }
      if ($empresaService->validaDataExpiracao($empresa)) {
        return response()->json(['error' => 'A empresa está expirada e não pode cadastrar produtos.'], Response::HTTP_FORBIDDEN);
      }

      if (!$produto = Produto::where('id', $produto_id)->where('empresa_id', $empresa->id)->first()) {
        return response()->json(['error' => 'Produto não encontrado.'], Response::HTTP_NOT_FOUND);
      }

      $request->merge([
        'vlr_unitario' => Str::replace(',', '.', $request->vlr_unitario)
      ]);

      $produto_db = $produtoService->update($request, $produto, $empresa);

      return response()->json(['message' => 'Produto atualizado com sucesso.', 'produto' => $produto_db], Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function get(Request $request)
  {
    try {
      $empresa_id = $request->input('empresa_id');
      $limit = $request->input('limit', 10);
      $page = $request->input('page', 1);
      $query = Produto::query();
      $query->where('empresa_id', $empresa_id);

      if (!is_null($request->input('produto_id'))) {
        $query->where('id', $request->input('produto_id'));
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

  public function updateStatus(Request $request, string $id, EmpresaService $empresaService)
  {
    try {
      $empresa_id = $request->input('empresa_id');
      $produto_id = $request->input('produto_id');

      $empresa = Empresa::find($empresa_id);
      if (!$empresa) {
        return response()->json(['error' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);
      }
      if ($empresaService->validaDataExpiracao($empresa)) {
        return response()->json(['error' => 'A empresa está expirada e não pode atualizar produtos.'], Response::HTTP_FORBIDDEN);
      }

      if (!$produto = Produto::where('id', $produto_id)->where('empresa_id', $empresa_id)->first()) {
        return response()->json(['error' => 'Produto não encontrado.'], Response::HTTP_NOT_FOUND);
      }

      $produto->status = $produto->status === "D" ? "A" : "D";
      $produto->save();

      return response()->json([
        ['message' => 'Produto atualizado com sucesso.']
      ], Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }
}
