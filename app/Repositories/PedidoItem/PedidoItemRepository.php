<?php

namespace App\Repositories\PedidoItem;

use App\Models\PedidoItem;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Auth;

class PedidoItemRepository
{
  private $model;

  public function __construct(PedidoItem $model)
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

  public function findByIDPedido(string $pedido_id)
  {
    return $this->model->select('produtos.descricao','produtos.vlr_unitario', 'pedido_items.qtd', 'pedido_items.vlr_total')
    ->where('pedido_id', '=', $pedido_id)->where('pedido_items.empresa_id', '=', Auth::user()->empresa_id)
    ->join('produtos', 'pedido_items.produto_id', '=', 'produtos.id')->get();
  }

  public function create(PedidoItem $model){
    return PedidoItem::create([
      "qtd" => $model["qtd"],
      "vlr_unitario" => $model["vlr_unitario"],
      "vlr_total" => $model["vlr_total"],
      "pedido_id" => $model["pedido_id"],
      "empresa_id" => $model["empresa_id"],
      "cliente_id" => $model["cliente_id"],
      "produto_id" => $model["produto_id"],
  ])->id;
}

  public function update(Request $request, string $id)
  {
    if (!$pedido = new PedidoItem($this->model->where('empresa_id', '=', Auth::user()->empresa_id)->get())) {
      return null;
    }

    return $pedido->update($request->only([
      'qtd', 'vlr_unitario', 'vlr_total', 'pedido_id', 'empresa_id', 'cliente_id'
    ]));
  }
}
