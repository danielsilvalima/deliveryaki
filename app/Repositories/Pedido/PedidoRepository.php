<?php

namespace App\Repositories\Pedido;

use App\Models\Pedido;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Auth;

class PedidoRepository
{
  private $model;

  public function __construct(Pedido $model)
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
    $data['empresa_id'] = Auth::user()->empresa_id;

    return $this->model->create($data);
  }

  public function update(Request $request, string $id)
  {
    if (!$pedido = new Pedido($this->model->where('empresa_id', '=', Auth::user()->empresa_id)->get())) {
      return null;
    }

    return $pedido->update($request->only([
      'tipo_pagamento', 'vlr_taxa', 'vlr_total', 'delivered_at'
    ]));
  }
}
