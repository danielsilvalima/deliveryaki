<?php

namespace App\Services\EmpresaExpediente;
use Illuminate\Support\Facades\DB;
use App\Models\AgendaEmpresaExpediente;
use App\Models\AgendaEmpresa;
use App\Services\Empresa\AgendaEmpresaService;

class AgendaEmpresaExpedienteService
{
  public function findExpedienteByIDEmpresa($agendaEmpresa, AgendaEmpresaService $agendaEmpresaService){
    try{
      $empresa = AgendaEmpresa::with([
        'agenda_empresa_expedientes' => function ($query) use ($agendaEmpresa) {
            $query->where('empresa_recurso_id', $agendaEmpresa->empresa_recurso_id)
              ->where('status', 'A')
                  ->with(
                    'agenda_horario_expedientes',
                    'agenda_empresa_recursos');
          }
        ])

        ->where('status', 'A') // Empresa ativa
        ->where('id', $agendaEmpresa->id) // Ajuste conforme a origem do ID da empresa
        ->first();

      if($agendaEmpresaService->validaDataExpiracao($empresa)){
        $empresa->expiration = true;
        $empresa->message = 'CADASTRO DA EMPRESA EXPIRADO, ENTRE EM CONTATO COM O SUPORTE';
      }else{
        $empresa->expiration = false;
      }

      if(!$empresa){
        throw new \Exception("EMPRESA NÃO ENCONTRADA");
      }
      if($empresa->expiration){
        throw new \Exception("CADASTRO DA EMPRESA EXPIRADO, ENTRE EM CONTATO COM O SUPORTE");
      }

      unset($empresa->token_notificacao, $empresa->hash);

      return $empresa;
    } catch (\Exception $e) {
      throw new \Exception('ERRO AO CONSULTAR EMPRESA: ' . $e->getMessage());
    }
  }

  public function createOrUpdate(AgendaEmpresa $empresa, string $agenda_empresa_recursos, array $listaExpedientes, AgendaEmpresaService $agendaEmpresaService)
  {
    try {
        // Validação de expiração da empresa
        $this->handleEmpresaExpiration($empresa, $agendaEmpresaService);

        // IDs dos serviços existentes para rastreamento
        $expedienteIdsExistentes = [];

        if(empty($listaExpedientes) && $empresa->agenda_empresa_recursos){
          $this->deactivateMissingOfficeHoursAll($empresa, $agenda_empresa_recursos);
          return $empresa->load('agenda_empresa_expedientes');
        }

        foreach ($listaExpedientes as $expediente) {
            if (!empty($expediente['id'])) {
                // Atualizar expediente existente
                $this->updateExistingOfficeHours($empresa, $expediente, $expedienteIdsExistentes, );
            } else {
                // Criar novo serviço
                $this->createNewOfficeHours($empresa, $expediente, $expedienteIdsExistentes, );
            }
        }

        $empresa_recurso_id = array_column($listaExpedientes, 'agenda_empresa_recursos');
        // Desativar expediente não presentes na lista
        $this->deactivateMissingOfficeHours($empresa, array_column($empresa_recurso_id, 'id'), $expedienteIdsExistentes);

        return $empresa->load('agenda_empresa_expedientes'); // Retorna a empresa com os expedientes atualizados
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  private function handleEmpresaExpiration(AgendaEmpresa $empresa, AgendaEmpresaService $agendaEmpresaService)
  {
    if ($agendaEmpresaService->validaDataExpiracao($empresa)) {
        $empresa->expiration = true;
        $empresa->message = 'CADASTRO DA EMPRESA EXPIRADO, ENTRE EM CONTATO COM O SUPORTE';
    } else {
        $empresa->expiration = false;
    }
    unset($empresa->expiration_at);
  }

  private function updateExistingOfficeHours(
    AgendaEmpresa $empresa,
    array $expediente,
    array &$expedienteIdsExistentes
  ) {
      $expedienteExistente = AgendaEmpresaExpediente::where('id', $expediente['id'])
          ->where('empresa_id', $empresa->id)
          ->where('empresa_recurso_id', $expediente['empresa_recurso_id'])
          ->first();

      if ($expedienteExistente) {
          $expedienteExistente->update([
              'horario_expediente_id' => $expediente['horario_expediente_id'],
              'hora_abertura' => $expediente['hora_abertura'],
              'hora_fechamento' => $expediente['hora_fechamento'],
              'intervalo_inicio' => $expediente['intervalo_inicio'],
              'intervalo_fim' => $expediente['intervalo_fim'],
              'empresa_recurso_id' => $expediente['agenda_empresa_recursos']['id']
          ]);

          $expedienteIdsExistentes[] = $expediente['id'];
      } else {
          throw new \Exception("EXPEDIENTE NÃO LOCALIZADO: " . $expediente['id']);
      }
  }

  private function createNewOfficeHours(AgendaEmpresa $empresa, array $expediente, array &$expedienteIdsExistentes)
  {
    $expedienteNovo = AgendaEmpresaExpediente::create([
        'empresa_id' => $empresa->id,
        'horario_expediente_id' => $expediente['horario_expediente_id'],
        'hora_abertura' => $expediente['hora_abertura'],
        'hora_fechamento' => $expediente['hora_fechamento'],
        'intervalo_inicio' => $expediente['intervalo_inicio'],
        'intervalo_fim' => $expediente['intervalo_fim'],
        'empresa_recurso_id' => $expediente['agenda_empresa_recursos']['id']
    ]);

    $expedienteIdsExistentes[] = $expedienteNovo->id;
  }

  private function deactivateMissingOfficeHours(AgendaEmpresa $empresa, array $empresaRecursoIds, array $expedienteIdsExistentes)
  {
    // Desativar registros em AgendaEmpresaServico
    AgendaEmpresaExpediente::where('empresa_id', $empresa->id)
        ->whereNotIn('id', $expedienteIdsExistentes)
        ->where('empresa_recurso_id', $empresaRecursoIds)
        ->update(['status' => 'D']);
  }

  private function deactivateMissingOfficeHoursAll(AgendaEmpresa $empresa, string $empresaRecursoIds)
  {
    // Desativar registros em AgendaEmpresaServico
    AgendaEmpresaExpediente::where('empresa_id', $empresa->id)
        ->where('empresa_recurso_id', $empresaRecursoIds)
        ->update(['status' => 'D']);
  }

}
