<?php

namespace App\Http\Controllers\Pedido;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\User;
use App\Repositories\Cardapio\CardapioRepository;
use App\Repositories\Cliente\ClienteRepository;
use App\Repositories\Pedido\PedidoRepository;
use App\Repositories\Empresa\EmpresaRepository;
use App\Repositories\PedidoItem\PedidoItemRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{

  private EmpresaRepository $empresaRepository;
  private PedidoRepository $pedidoRepository;
  private PedidoItemRepository $pedidoItemRepository;
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
    PedidoItemRepository $pedidoItemRepository,
    CardapioRepository $cardapioRepository,
    ClienteRepository $clienteRepository,
  ) {
    $this->empresaRepository = $empresaRepository;
    $this->pedidoRepository = $pedidoRepository;
    $this->pedidoItemRepository = $pedidoItemRepository;
    $this->cardapioRepository = $cardapioRepository;
    $this->clienteRepository = $clienteRepository;
  }

  public function index(Pedido $pedido)
  {
    $pedidos = Pedido::select('pedidos.*', 'clientes.nome_completo', 'clientes.logradouro', 'clientes.numero', 'clientes.bairro')->where('pedidos.empresa_id', Auth::user()->empresa_id)
    ->join('clientes', 'pedidos.cliente_id', '=', 'clientes.id')->get();


    foreach ($pedidos as $pedido) {
      $itens = $this->pedidoItemRepository->findByIDPedido($pedido->id);

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

    if ($empresa = $this->empresaRepository->findByHash($id)) {
      $cardapio = $this->cardapioRepository->findAllActiveByEmpresaID($empresa->id);

      $cardapio = $this->groupByCategory($cardapio);

      return response()->json(
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
    //try{
      if(!$empresa = Empresa::where('hash', $id)->first()){
        return response()->json([
          'message' => 'Empresa não encontrada'
        ], Response::HTTP_NOT_FOUND,
        $this->header,
        $this->options);
      }

      //CADASTRA O CLIENTE SE NECESSARIO
      DB::beginTransaction();

      if(!$cliente = Cliente::where('celular', $request->cliente['celular'])->first()){
        $cliente = $this->getObjCliente($request->cliente);
        $cliente->empresa_id = $empresa->id;
        if(!$cliente->id = $this->clienteRepository->create($cliente)){
          DB::rollBack();
          return response()->json([
            'message' => 'NÃO FOI POSSÍVEL GRAVAR O PEDIDO'
          ], Response::HTTP_INTERNAL_SERVER_ERROR,
          $this->header,
          $this->options);
        }
      }else{
        $cliente = $this->getObjCliente($request->cliente);
        if(!$cliente = $this->clienteRepository->update($cliente)){
          DB::rollBack();
          return response()->json([
            'message' => 'NÃO FOI POSSÍVEL ATUALIZAR O CLIENTE'
          ], Response::HTTP_INTERNAL_SERVER_ERROR,
          $this->header,
          $this->options);
        }
      }

      //GERA O PEDIDO
      /*$pedido = new Pedido([
        "status" => "A",
        "tipo_pagamento" => strtoupper($request->entrega["tipo_pagamento"]),
        "tipo_entrega" => strtoupper($request->entrega["tipo_entrega"]),
        "vlr_taxa" => $request->entrega["valor_taxa"],
        "vlr_total" => $request->entrega["valor_total"],
        "deliver_at" => $request->entrega["horario_entrega"],
        "empresa_id" => $empresa->id,
        "cliente_id" => $cliente->id,
      ]);*/
      $pedido = $this->getObjPedido($request->entrega);
      $pedido->empresa_id = $empresa->id;
      $pedido->cliente_id = $cliente->id;
      if(!$pedido->id = $this->pedidoRepository->create($pedido)){
        DB::rollBack();
        return response()->json([
          'message' => 'NÃO FOI POSSÍVEL GRAVAR O PEDIDO'
        ], Response::HTTP_INTERNAL_SERVER_ERROR,
        $this->header,
        $this->options);
      }

      //GERA OS ITENS DO PEDIDO
      foreach ($request->pedido as $item) {
        /*$valorUnitario = str_replace(',', '.', $item['valor']);
        /*$pedido_item = new PedidoItem([
          "produto_id" => intval($item["id"]),
          "qtd" => $item["qtd"],
          "vlr_unitario" => floatval($valorUnitario),
          "vlr_total" => $item["qtd"] * $valorUnitario,
          "pedido_id" => $pedido->id,
          "empresa_id" => $empresa->id,
          "cliente_id" => $cliente->id,
        ]);*/
        $pedido_item = $this->getObjPedidoItens($item);
        $pedido_item->pedido_id = $pedido->id;
        $pedido_item->empresa_id = $empresa->id;
        $pedido_item->cliente_id = $cliente->id;

        if(!$pedido_item->id = $this->pedidoItemRepository->create($pedido_item)){
          DB::rollBack();
          return response()->json([
            'message' => 'NÃO FOI POSSÍVEL GRAVAR O PEDIDO'
          ], Response::HTTP_INTERNAL_SERVER_ERROR,
          $this->header,
          $this->options);
        }
      }

      DB::commit();

      return response()->json(
        [
          'sucesso' => 'PEDIDO GERADO COM SUCESSO',
        ],
        Response::HTTP_OK,
        $this->header,
        $this->options
      );
    /*} catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'ERRO AO GRAVAR O PEDIDO',
            'error' => $e->getMessage()
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }*/
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
          'id' => $product['id'],
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

  private function getObjCliente($objCliente){
    return new Cliente([
        "nome_completo" => strtoupper($objCliente["nome_completo"]),
        "celular" => $objCliente["celular"],
        "status" => "A",
        "logradouro" => strtoupper($objCliente["logradouro"]),
        "numero" => strtoupper($objCliente["numero"]),
        "bairro" => strtoupper($objCliente["bairro"]),
        "complemento" => strtoupper($objCliente["complemento"]),
        "numero" => strtoupper($objCliente["numero"]),
        "cidade" => strtoupper($objCliente["cidade"]),
        "cep" => strtoupper($objCliente["cep"]),
      ]);
  }

  private function getObjPedido($objPedido){
    return new Pedido([
      "status" => "A",
      "tipo_pagamento" => strtoupper($objPedido["tipo_pagamento"]),
      "tipo_entrega" => strtoupper($objPedido["tipo_entrega"]),
      "vlr_taxa" => $objPedido["valor_taxa"],
      "vlr_total" => $objPedido["valor_total"],
      "deliver_at" => $objPedido["horario_entrega"],
    ]);
  }

  private function getObjPedidoItens($objPedidoItens){
    return new PedidoItem([
      "produto_id" => intval($objPedidoItens["id"]),
      "qtd" => $objPedidoItens["qtd"],
      "vlr_unitario" => floatval($objPedidoItens["valor"]),
      "vlr_total" => floatval($objPedidoItens["valor"]),
    ]);
  }
}
