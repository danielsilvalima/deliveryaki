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
  private $base_url;
  public function __construct()
  {
      $this->base_url = config('app.url_agendacliente'); // Inicializa o valor da variável a partir da configuração
  }

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
        'razao_social' => strtoupper($empresa['razao_social']),
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
              'vlr' => str_replace(',', '.', $servico['vlr']),
              'duracao' => $servico['duracao'],
          ]);
        }
      }

      unset($empresa->listaExpedientes);
      unset($empresa->listaServicos);
      unset($empresa->expiration);
      unset($empresa->message);
      unset($empresa->hash);
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
      'agenda_empresa_servicos'
      ])->find($id);
	}

  public function findByEmail(string $email)
	{
    try{
      $empresa = AgendaEmpresa::with([
        'agenda_user', // Relacionamento direto com usuários
        'agenda_empresa_expedientes.agenda_horario_expedientes', // Relacionamento de expediente e horários
        'agenda_empresa_servicos' => function ($query) { // Relacionamento de serviços da empresa
            $query->where('status', 'A'); // Apenas registros com status 'A'
        }
      ])
      ->whereHas('agenda_user', function ($query) use ($email) {
          $query->where('email', $email)
                ->where('status', 'A'); // Usuário ativo
      })
      ->where('status', 'A') // Empresa ativa
      ->first();

      if($empresa){
        $empresa->hash = $empresa->hash ? $this->base_url . $empresa->hash : '';

        if($this->validaDataExpiracao($empresa)){
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
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      throw new \Exception('EMAIL NÃO ENCONTRADO.' . $e->getMessage());
    } catch (\Exception $e) {
        throw new \Exception('ERRO AO CONSULTAR EMPRESA: ' . $e->getMessage());
    }
	}

  public function findByEmailSummary(string $email)
	{
    try{
      $empresa = AgendaEmpresa::with([
        'agenda_user', // Relacionamento direto com usuários
      ])
      ->whereHas('agenda_user', function ($query) use ($email) {
          $query->where('email', $email)
                ->where('status', 'A'); // Usuário ativo
      })
      ->where('status', 'A') // Empresa ativa
      ->first();

      if($empresa){
        if($this->validaDataExpiracao($empresa)){
          return [];
        }else{
          return $empresa;
        }
      }else{
        return $empresa;
      }
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      throw new \Exception('EMAIL NÃO ENCONTRADO.' . $e->getMessage());
    } catch (\Exception $e) {
        throw new \Exception('ERRO AO CONSULTAR EMPRESA: ' . $e->getMessage());
    }
	}

  public function findByHashEmailCliente(string $hash, string $email)
	{
    try{
      $empresa = AgendaEmpresa::select(['id', 'razao_social'])
        ->with([
            'agenda_empresa_expedientes.agenda_horario_expedientes', // Relacionamento de expediente e horários
            'agenda_empresa_servicos' => function ($query) {         // Filtra serviços com status = 'A'
                $query->where('status', 'A');
            },             // Relacionamento de serviços
            'agenda_clientes' => function ($query) use ($email) {   // Filtra clientes pelo email
                $query->where('email', $email)
                      ->with(['agenda_cliente_agendamentos']);   // Inclui os agendamentos do cliente
            }
        ])
        ->where('status', 'A') // Empresa ativa
        ->where('hash', $hash)
        ->first();

      $empresa->hash = $this->base_url . $empresa->hash;

      if($this->validaDataExpiracao($empresa)){
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

  public function findByHash(string $hash)
	{
    try{
      $empresa = AgendaEmpresa::select(['id', 'razao_social'])
        ->with([
            'agenda_empresa_expedientes.agenda_horario_expedientes', // Relacionamento de expediente e horários
            'agenda_empresa_servicos',              // Relacionamento de serviços
            'agenda_clientes'
        ])
        ->where('status', 'A') // Empresa ativa
        ->where('hash', $hash)
        ->first();

      $empresa->hash = $this->base_url . $empresa->hash;

      if($this->validaDataExpiracao($empresa)){
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

  public function validaDataExpiracao(AgendaEmpresa $empresa){
    $dataHoje = Carbon::now()->startOfDay(); // Ajusta a data atual para o início do dia
    $dataExpiracao = Carbon::parse($empresa->expiration_at)->startOfDay(); // Ajusta a data de expiração para o início do dia

    // Retorna true se a data de expiração for igual ou posterior à data de hoje
    return $dataExpiracao < $dataHoje;
  }

}
