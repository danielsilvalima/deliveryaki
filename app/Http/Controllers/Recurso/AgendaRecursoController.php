<?php

namespace App\Http\Controllers\Recurso;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EmpresaRecurso\AgendaEmpresaRecursoService;
use App\Services\Empresa\AgendaEmpresaService;
use Illuminate\Http\Response;
use App\Helpers\ResponseHelper;

class AgendaRecursoController extends Controller
{
  public function get(Request $request, AgendaEmpresaRecursoService $gendaEmpresaRecursoService, AgendaEmpresaService $agendaEmpresaService)
  {
    try{
      $id = $request->query('id');
      if (empty($id)) {
        return ResponseHelper::error('O "ID" É OBRIGATÓRIO', Response::HTTP_BAD_REQUEST);
      }
      $empresa = $gendaEmpresaRecursoService->findByResourceByID($id, $agendaEmpresaService);

      return response()->json(
        $empresa,
        Response::HTTP_OK,
      );
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }

  public function store(Request $request, AgendaEmpresaService $agendaEmpresaService, AgendaEmpresaRecursoService $agendaEmpresaRecursoService )
  {
    try {
      $agenda_user = json_decode($request->input('agenda_user'), true);
      $agenda_empresa_recursos = $request->input('agenda_empresa_recursos', []);

      if (empty($agenda_user['email'])) {
        return ResponseHelper::error('O "E-MAIL" É OBRIGATÓRIO', Response::HTTP_BAD_REQUEST);
      }

      $empresa_db = $agendaEmpresaService->findByEmailSummary($agenda_user['email']);
      if($empresa_db){
        $empresa_db = $agendaEmpresaRecursoService->createOrUpdate($empresa_db, $agenda_empresa_recursos, $request, $agendaEmpresaService);
      }

      return response()->json(
        $empresa_db,
        Response::HTTP_OK,
      );
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }
}
