<?php

namespace App\Services\Empresa;

use InvalidArgumentException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Empresa;

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
    try{
      return Empresa::where('hash', '=', $hash)->where('status', 'A')->first();
    } catch (\Exception $e) {
      throw new \Exception('HASH INVÁLIDO');
    }
	}

  public function verificaExpedienteByHash($hash){
    $diaSemanaAtual = Carbon::now()->dayOfWeek; // 0 = domingo, ..., 6 = sábado
    $horaAtual = Carbon::now()->toTimeString();

    $empresa = Empresa::with(['empresa_expedientes.horario_expedientes'])
      ->where('hash', $hash)
      ->first();

    // Verificar se a empresa foi encontrada
    if (!$empresa || $empresa->empresa_expedientes->isEmpty()) {
      return ['status' => 'Fechado'];
    }

    $expediente = $empresa->empresa_expedientes->firstWhere('horario_expedientes.dia_semana', $diaSemanaAtual);

    if (!$expediente) {
        return ['status' => 'Fechado'];
    }

    $aberto = $horaAtual >= $expediente->hora_abertura && $horaAtual <= $expediente->hora_fechamento;
    $noIntervalo = $horaAtual >= $expediente->intervalo_inicio && $horaAtual <= $expediente->intervalo_fim;
    $status = $aberto && !$noIntervalo ? 'Aberto' : 'Fechado';

    return ['status' => $status];
  }
}
