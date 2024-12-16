<?php
namespace App\Services\Fcm;

use NotificationChannels\Fcm\FcmMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Pedido;
use Kreait\Firebase\Factory;


class FcmService
{
  public function enviaPushNotification($pedido, $token)
  {
    try{
      if(!$pedido){
        throw new \Exception('PEDIDO NÃO FOI LOCALIZADO');
      }

      if (!$token) {
        return [
          'success' => true,
          'message' => 'CLIENTE/PEDIDO NÃO ENCONTRADO OU TOKEN DO DISPOSITIVO NÃO DISPONÍVEL'
        ];
      }

      $firebase = (new Factory)->withServiceAccount(config('firebase.credentials.file'));
      $messaging = $firebase->createMessaging();

      $message = [
        'token' => $token,
        'notification' => [
            'title' => 'SEU PEDIDO ESTÁ A CAMINHO!',
            'body' => "O PEDIDO #$pedido->id SAIU PARA ENTREGA",
        ],
        'data' => [
            'pedido_id' => $pedido->id,
            'status' => 'saiu para entrega'
        ]
      ];

      $messaging->send($message);

      return response()->json(['success' => true, 'message' => 'NOTIFICAÇÃO ENVIADA COM SUCESSO']);
    } catch (\Exception $e) {
      throw new \Exception('FALHA AO ENVIAR A NOTIFICAÇÃO DO PEDIDO: '. $e->getMessage());
    }
  }
}
