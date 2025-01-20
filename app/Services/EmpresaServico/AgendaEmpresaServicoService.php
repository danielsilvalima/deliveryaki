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

  public function createOrUpdate(AgendaEmpresa $empresa, array $listaServicos, AgendaEmpresaService $agendaEmpresaService)
  {
    try {
        // Validação de expiração da empresa
        $this->handleEmpresaExpiration($empresa, $agendaEmpresaService);

        // IDs dos serviços existentes para rastreamento
        $servicoIdsExistentes = [];

        foreach ($listaServicos as $servico) {
            if (!empty($servico['id'])) {
                // Atualizar serviço existente
                $this->updateExistingService($empresa, $servico, $servicoIdsExistentes);
            } else {
                // Criar novo serviço
                $this->createNewService($empresa, $servico, $servicoIdsExistentes);
            }
        }

        // Desativar serviços não presentes na lista
        $this->deactivateMissingServices($empresa, $servicoIdsExistentes);

        return $empresa->load('agenda_empresa_servicos'); // Retorna a empresa com os serviços atualizados
    } catch (\Exception $e) {
        throw new \Exception("ERRO AO ATUALIZAR SERVIÇOS: " . $e->getMessage());
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
              'descricao' => strtoupper($servico['descricao'])
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
    ]);

    $servicoIdsExistentes[] = $servicoNovo->id;
  }

  private function deactivateMissingServices(AgendaEmpresa $empresa, array $servicoIdsExistentes)
  {
    // Desativar registros em AgendaEmpresaServico
    AgendaEmpresaServico::where('empresa_id', $empresa->id)
        ->whereNotIn('id', $servicoIdsExistentes)
        ->update(['status' => 'D']);
  }
}
