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
  public function get(Request $request, AgendaEmpresaServicoService $gendaEmpresaServicoService)
  {
    try{
      $agendaServico = $gendaEmpresaServicoService->findAll();

      return response()->json(
        $agendaServico,
        Response::HTTP_OK,
      );
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }

  public function store(Request $request, AgendaEmpresaService $agendaEmpresaService, AgendaEmpresaServicoService $gendaEmpresaServicoService, )
  {
    try {
      $empresa = (object) $request->post();
      $requiredFields = ['email'];
      foreach ($requiredFields as $field) {
        if (empty($empresa->agenda_user[$field])) {
          return ResponseHelper::error(strtoupper(str_replace('_', ' ', $field)) . " É OBRIGATÓRIO", Response::HTTP_BAD_REQUEST);
        }
      }

      $empresa_db = $agendaEmpresaService->findByEmailSummary($empresa->agenda_user['email']);
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
}
