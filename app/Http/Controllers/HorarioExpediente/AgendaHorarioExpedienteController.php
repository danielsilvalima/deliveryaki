<?php

namespace App\Http\Controllers\HorarioExpediente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\ResponseHelper;
use App\Services\HorarioExpediente\AgendaHorarioExpedienteService;

class AgendaHorarioExpedienteController extends Controller
{
  public function get(Request $request, AgendaHorarioExpedienteService $agendaHorarioExpedienteService)
  {
    try{
      $agendaHorarioExpediente = $agendaHorarioExpedienteService->findByIDEmpresaResource();

      return response()->json(
        $agendaHorarioExpediente,
        Response::HTTP_OK,
      );
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }
}
