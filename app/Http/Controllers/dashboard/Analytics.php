<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Pedido;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class Analytics extends Controller
{
  public function index()
  {
    return view('content.dashboard.dashboards-analytics')->with(['email' => Auth::user()->email]);
  }

  public function get(Request $request)
  {
    try {
      $empresa_id = $request->input('empresa_id');

      $empresa = Empresa::where('id', $empresa_id);

      if (!$empresa) {
        return response()->json(['error' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);
      }

      $dataInicial = Carbon::parse($request->input('dataInicial', now()->startOfMonth()))->toDateString() . ' 00:00:00';
      $dataFinal = Carbon::parse($request->input('dataFinal', now()->startOfMonth()))->toDateString() . ' 23:59:59';

      $faturamento = Pedido::whereBetween('created_at', [$dataInicial, $dataFinal])
        ->sum('vlr_total', 'vlr_taxa');

      //$totalPedidos = Pedido::whereBetween('created_at', [$dataInicial, $dataFinal])->count();
      $pedidos = Pedido::whereBetween('created_at', [$dataInicial, $dataFinal])
        ->selectRaw('COUNT(*) as totalPedidos')
        ->selectRaw('tipo_pagamento, COUNT(*) as totalPorTipoPagamento')
        ->selectRaw('tipo_entrega, COUNT(*) as totalPorTipoEntrega')
        ->groupBy('tipo_pagamento', 'tipo_entrega')
        ->get();

      $pedidosAtivos = Pedido::whereIn('status', ['A'])
        ->whereBetween('created_at', [$dataInicial, $dataFinal])
        ->count();

      $pedidosPreparacao = Pedido::where('status', 'P')
        ->whereBetween('created_at', [$dataInicial, $dataFinal])
        ->count();

      $pedidosSaiuParaEntrega = Pedido::where('status', 'S')
        ->whereBetween('created_at', [$dataInicial, $dataFinal])
        ->count();

      $pedidosEntregues = Pedido::where('status', 'E')
        ->whereBetween('created_at', [$dataInicial, $dataFinal])
        ->count();

      $pedidosCancelados = Pedido::where('status', 'C')
        ->whereBetween('created_at', [$dataInicial, $dataFinal])
        ->count();

      $pedidosFinalizados = Pedido::where('status', 'F')
        ->whereBetween('created_at', [$dataInicial, $dataFinal])
        ->count();

      $ultimosPedidos = Pedido::whereBetween('created_at', [$dataInicial, $dataFinal])
        ->orderBy('created_at', 'desc')
        ->take(8)
        ->with('cliente:id,nome_completo')
        ->get(['id', 'cliente_id', 'vlr_total', 'vlr_taxa', 'pago', 'tipo_pagamento', 'tipo_entrega', 'created_at', 'status'])
        ->map(function ($pedido) {
          return [
            'nome' => $pedido->cliente->nome_completo ?? 'Cliente Desconhecido',
            'valor' => $pedido->vlr_total,
            'taxa' => $pedido->vlr_taxa,
            'pago' => $pedido->pago,
            'tipo_pagamento' => $pedido->tipo_pagamento,
            'tipo_entrega' => $pedido->tipo_entrega,
            'horario' => $pedido->created_at->format('H:i'),
            'status' => $pedido->status,
          ];
        });

      $pedidosPorDia = Pedido::whereBetween('created_at', [$dataInicial, $dataFinal])
        ->selectRaw('DATE(created_at) as data, COUNT(*) as total')
        ->groupBy('data')
        ->orderBy('data', 'asc')
        ->get();

      // Evolução dos pedidos por dia
      $pedidosPorDia = Pedido::select(
        DB::raw('DATE(created_at) as data'),
        DB::raw('COUNT(*) as totalPedidos')
      )
        ->whereBetween('created_at', [$dataInicial, $dataFinal])
        ->groupBy('data')
        ->orderBy('data', 'ASC')
        ->get();

      // Formatar resposta para o gráfico
      $evolucaoPedidos = $pedidosPorDia->map(function ($pedido) {
        return [
          'data' => Carbon::parse($pedido->data)->format('d/m'),
          'totalPedidos' => $pedido->totalPedidos,
        ];
      });

      $pedidosPorCategoria = Pedido::join('pedido_items', 'pedidos.id', '=', 'pedido_items.pedido_id')
        ->join('produtos', 'pedido_items.produto_id', '=', 'produtos.id')
        ->join('categorias', 'produtos.categoria_id', '=', 'categorias.id')
        ->whereBetween('pedidos.created_at', [$dataInicial, $dataFinal])
        ->selectRaw('categorias.descricao as categoria, COUNT(*) as total')
        ->groupBy('categorias.descricao')
        ->get();

      return response()->json([
        'faturamento' => $faturamento,
        //'totalPedidos' => $totalPedidos,
        'pedidosPorDia' => $pedidosPorDia,
        'pedidosAtivos' => $pedidosAtivos,
        'pedidosPreparacao' => $pedidosPreparacao,
        'pedidosSaiuParaEntrega' => $pedidosSaiuParaEntrega,
        'pedidosEntregues' => $pedidosEntregues,
        'pedidosCancelados' => $pedidosCancelados,
        'pedidosFinalizados' => $pedidosFinalizados,
        'ultimosPedidos' => $ultimosPedidos,
        'evolucaoPedidos' => $evolucaoPedidos,
        'pedidosPorCategoria' => $pedidosPorCategoria,
        'pedidos' => $pedidos
      ], Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }
}
