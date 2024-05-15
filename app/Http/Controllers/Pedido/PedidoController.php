<?php

namespace App\Http\Controllers\Pedido;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Pedido;
use App\Models\User;
use App\Repositories\Cardapio\CardapioRepository;
use App\Repositories\Cliente\ClienteRepository;
use App\Repositories\Pedido\PedidoRepository;
use App\Repositories\Empresa\EmpresaRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{

  private EmpresaRepository $empresaRepository;
  private PedidoRepository $pedidoRepository;
  private CardapioRepository $cardapioRepository;
  private ClienteRepository $clienteRepository;
  private $header = array(
    //'Content-Type' => 'text/html; charset=UTF-8',
    'Content-Type' => 'application/json; charset=UTF-8',
    'charset' => 'utf-8'
  );
  private $options = JSON_UNESCAPED_UNICODE;

  public function __construct(
    EmpresaRepository $empresaRepository,
    PedidoRepository $pedidoRepository,
    CardapioRepository $cardapioRepository,
    ClienteRepository $clienteRepository,
  ) {
    $this->empresaRepository = $empresaRepository;
    $this->pedidoRepository = $pedidoRepository;
    $this->cardapioRepository = $cardapioRepository;
    $this->clienteRepository = $clienteRepository;
  }

  public function index(Pedido $pedido)
  {
    $pedidos = Pedido::select('pedidos.*', 'clientes.nome_completo as nome_completo')->where('pedidos.empresa_id', Auth::user()->empresa_id)
    ->join('clientes', 'pedidos.cliente_id', '=', 'clientes.id')->get();

    return view('content.pedido.index', [
      'pedidos' => $pedidos,
      'email' => Auth::user()->email
    ]);
  }

  public function show(Pedido $pedido, string|int $id)
    {
        if (!$pedido = Pedido::select('pedidos.*', 'clientes.nome_completo as nome_completo')->where('produtos.id', $id)->where('produtos.empresa_id', Auth::user()->empresa_id)
            ->join('clientes', 'pedidos.cliente_id', '=', 'clientes.id')->first()) {
            return back();
        }

        //$categorias = $this->categoriaRepository->findAllActiveByEmpresaID(Auth::user()->empresa_id);
        return view('content.pedido.show')->with([
            'email' => Auth::user()->email,
            'pedido' => $pedido
        ]);
    }

  public function get(Request $request, string $id)
  {

    if ($empresa = $this->empresaRepository->findByUUID($id)) {
      $cardapio = $this->cardapioRepository->findAllActiveByEmpresaID($empresa->id);

      $cardapio = $this->groupByCategory($cardapio);

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

    DB::beginTransaction();
    if(!$cliente = Cliente::where('celular', $request->cliente['celular'])->first()){
      $cliente = new Cliente([
        "nome_completo" => $request->cliente["nome_completo"],
        "celular" => $request->cliente["celular"],
        "status" => "A",
        "logradouro" => $request->cliente["logradouro"],
        "numero" => $request->cliente["numero"],
        "bairro" => $request->cliente["bairro"],
        "complemento" => $request->cliente["complemento"],
        "numero" => $request->cliente["numero"],
        "cidade" => $request->cliente["cidade"],
        "cep" => $request->cliente["cep"],
        "empresa_id" => $empresa->id,
      ]);
      if(!$cliente->id = $this->clienteRepository->create($cliente)){
        DB::rollBack();
        return response()->json([
          'message' => 'NÃO FOI POSSÍVEL GRAVAR O PEDIDO'
        ], Response::HTTP_NOT_FOUND);
      }
    }

    //GERAR O PEDIDO
    $pedido = new Pedido([
      "status" => "A",
      "tipo_pagamento" => "CR",
      "tipo_entrega" => ($request->entrega["tipo_entrega"] == "retira" ? "R": ($request->entrega["tipo_entrega"] == "entrega" ? "E": "null")),
      //"vlr_taxa" => $request->entrega["valor_taxa"],
      //"vlr_total" => $request->entrega["valor_total"],
      "vlr_taxa" => 0,
      "vlr_total" => 0,
      "deliver_at" => $request->entrega["horario_entrega"],
      "empresa_id" => $empresa->id,
      "cliente_id" => $cliente->id,
    ]);
    if(!$pedido->id = $this->pedidoRepository->create($pedido)){
      DB::rollBack();
      return response()->json([
        'message' => 'NÃO FOI POSSÍVEL GRAVAR O PEDIDO'
      ], Response::HTTP_NOT_FOUND);
    }

    DB::commit();

    return response()->json(

      //'data' => $produto
      ['sucesso' => 'PEDIDO GERADO COM SUCESSO',

      //['sucesso' => $pedido,
      ],
      Response::HTTP_OK,
      $this->header,
      $this->options
    );
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
