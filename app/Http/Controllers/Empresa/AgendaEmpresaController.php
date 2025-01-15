<?php

namespace App\Http\Controllers\Empresa;

use App\Http\Controllers\Controller;
use App\Services\Empresa\AgendaEmpresaService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\ResponseHelper;

class AgendaEmpresaController extends Controller
{
  /*private $header = array(
    'Content-Type' => 'application/json; charset=UTF-8',
    'charset' => 'utf-8'
  );
  private $options = JSON_UNESCAPED_UNICODE;*/

  public function get(Request $request, AgendaEmpresaService $agendaEmpresaService)
  {
    try{
      $email = $request->query('email');
      if (empty($email)) {
        return ResponseHelper::error('O "E-MAIL" É OBRIGATÓRIO', Response::HTTP_BAD_REQUEST);
      }
      $empresa = $agendaEmpresaService->findByEmail($email);

      return response()->json(
        $empresa,
        Response::HTTP_OK,
        //$this->header,
        //$this->options
      );
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }

  public function store(Request $request, AgendaEmpresaService $agendaEmpresaService)
  {
    try {
      $requiredFields = ['razao_social', 'cnpj', 'email', 'nome_completo'];
        foreach ($requiredFields as $field) {
            if (empty($request->$field)) {
                return ResponseHelper::error(strtoupper(str_replace('_', ' ', $field)) . " É OBRIGATÓRIO", Response::HTTP_BAD_REQUEST);
            }
        }

      $empresa = $agendaEmpresaService->findByEmail($request->email);
      if($empresa){
        $empresa->razao_social = strtoupper($request->razao_social);
        $empresa->cnpj = $request->cnpj;

        if ($empresa->agenda_user) {
            $empresa->agenda_user->nome_completo = $request->nome_completo;
            $empresa->agenda_user->email = $request->email;
            $empresa->agenda_user->celular = $request->celular;
        }

        $empresa->listaExpedientes = $request->listaExpedientes ?? []; // Atualiza os expedientes
        $empresa->listaServicos = $request->listaServicos ?? []; // Atualiza os servicos

        $empresa = $agendaEmpresaService->update($empresa);
      }else{
        $empresa = $agendaEmpresaService->create(
          $request
        );
      }

      return response()->json(
        $empresa,
        Response::HTTP_OK,
      );
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }
}
