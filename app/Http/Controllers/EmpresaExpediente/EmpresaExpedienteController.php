<?php

namespace App\Http\Controllers\EmpresaExpediente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmpresaExpediente;
use App\Services\EmpresaExpediente\AgendaEmpresaExpedienteService;
use App\Services\Empresa\AgendaEmpresaService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Response;

class EmpresaExpedienteController extends Controller
{
  public function destroy(string $id, EmpresaExpediente $empresaExpediente)
  {
    try{
      $empresaExpediente = EmpresaExpediente::findOrFail($id);

      $empresaExpediente->delete();

      return redirect()->route('empresa.edit', $empresaExpediente->empresa_id);
    } catch (\Exception $e) {
      return back()->with('error', 'NÃO FOI POSSÍVEL ATUALIZAR O EXPEDIENTE. '.$e);
    }
  }

  public function get (Request $request, AgendaEmpresaExpedienteService $agendaEmpresaExpedienteService, AgendaEmpresaService $agendaEmpresaService){
    try{
      if (empty($request->id)) {
        return ResponseHelper::error('O "ID" É OBRIGATÓRIOOO', Response::HTTP_BAD_REQUEST);
      }
      if (empty($request->empresa_recurso_id)) {
        return ResponseHelper::error('O "RECURSO" É OBRIGATÓRIOOO', Response::HTTP_BAD_REQUEST);
      }

      $expediente = $agendaEmpresaExpedienteService->findExpedienteByIDEmpresa($request, $agendaEmpresaService);
      return response()->json(
        $expediente,
        Response::HTTP_OK,
      );
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }

  public function store(Request $request, AgendaEmpresaExpedienteService $agendaEmpresaExpedienteService, AgendaEmpresaService $agendaEmpresaService){
    try{
      $empresa = (object) $request->post();
      $requiredFields = ['id', 'email'];
      foreach ($requiredFields as $field) {
        if (empty($empresa->agenda_user[$field])) {
          return ResponseHelper::error(strtoupper(str_replace('_', ' ', $field)) . " É OBRIGATÓRIO", Response::HTTP_BAD_REQUEST);
        }
      }

      $id = $empresa->id;
      $email = $empresa->agenda_user['email'];

      $empresa_db = $agendaEmpresaService->findByIDEmailSummary($id, $email);
      if($empresa_db){
        $empresa_db = $agendaEmpresaExpedienteService->createOrUpdate($empresa_db, $empresa->agenda_empresa_expedientes, $agendaEmpresaService);
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
