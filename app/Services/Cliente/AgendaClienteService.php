<?php

namespace App\Services\Cliente;

use App\Models\AgendaClienteAgendamento;
use App\Models\AgendaCliente;
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

      $cliente = AgendaCliente::create([
        "nome_completo" => $agendaCliente->nome_completo,
        "email" => $agendaCliente->email,
        "cnpj" => $agendaCliente["cnpj"],
        "celular" => $agendaCliente["celular"],
        "empresa_id" => $empresa->id,
      ])->id;

      $start_scheduling = $agendaCliente->data. ' : '.$agendaCliente->horario;
      //acrescentar a duracao
      $end_scheduling = "";
      $cliente = AgendaClienteAgendamento::create([
        "duracao" => $agendaCliente->duracao,
        "start_scheduling" => $start_scheduling,
        "end_scheduling" => $end_scheduling,
        "empresa_id" => $empresa->id,
        "cliente_id" => $cliente->id,
        "servico_id" => $agendaCliente->servico_id,
        "empresa_servico_id" =>$agendaCliente->empresa_servico_id
      ])->id;

      DB::commit();

      return $cliente;
    } catch (\Exception $e) {
      DB::rollBack();
      throw new \Exception('Erro ao criar o pedido: ' . $e->getMessage());
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

      //$servico = json_decode($agendaCliente->servico, true);
      $servico = ["id"=>"13", "duracao"=>"00:30:00", "empresa_id"=>1,"servico_id"=>10];//"updated_at":"2025-01-16T20:56:52.000000Z",,"vlr":"120.00","status":"A",,"agenda_servicos":{"id":10,"empresa_id":1,"created_at":"2025-01-16T20:25:30.000000Z","updated_at":"2025-01-16T20:25:30.000000Z","descricao":"SERVICO DE TESTE","status":"A"}];
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
        }
      }

      return $horariosDisponiveis;
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
}
