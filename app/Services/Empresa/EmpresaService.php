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

    $empresa = Empresa::with(['expedientes.horarioExpediente'])
      ->where('hash', $hash)
      ->first();

    $expediente = $empresa->expedientes->firstWhere('horarioExpediente.dia_semana', $diaSemanaAtual);

    if (!$expediente) {
        return response()->json(['status' => 'Fechado']);
    }

    $aberto = $horaAtual >= $expediente->hora_abertura && $horaAtual <= $expediente->hora_fechamento;
    $noIntervalo = $horaAtual >= $expediente->intervalo_inicio && $horaAtual <= $expediente->intervalo_fim;
    $status = $aberto && !$noIntervalo ? 'Aberto' : 'Fechado';

    return ['status' => $status];
  }
}
