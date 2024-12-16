<?php

namespace App\Services\Cardapio;

use InvalidArgumentException;
use App\Models\Cliente;
use App\Models\Pedido;
use Illuminate\Support\Facades\DB;

class CardapioService
{
  public function groupByCategory($data)
  {
    $groupedData = [];

    foreach ($data as $product) {
      $categories = $product['categorias'];

      // Verifica se $categories é uma string, e se for, converte para um array associativo com uma única entrada
      if (!is_array($categories)) {
        //$categories = ['default' => $categories];
        $categories = [$categories => $categories];
      }

      foreach ($categories as $category => $items) {
        if (!isset($groupedData[$category])) {
          $groupedData[$category] = [];
        }

        // Adiciona o item à categoria correspondente
        $groupedData[$category][] = [
          'id' => $product['id'],
          'descricao' => $product['descricao'],
          'apresentacao' => $product['apresentacao'],
          'valor' => isset($product['vlr_unitario']) ? number_format($product['vlr_unitario'], 2, ',', '.') : null,
        ];
      }
    }

    // Constrói a estrutura final do JSON
    $jsonOutput = [
      "categorias" => []
    ];

    foreach ($groupedData as $category => $items) {
      $jsonOutput["categorias"][] = [
        "nome" => $category,
        "itens" => $items
      ];
    }

    return $jsonOutput;
  }
}
