<?php

namespace App\Http\Controllers\Empresa;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\HashGenerator;

class EmpresaController extends Controller
{
  public function index(Empresa $empresa)
  {
    $empresas = Empresa::select('*')->where('id', Auth::user()->empresa_id)->get();
    return view('content.empresa.index', [
      'empresas' => $empresas,
      'email' => Auth::user()->email
    ]);
  }

  public function create()
  {
    return view('content.empresa.create')->with(['email' => Auth::user()->email]);
  }

  public function store(Request $request, Empresa $empresa)
  {
    $data = $request->post();

    do {
      $data['hash'] = HashGenerator::generateUniqueHash8Caracter();
    } while ($empresa->where('hash', $data['hash'])->exists());

    $empresa->create($data);

    return redirect()->route('empresa.index');
  }

  public function edit(Request $request, string $id, Empresa $empresa)
  {
    if (!$empresa = $empresa->find($id)) {
      return back();
    }

    $empresa->update($request->only([
      'cnpj', 'razao_social', 'telefone', 'celular', 'email', 'status'
    ]));

    return redirect()->route('empresa.index');
  }

  public function show(Empresa $empresa, string|int $id)
  {
    if (!$empresa = $empresa->where('id', $id)->where('id', Auth::user()->empresa_id)->first()) {
      return back();
    }

    return view('content.empresa.show', compact(('empresa')))->with(['email' => Auth::user()->email]);
  }

  public function modal(string $id, Empresa $empresa)
  {
    if (!$empresa = $empresa->where('id', $id)->where('id', Auth::user()->empresa_id)) {
      return back();
    }

    return redirect()->route('empresa.index')->with(['empresa' => $empresa]);
  }

  public function delete(string $id, Empresa $empresa)
  {
    if (!$empresa = $empresa->where('id', $id)->where('id', Auth::user()->empresa_id)) {
      return back();
    }

    $empresa->delete();

    return redirect()->route('empresa.index');
  }
}
