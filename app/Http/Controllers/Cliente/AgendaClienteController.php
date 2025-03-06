<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\ResponseHelper;
use App\Services\Empresa\AgendaEmpresaService;
use App\Services\Cliente\AgendaClienteService;
use App\Services\Fcm\FcmService;

class AgendaClienteController extends Controller
{
  public function getByIDEmail(Request $request, AgendaEmpresaService $agendaEmpresaService)
  {
    try {
      $hash = $request->query('id');
      $email = $request->query('email');
      if (empty($hash)) {
        return ResponseHelper::error('O "ID" É OBRIGATÓRIO', Response::HTTP_BAD_REQUEST);
      }
      if (empty($email)) {
        return ResponseHelper::error('O "E-MAIL" É OBRIGATÓRIO', Response::HTTP_BAD_REQUEST);
      }
      $empresa = $agendaEmpresaService->findByHashEmailCliente($hash, $email);

      return response()->json(
        $empresa,
        Response::HTTP_OK,
      );
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }

  public function get(Request $request, AgendaClienteService $agendaClienteService, AgendaEmpresaService $agendaEmpresaService)
  {
    try {
      if (empty($request->id)) {
        return ResponseHelper::error('O "ID" É OBRIGATÓRIO', Response::HTTP_BAD_REQUEST);
      }
      if (empty($request->data)) {
        return ResponseHelper::error('A "DATA" É OBRIGATÓRIO', Response::HTTP_BAD_REQUEST);
      }

      $agendaCliente = $agendaClienteService->horariosDisponiveis($request, $agendaEmpresaService);
      return response()->json(
        $agendaCliente,
        Response::HTTP_OK,
      );
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }

  public function store(Request $request, AgendaClienteService $agendaClienteService, AgendaEmpresaService $agendaEmpresaService, FcmService $fcmService)
  {
    try {
      if (empty($request->id)) {
        return ResponseHelper::error('O "ID" É OBRIGATÓRIO', Response::HTTP_BAD_REQUEST);
      }
      if (empty($request->email)) {
        return ResponseHelper::error('O "E-MAIL" É OBRIGATÓRIO', Response::HTTP_BAD_REQUEST);
      }
      if (empty($request->data)) {
        return ResponseHelper::error('A "DATA" É OBRIGATÓRIO', Response::HTTP_BAD_REQUEST);
      }

      $empresa = $agendaClienteService->create($request, $agendaEmpresaService, $fcmService);

      if ($empresa instanceof \Illuminate\Http\JsonResponse) {
        return $empresa;
      }

      return response()->json([
        "message" => "AGENDAMENTO CRIADO COM SUCESSO!",
        "data" => $empresa
      ], Response::HTTP_OK);
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }

  public function getAgendamentoByIDEmail(Request $request, AgendaClienteService $agendaClienteService, AgendaEmpresaService $agendaEmpresaService)
  {
    try {
      $hash = $request->query('id');
      $email = $request->query('email');
      if (empty($hash)) {
        return ResponseHelper::error('O "ID" É OBRIGATÓRIO', Response::HTTP_BAD_REQUEST);
      }

      if (empty($email)) {
        return ResponseHelper::error('O "E-MAIL" É OBRIGATÓRIO', Response::HTTP_BAD_REQUEST);
      }
      $empresa = $agendaClienteService->findByHashEmailClienteAgendamento($hash, $email, $agendaEmpresaService);

      return response()->json(
        $empresa,
        Response::HTTP_OK,
      );
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }

  public function destroy(Request $request, AgendaClienteService $agendaClienteService, AgendaEmpresaService $agendaEmpresaService)
  {
    try {
      $requiredFields = ['id', 'empresa_servico_id', 'empresa_id', 'hash', 'email'];
      foreach ($requiredFields as $field) {
        if (empty($request->agendamento[$field])) {
          return ResponseHelper::error("O campo \"$field\" é obrigatório.", Response::HTTP_BAD_REQUEST);
        }
      }

      $agenda_cliente = $agendaClienteService->delete($request->agendamento, $agendaEmpresaService);

      return response()->json(
        $agenda_cliente,
        Response::HTTP_OK,
      );
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }

  public function getClienteByIDEmail(Request $request, AgendaClienteService $agendaClienteService, AgendaEmpresaService $agendaEmpresaService)
  {
    try {
      $requiredFields = ['id', 'empresa_recurso_id', 'email'];
      foreach ($requiredFields as $field) {
        if (empty($request[$field])) {
          return ResponseHelper::error("O campo \"$field\" é obrigatório.", Response::HTTP_BAD_REQUEST);
        }
      }
      $hash = $request->query('id');
      $email = $request->query('email');
      $empresa_recurso_id = $request->query('empresa_recurso_id');

      $agenda_cliente = $agendaClienteService->findServiceBydResource($hash, $email, $empresa_recurso_id, $agendaEmpresaService);

      return response()->json(
        $agenda_cliente,
        Response::HTTP_OK,
      );
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }
}
