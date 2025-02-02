<?php

namespace App\Services\EmpresaRecurso;
use Illuminate\Support\Facades\DB;
use App\Models\AgendaEmpresaRecurso;
use App\Models\AgendaEmpresa;
use App\Services\Empresa\AgendaEmpresaService;
use Illuminate\Http\Request;


class AgendaEmpresaRecursoService
{
  public function createOrUpdate(AgendaEmpresa $empresa, array $listaRecursos, Request $request, AgendaEmpresaService $agendaEmpresaService)
  {
    try {
        // Validação de expiração da empresa
        $this->handleEmpresaExpiration($empresa, $agendaEmpresaService);

        // IDs dos recursos existentes para rastreamento
        $recursoIdsExistentes = [];

        foreach ($listaRecursos as $index => $recurso) {
          if ($request->hasFile("agenda_empresa_recursos.$index.imagem") && empty($recurso->id)) {
            $imagem = $request->file("agenda_empresa_recursos.$index.imagem");

            if ($imagem->isValid()) {
                $directory = "public/recursos/{$empresa->cnpj}";

                $timestamp = strtotime(now());
                $extension = $imagem->getClientOriginalExtension();
                $fileName = "{$empresa->cnpj}_{$timestamp}.{$extension}";
                $filePath = $imagem->storeAs($directory, $fileName);

                // Ajustar caminho para o banco
                $recurso['path'] = str_replace('public/', '', $filePath);
            }
          }

          if (!empty($recurso['id'])) {
              // Atualizar recurso existente
              $this->updateExistingRecurso($empresa, $recurso, $recursoIdsExistentes);
          } else {
              // Criar novo recurso
              $this->createNewRecurso($empresa, $recurso, $recursoIdsExistentes);
          }
        }

        // Desativar serviços não presentes na lista
        $this->deactivateMissingResources($empresa, $recursoIdsExistentes);
        return $this->findByResourceByID($empresa->id, $agendaEmpresaService);
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

  private function updateExistingRecurso(
    AgendaEmpresa $empresa,
    array $recurso,
    array &$recursoIdsExistentes
  ) {
      $recursoExistente = AgendaEmpresaRecurso::where('id', $recurso['id'])
          ->where('empresa_id', $empresa->id)
          ->first();

      if ($recursoExistente) {
          $recursoExistente->update([
              'descricao' => strtoupper($recurso['descricao']),
              'path' => $recurso['path']
          ]);

          $recursoIdsExistentes[] = $recurso['id'];
      } else {
        throw new \Exception("SERVIÇO NÃO LOCALIZADO: " . $recurso['descricao']);
      }
  }

  private function createNewRecurso(AgendaEmpresa $empresa, array $recurso, array &$recursoIdsExistentes)
  {
    $recursoNovo = AgendaEmpresaRecurso::create([
        'empresa_id' => $empresa->id,
        'descricao' => strtoupper($recurso['descricao']),
        'path' => $recurso['path']
    ]);

    $recursoIdsExistentes[] = $recursoNovo->id;
  }

  private function deactivateMissingResources(AgendaEmpresa $empresa, array $recursoIdsExistentes)
  {
    try{
      $recursosParaDesativar = AgendaEmpresaRecurso::where('empresa_id', $empresa->id)
        ->whereNotIn('id', $recursoIdsExistentes)
        ->get();

      foreach ($recursosParaDesativar as $recurso) {
          $this->deleteOldFile($recurso->id); // Remove o arquivo antes de desativar
      }

      // Desativar os registros no banco de dados
      AgendaEmpresaRecurso::where('empresa_id', $empresa->id)
        ->whereNotIn('id', $recursoIdsExistentes)
        ->update(['status' => 'D']);
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function findByResourceByID($id, AgendaEmpresaService $agendaEmpresaService){
    try{
      $empresa = AgendaEmpresa::with([
        'agenda_empresa_recursos' => function ($query) { // Relacionamento de recursos da empresa
            $query->where('status', 'A'); // Apenas registros com status 'A'
        },
      ])
      ->where('status', 'A') // Empresa ativa
      ->where('id', $id)
      ->first();

      if($empresa){
        if($agendaEmpresaService->validaDataExpiracao($empresa)){
          $empresa->expiration = true;
          $empresa->message = 'CADASTRO DA EMPRESA EXPIRADO, ENTRE EM CONTATO COM O SUPORTE';
        }else{
          $empresa->expiration = false;
        }
        unset($empresa->expiration_at, $empresa->token_notificacao, $empresa->hash);
        return $empresa;
      }else{
        return $empresa;
      }
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  private function deleteOldFile($recursoId)
  {
    $recurso = AgendaEmpresaRecurso::find($recursoId);
    if ($recurso && $recurso->path) {
        $oldFilePath = storage_path("app/public/{$recurso->path}");
        if (file_exists($oldFilePath)) {
            unlink($oldFilePath);
        }else {
          info("Arquivo não encontrado: " . $oldFilePath);
        }
    }
  }
}
