<?php

namespace App\Services\Cliente;

use App\Models\AgendaClienteAgendamento;
use App\Models\AgendaCliente;
use App\Models\AgendaEmpresa;
use App\Models\AgendaHorarioExpediente;
use App\Services\Empresa\AgendaEmpresaService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AgendaClienteService
{
  public function create($agendaCliente, AgendaEmpresaService $agendaEmpresaService){
    DB::beginTransaction();
    try{
      $empresa = $agendaEmpresaService->findByHashEmailCliente($agendaCliente->id, $agendaCliente->email);

      if(!$empresa){
        throw new \Exception("EMPRESA NÃO ENCONTRADA");
      }
      if($agendaEmpresaService->validaDataExpiracao($empresa)){
        throw new \Exception("CADASTRO DA EMPRESA EXPIRADO, ENTRE EM CONTATO COM O SUPORTE");
      }

      $agenda_cliente = AgendaCliente::where('email', $agendaCliente->email)
          ->where('empresa_id', $empresa->id)
          ->first();

      if ($agenda_cliente) {
        $agenda_cliente->update([
            'nome_completo' => $agendaCliente->nome_completo,
            'celular' => $agendaCliente->celular,
        ]);
      } else {
        $agenda_cliente = AgendaCliente::create([
          "nome_completo" => $agendaCliente->nome_completo,
          "email" => $agendaCliente->email,
          "cnpj" => $agendaCliente->cnpj,
          "celular" => $agendaCliente->celular,
          "empresa_id" => $empresa->id,
        ]);
      }

      $start_scheduling_at = $agendaCliente->data. ' '.$agendaCliente->horario;

      $end_scheduling_at = $this->getEndScheduling($start_scheduling_at, $agendaCliente->duracao);

      $agenda_cliente_agendamento = AgendaClienteAgendamento::create([
        "duracao" => $agendaCliente->duracao,
        "vlr" => $agendaCliente->vlr,
        "start_scheduling_at" => $start_scheduling_at,
        "end_scheduling_at" => $end_scheduling_at,
        "empresa_id" => $empresa->id,
        "cliente_id" => $agenda_cliente->id,
        "empresa_servico_id" =>$agendaCliente->empresa_servico_id,
        "empresa_expediente_id" => $agendaCliente->empresa_expediente_id
      ]);

      DB::commit();

      return $agenda_cliente;
    } catch (\Exception $e) {
      DB::rollBack();
      throw new \Exception('ERRO AO CRIAR O AGENDAMENTO: ' . $e->getMessage());
    }
  }

  public function horariosDisponiveis($agendaCliente, AgendaEmpresaService $agendaEmpresaService)
  {
    try {
      $hash = $agendaCliente->id;
      $data = Carbon::parse($agendaCliente->data);
      $diaSemana = $data->dayOfWeek;

      $empresa = $agendaEmpresaService->findByHash($hash);

      if(!$empresa){
        throw new \Exception("EMPRESA NÃO ENCONTRADA");
      }
      if($empresa->expiration){
        throw new \Exception("CADASTRO DA EMPRESA EXPIRADO, ENTRE EM CONTATO COM O SUPORTE");
      }

      $servico = json_decode($agendaCliente->servico, true);
      $duracaoServico = Carbon::createFromTimeString($servico['duracao'])->diffInMinutes();

      $expedientes = AgendaHorarioExpediente::with(['agenda_empresa_expedientes' => function ($query) use ($empresa) {
        $query->where('empresa_id', $empresa->id);
      }])
      ->where('dia_semana', $diaSemana)
      ->get();

      if ($expedientes->isEmpty()) {
        return [];
      }

      $horariosDisponiveis = [];
      $empresa_expediente_id = null;
      // Itera pelos expedientes do dia e gera os horários disponíveis
      foreach ($expedientes as $expediente) {
        foreach ($expediente->agenda_empresa_expedientes as $empresaExpediente) {
          // Recuperar os horários de abertura, fechamento e intervalos
          $horaAbertura = Carbon::parse($empresaExpediente->hora_abertura, 'UTC')->setTimezone('America/Sao_Paulo');
          $horaFechamento = Carbon::parse($empresaExpediente->hora_fechamento, 'UTC')->setTimezone('America/Sao_Paulo');
          $intervaloInicio = Carbon::parse($empresaExpediente->intervalo_inicio, 'UTC')->setTimezone('America/Sao_Paulo');
          $intervaloFim = Carbon::parse($empresaExpediente->intervalo_fim, 'UTC')->setTimezone('America/Sao_Paulo');

          $horariosDisponiveis = array_merge(
            $horariosDisponiveis,
            $this->gerarHorariosIntervalo($horaAbertura, $intervaloInicio)
          );

          $horariosDisponiveis = array_merge(
            $horariosDisponiveis,
            $this->gerarHorariosIntervalo($intervaloFim, $horaFechamento)
          );

          $empresa_expediente_id = $empresaExpediente->id;

          $horariosDisponiveis = $this->filtraHorarioDisponivel($empresa, $horariosDisponiveis, $data);
        }
      }

      return ['horarios' => $horariosDisponiveis, 'empresa_expediente_id' => $empresa_expediente_id];
    } catch (\Exception $e) {
        return response()->json(['message' => 'ERRO AO CONSULTAR HORÁRIOS DISPONÍVEIS: ' . $e->getMessage()], 500);
    }
  }

  function gerarHorariosIntervalo($dataInicial, $dataFinal, $intervaloMinutos = 30)
  {
    $horarios = [];

    // Garantir que a data inicial seja menor que a data final
    if ($dataInicial->greaterThanOrEqualTo($dataFinal)) {
        return $horarios; // Retorna vazio se o intervalo for inválido
    }

    // Gerar os horários
    while ($dataInicial->lessThan($dataFinal)) {
      $horarios[] = $dataInicial->copy()->setTimezone('UTC')->format('Y-m-d H:i');
      $dataInicial->addMinutes($intervaloMinutos); // Incrementar pelo intervalo
    }

    return $horarios;
  }

  function getEndScheduling($dataInicial, $duracao){
    $data = Carbon::createFromFormat('Y-m-d H:i', $dataInicial, 'UTC');
    $intervaloMinutos = Carbon::createFromTimeString($duracao, 'UTC')->hour * 60 + Carbon::createFromTimeString($duracao, 'UTC')->minute;

    // Adicionar o intervalo de minutos à data inicial
    return $data->addMinutes($intervaloMinutos)->setTimezone('UTC')->format('Y-m-d H:i:s');
  }

  function filtraHorarioDisponivel($empresa, array $horariosDisponiveis, $data){
    $agendamentos = AgendaClienteAgendamento::where('empresa_id', $empresa->id)
      ->whereDate('start_scheduling_at', $data)
      ->get();

    // Filtra os horários disponíveis removendo os agendados
    foreach ($agendamentos as $agendamento) {
        $startTime = strtotime($agendamento->start_scheduling_at);
        $endTime = strtotime($agendamento->end_scheduling_at);

        // Remove os horários que caem dentro do intervalo do agendamento
        $horariosDisponiveis = array_filter($horariosDisponiveis, function($horario) use ($startTime, $endTime) {
            $horarioTimestamp = strtotime($horario);
            return !($horarioTimestamp >= $startTime && $horarioTimestamp < $endTime);
        });
    }

    // Retorna os horários disponíveis
    return array_values($horariosDisponiveis); // Reindexa o array
  }
}
