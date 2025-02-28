<?php

namespace App\Http\Controllers\Pedido;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseHelper;
use App\Services\Pedido\PedidoService;
use App\Services\Produto\ProdutoService;
use App\Services\Fcm\FcmService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PedidoController extends Controller
{
  public function index(Pedido $pedido, PedidoService $pedidoService)
  {
    $data_inicio = Carbon::now()->startOfMonth()->toDateString(); // Primeiro dia do mês
    $data_fim = Carbon::now()->toDateString(); // Data atual


    $pedidos = $pedidoService->buscaPedidosPorData($data_fim, $data_fim);

    return view('content.pedido.index', [
      'pedidos' => $pedidos,
      'email' => Auth::user()->email,
      'data_inicio' => $data_fim,
      'data_fim' => $data_fim,
      'tipo_entrega' => 'E',
      'status' => 'A'
    ]);
  }

  public function postPedido(Request $request, PedidoService $pedidoService)
  {
    try {
      if ($request->data_inicio === null || $request->data_fim === null) {
        return back()->with('error', 'PREENCHA O CAMPO DE DATA INICIAL E FINAL');
      }

      $pedidos = $pedidoService->buscaPedidosPorData($request->data_inicio, $request->data_fim, $request->tipo_entrega, $request->status);

      return view('content.pedido.index', [
        'pedidos' => $pedidos,
        'email' => Auth::user()->email,
        'data_inicio' => $request->data_inicio,
        'data_fim' => $request->data_fim,
        'tipo_entrega' => $request->tipo_entrega,
        'status' => $request->status
      ]);
    } catch (\Exception $e) {
      return back()->with('error', 'NÃO FOI POSSÍVEL PESQUISAR. ' . $e->getMessage());
    }
  }

  public function show(string|int $id, PedidoService $pedidoService, ProdutoService $produtoService)
  {
    try {

      if ($id === null) {
        return back()->with('error', 'ID É OBRIGATÓRIO');
      }

      $pedido = $pedidoService->buscaPedidosPorID($id);

      if (!$pedido) {
        return back()->with('error', 'O PEDIDO NÃO FOI LOCALIZADO');
      }

      $produtos = $produtoService->findAllProductActiveByEmpresaID($pedido->empresa_id);

      return view('content.pedido.show')->with([
        'email' => Auth::user()->email,
        'pedido' => $pedido,
        'produtos' => $produtos->produtos
      ]);
    } catch (\Exception $e) {
      return back()->with('error', 'NÃO FOI POSSÍVEL ATUALIZAR O PEDIDO. ' . $e->getMessage());
    }
  }

  public function post(Request $request, string $id, PedidoService $pedidoService)
  {
    try {
      if (!($empresa = Empresa::where('hash', $id)->first())) {
        return ResponseHelper::notFound('EMPRESA NÃO ENCONTRADA');
      }

      $cliente = $request->cliente;
      $cliente['empresa_id'] = $empresa->id;
      $entrega = $request->entrega;
      $entrega['empresa_id'] = $empresa->id;

      $pedido = $pedidoService->createPedido($cliente, $entrega, $request->pedido);

      return ResponseHelper::success('PEDIDO GERADO COM SUCESSO');
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }

  public function updateStatus(Request $request, $id, FcmService $fcmService)
  {
    DB::beginTransaction();
    try {
      $pedido = Pedido::findOrFail($id);

      $resultado = ['success' => true, 'message' => ''];

      $pedido->status = $request->input('status');
      $pedido->save();

      if ($request->input('status') === 'S') {
        $token = optional($pedido->pedido_notificacaos->first())->token_notificacao;

        $resultado = $fcmService->enviaPushNotificationDelivery($pedido, $token);
      }

      if ($request->input('status') === 'C') {
        $token = optional($pedido->pedido_notificacaos->first())->token_notificacao;

        $resultado = $fcmService->enviaPushNotificationCanceled($pedido, $token);
      }

      DB::commit();

      $mensagem = $resultado['success']
        ? 'PEDIDO ATUALIZADO COM SUCESSO. ' . $resultado['message']
        : 'PEDIDO ATUALIZADO COM SUCESSO, MAS A NOTIFICAÇÃO FALHOU: ' . $resultado['message'];

      return redirect()
        ->back()
        ->with('success', $mensagem);
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()
        ->back()
        ->with('error', 'PEDIDO NÃO FOI ATUALIZADO. ' . $e->getMessage());
    }
  }
}
