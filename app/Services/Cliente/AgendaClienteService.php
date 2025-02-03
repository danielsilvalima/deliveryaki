<?php

namespace App\Services\Cliente;

use App\Models\AgendaClienteAgendamento;
use App\Models\AgendaCliente;
use App\Models\AgendaEmpresa;
use App\Models\AgendaHorarioExpediente;
use App\Services\Empresa\AgendaEmpresaService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\Fcm\FcmService;

class AgendaClienteService
{
  private $base_url;
  public function __construct()
  {
      $this->base_url = config('app.url_agendacliente'); // Inicializa o valor da variável a partir da configuração
  }

  public function create($agendaCliente, AgendaEmpresaService $agendaEmpresaService, FcmService $fcmService){
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

      // Verificar se já existe um agendamento para a mesma data e empresa
      $agendamento_existente = AgendaClienteAgendamento::where('empresa_id', $empresa->id)
      ->where('start_scheduling_at', $start_scheduling_at)
      ->where('empresa_recurso_id', $empresa->empresa_recurso_id)
      ->exists();

      if ($agendamento_existente) {
          DB::rollBack();
          return response()->json(["message" => "JÁ EXISTE UM AGENDAMENTO PARA ESTA DATA E HORÁRIO"], 400);
      }

      // Criar o agendamento
      $agenda_cliente_agendamento = AgendaClienteAgendamento::create([
          "duracao" => $agendaCliente->duracao,
          "vlr" => $agendaCliente->vlr,
          "start_scheduling_at" => $start_scheduling_at,
          "end_scheduling_at" => $end_scheduling_at,
          "empresa_id" => $empresa->id,
          "cliente_id" => $agenda_cliente->id,
          "empresa_servico_id" => $agendaCliente->empresa_servico_id,
          "empresa_expediente_id" => $agendaCliente->empresa_expediente_id,
          "empresa_recurso_id" => $agendaCliente->empresa_recurso_id
      ]);

      DB::commit();

      return $agenda_cliente;
    } catch (\Exception $e) {
      DB::rollBack();
      throw new \Exception('ERRO AO CRIAR O AGENDAMENTO: ' . $e->getMessage());
    }
  }

  public function delete(array $agendamento, AgendaEmpresaService $agendaEmpresaService){
    DB::beginTransaction();
    try{
      $empresa = $agendaEmpresaService->findByHashEmailCliente($agendamento['hash'], $agendamento['email']);

      if(!$empresa){
        throw new \Exception("EMPRESA NÃO ENCONTRADA");
      }
      if($agendaEmpresaService->validaDataExpiracao($empresa)){
        throw new \Exception("CADASTRO DA EMPRESA EXPIRADO, ENTRE EM CONTATO COM O SUPORTE");
      }

      $agenda = AgendaClienteAgendamento::find($agendamento['id']);
      if (!$agenda) {
        throw new \Exception("AGENDAMENTO NÃO ENCONTRADO");
      }

      $agenda->tipo_notificacao = 'C';
      $agenda->notificado = false;
      $agenda->update();

      DB::commit();
      return $agenda;
    } catch (\Exception $e) {
      DB::rollBack();
      throw new \Exception('ERRO AO CRIAR O AGENDAMENTO: ' . $e->getMessage());
    }
  }

  public function horariosDisponiveis($agendaCliente, AgendaEmpresaService $agendaEmpresaService)
  {
    try {
      $hash = $agendaCliente->id;
      $data = Carbon::parse($agendaCliente->data, 'UTC')->setTimezone('America/Sao_Paulo');
      $diaSemana = $data->dayOfWeek;

      $empresa = $agendaEmpresaService->findByHash($hash);

      if(!$empresa){
        throw new \Exception("EMPRESA NÃO ENCONTRADA");
      }
      if($empresa->expiration){
        throw new \Exception("CADASTRO DA EMPRESA EXPIRADO, ENTRE EM CONTATO COM O SUPORTE");
      }

      $servico = json_decode($agendaCliente->servico, true);
      $expedientes = AgendaHorarioExpediente::with(['agenda_empresa_expedientes' => function ($query) use ($empresa, $servico) {
        $query->where('empresa_id', $empresa->id)
        ->where('empresa_recurso_id', $servico['empresa_recurso_id'])
        ->where('status', 'A');
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

          $horaAbertura = Carbon::parse($empresaExpediente->hora_abertura, 'UTC')
          ->setTimezone('America/Sao_Paulo')
          ->setDate($data->year, $data->month, $data->day);

          $horaFechamento = Carbon::parse($empresaExpediente->hora_fechamento, 'UTC')
            ->setTimezone('America/Sao_Paulo')
            ->setDate($data->year, $data->month, $data->day);

          $intervaloInicio = Carbon::parse($empresaExpediente->intervalo_inicio, 'UTC')
            ->setTimezone('America/Sao_Paulo')
            ->setDate($data->year, $data->month, $data->day);

          $intervaloFim = Carbon::parse($empresaExpediente->intervalo_fim, 'UTC')
            ->setTimezone('America/Sao_Paulo')
            ->setDate($data->year, $data->month, $data->day);

          $horariosDisponiveis = array_merge(
            $horariosDisponiveis,
            $this->gerarHorariosIntervalo($horaAbertura, $intervaloInicio, $servico['duracao'])
          );

          $horariosDisponiveis = array_merge(
            $horariosDisponiveis,
            $this->gerarHorariosIntervalo($intervaloFim, $horaFechamento, $servico['duracao'])
          );

          $empresa_expediente_id = $empresaExpediente->id;

          $horariosDisponiveis = $this->filtraHorarioDisponivel($empresa, $horariosDisponiveis, $data, $servico['duracao']);

        }
      }

      return ['horarios' => $horariosDisponiveis, 'empresa_expediente_id' => $empresa_expediente_id];
    } catch (\Exception $e) {
        return response()->json(['message' => 'ERRO AO CONSULTAR HORÁRIOS DISPONÍVEIS: ' . $e->getMessage()], 500);
    }
  }

  function gerarHorariosIntervalo($dataInicial, $dataFinal, $duracao)
  {
    $intervaloMinutos = 30;
    $horarios = [];

    $intervalo = Carbon::createFromTimeString($duracao, 'UTC')->hour * 60 + Carbon::createFromTimeString($duracao, 'UTC')->minute;

    // Garantir que a data inicial seja menor que a data final
    if ($dataInicial->greaterThanOrEqualTo($dataFinal)) {
        return $horarios; // Retorna vazio se o intervalo for inválido
    }

    while ($dataInicial->lessThan($dataFinal) && $dataInicial->copy()->setTimezone('UTC')->addMinutes($intervalo)->lessThanOrEqualTo($dataFinal)) {
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

  function filtraHorarioDisponivel($empresa, array $horariosDisponiveis, $data, $duracaoServico){
    $dataAtual = now()->format('Y-m-d');
    $agora = strtotime(now()->format('Y-m-d H:i:s'));

    if($data->format('Y-m-d') < $dataAtual){
      return [];
    }

    $agendamentos = AgendaClienteAgendamento::where('empresa_id', $empresa->id)
      ->whereDate('start_scheduling_at', $data)
      ->where('status', 'A')
      ->get();

    if ($agendamentos->isEmpty()) {
      if($data->format('Y-m-d') === $dataAtual){
        $horariosDisponiveis = array_filter($horariosDisponiveis, function($horario) use ($agora) {
          $horarioTimestamp = strtotime($horario);
          return $horarioTimestamp >= $agora ;
        });
      }
    }else{

      foreach ($agendamentos as $agendamento) {
        $startTime = strtotime($agendamento->start_scheduling_at);//10
        $endTime = strtotime($agendamento->end_scheduling_at);//11

        $intervaloMinutos = Carbon::createFromTimeString($duracaoServico, 'UTC')->hour * 60 + Carbon::createFromTimeString($duracaoServico, 'UTC')->minute;
        $startSchedulingAt = Carbon::parse($agendamento->start_scheduling_at, 'UTC');

        $horarioComparacaoAdd = strtotime($startSchedulingAt->copy()->addMinutes($intervaloMinutos)->format('Y-m-d H:i:s'));
        $horarioComparacaoSub = strtotime($startSchedulingAt->copy()->subMinutes($intervaloMinutos)->format('Y-m-d H:i:s'));

        $horariosDisponiveis = array_filter($horariosDisponiveis, function($horario) use ($startTime, $endTime, $agora) {
            $horarioTimestamp = strtotime($horario);
            return $horarioTimestamp >= $agora && $horarioTimestamp && !($horarioTimestamp >= $startTime && $horarioTimestamp < $endTime);
        });

        $horariosDisponiveis = array_filter($horariosDisponiveis, function($horario) use ($horarioComparacaoAdd, $horarioComparacaoSub) {
          $horarioTimestamp = strtotime($horario); // Converte o horário atual para timestamp
          return !($horarioTimestamp < $horarioComparacaoAdd && $horarioTimestamp > $horarioComparacaoSub);
        });
      }
    }
    return array_values($horariosDisponiveis);
  }

  public function findByHashEmailClienteAgendamento(string $hash, string $email, AgendaEmpresaService $agendaEmpresaService)
	{
    try{
      $empresa = AgendaEmpresa::select(['id', 'razao_social'])
        ->with([
            'agenda_empresa_servicos' => function ($query) {         // Filtra serviços com status = 'A'
                $query->where('status', 'A');
            },             // Relacionamento de serviços
            'agenda_clientes' => function ($query) use ($email) {   // Filtra clientes pelo email
                $query->where('email', $email)
                  ->with([
                    'agenda_cliente_agendamentos' => function ($query) {
                      $query->where('status', 'A')
                      ->orderBy('start_scheduling_at', 'DESC'); // Ordena os agendamentos
                  }
                ]);
            }
        ])
        ->where('status', 'A') // Empresa ativa
        ->where('hash', $hash)
        ->first();

      $empresa->hash = $this->base_url . $empresa->hash;

      if($agendaEmpresaService->validaDataExpiracao($empresa)){
        $empresa->expiration = true;
        $empresa->message = 'CADASTRO DA EMPRESA EXPIRADO, ENTRE EM CONTATO COM O SUPORTE';
      }else{
        $empresa->expiration = false;
      }
      unset($empresa->expiration_at);
      return $empresa;
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      throw new \Exception('ID NÃO ENCONTRADO.' . $e->getMessage());
    } catch (\Exception $e) {
        throw new \Exception('ERRO AO CONSULTAR EMPRESA: ' . $e->getMessage());
    }
	}

  public function updateStatus($agendamento){
    DB::beginTransaction();
    try{

      $agenda = AgendaClienteAgendamento::find($agendamento->id);
      if (!$agenda) {
        throw new \Exception("AGENDAMENTO NÃO ENCONTRADO");
      }

      $agenda->notificado = true;
      $agenda->update();

      DB::commit();
      return $agenda;
    } catch (\Exception $e) {
      DB::rollBack();
      throw new \Exception('ERRO AO CRIAR O AGENDAMENTO: ' . $e->getMessage());
    }
  }

  public function findServiceBydResource(string $hash, string $email, string  $empresa_recurso_id, AgendaEmpresaService $agendaEmpresaService){
    try{
      $empresa = AgendaEmpresa::select(['id', 'razao_social'])
        ->with([
            'agenda_empresa_servicos' => function ($query) use ($empresa_recurso_id) {         // Filtra serviços com status = 'A'
                $query->where('status', 'A')
                ->where('empresa_recurso_id', $empresa_recurso_id);
            },             // Relacionamento de serviços
            'agenda_clientes' => function ($query) use ($email) {   // Filtra clientes pelo email
                $query->where('email', $email);
            }
        ])
        ->where('status', 'A') // Empresa ativa
        ->where('hash', $hash)
        ->first();

      if($agendaEmpresaService->validaDataExpiracao($empresa)){
        $empresa->expiration = true;
        $empresa->message = 'CADASTRO DA EMPRESA EXPIRADO, ENTRE EM CONTATO COM O SUPORTE';
      }else{
        $empresa->expiration = false;
      }
      unset($empresa->expiration_at);
      return $empresa;
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      throw new \Exception('ID NÃO ENCONTRADO.' . $e->getMessage());
    } catch (\Exception $e) {
        throw new \Exception('ERRO AO CONSULTAR EMPRESA: ' . $e->getMessage());
    }
  }
}
