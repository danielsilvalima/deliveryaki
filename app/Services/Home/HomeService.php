<?php

namespace App\Services\Home;
use Illuminate\Support\Facades\DB;


class HomeService
{
  public function getIndicadores($empresaId, $dataInicio, $dataFim)
    {
      if($dataInicio === null && $dataFim === null){
        $dataInicio = now()->startOfMonth()->toDateString() . ' 00:00:00';
        $dataFim = now()->endOfDay()->toDateString() . ' 23:59:59';
      }else{
        $dataInicio = $dataInicio . ' 00:00:00';
        $dataFim = $dataFim . ' 23:59:59';
      }

      // Total de vendas no mês atual
      $valorTotalVendas = DB::table('pedidos')
          ->where('empresa_id', $empresaId)
          ->whereBetween('created_at', [$dataInicio, $dataFim])
          ->sum('vlr_total');

      // Produto mais vendido em valor
      $produtoMaisVendido = DB::table('pedido_items')
        ->join('produtos', 'pedido_items.produto_id', '=', 'produtos.id')
        ->where('pedido_items.empresa_id', $empresaId)
        ->whereBetween('pedido_items.created_at', [$dataInicio, $dataFim])
        ->select(
            'produtos.descricao as produto',
            'pedido_items.vlr_unitario as vlr_unitario', // Seleciona o valor unitário
            DB::raw('SUM(pedido_items.vlr_total) as total_vendido')
        )
        ->groupBy('produtos.id', 'produtos.descricao', 'pedido_items.vlr_unitario') // Agrupa por valor unitário também
        ->orderByDesc('total_vendido')
        ->first();

      // Caso a consulta não retorne resultados, defina valores padrão
      $produtoMaisVendido = $produtoMaisVendido ?? (object) [
        'produto' => 'Nenhum',
        'total_vendido' => 0,
        'vlr_unitario' => 0
      ];

      // Grupo (categoria) mais vendido em valor
      $grupoMaisVendido = DB::table('pedido_items')
          ->join('produtos', 'pedido_items.produto_id', '=', 'produtos.id')
          ->join('categorias', 'produtos.categoria_id', '=', 'categorias.id')
          ->where('pedido_items.empresa_id', $empresaId)
          ->whereBetween('pedido_items.created_at', [$dataInicio, $dataFim])
          ->select('categorias.descricao as categoria', DB::raw('SUM(pedido_items.vlr_total) as total_vendido'))
          ->groupBy('categorias.id', 'categorias.descricao')
          ->orderByDesc('total_vendido')
          ->first();

      // Caso a consulta não retorne resultados, defina valores padrão
      $grupoMaisVendido = $grupoMaisVendido ?? (object) [
        'categoria' => 'Nenhum',
        'total_vendido' => 0
      ];

      // Tipo de entrega mais utilizado
      $tipoEntregaMaisUtilizado = DB::table('pedidos')
        ->where('empresa_id', $empresaId)
        ->whereBetween('created_at', [$dataInicio, $dataFim])
        ->select('tipo_entrega', DB::raw('COUNT(*) as quantidade'))
        ->groupBy('tipo_entrega')
        ->orderByDesc('quantidade')
        ->limit(1) // Retorna apenas a primeira linha com a maior quantidade
        ->get()
        ->map(function ($item) {
            $item->tipo_entrega = $item->tipo_entrega === 'E' ? 'ENTREGA' : 'RETIRA';
            return $item;
        });

      // Verifica se não há registros e retorna um valor padrão
      if ($tipoEntregaMaisUtilizado->isEmpty()) {
        $tipoEntregaMaisUtilizado = collect([
            (object)[
                'tipo_entrega' => 'Nenhum',
                'quantidade' => 0
            ]
        ]);
      }

      // Tipo de pagamento mais utilizado
      $tipoPagamentoMaisUtilizado = DB::table('pedidos')
        ->where('empresa_id', $empresaId)
        ->whereBetween('created_at', [$dataInicio, $dataFim])
        ->select('tipo_pagamento', DB::raw('COUNT(*) as quantidade'))
        ->groupBy('tipo_pagamento')
        ->orderByDesc('quantidade')
        ->limit(1) // Retorna apenas a primeira linha com a maior quantidade
        ->get()
        ->map(function ($item) {
            switch ($item->tipo_pagamento) {
                case 'CR':
                    $item->tipo_pagamento = 'CRÉDITO';
                    break;
                case 'DE':
                    $item->tipo_pagamento = 'DÉBITO';
                    break;
                case 'PI':
                    $item->tipo_pagamento = 'PIX';
                    break;
                case 'DI':
                    $item->tipo_pagamento = 'DINHEIRO';
                    break;
            }
            return $item;
        });

      // Verifica se não há registros e retorna um valor padrão
      if ($tipoPagamentoMaisUtilizado->isEmpty()) {
        $tipoPagamentoMaisUtilizado = collect([
            (object)[
                'tipo_pagamento' => 'Nenhu',
                'quantidade' => 0
            ]
        ]);
      }

      return [
          'total_vendas' => $valorTotalVendas,
          'produto_mais_vendido' => $produtoMaisVendido,
          'grupo_mais_vendido' => $grupoMaisVendido,
          'tipo_entrega_mais_utilizado' => $tipoEntregaMaisUtilizado,
          'tipo_pagamento_mais_utilizado' => $tipoPagamentoMaisUtilizado,
      ];
    }
}
