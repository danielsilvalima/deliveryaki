<?php
namespace App\Services\Fcm;

use NotificationChannels\Fcm\FcmMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Pedido;
use Kreait\Firebase\Factory;


class FcmService
{
  public function enviaPushNotification($pedido_id)
  {
    try{
      $pedido = Pedido::whereHas('pedido_notificacaos', function ($query) use ($pedido_id) {
        $query->where('pedido_id', $pedido_id);
      })->with('pedido_notificacaos')->first();

      $token = optional($pedido->pedido_notificacaos->first())->token_notificacao;
      if (!$pedido || !$token) {
        throw new \Exception('CLIENTE/PEDIDO NÃO ENCONTRADO OU TOKEN DO DISPOSITIVO NÃO DISPONÍVEL');
      }

      $firebase = (new Factory)->withServiceAccount(config('firebase.credentials.file'));
      $messaging = $firebase->createMessaging();

      $message = [
        'token' => $token, // Token do dispositivo cliente
        'notification' => [
            'title' => 'SEU PEDIDO ESTÁ A CAMINHO!',
            'body' => "O PEDIDO #$pedido_id SAIU PARA ENTREGA",
        ],
        'data' => [
            'pedido_id' => $pedido_id,
            'status' => 'saiu para entrega'
        ]
      ];

      $messaging->send($message);

      return response()->json(['success' => true, 'message' => 'NOTIFICAÇÃO ENVIADA COM SUCESSO']);
    } catch (\Exception $e) {
      //throw new \Exception('FALHA AO ENVIAR A NOTIFICAÇÃO DO PEDIDO: '.$pedido_id. $e);
      throw new \Exception($e);
      // Log de erro
      /*Log::error('Erro no envio de notificação', [
          'pedido_id' => $pedido_id,
          'error' => $e->getMessage()
      ]);

      return response()->json(['error' => false, 'message' => 'Erro ao enviar a notificação.', 'error' => $e->getMessage()], 500);*/
    }
  }
}
