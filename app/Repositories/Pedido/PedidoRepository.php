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

  public function create(Pedido $model){
    return Pedido::create([
      "status" => $model["status"],
      "tipo_pagamento" => $model["tipo_pagamento"],
      "tipo_entrega" => $model["tipo_entrega"],
      "vlr_taxa" => $model["vlr_taxa"],
      "vlr_total" => $model["vlr_total"],
      "deliver_at" => $model["deliver_at"],
      "empresa_id" => $model["empresa_id"],
      "cliente_id" => $model["cliente_id"],
  ])->id;
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
