<?php

namespace App\Http\Controllers\Categoria;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseHelper;

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

  public function store(Request $request, Categoria $categoria)
  {
    try{
      $data = $request->only('descricao', 'status');
      $data['empresa_id'] = Auth::user()->empresa_id;

      if (!$categoria->create($data)) {
          return back();
      }
      return redirect()->route('categoria.index')->with('success', 'CATEGORIA CADASTRADO COM SUCESSO');
    } catch (\Exception $e) {
      return back()->with('error', 'NÃO FOI POSSÍVEL CADASTRAR A CATEGORIA. '.$e);
    }
  }

  public function edit(Request $request, string $id, Categoria $categoria)
  {
    try{
      if (!$categoria = $categoria->find($id)) {
          return back()->with('error', 'CATEGORIA NÃO FOI LOCALIZADA');
      }

      $categoria->update($request->only([
          'descricao', 'status'
      ]));

      return redirect()->route('categoria.index')->with('success', 'CATEGORIA ATUALIZADO COM SUCESSO');
    } catch (\Exception $e) {
      return back()->with('error', 'CATEGORIA NÃO FOI ATUALIZADA. '.$e);
    }
  }

  public function show(Categoria $categoria, string|int $id)
  {
    try{
      if (!$categoria = $categoria->where('id', $id)->where('empresa_id', Auth::user()->empresa_id)->first()) {
          return back()->with('error', 'CATEGORIA NÃO FOI LOCALIZADA');
      }

      return view('content.categoria.show', compact(('categoria')))->with(['email' => Auth::user()->email]);
    } catch (\Exception $e) {
      return back()->with('error', 'CATEGORIA NÃO FOI LOCALIZADA.'.$e);
    }
  }
}
