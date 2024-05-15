<?php

namespace App\Repositories\Cliente;

use App\Models\Cliente;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Auth;

class ClienteRepository
{
  private $model;

  public function __construct(Cliente $model)
  {
    $this->model = $model;
  }

  public function findAll()
  {
    return $this->model->where('empresa_id', '=', Auth::user()->empresa_id)->get();
  }

  public function findByID(string $id)
  {
    return $this->model->where('id', '=', $id)->where('empresa_id', '=', Auth::user()->empresa_id)->get();
  }

  public function findByCel(string $celular)
  {
    return $this->model->where('celular', '=', $celular)->first();
  }

  public function store(Request $request)
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

    return $this->model->create($data);
  }

  public function create(Cliente $model){
      return Cliente::create([
        "nome_completo" => $model["nome_completo"],
        "celular" => $model["celular"],
        "status" => $model["status"],
        "logradouro" => $model["logradouro"],
        "numero" => $model["numero"],
        "bairro" => $model["bairro"],
        "complemento" => $model["complemento"],
        "numero" => $model["numero"],
        "cidade" => $model["cidade"],
        "cep" => $model["cep"],
        "empresa_id" => $model["empresa_id"],
    ])->id;
  }

  /*public function update(Request $request, string $id)
  {
    if (!$pedido = new Pedido($this->model->where('empresa_id', '=', Auth::user()->empresa_id)->get())) {
      return null;
    }

    return $pedido->update($request->only([
      'tipo_pagamento', 'vlr_taxa', 'vlr_total', 'delivered_at'
    ]));
  }*/
}
