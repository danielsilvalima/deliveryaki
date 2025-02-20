<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Cep;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Services\Cliente\ClienteService;
use App\Services\Empresa\EmpresaService;
use App\Helpers\ResponseHelper;

class ClienteController extends Controller
{
  private $header = [
    //'Content-Type' => 'text/html; charset=UTF-8',
    'Content-Type' => 'application/json; charset=UTF-8',
    'charset' => 'utf-8',
  ];
  private $options = JSON_UNESCAPED_UNICODE;

  public function index(Cliente $cliente)
  {
    $clientes = Cliente::select(
      'clientes.id as id',
      'clientes.nome_completo as nome_completo',
      'clientes.status as status'
    )
      ->where('empresa_id', '=', Auth::user()->empresa_id)
      ->leftJoin('ceps', 'clientes.cep_id', '=', 'ceps.id')
      ->orderBy('clientes.id', 'ASC')
      ->get();

    return view('content.cliente.index', [
      'clientes' => $clientes,
      'email' => Auth::user()->email,
    ]);
  }

  public function create()
  {
    return view('content.cliente.create')->with(['email' => Auth::user()->email]);
  }

  public function store(Request $request, Cliente $cliente)
  {
    try {
      $dataCep = $request->only('cep', 'logradouro', 'complemento', 'bairro', 'cidade', 'uf');
      $data = $request->only('nome_completo', 'cep', 'numero', 'celular', 'status');
      $data['empresa_id'] = Auth::user()->empresa_id;

      $cep = Cep::firstOrCreate(
        ['cep' => $dataCep['cep']],
        [
          'logradouro' => $dataCep['logradouro'],
          'bairro' => $dataCep['bairro'],
          'complemento' => $dataCep['complemento'],
          'cidade' => $dataCep['cidade'],
          'uf' => $dataCep['uf'],
        ]
      );
      $data['cep_id'] = $cep->id;

      if (!$cliente->create($data)) {
        return back()->with('error', 'NÃO FOI POSSÍVEL CADASTRAR O CLIENTE');
      }
      return redirect()
        ->route('cliente.index')
        ->with('success', 'CLIENTE CADASTRADO COM SUCESSO');
    } catch (\Exception $e) {
      return back()->with('error', 'NÃO FOI POSSÍVEL CADASTRAR O CLIENTE. ' . $e);
    }
  }

  public function edit(Request $request, string $id, Cliente $cliente)
  {
    try {
      // Verificar se o cliente existe
      if (!($cliente = $cliente->find($id))) {
        return back()->with('error', 'NÃO FOI POSSÍVEL LOCALIZAR O CLIENTE');
      }

      // Extrair dados do CEP
      $dataCep = $request->only('cep', 'logradouro', 'complemento', 'bairro', 'cidade', 'uf');
      if (!empty($dataCep['cep'])) {
        $cep = Cep::firstOrCreate(
          ['cep' => $dataCep['cep']],
          [
            'logradouro' => $dataCep['logradouro'],
            'bairro' => $dataCep['bairro'],
            'complemento' => $dataCep['complemento'],
            'cidade' => $dataCep['cidade'],
            'uf' => $dataCep['uf'],
          ]
        );

        // Associar o 'cep_id' ao cliente
        $request->merge(['cep_id' => $cep->id]);
      }

      $cliente->update($request->only(['nome_completo', 'cep', 'numero', 'celular', 'status', 'cep_id']));

      return redirect()
        ->route('cliente.index')
        ->with('success', 'CLIENTE ATUALIZADO COM SUCESSO');
    } catch (\Exception $e) {
      return back()->with('error', 'NÃO FOI POSSÍVEL ATUALIZAR O CLIENTE. ' . $e);
    }
  }

  public function show(Cliente $cliente, string|int $id)
  {
    $cliente = Cliente::select(
      'clientes.*',
      'ceps.logradouro',
      'ceps.bairro',
      'ceps.complemento',
      'ceps.cidade',
      'ceps.uf'
    )
      ->leftJoin('ceps', 'clientes.cep_id', '=', 'ceps.id')
      ->where('clientes.id', $id)
      ->where('clientes.empresa_id', Auth::user()->empresa_id)
      ->first();

    if (!$cliente) {
      return back()->with('error', 'NÃO FOI POSSÍVEL LOCALIZAR O CLIENTE');
    }

    return view('content.cliente.show', compact('cliente'))->with(['email' => Auth::user()->email]);
  }

  public function get(Request $request, string $id, ClienteService $clienteService, EmpresaService $empresaService)
  {
    try {
      if (!($empresa = $empresaService->findByHash($id))) {
        return ResponseHelper::notFound('EMPRESA NÃO ENCONTRADA');
      }

      $cliente = $clienteService->findByCelByEmpresaID($request->celular, $empresa->id);

      return response()->json($cliente, Response::HTTP_OK, $this->header, $this->options);
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }
}
