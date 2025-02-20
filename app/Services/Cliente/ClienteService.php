<?php

namespace App\Services\Cliente;

use App\Models\Cliente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ClienteService
{
  public function findAll()
  {
    return Cliente::where('empresa_id', '=', Auth::user()->empresa_id)->get();
  }

  public function findByID(string $id)
  {
    return Cliente::where('id', '=', $id)
      ->where('empresa_id', '=', Auth::user()->empresa_id)
      ->get();
  }

  public function findByCelByEmpresaID(string $celular, string $empresa_id)
  {
    return Cliente::with('ceps', 'pedidos.pedido_items.produto.categoria')
      ->where('celular', '=', $celular)
      ->where('empresa_id', '=', $empresa_id)
      ->first();
  }

  /*public function store(Request $request)
  {
    $data = $request->only(
      'nome_completo',
      'cep',
      'logradouro',
      'numero',
      'complemento',
      'bairro',
      'cidade',
      'celular',
      'status'
    );
    //$data['empresa_id'] = Auth::user()->empresa_id;
    $data['empresa_id'] = 1;

    return Cliente::create($data);
  }*/

  public function create(Cliente $model)
  {
    DB::beginTransaction();
    try {
      $cliente = Cliente::create([
        'nome_completo' => $model['nome_completo'],
        'celular' => $model['celular'],
        'status' => $model['status'],
        'logradouro' => $model['logradouro'],
        'numero' => $model['numero'],
        'bairro' => $model['bairro'],
        'complemento' => $model['complemento'],
        'numero' => $model['numero'],
        'cidade' => $model['cidade'],
        'cep' => $model['cep'],
        'empresa_id' => $model['empresa_id'],
      ])->id;

      DB::commit();

      return $cliente;
    } catch (\Exception $e) {
      DB::rollBack();
      throw new \Exception('Erro ao criar o pedido: ' . $e->getMessage());
    }
  }

  public function update(Cliente $cliente)
  {
    DB::beginTransaction();
    try {
      /*$cli = $this->model->where('celular', '=', $cliente->celular)->first();
      //return $cliente;
      return $cli->update($cliente->only([
      'nome_completo', 'cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'celular', 'status'
      ]));*/
      $cli = Cliente::where('celular', '=', $cliente->celular)->first();

      $cli->update(
        $cliente->only([
          'nome_completo',
          'cep',
          'logradouro',
          'numero',
          'complemento',
          'bairro',
          'cidade',
          'celular',
          'status',
        ])
      );

      DB::commit();

      return $cli;
    } catch (\Exception $e) {
      DB::rollBack();
      throw new \Exception('Erro ao criar o pedido: ' . $e->getMessage());
    }
  }
}
