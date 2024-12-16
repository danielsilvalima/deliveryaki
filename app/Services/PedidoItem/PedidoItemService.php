<?php

namespace App\Services\PedidoItem;

use App\Models\PedidoItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PedidoItemService
{

  public function findAll()
  {
    return PedidoItem::where('empresa_id', '=', Auth::user()->empresa_id)->get();
  }

  public function findByID(string $id)
  {
    return PedidoItem::where('id', '=', $id)->where('empresa_id', '=', Auth::user()->empresa_id)->get();
  }

  public function findByIDPedido(string $pedido_id)
  {
    return PedidoItem::select('produtos.descricao','produtos.vlr_unitario', 'pedido_items.qtd', 'pedido_items.vlr_total')
    ->where('pedido_id', '=', $pedido_id)->where('pedido_items.empresa_id', '=', Auth::user()->empresa_id)
    ->join('produtos', 'pedido_items.produto_id', '=', 'produtos.id')->get();
  }

  public function create(PedidoItem $model){
    DB::beginTransaction();
    try{
        $pedido_item = PedidoItem::create([
        "qtd" => $model["qtd"],
        "vlr_unitario" => $model["vlr_unitario"],
        "vlr_total" => $model["vlr_total"],
        "pedido_id" => $model["pedido_id"],
        "empresa_id" => $model["empresa_id"],
        "cliente_id" => $model["cliente_id"],
        "produto_id" => $model["produto_id"],
      ])->id;
      DB::commit();

      return $pedido_item;
    } catch (\Exception $e) {
      DB::rollBack();
      throw new \Exception('Não foi possível gravar os itens do pedido');
    }
  }

  /*public function update(Request $request, string $id)
  {
    if (!$pedido = new PedidoItem(PedidoItem::where('empresa_id', '=', Auth::user()->empresa_id)->get())) {
      return null;
    }

    return $pedido->update($request->only([
      'qtd', 'vlr_unitario', 'vlr_total', 'pedido_id', 'empresa_id', 'cliente_id'
    ]));
  }*/
}
