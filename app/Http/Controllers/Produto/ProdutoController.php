<?php

namespace App\Http\Controllers\Produto;

use App\Http\Controllers\Controller;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseHelper;
use App\Services\Categoria\CategoriaService;

class ProdutoController extends Controller
{
    public function index(Produto $produto)
    {
        $produtos = Produto::select('produtos.id', 'produtos.descricao as produto', 'categorias.descricao as categoria', 'produtos.status', 'produtos.vlr_unitario')
            ->where('produtos.empresa_id', '=', Auth::user()->empresa_id)->join('categorias', 'produtos.categoria_id', '=', 'categorias.id')->get();

        return view('content.produto.index', [
            'produtos' => $produtos,
            'email' => Auth::user()->email
        ]);
    }

    public function create(CategoriaService $categoriaService)
    {
      try{
        $categorias = $categoriaService->findAllActiveByEmpresaID(Auth::user()->empresa_id);
        return view('content.produto.create')->with([
            'email' => Auth::user()->email,
            'categorias' => $categorias
        ]);
      } catch (\Exception $e) {
        return ResponseHelper::error($e->getMessage());
      }
    }

    public function store(Request $request, Produto $produto)
    {
      try{
        $data = $request->only('descricao', 'status', 'vlr_unitario', 'categoria_id');
        $data['empresa_id'] = Auth::user()->empresa_id;

        if (!$produto->create($data)) {
            return back();
        }
        return redirect()->route('produto.index');
      } catch (\Exception $e) {
        return ResponseHelper::error($e->getMessage());
      }
    }

    public function edit(Request $request, string $id, Produto $produto)
    {
      try{
        if (!$produto = Produto::where('id', '=', $id)->where('empresa_id', '=', Auth::user()->empresa_id)->first()) {
            return back();
        }

        $produto->update($request->only([
            'descricao', 'status', 'vlr_unitario', 'categoria_id'
        ]));

        return redirect()->route('produto.index');
      } catch (\Exception $e) {
        return ResponseHelper::error($e->getMessage());
      }
    }

    public function show(Produto $produto, string|int $id, CategoriaService $categoriaService)
    {
        if (!$produto = Produto::select('produtos.*', 'categorias.descricao as categorias')->where('produtos.id', $id)->where('produtos.empresa_id', Auth::user()->empresa_id)
            ->join('categorias', 'produtos.categoria_id', '=', 'categorias.id')->first()) {
            return back();
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
            return back();
        }

        return redirect()->route('produto.index')->with(['produto' => $produto]);
    }

    public function delete(string $id, Produto $produto)
    {
      try{
        if (!$produto = $produto->where('id', $id)->where('empresa_id', Auth::user()->empresa_id)) {
            $produtos[] = new Produto();
            return back();
        }

        $produto->delete();

        return redirect()->route('produto.index');
      } catch (\Exception $e) {
        return ResponseHelper::error($e->getMessage());
      }
    }
}
