<?php

namespace App\Http\Controllers\Cardapio;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\Empresa\EmpresaService;
use App\Services\Produto\ProdutoService;
use App\Services\Cardapio\CardapioService;
use App\Helpers\ResponseHelper;

class CardapioController extends Controller
{
  private $header = [
    //'Content-Type' => 'text/html; charset=UTF-8',
    'Content-Type' => 'application/json; charset=UTF-8',
    'charset' => 'utf-8',
  ];
  private $options = JSON_UNESCAPED_UNICODE;

  public function get(
    Request $request,
    string $id,
    EmpresaService $empresaService,
    ProdutoService $produtoService,
    CardapioService $cardapioService
  ) {
    try {
      if (!($empresa = $empresaService->findByHash($id))) {
        return ResponseHelper::notFound('EMPRESA NÃO ENCONTRADA');
      }

      $empresa = $produtoService->findAllProductActiveByEmpresaID($empresa->id);

      //$cardapio = $cardapioService->groupByCategory($cardapio);

      $horario_expediente = $empresaService->verificaExpedienteByHash($empresa->hash);

      $empresa['horario_expediente'] = $horario_expediente;

      return response()->json($empresa, Response::HTTP_OK, $this->header, $this->options);
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }
}
