<?php

namespace App\Http\Controllers\Pedido;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseHelper;
use App\Services\Pedido\PedidoService;
use App\Services\PedidoItem\PedidoItemService;

class PedidoController extends Controller
{
  public function index(Pedido $pedido, PedidoItemService $pedidoItemService)
  {
    $pedidos = Pedido::select('pedidos.*', 'clientes.nome_completo', 'ceps.logradouro', 'clientes.numero', 'ceps.bairro')->where('pedidos.empresa_id', Auth::user()->empresa_id)
    ->join('clientes', 'pedidos.cliente_id', '=', 'clientes.id')
    ->leftJoin('ceps', 'clientes.cep_id', '=', 'ceps.id')
    ->orderBy('id', 'ASC')->get();

    foreach ($pedidos as $pedido) {
      $itens = $pedidoItemService->findByIDPedido($pedido->id);

      // Adiciona os itens ao pedido
      $pedido->itens = $itens;
    }
    return view('content.pedido.index', [
      'pedidos' => $pedidos,
      'email' => Auth::user()->email
    ]);
  }

  public function show(Pedido $pedido, string|int $id)
    {
        if (!$pedido = Pedido::select('pedidos.*', 'clientes.nome_completo as nome_completo')->where('produtos.id', $id)->where('produtos.empresa_id', Auth::user()->empresa_id)
            ->join('clientes', 'pedidos.cliente_id', '=', 'clientes.id')->first()) {
            return back()->with('error', 'NÃO FOI POSSÍVEL LOCALIZAR O PEDIDO');
        }

        return view('content.pedido.show')->with([
            'email' => Auth::user()->email,
            'pedido' => $pedido
        ]);
    }

  public function post(Request $request, string $id, PedidoService $pedidoService)
  {
    try {
      if(!$empresa = Empresa::where('hash', $id)->first()){
        return ResponseHelper::notFound('EMPRESA NÃO ENCONTRADA');
      }

      $cliente = $request->cliente;
      $cliente['empresa_id'] = $empresa->id;
      $entrega = $request->entrega;
      $entrega['empresa_id'] = $empresa->id;

      $pedido = $pedidoService->createPedido(
        $cliente,
        $entrega,
        $request->pedido
      );

      //CADASTRA O CLIENTE SE NECESSARIO
      //DB::beginTransaction();

      /*if(!$cliente = Cliente::where('celular', $request->cliente['celular'])->first()){
        $cliente = $this->getObjCliente($request->cliente);
        $cliente->empresa_id = $empresa->id;
        if(!$cliente->id = $this->clienteRepository->create($cliente)){
          DB::rollBack();
          /*return response()->json([
            'message' => 'NÃO FOI POSSÍVEL GRAVAR O PEDIDO'
          ], Response::HTTP_INTERNAL_SERVER_ERROR,
          $this->header,
          $this->options);*/
    /*      return ResponseHelper::error('NÃO FOI POSSÍVEL GRAVAR O PEDIDO');
        }
      }else{
        $cliente = $this->getObjCliente($request->cliente);
        if(!$cliente = $this->clienteRepository->update($cliente)){
          DB::rollBack();
          /*return response()->json([
            'message' => 'NÃO FOI POSSÍVEL ATUALIZAR O CLIENTE'
          ], Response::HTTP_INTERNAL_SERVER_ERROR,
          $this->header,
          $this->options);*/
   /*       return ResponseHelper::error('NÃO FOI POSSÍVEL ATUALIZAR O CLIENTE');
        }
      }

      //GERA O PEDIDO
      /*$pedido = new Pedido([
        "status" => "A",
        "tipo_pagamento" => strtoupper($request->entrega["tipo_pagamento"]),
        "tipo_entrega" => strtoupper($request->entrega["tipo_entrega"]),
        "vlr_taxa" => $request->entrega["valor_taxa"],
        "vlr_total" => $request->entrega["valor_total"],
        "deliver_at" => $request->entrega["horario_entrega"],
        "empresa_id" => $empresa->id,
        "cliente_id" => $cliente->id,
      ]);*/
  /*    $pedido = $this->getObjPedido($request->entrega);
      $pedido->empresa_id = $empresa->id;
      $pedido->cliente_id = $cliente->id;
      if(!$pedido->id = $this->pedidoRepository->create($pedido)){
        DB::rollBack();
        /*return response()->json([
          'message' => 'NÃO FOI POSSÍVEL GRAVAR O PEDIDO'
        ], Response::HTTP_INTERNAL_SERVER_ERROR,
        $this->header,
        $this->options);*/
  /*      return ResponseHelper::error('NÃO FOI POSSÍVEL GRAVAR O PEDIDO');
      }

      //GERA OS ITENS DO PEDIDO
      foreach ($request->pedido as $item) {
        /*$valorUnitario = str_replace(',', '.', $item['valor']);
        /*$pedido_item = new PedidoItem([
          "produto_id" => intval($item["id"]),
          "qtd" => $item["qtd"],
          "vlr_unitario" => floatval($valorUnitario),
          "vlr_total" => $item["qtd"] * $valorUnitario,
          "pedido_id" => $pedido->id,
          "empresa_id" => $empresa->id,
          "cliente_id" => $cliente->id,
        ]);*/
  /*      $pedido_item = $this->getObjPedidoItens($item);
        $pedido_item->pedido_id = $pedido->id;
        $pedido_item->empresa_id = $empresa->id;
        $pedido_item->cliente_id = $cliente->id;

        if(!$pedido_item->id = $this->pedidoItemRepository->create($pedido_item)){
          DB::rollBack();
          /*return response()->json([
            'message' => 'NÃO FOI POSSÍVEL GRAVAR O PEDIDO'
          ], Response::HTTP_INTERNAL_SERVER_ERROR,
          $this->header,
          $this->options);*/
  /*        return ResponseHelper::error('NÃO FOI POSSÍVEL GRAVAR O PEDIDO');
        }
      }

      DB::commit();

      /*return response()->json(
        [
          'sucesso' => 'PEDIDO GERADO COM SUCESSO',
        ],
        Response::HTTP_OK,
        $this->header,
        $this->options
      );*/
      return ResponseHelper::success('PEDIDO GERADO COM SUCESSO');
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }

  public function update(Request $request, $id)
    {
      try{
        // Encontrar o pedido pelo ID
        $pedido = Pedido::findOrFail($id);

        // Atualizar o status do pedido
        $pedido->status = $request->input('status');
        $pedido->save();

        // Redirecionar com mensagem de sucesso
        return redirect()->back()->with('success', 'PEDIDO ATUALIZADO COM SUCESSO');
      } catch (\Exception $e) {
        return back()->with('error', 'PEDIDO NÃO FOI ATUALIZADO. '.$e);
      }
    }
}
