<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\ResponseHelper;
use App\Services\Empresa\AgendaEmpresaService;
use App\Services\Cliente\AgendaClienteService;

class AgendaClienteController extends Controller
{
  public function getByIDEmail(Request $request, AgendaEmpresaService $agendaEmpresaService)
  {
    try{
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

  public function get(Request $request, AgendaClienteService $agendaClienteService, AgendaEmpresaService $agendaEmpresaService){
    try{

      if (empty($request->id)) {
        return ResponseHelper::error('O "ID" É OBRIGATÓRIOOO', Response::HTTP_BAD_REQUEST);
      }
      if (empty($request->data)) {
        return ResponseHelper::error('A "DATA" É OBRIGATÓRIOOOO', Response::HTTP_BAD_REQUEST);
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

  public function store(Request $request, AgendaClienteService $agendaClienteService, AgendaEmpresaService $agendaEmpresaService){
    try{
      if (empty($request->id)) {
        return ResponseHelper::error('O "ID" É OBRIGATÓRIO', Response::HTTP_BAD_REQUEST);
      }
      if (empty($request->email)) {
        return ResponseHelper::error('O "E-MAIL" É OBRIGATÓRIO', Response::HTTP_BAD_REQUEST);
      }
      if (empty($request->data)) {
        return ResponseHelper::error('A "DATA" É OBRIGATÓRIO', Response::HTTP_BAD_REQUEST);
      }

      $empresa = $agendaClienteService->create($request, $agendaEmpresaService);

      return response()->json(
        $empresa,
        Response::HTTP_OK,
      );
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }
}
