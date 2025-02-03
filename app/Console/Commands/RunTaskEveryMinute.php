<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Fcm\FcmService;
use App\Services\Empresa\AgendaEmpresaService;
use App\Services\Cliente\AgendaClienteService;
use App\Models\AgendaEmpresa;
use Illuminate\Support\Carbon;
use App\Mail\NotificacaoEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class RunTaskEveryMinute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-task-every-minute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'nvia notificações push para os administradores sobre a agenda';

    /**
     * Execute the console command.
     */
    public function handle(FcmService $fcmService, AgendaEmpresaService $agendaEmpresaService, AgendaClienteService $agendaClienteService)
    {
      try{
        $this->info('Iniciando envio de notificações...');
        $empresas = $agendaEmpresaService->findByAtivoNotExpirated();
        foreach($empresas as $empresa){
          if(!empty($empresa->token_notificacao)){//notificacao para o admin
            foreach ($empresa->agenda_cliente_agendamentos as $agendamento){
              if(!$agendamento->notificado && $agendamento->tipo_notificacao === 'A'){
                $this->enviaNotificacaoAgendamento($empresa, $agendamento, $fcmService, $agendaClienteService);
              }else if(!$agendamento->notificado && $agendamento->tipo_notificacao === 'C'){
                $this->enviaNotificacaoCancelamentoAgendamento($empresa, $agendamento, $fcmService, $agendaClienteService);
              }

            }

            $this->info('Notificação enviada para empresa '.$empresa->razao_social);
          }else if($empresa && !$empresa->token_notificacao){
            $this->line('Empresa '. $empresa->razao_social.' está sem token');
          }

          if($empresa->notificado === false){
            $this->enviarEmail($empresa);
          }
          $this->info('Notificações enviadas com sucesso!');
        }

      } catch (\Exception $e) {
        $this->error('Erro ao enviar notificações: ' . $e->getMessage());
      }

      $this->line('Processo finalizado.');
      return 0;
    }

    public function enviaNotificacaoCancelamentoAgendamento($empresa, $agendamento, FcmService $fcmService, AgendaClienteService $agendaClienteService){
      $mensagem = "AGENDAMENTO CANCELADO PARA: ".
      Carbon::parse($agendamento->start_scheduling_at)->format('Y-m-d H:i') .
      " COM TÉRMINO EM: " . Carbon::parse($agendamento->end_scheduling_at)->format('Y-m-d H:i');
      $titulo = "AGENDAMENTO CANCELADO";
      $retorno = $fcmService->enviaPushNotificationAgendaAdmin($empresa, $mensagem, $titulo);
      $this->line('Retorno da notificacao.');
      $this->line($retorno);
      $this->atualizaStatusAgendamentoCliente($agendamento, $agendaClienteService);
    }

    public function enviaNotificacaoAgendamento($empresa, $agendamento, FcmService $fcmService, AgendaClienteService $agendaClienteService){
      $mensagem = "NOVO AGENDAMENTO PARA: ".
      Carbon::parse($agendamento->start_scheduling_at)->format('Y-m-d H:i') .
      " COM TÉRMINO EM: " . Carbon::parse($agendamento->end_scheduling_at)->format('Y-m-d H:i');
      $titulo = "NOVO AGENDAMENTO";
      $retorno = $fcmService->enviaPushNotificationAgendaAdmin($empresa, $mensagem, $titulo);
      $this->line('Retorno da notificacao.');
      $this->line($retorno);
      $this->atualizaStatusAgendamentoCliente($agendamento, $agendaClienteService);
    }

    public function atualizaStatusAgendamentoCliente($agendamento, $gendaClienteService){
      $gendaClienteService->updateStatus($agendamento);
    }

    public function enviarEmail($empresa)
    {
      Log::info('Método enviarEmail() chamado para: ' . $empresa['email']);

      $emailDestino = config('app.email_adress');
      $mensagem = "NOVO CLIENTE COM EXPIRAÇÃO PARA: ". Carbon::parse($empresa['expiration_at'])->format('d/m/Y H:i');

      if (empty($emailDestino)) {
          Log::error('Erro: MAIL_FROM_ADDRESS não está configurado.');
          return;
      }

      $dados = [
          'nome' => $empresa['razao_social'],
          'mensagem' => $mensagem
      ];

      try {
          Mail::to($emailDestino)->send(new NotificacaoEmail($dados));
          Log::info('E-mail enviado com sucesso para ' . $emailDestino);

          AgendaEmpresa::where('id', $empresa['id'])->update(['notificado' => true]);
      } catch (\Exception $e) {
          Log::error('Erro ao enviar e-mail: ' . $e->getMessage());
      }
    }

}
