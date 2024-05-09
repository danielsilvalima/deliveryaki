<?php

namespace App\Http\Controllers\Cardapio;

use App\Http\Controllers\Controller;
use App\Repositories\Cardapio\CardapioRepository;
use App\Repositories\Empresa\EmpresaRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CardapioController extends Controller
{
  private EmpresaRepository $empresaRepository;
  private CardapioRepository $cardapioRepository;
  private $header = array(
    //'Content-Type' => 'text/html; charset=UTF-8',
    'Content-Type' => 'application/json; charset=UTF-8',
    'charset' => 'utf-8'
  );
  private $options = JSON_UNESCAPED_UNICODE;

  public function __construct(
    EmpresaRepository $empresaRepository,
    CardapioRepository $cardapioRepository
  ) {
    $this->empresaRepository = $empresaRepository;
    $this->cardapioRepository = $cardapioRepository;
  }

  public function get(Request $request, string $id)
  {

    if ($empresa = $this->empresaRepository->findByUUID($id)) {
      $produto = $this->cardapioRepository->findAllActiveByEmpresaID($empresa->id);

      $produto = $this->groupByCategory($produto);
      //return $produto;
      return response()->json(

        //'data' => $produto
        $produto,
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
      $category = $product['categorias'];

      if (!isset($groupedData[$category])) {
        $groupedData[$category] = [];
      }

      $groupedData[$category][] = [
        'descricao' => $product['descricao']
      ];
    }

    $jsonOutput["categorias"][] = $groupedData;

    //return $groupedData;
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
