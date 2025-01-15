<?php

namespace App\Http\Controllers\Servico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Servico\AgendaServicoService;
use Illuminate\Http\Response;
use App\Helpers\ResponseHelper;

class AgendaServicoController extends Controller
{
  public function get(Request $request, AgendaServicoService $agendaServicoService)
  {
    try{
      $agendaServico = $agendaServicoService->findAll();

      return response()->json(
        $agendaServico,
        Response::HTTP_OK,
      );
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }
}
