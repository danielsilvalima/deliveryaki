<?php

namespace App\Services\Empresa;

use InvalidArgumentException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Empresa;
use App\Models\EmpresaExpediente;
use Illuminate\Http\Request;

class EmpresaService
{
  public function findAll()
  {
    return Empresa::all();
  }

  public function findByID(string $id)
  {
    return Empresa::where('id', '=', $id)->first();
  }

  public function findByUUID(string $id)
  {
    return Empresa::where('uuid', '=', $id)->first();
  }

  public function findByHash(string $hash)
  {
    try {
      return Empresa::where('hash', '=', $hash)
        ->where('status', 'A')
        ->first();
    } catch (\Exception $e) {
      throw new \Exception('Hash inválido');
    }
  }

  public function verificaExpedienteByHash($hash)
  {
    $diaSemanaAtual = Carbon::now()->dayOfWeek; // 0 = domingo, ..., 6 = sábado
    $horaAtual = Carbon::now()->toTimeString();

    $empresa = Empresa::with(['empresa_expedientes.horario_expedientes'])
      ->where('hash', $hash)
      ->first();

    // Verificar se a empresa foi encontrada
    if (!$empresa || $empresa->empresa_expedientes->isEmpty()) {
      return ['mensagem' => 'Estamos Fechados', 'status' => 'fechado'];
    }

    $expediente = $empresa->empresa_expedientes->firstWhere('horario_expedientes.dia_semana', $diaSemanaAtual);

    if (!$expediente) {
      return ['mensagem' => 'Estamos Fechados', 'status' => 'fechado'];
    }

    $aberto = $horaAtual >= $expediente->hora_abertura && $horaAtual <= $expediente->hora_fechamento;
    $noIntervalo = $horaAtual >= $expediente->intervalo_inicio && $horaAtual <= $expediente->intervalo_fim;

    $horaAbertura = date('H\H:i', strtotime($expediente->hora_abertura)) . 'MIN';
    $horaFechamento = date('H\H:i', strtotime($expediente->hora_fechamento)) . 'MIN';

    $mensagem =
      $aberto && !$noIntervalo ? "Estamos Abertos das {$horaAbertura} até {$horaFechamento}" : 'Estamos Fechados';
    $status = $aberto && !$noIntervalo ? 'aberto' : 'fechado';

    return ['status' => $status, 'mensagem' => $mensagem];
  }

  public function update(Request $request, Empresa $empresa)
  {
    DB::beginTransaction();
    try {
      if ($request->hasFile('imagem')) {
        $this->deleteOldFile($empresa->id);

        $directory = "public/logos/empresas/{$empresa->cnpj}";
        $file = $request->file('imagem');
        $filename = uniqid() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs($directory, $filename);
        $empresa->path = $recurso['path'] = str_replace('public/', '', $filePath);
        $empresa->save();
      }

      $empresa->update(
        $request->only([
          'cnpj',
          'razao_social',
          'telefone',
          'celular',
          'email',
          'status',
          'cep',
          'logradouro',
          'numero',
          'complemento',
          'bairro',
          'cidade',
          'uf',
          'vlr_km',
          'tipo_taxa',
          'inicio_distancia',
        ])
      );

      $expedientes = json_decode($request->input('expedientes'), true);

      if (!empty($expedientes)) {
        // Obtém os IDs existentes para evitar re-criação desnecessária
        $existentes = EmpresaExpediente::where('empresa_id', $empresa->id)->pluck('horario_expediente_id')->toArray();

        foreach ($expedientes as $expediente) {
          if (!in_array($expediente['horario_expediente_id'], $existentes)) {
            EmpresaExpediente::create([
              'empresa_id' => $empresa->id,
              'horario_expediente_id' => $expediente['horario_expediente_id'],
              'hora_abertura' => $expediente['hora_abertura'],
              'hora_fechamento' => $expediente['hora_fechamento'],
              'intervalo_inicio' => $expediente['intervalo_inicio'],
              'intervalo_fim' => $expediente['intervalo_fim'],
            ]);
          }
        }
        // Remove os que não estão mais na lista
        EmpresaExpediente::where('empresa_id', $empresa->id)
          ->whereNotIn('horario_expediente_id', array_column($expedientes, 'horario_expediente_id'))
          ->delete();
      }

      DB::commit();

      return $empresa;
    } catch (\Exception $e) {
      DB::rollBack();
      //throw new \Exception('ERRO AO EDITAR A EMPRESA ' . $e->getMessage());
      return back()->with('error', 'EMPRESA NÃO FOI ATUALIZADA. ' . $e->getMessage());
    }
  }

  public function deleteOldFile($id)
  {
    $empresa = Empresa::find($id);
    if ($empresa && $empresa->path) {
      $oldFilePath = storage_path("app/public/{$empresa->path}");
      if (file_exists($oldFilePath)) {
        unlink($oldFilePath);
      } else {
        info("Arquivo não encontrado: " . $oldFilePath);
      }
    }
  }

  public function removeCaracteres($valor)
  {
    return preg_replace('/\D/', '', $valor);
  }

  public function validaDataExpiracao(Empresa $empresa)
  {
    $dataHoje = Carbon::now()->startOfDay(); // Ajusta a data atual para o início do dia
    $dataExpiracao = Carbon::parse($empresa->expiration_at)->startOfDay(); // Ajusta a data de expiração para o início do dia

    // Retorna true se a data de expiração for igual ou posterior à data de hoje
    return $dataExpiracao < $dataHoje;
  }
}
