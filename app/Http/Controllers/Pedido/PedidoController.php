<?php

namespace App\Http\Controllers\Pedido;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\User;
use App\Repositories\Cardapio\CardapioRepository;
use App\Repositories\Pedido\PedidoRepository;
use App\Repositories\Empresa\EmpresaRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

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

  public function post(Request $request, string $id)
  {
    if(!$empresa = Empresa::where('uuid', $id)->first()){
      return response()->json([
        'message' => 'Empresa não encontrada'
      ], 403);
    }

    if(!$user = User::where('empresa_id', $empresa->id)->first()){
      return response()->json([
        'message' => 'Empresa não encontrada'
      ], 403);
    }

    return response()->json(

      //'data' => $produto
      [$request->only('data','body', 'params')],
      Response::HTTP_OK,
      $this->header,
      $this->options
    );
    //return $user->only('email', 'password');

    //return $request->params;
    /*return response()->json(

      //'data' => $produto
      ["teste"],
      Response::HTTP_OK,
      $this->header,
      $this->options
    );*/

    /*if ($empresa = $this->empresaRepository->findByUUID($id)) {
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
    }*/
  }

  public function teste(Request $request)
  {
    if (Auth::attempt($request->only('email', 'password'))) {
      return response()->json([
       'message' => 'Credenciais corretas'
      ], 200);
    }else{
      return response()->json([
        'message' => 'Credenciais invalidas'
       ], 403);
    }

    /*if(!$empresa = Empresa::where('uuid', $request['id'])->first()){
      return response()->json([
        'message' => 'Empresa não encontrada'
      ], 401);
    }

    if(!$user = User::where('empresa_id', $empresa->id)->first()){
      return response()->json([
        'message' => 'Empresa não encontrada 2'
      ], 401);
    }*/

    //return $user->only('email', 'password');


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
