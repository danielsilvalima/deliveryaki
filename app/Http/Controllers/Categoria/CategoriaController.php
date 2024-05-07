<?php

namespace App\Http\Controllers\Categoria;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
      $data = $request->only('descricao', 'status');
      $data['empresa_id'] = Auth::user()->empresa_id;

      if (!$categoria->create($data)) {
          return back();
      }
      return redirect()->route('categoria.index');
  }

  public function edit(Request $request, string $id, Categoria $categoria)
    {
        if (!$categoria = $categoria->find($id)) {
            return back();
        }

        $categoria->update($request->only([
            'descricao', 'status'
        ]));

        return redirect()->route('categoria.index');
    }

    public function show(Categoria $categoria, string|int $id)
    {
        if (!$categoria = $categoria->where('id', $id)->where('empresa_id', Auth::user()->empresa_id)->first()) {
            return back();
        }

        return view('content.categoria.show', compact(('categoria')))->with(['email' => Auth::user()->email]);
    }
}
