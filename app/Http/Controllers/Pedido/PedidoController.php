<?php

namespace App\Http\Controllers\Pedido;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Pedido;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseHelper;
use App\Models\PedidoItem;
use App\Services\Empresa\EmpresaService;
use App\Services\Pedido\PedidoService;
use App\Services\Produto\ProdutoService;
use App\Services\Fcm\FcmService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Http\Response;

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
      'tipo_entrega' => '',
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
        return ResponseHelper::notFound('Empresa não encontrada');
      }

      $cliente = $request->cliente;
      $cliente['empresa_id'] = $empresa->id;
      $entrega = $request->entrega;
      $entrega['empresa_id'] = $empresa->id;

      $pedido = $pedidoService->createPedido($cliente, $entrega, $request->pedido);

      return ResponseHelper::success('Pedido gerado com sucesso.');
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }

  public function updateStatus(Request $request, EmpresaService $empresaService, FcmService $fcmService)
  {
    DB::beginTransaction();
    try {
      $empresa = $request->input('empresa');
      $pedido = $request->input('pedido');
      $status = $request->input('status');

      $empresa = Empresa::find($empresa['id']);
      if (!$empresa) {
        return response()->json(['error' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);
      }
      if ($empresaService->validaDataExpiracao($empresa)) {
        return response()->json(['error' => 'A empresa está expirada e não pode atualizar pedidos.'], Response::HTTP_FORBIDDEN);
      }

      $resultado = ['success' => true, 'message' => ''];

      $pedido_db = Pedido::find($pedido['id']);
      if (!$pedido_db) {
        return response()->json(['error' => 'Pedido não encontrado.'], Response::HTTP_NOT_FOUND);
      }

      $pedido_db->status = $status;
      $pedido_db->save();

      if ($pedido_db->status === 'S') {
        $token = optional($pedido_db->pedido_notificacaos->first())->token_notificacao;

        $resultado = $fcmService->enviaPushNotificationDelivery($pedido_db, $token);
      }

      if ($pedido_db->status === 'C') {
        $token = optional($pedido_db->pedido_notificacaos->first())->token_notificacao;

        $resultado = $fcmService->enviaPushNotificationCanceled($pedido_db, $token);
      }

      DB::commit();

      $mensagem = $resultado['success']
        ? 'Pedido atualizado com sucesso. ' . $resultado['message']
        : 'Pedido atualizado com sucesso, a notificação falhou: ' . $resultado['message'];

      return response()->json(
        ['message' => $mensagem],
        Response::HTTP_OK
      );
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function update(Request $request, $id, EmpresaService $empresaService)
  {
    DB::beginTransaction();
    try {
      $pedido = Pedido::with('pedido_items')->findOrFail($id);

      $empresa = Empresa::find($pedido->empresa_id);
      if (!$empresa) {
        return response()->json(['error' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);
      }
      if ($empresaService->validaDataExpiracao($empresa)) {
        return response()->json(['error' => 'A empresa está expirada e não pode atualizar produtos.'], Response::HTTP_FORBIDDEN);
      }

      if (!$request->all()) {
        return response()->json(['error' => 'Nenhum item foi enviado para atualização.'], Response::HTTP_BAD_REQUEST);
      }

      $pedidos = collect($request->input('pedido'));

      if ($pedidos->isEmpty()) {
        return response()->json(['error' => 'Nenhum produto foi enviado.'], Response::HTTP_BAD_REQUEST);
      }

      $novosProdutoIds = $pedidos->pluck('produto_id')->toArray();

      $pedido->pedido_items()->whereNotIn('produto_id', $novosProdutoIds)->delete();

      foreach ($pedidos as $itemData) {
        $produtoId = $itemData['produto_id'];

        // Busca o item no pedido
        $item = $pedido->pedido_items()->where('produto_id', $produtoId)->first();

        if ($item) {
          // Se a quantidade mudou, atualiza o item
          if ($item->qtd != $itemData['qtd']) {
            $item->update([
              'qtd' => $itemData['qtd'],
              'vlr_total' => $itemData['qtd'] * $itemData['vlr_unitario'],
            ]);
          }
        } else {
          // Se não existe, cria um novo item no pedido
          PedidoItem::create([
            'pedido_id' => $pedido->id,
            'produto_id' => $produtoId,
            'qtd' => $itemData['qtd'],
            'vlr_unitario' => $itemData['vlr_unitario'],
            'vlr_total' => $itemData['qtd'] * $itemData['vlr_unitario'],
            'empresa_id' => $pedido->empresa_id,
            'cliente_id' => $pedido->cliente_id,
          ]);
        }
      }

      $pedido->vlr_total = $pedido->pedido_items()->sum('vlr_total');

      $status = $request->input('status');
      if (!is_null($status)) {
        $pedido->status = $status;
      }

      $pedido->save();

      DB::commit();

      return response()->json(
        ['message' => 'Pedido atualizado com sucesso.'],
        Response::HTTP_OK
      );
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function updateBaixa(Request $request, EmpresaService $empresaService)
  {
    DB::beginTransaction();
    try {
      $empresa = $request->input('empresa');
      $pedidoIds = collect($request->input('pedidos'))->pluck('id');

      $empresa = Empresa::find($empresa['id']);
      if (!$empresa) {
        return response()->json(['error' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);
      }
      if ($empresaService->validaDataExpiracao($empresa)) {
        return response()->json(['error' => 'A empresa está expirada e não pode atualizar pedidos.'], Response::HTTP_FORBIDDEN);
      }

      $pedidos_db = Pedido::whereIn('id', $pedidoIds)->get()->keyBy('id');

      $pedidosNaoEncontrados = array_diff($pedidoIds->toArray(), $pedidos_db->keys()->toArray());

      foreach ($pedidos_db as $pedido_db) {
        $pedido_db->pago = $pedido_db->pago === 0 ? 1 : 0;
        $pedido_db->save();
      }

      DB::commit();

      return response()->json(
        [
          'message' => 'Pedido(s) atualizados com sucesso.',
          'pedidos_nao_encontrados' => $pedidosNaoEncontrados
        ],
        Response::HTTP_OK
      );
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function get(Request $request, EmpresaService $empresaService)
  {
    try {
      $empresa_id = $request->input('empresa_id');

      $empresa = Empresa::find($empresa_id);
      if (!$empresa) {
        return response()->json(['error' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);
      }
      if ($empresaService->validaDataExpiracao($empresa)) {
        return response()->json(['error' => 'A empresa está expirada e não pode consultar pedidos.'], Response::HTTP_FORBIDDEN);
      }

      //$limit = $request->input('per_page', 10);
      $page = $request->input('page', 1);

      $query = Pedido::query();
      $query->where('empresa_id', $empresa_id);

      // Aplicando filtros
      if (!is_null($request->input('pedido_id'))) {
        $query->where('id', $request->input('pedido_id'));
      }
      $query->when($request->filled('filtros.status'), function ($q) use ($request) {
        $q->whereIn('status', $request->input('filtros.status', []));
      });

      $query->when($request->filled('filtros.tipoPagamento'), function ($q) use ($request) {
        $q->whereIn('tipo_pagamento', $request->input('filtros.tipoPagamento', []));
      });

      $query->when($request->filled('filtros.tipoEntrega'), function ($q) use ($request) {
        $q->whereIn('tipo_entrega', $request->input('filtros.tipoEntrega', []));
      });

      $query->when($request->filled('filtros.startDate'), function ($q) use ($request) {
        $q->whereDate('created_at', '>=', $request->input('filtros.startDate'));
      });

      $query->when($request->filled('filtros.endDate'), function ($q) use ($request) {
        $q->whereDate('created_at', '<=', $request->input('filtros.endDate'));
      });

      /*$query->when($request->filled('filtros.pago'), function ($q) use ($request) {
        $q->whereIn('pago', $request->input('filtros.pago', []));
      });*/
      if ($request->has('filtros.pago')) {
        $query->whereIn('pago', $request->input('filtros.pago'));
      } else {
        $query->where('pago', 0);
      }

      // Relacionamentos necessário
      $query->with(['cliente', 'cliente.ceps', 'pedido_items.produto']);

      //ordenação
      $query->orderBy('created_at', 'asc');

      // Paginação
      //$itensPaginados = $query->paginate($limit, ['*'], 'page', $page);
      $pedidos = $query->get();

      return response()->json([
        //'current_page' => $itensPaginados->currentPage(),
        'data' => $pedidos->map(function ($pedido) {
          return [
            'id' => $pedido->id,
            'uuid' => $pedido->uuid,
            'status' => $pedido->status,
            'pago' => $pedido->pago,
            'tipo_pagamento' => $pedido->tipo_pagamento,
            'tipo_entrega' => $pedido->tipo_entrega,
            'vlr_taxa' => $pedido->vlr_taxa,
            'vlr_total' => $pedido->vlr_total,
            'created_at' => $pedido->created_at,
            'cliente' => $pedido->cliente,
            'pedido_items' => $pedido->pedido_items
          ];
        }),
        /*'total_pages' => $itensPaginados->lastPage(),
        'total' => $itensPaginados->total(),
        'per_page' => $itensPaginados->perPage()*/
      ], Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }
}
