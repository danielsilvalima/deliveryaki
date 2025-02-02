<?php

namespace App\Http\Controllers\Servico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EmpresaServico\AgendaEmpresaServicoService;
use App\Services\Empresa\AgendaEmpresaService;
use Illuminate\Http\Response;
use App\Helpers\ResponseHelper;

class AgendaServicoController extends Controller
{
  public function get(Request $request, AgendaEmpresaServicoService $gendaEmpresaServicoService, AgendaEmpresaService $agendaEmpresaService)
  {
    try{
      $id = $request->query('id');
      if (empty($id)) {
        return ResponseHelper::error('O "ID" É OBRIGATÓRIO', Response::HTTP_BAD_REQUEST);
      }
      $empresa = $gendaEmpresaServicoService->findByServiceByID($id, $agendaEmpresaService);

      return response()->json(
        $empresa,
        Response::HTTP_OK,
      );
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }

  public function store(Request $request, AgendaEmpresaService $agendaEmpresaService, AgendaEmpresaServicoService $gendaEmpresaServicoService )
  {
    try {
      $empresa = (object) $request->post();
      $requiredFields = ['email'];
      foreach ($requiredFields as $field) {
        if (empty($empresa->agenda_user[$field])) {
          return ResponseHelper::error(strtoupper(str_replace('_', ' ', $field)) . " É OBRIGATÓRIO", Response::HTTP_BAD_REQUEST);
        }
      }
      $id = $empresa->id;
      $email = $empresa->agenda_user['email'];

      $empresa_db = $agendaEmpresaService->findByIDEmailSummary($id, $email);
      if($empresa_db){
        $empresa_db = $gendaEmpresaServicoService->createOrUpdate($empresa_db, $empresa->agenda_empresa_servicos, $agendaEmpresaService);
      }

      return response()->json(
        $empresa_db,
        Response::HTTP_OK,
      );
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }

  public function getByIDEmpresaResource(Request $request, AgendaEmpresaServicoService $gendaEmpresaServicoService, AgendaEmpresaService $agendaEmpresaService){
    try {
      $id = $request->query('id');
      $empresa_recurso_id = $request->query('empresa_recurso_id');
      if (empty($id)) {
        return ResponseHelper::error('O "ID" É OBRIGATÓRIO', Response::HTTP_BAD_REQUEST);
      }
      if (empty($empresa_recurso_id)) {
        return ResponseHelper::error('O "RECURSO" É OBRIGATÓRIO', Response::HTTP_BAD_REQUEST);
      }

      $servico = $gendaEmpresaServicoService->findByServiceByIDEmpresaResource($id, $empresa_recurso_id, $agendaEmpresaService);
      return response()->json(
        $servico,
        Response::HTTP_OK,
      );
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }
}
