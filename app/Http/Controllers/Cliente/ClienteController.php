<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClienteController extends Controller
{
  public function index(Cliente $cliente)
  {
    $clientes = Cliente::where('empresa_id', '=', Auth::user()->empresa_id)->get();
    return view('content.cliente.index', [
      'clientes' => $clientes,
      'email' => Auth::user()->email
    ]);
  }

  public function create()
  {
      return view('content.cliente.create')->with(['email' => Auth::user()->email]);
  }

  public function store(Request $request, Cliente $cliente)
  {
      $data = $request->only('nome_completo', 'cep', 'logradouro', 'numero', 'complemento', 'bairro',
      'cidade', 'celular', 'status');
      $data['empresa_id'] = Auth::user()->empresa_id;

      if (!$cliente->create($data)) {
          return back();
      }
      return redirect()->route('cliente.index');
  }

  public function edit(Request $request, string $id, Cliente $cliente)
    {
        if (!$cliente = $cliente->find($id)) {
            return back();
        }

        $cliente->update($request->only([
            'nome_completo', 'cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'celular', 'status'
        ]));

        return redirect()->route('cliente.index');
    }

    public function show(Cliente $cliente, string|int $id)
    {
        if (!$cliente = $cliente->where('id', $id)->where('empresa_id', Auth::user()->empresa_id)->first()) {
            return back();
        }

        return view('content.cliente.show', compact(('cliente')))->with(['email' => Auth::user()->email]);
    }
}
