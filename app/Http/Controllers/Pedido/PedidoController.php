<?php

namespace App\Http\Controllers\Pedido;

use App\Http\Controllers\Controller;
use App\Repositories\Pedido\PedidoRepository;
use App\Repositories\Empresa\EmpresaRepository;
use App\Repositories\Produto\ProdutoRepository;
use Illuminate\Http\Request;

class PedidoController extends Controller
{

  private PedidoRepository $pedidoRepository;
  private EmpresaRepository $empresaRepository;
  private ProdutoRepository $produtoRepository;

  public function __construct(PedidoRepository $pedidoRepository, EmpresaRepository $empresaRepository,
  ProdutoRepository $produtoRepository )
    {
        $this->pedidoRepository = $pedidoRepository;
        $this->empresaRepository = $empresaRepository;
        $this->produtoRepository = $produtoRepository;
    }

  public function get(Request $request, string $id)
  {
    if($empresa = $this->empresaRepository->findByUUID($id)){
      $produto = $this->produtoRepository->findAllActiveByEmpresaID($empresa->id);

      return response()->json($produto);
    }

  }

  /*public function store(Request $request): JsonResponse
    {
        $orderDetails = $request->only([
            'client',
            'details'
        ]);

        return response()->json(
            [
                'data' => $this->orderRepository->createOrder($orderDetails)
            ],
            Response::HTTP_CREATED
        );
    }

    public function destroy(Request $request): JsonResponse
    {
        $orderId = $request->route('id');
        $this->orderRepository->deleteOrder($orderId);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }*/
}
