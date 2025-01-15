<?php

namespace App\Services\Empresa;

use InvalidArgumentException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\AgendaEmpresa;
use App\Models\AgendaUser;
use App\Models\AgendaEmpresaExpediente;
use App\Helpers\HashGenerator;
use App\Models\AgendaEmpresaServico;

class AgendaEmpresaService
{
  public function create($empresa)
  {
    DB::beginTransaction();
    try {
      // Adiciona 30 dias à data atual
      $expiration = Carbon::now()->addDays(30);

      $hash = null;
      do {
        $hash = HashGenerator::generateUniqueHash8Caracter();
      } while (AgendaEmpresa::where('hash', $hash)->exists());

      $empresa_db = AgendaEmpresa::create([
        'razao_social' => $empresa['razao_social'],
        'cnpj' => $empresa['cnpj'],
        'expiration_at' => $expiration,
        'hash' => $hash
      ]);

      $user_db = AgendaUser::create([
        'nome_completo' => $empresa['nome_completo'],
        'email' => $empresa['email'],
        'celular' => $empresa['celular'],
        'empresa_id' => $empresa_db->id,
      ]);

      if (!empty($empresa['listaExpedientes']) && is_array($empresa['listaExpedientes'])) {
        foreach ($empresa['listaExpedientes'] as $expediente) {
          AgendaEmpresaExpediente::create([
            'empresa_id' => $empresa_db->id,
            'horario_expediente_id' => $expediente['horario_expediente_id'],
            'hora_abertura' => $expediente['hora_abertura'],
            'hora_fechamento' => $expediente['hora_fechamento'],
            'intervalo_inicio' => $expediente['intervalo_inicio'],
            'intervalo_fim' => $expediente['intervalo_fim']
          ]);
        }
      }

      if (!empty($empresa['listaServicos']) && is_array($empresa['listaServicos'])) {
        foreach ($empresa['listaServicos'] as $servico) {
          AgendaEmpresaServico::create([
            'empresa_id' => $empresa_db->id,
            'servico_id' => $servico['servico_id'],
            'vlr' => str_replace(',', '.', $servico['vlr']),
            'duracao' => $servico['duracao'],
          ]);
        }
      }

      DB::commit();

      return $this->findByID($empresa_db->id);
    } catch (\Exception $e) {
        DB::rollBack();
        throw new \Exception('ERRO AO CRIAR A EMPRESA: ' . $e->getMessage());
    }
  }

  public function update(AgendaEmpresa $empresa)
  {
    DB::beginTransaction();
    try {
      $user_db = $empresa->agenda_user;
      if ($user_db) {
          $user_db->nome_completo = $empresa->agenda_user['nome_completo'];
          $user_db->email = $empresa->agenda_user['email'];
          $user_db->celular = $empresa->agenda_user['celular'];
          $user_db->save();
      }

      // Remover os expedientes existentes
      AgendaEmpresaExpediente::where('empresa_id', $empresa->id)->delete();
      if (!empty($empresa->listaExpedientes) && is_array($empresa->listaExpedientes)) {
        // Inserir os novos expedientes
        foreach ($empresa->listaExpedientes as $expediente) {
          AgendaEmpresaExpediente::create([
              'empresa_id' => $empresa->id,
              'horario_expediente_id' => $expediente['horario_expediente_id'],
              'hora_abertura' => $expediente['hora_abertura'],
              'hora_fechamento' => $expediente['hora_fechamento'],
              'intervalo_inicio' => $expediente['intervalo_inicio'],
              'intervalo_fim' => $expediente['intervalo_fim']
          ]);
        }
      }

      // Remover os servicos existentes
      AgendaEmpresaServico::where('empresa_id', $empresa->id)->delete();
      if (!empty($empresa->listaServicos) && is_array($empresa->listaServicos)) {
        // Inserir os novos expedientes
        foreach ($empresa->listaServicos as $servico) {
          AgendaEmpresaServico::create([
              'empresa_id' => $empresa->id,
              'servico_id' => $servico['servico_id'],
              'vlr' => str_replace(',', '.', $servico['vlr']),
              'duracao' => $servico['duracao'],
          ]);
        }
      }

      unset($empresa->listaExpedientes);
      unset($empresa->listaServicos);
      $empresa->save();

      DB::commit();

      return $this->findByID($empresa->id);
    } catch (\Exception $e) {
        DB::rollBack();
        throw new \Exception('ERRO AO ATUALIZAR A EMPRESA: ' . $e->getMessage());
    }
  }

  public function findAll()
	{
    return AgendaEmpresa::with(['agenda_user', 'agenda_empresa_expedientes'])->get();
	}

  public function findByID(string $id)
	{
    return AgendaEmpresa::with([
      'agenda_user',
      'agenda_empresa_expedientes.agenda_horario_expedientes',
      'agenda_empresa_servicos.agenda_servicos'
      ])->find($id);
	}

  public function findByHash(string $hash)
	{
    try{
      return AgendaEmpresa::where('hash', '=', $hash)->where('status', 'A')->first();
    } catch (\Exception $e) {
      throw new \Exception('HASH INVÁLIDO');
    }
	}

  public function findByEmail(string $email)
	{
    try{
      return AgendaEmpresa::with([
        'agenda_user', // Relacionamento direto com usuários
        'agenda_empresa_expedientes.agenda_horario_expedientes', // Relacionamento de expediente e horários
        'agenda_empresa_servicos.agenda_servicos' // Relacionamento de serviços
      ])
      ->whereHas('agenda_user', function ($query) use ($email) {
          $query->where('email', $email)
                ->where('status', 'A'); // Usuário ativo
      })
      ->where('status', 'A') // Empresa ativa
      ->first();
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      throw new \Exception('EMAIL NÃO ENCONTRADO.' . $e->getMessage());
    } catch (\Exception $e) {
        throw new \Exception('ERRO AO CONSULTAR EMPRESA: ' . $e->getMessage());
    }
	}

  /*public function verificaExpedienteByHash($hash){
    $diaSemanaAtual = Carbon::now()->dayOfWeek; // 0 = domingo, ..., 6 = sábado
    $horaAtual = Carbon::now()->toTimeString();

    $empresa = AgendaEmpresa::with(['empresa_expedientes.horario_expedientes'])
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
    $mensagem = $aberto && !$noIntervalo ? 'Estamos Abertos! Seja Bem-vindo(a)!' : 'Estamos Fechados';
    $status = $aberto && !$noIntervalo ? 'aberto' : 'fechado';

    return ['status' => $status, 'mensagem' => $mensagem];
  }*/
}
