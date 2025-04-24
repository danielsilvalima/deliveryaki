<?php

namespace App\Http\Controllers\Fatura;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Fatura;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\Fatura\FaturaService;
use Illuminate\Support\Facades\DB;

class FaturaController extends Controller
{
  public function getPagas(Request $request)
  {
    try {
      $empresa_id = $request->input('empresa_id');
      $limit = $request->input('limit', 5);
      $page = $request->input('page', 1);

      $empresa = Empresa::find($empresa_id);
      if (!$empresa) {
        return response()->json(['error' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);
      }
      $query = Fatura::where('empresa_id', $empresa_id)->where('status', 'paga')->orderBy('id', 'ASC');

      $itensPaginados = $query->paginate($limit, ['*'], 'page', $page);

      return response()->json(
        [
          'current_page' => $itensPaginados->currentPage(),
          'data' => $itensPaginados->items(),
          'total_pages' => $itensPaginados->lastPage(),
          'total' => $itensPaginados->total(),
          'per_page' => $itensPaginados->perPage(),
        ],
        Response::HTTP_OK
      );
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getPagar(Request $request)
  {
    try {
      $empresa_id = $request->input('empresa_id');
      $limit = $request->input('limit', 5);
      $page = $request->input('page', 1);

      $empresa = Empresa::find($empresa_id);
      if (!$empresa) {
        return response()->json(['error' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);
      }
      $query = Fatura::where('empresa_id', $empresa_id)->where('status', 'pendente')->orderBy('id', 'ASC');

      $itensPaginados = $query->paginate($limit, ['*'], 'page', $page);

      return response()->json(
        [
          'current_page' => $itensPaginados->currentPage(),
          'data' => $itensPaginados->items(),
          'total_pages' => $itensPaginados->lastPage(),
          'total' => $itensPaginados->total(),
          'per_page' => $itensPaginados->perPage(),
        ],
        Response::HTTP_OK
      );
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function store(Request $request, FaturaService $faturaService)
  {
    $request->validate([
      'fatura.id' => 'required|integer|exists:faturas,id',
    ]);
    try {
      $faturaId = $request->input('fatura.id');
      $fatura = Fatura::findOrFail($faturaId);

      $empresa = Empresa::find($fatura->empresa_id);
      if (!$empresa) {
        return response()->json(['error' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);
      }


      DB::beginTransaction();

      $qrcode = $faturaService->gerarQrCodePix($fatura, $empresa);

      if (!empty($qrcode['copia_e_cola']) && !empty($qrcode['txid'])) {
        $fatura->txid = $qrcode['txid'];
        $fatura->save();
      }
      DB::commit();

      return response()->json(
        $qrcode,
        Response::HTTP_OK
      );
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getPix(Request $request, FaturaService $faturaService)
  {
    try {
      $fatura_id = $request->input('fatura_id');
      $fatura = Fatura::where('id', $fatura_id)->first();
      //$tx_id = $request->input('tx_id');
      $tx_id = $fatura->tx_id;

      /*if (!$tx_id) {
        return response()->json(['error' => 'txid é obrigatório'], 400);
      }*/

      $pix = $faturaService->consultarPixPorTxid($tx_id);
      return response()->json($pix);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }
}
