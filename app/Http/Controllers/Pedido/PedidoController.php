<?php

namespace App\Http\Controllers\Pedido;

use App\Http\Controllers\Controller;
use App\Repositories\Cardapio\CardapioRepository;
use App\Repositories\Pedido\PedidoRepository;
use App\Repositories\Empresa\EmpresaRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PedidoController extends Controller
{

  private EmpresaRepository $empresaRepository;
  private PedidoRepository $pedidoRepository;
  private CardapioRepository $cardapioRepository;
  private $header = array(
    //'Content-Type' => 'text/html; charset=UTF-8',
    'Content-Type' => 'application/json; charset=UTF-8',
    'charset' => 'utf-8'
  );
  private $options = JSON_UNESCAPED_UNICODE;

  public function __construct(
    EmpresaRepository $empresaRepository,
    PedidoRepository $pedidoRepository,
    CardapioRepository $cardapioRepository
  ) {
    $this->empresaRepository = $empresaRepository;
    $this->pedidoRepository = $pedidoRepository;
    $this->cardapioRepository = $cardapioRepository;
  }

  public function get(Request $request, string $id)
  {

    if ($empresa = $this->empresaRepository->findByUUID($id)) {
      $cardapio = $this->cardapioRepository->findAllActiveByEmpresaID($empresa->id);
      //dd($cardapio);
      $cardapio = $this->groupByCategory($cardapio);
      //return $produto;

      return response()->json(

        //'data' => $produto
        [$cardapio],
        Response::HTTP_OK,
        $this->header,
        $this->options
      );
    } else {
      return response()->json(
        [
          'message' => 'Empresa não encontrada.'
        ],
        Response::HTTP_NOT_FOUND,
        $this->header,
        $this->options
      );
    }
  }

  private function groupByCategory($data)
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
          'id' => $product['uuid'],
          'descricao' => $product['descricao'],
          'valor' => isset($product['vlr_unitario']) ? number_format($product['vlr_unitario'], 2, ',', '.') : null,
          //'subtitulo' => isset($product['subtitulo']) ? $product['subtitulo'] : null
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

  /*post
  return response()->json(
    [
        'data' => $this->orderRepository->createOrder($orderDetails)
    ],
    Response::HTTP_CREATED
);

return response()->json(
  [
      'message' => 'Pedido excluído com sucesso!'
  ],
  Response::HTTP_OK
);

if (!$order) {
  return response()->json(
      [
          'message' => 'Pedido não encontrado.'
      ],
      Response::HTTP_NOT_FOUND
  );
}*/
}
