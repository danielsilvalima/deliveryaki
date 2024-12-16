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
use App\Services\Fcm\FcmService;

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

      return ResponseHelper::success('PEDIDO GERADO COM SUCESSO');
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }

  public function update(Request $request, $id, FcmService $fcmService)
    {
      try{
        // Encontrar o pedido pelo ID
        $pedido = Pedido::findOrFail($id);

        // Atualizar o status do pedido
        if($request->input('status') === 'S'){
          $teste = $fcmService->enviaPushNotification($id);
        }
        $pedido->status = $request->input('status');
        $pedido->save();

        // Redirecionar com mensagem de sucesso
        return redirect()->back()->with('success', 'PEDIDO ATUALIZADO COM SUCESSO');
      } catch (\Exception $e) {
        return back()->with('error', 'PEDIDO NÃO FOI ATUALIZADO. '.$e);
      }
    }
}
