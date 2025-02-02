<?php

namespace App\Services\EmpresaServico;
use Illuminate\Support\Facades\DB;
use App\Models\AgendaServico;
use App\Models\AgendaEmpresa;
use App\Models\AgendaEmpresaServico;
use App\Services\Empresa\AgendaEmpresaService;


class AgendaEmpresaServicoService
{
  public function findAll()
  {
    return AgendaEmpresaServico::select('id', 'descricao')->where('status', 'A')->orderBy('descricao', 'ASC')->get();
  }

  public function createOrUpdate(AgendaEmpresa $empresa, string $agenda_empresa_recursos, array $listaServicos, AgendaEmpresaService $agendaEmpresaService)
  {
    try {
        // Validação de expiração da empresa
        $this->handleEmpresaExpiration($empresa, $agendaEmpresaService);

        // IDs dos serviços existentes para rastreamento
        $servicoIdsExistentes = [];

        if(empty($listaServicos) && $empresa->agenda_empresa_recursos){
          $this->deactivateMissingServicesAll($empresa, $agenda_empresa_recursos);
          return $empresa->load('agenda_empresa_servicos');
        }

        foreach ($listaServicos as $servico) {
            if (!empty($servico['id'])) {
                // Atualizar serviço existente
                $this->updateExistingService($empresa, $servico, $servicoIdsExistentes, );
            } else {
                // Criar novo serviço
                $this->createNewService($empresa, $servico, $servicoIdsExistentes, );
            }
        }

        $empresa_recurso_id = array_column($listaServicos, 'agenda_empresa_recursos');
        // Desativar serviços não presentes na lista
        $this->deactivateMissingServices($empresa, array_column($empresa_recurso_id, 'id'), $servicoIdsExistentes);

        return $empresa->load('agenda_empresa_servicos'); // Retorna a empresa com os serviços atualizados
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

  private function updateExistingService(
    AgendaEmpresa $empresa,
    array $servico,
    array &$servicoIdsExistentes
  ) {
      $servicoExistente = AgendaEmpresaServico::where('id', $servico['id'])
          ->where('empresa_id', $empresa->id)
          ->first();

      if ($servicoExistente) {
          $servicoExistente->update([
              'duracao' => $servico['duracao'],
              'vlr' => $servico['vlr'],
              'descricao' => strtoupper($servico['descricao']),
              'empresa_recurso_id' => $servico['agenda_empresa_recursos']['id']
          ]);

          $servicoIdsExistentes[] = $servico['id'];
      } else {
          throw new \Exception("SERVIÇO NÃO LOCALIZADO: " . $servico['descricao']);
      }
  }

  private function createNewService(AgendaEmpresa $empresa, array $servico, array &$servicoIdsExistentes)
  {
    $servicoNovo = AgendaEmpresaServico::create([
        'empresa_id' => $empresa->id,
        'duracao' => $servico['duracao'],
        'vlr' => $servico['vlr'],
        'descricao' => strtoupper($servico['descricao']),
        'empresa_recurso_id' => $servico['agenda_empresa_recursos']['id']
    ]);

    $servicoIdsExistentes[] = $servicoNovo->id;
  }

  private function deactivateMissingServices(AgendaEmpresa $empresa, array $empresaRecursoIds, array $servicoIdsExistentes)
  {
    AgendaEmpresaServico::where('empresa_id', $empresa->id)
      ->whereNotIn('id', $servicoIdsExistentes)
      ->where('empresa_recurso_id', $empresaRecursoIds)
      ->update(['status' => 'D']);
  }

  private function deactivateMissingServicesAll(AgendaEmpresa $empresa, string $empresaRecursoIds)
  {
    AgendaEmpresaServico::where('empresa_id', $empresa->id)
      ->where('empresa_recurso_id', $empresaRecursoIds)
      ->update(['status' => 'D']);
  }

  public function findByServiceByID($id, AgendaEmpresaService $agendaEmpresaService){
    try{
      $empresa = AgendaEmpresa::with([
        'agenda_empresa_servicos' => function ($query) {
            $query->where('status', 'A')
                  ->with(['agenda_empresa_recursos' => function ($q) { // Pega os recursos de cada serviço
                      $q->where('status', 'A');
                  }]);
        },
        'agenda_empresa_recursos' => function ($query) {
            $query->where('status', 'A');
        }
      ])
      ->where('status', 'A')
      ->where('id', $id)
      ->first();

      if($empresa){
        if($agendaEmpresaService->validaDataExpiracao($empresa)){
          $empresa->expiration = true;
          $empresa->message = 'CADASTRO DA EMPRESA EXPIRADO, ENTRE EM CONTATO COM O SUPORTE';
        }else{
          $empresa->expiration = false;
        }
        unset($empresa->expiration_at);
        return $empresa;
      }else{
        return $empresa;
      }
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function findByServiceByIDEmpresaResource($id, $empresa_recurso_id, AgendaEmpresaService $agendaEmpresaService){
    try{
      $empresa = AgendaEmpresa::with([
        'agenda_empresa_servicos' => function ($query) use($empresa_recurso_id) {
            $query->where('status', 'A')
            ->where('empresa_recurso_id', $empresa_recurso_id)
            ->with('agenda_empresa_recursos');
        },
      ])
      ->where('status', 'A')
      ->where('id', $id)
      ->first();

      if($empresa){
        if($agendaEmpresaService->validaDataExpiracao($empresa)){
          $empresa->expiration = true;
          $empresa->message = 'CADASTRO DA EMPRESA EXPIRADO, ENTRE EM CONTATO COM O SUPORTE';
        }else{
          $empresa->expiration = false;
        }
        unset($empresa->expiration_at);
        return $empresa;
      }else{
        return $empresa;
      }
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }
}
