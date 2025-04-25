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

  public function getFaturaPagamento(Request $request, FaturaService $faturaService)
  {
    try {
      $fatura_id = $request->input('fatura_id');
      $fatura = Fatura::find($fatura_id);
      //$tx_id = $request->input('tx_id');
      //$tx_id = $fatura->tx_id;

      if (!$fatura->tx_id) {
        return response()->json(['error' => 'txid não foi encontrado'], 400);
      }

      $consulta = $faturaService->consultarPagamento($fatura);
      return response()->json(
        $consulta,
        Response::HTTP_OK
      );
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getFinanceiro(Request $request)
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

  public function getEmpresa(Request $request)
  {
    try {
      $limit = $request->input('limit', 10);
      $page = $request->input('page', 1);

      // Busca todas as faturas com os dados das empresas
      $faturas = Fatura::with(['empresaDelivery:id,cnpj,razao_social,status', 'empresaAgenda:id,cnpj,razao_social,status'])
        ->orderBy('id', 'asc')
        ->get();

      // Agrupar por empresa + tipo_app
      $agrupado = [];

      foreach ($faturas as $fatura) {
        if ($fatura->tipo_app === 'deliveryaki' && $fatura->empresaDelivery) {
          $chave = 'deliveryaki_' . $fatura->empresa_id;
          $empresa = $fatura->empresaDelivery;
        } elseif ($fatura->tipo_app === 'agendaadmin' && $fatura->empresaAgenda) {
          $chave = 'agendaadmin_' . $fatura->empresa_id;
          $empresa = $fatura->empresaAgenda;
        } else {
          continue; // ignora faturas sem empresa associada
        }

        if (!isset($agrupado[$chave])) {
          $agrupado[$chave] = [
            'cnpj' => $empresa->cnpj,
            'razao_social' => $empresa->razao_social,
            'status' => $empresa->status,
            'tipo_app' => $fatura->tipo_app,
            'faturas' => [],
          ];
        }

        $agrupado[$chave]['faturas'][] = [
          'id' => $fatura->id,
          'referencia' => $fatura->referencia,
          'status' => $fatura->status,
          'valor_total' => $fatura->valor_total,
          'valor_pago' => $fatura->valor_pago,
          'vencimento' => $fatura->vencimento,
          'pago_em' => $fatura->pago_em,
        ];
      }

      // Paginação manual
      $coletado = array_values($agrupado);
      $total = count($coletado);
      $offset = ($page - 1) * $limit;
      $paginado = array_slice($coletado, $offset, $limit);

      return response()->json([
        'current_page' => $page,
        'data' => $paginado,
        'total_pages' => ceil($total / $limit),
        'total' => $total,
        'per_page' => $limit,
      ], Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function update(Request $request, string $id)
  {
    try {

      if (!$fatura = Fatura::find($id)) {
        return response()->json(['error' => 'Fatura não encontrado.'], Response::HTTP_NOT_FOUND);
      }

      $fatura->status = 'cancelado';
      $fatura->save();

      return response()->json([
        'message' => 'Fatura atualizado com sucesso.'
      ], Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }
}
