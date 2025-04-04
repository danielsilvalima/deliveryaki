<?php

namespace App\Services\Fcm;

use NotificationChannels\Fcm\FcmMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Pedido;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Log;

class FcmService
{
  public function enviaPushNotificationDelivery($pedido, $token)
  {
    try {
      if (!$pedido) {
        throw new \Exception('Pedido não foi localizado');
      }

      if (!$token) {
        return [
          'success' => true,
          'message' => 'Cliente/Pedido não encontrado ou Token do dispositivo não disponível',
        ];
      }

      $firebase = (new Factory())->withServiceAccount(config('firebase.credentials.file'));
      $messaging = $firebase->createMessaging();

      $message = [
        'token' => $token,
        'notification' => [
          'title' => 'Seu pedido está a caminho!',
          'body' => "O pedido #{$pedido->id} saiu para entrega",
        ],
        'data' => [
          'pedido_id' => (string) $pedido->id,
          'status' => 'saiu para entrega',
        ],
      ];

      $messaging->send($message);

      return [
        'success' => true,
        'message' => 'Notificação enviada com sucesso.',
      ];
    } catch (\Kreait\Firebase\Exception\Messaging\NotFound $e) {
      return [
        'success' => false,
        'message' => 'Token de notificação inválido ou expirado.',
      ];
    } catch (\Exception $e) {
      throw new \Exception('Falha ao enviar a notificação do pedido: ' . $e->getMessage());
    }
  }

  public function enviaPushNotificationCanceled($pedido, $token)
  {
    try {
      if (!$pedido) {
        throw new \Exception('Pedido não foi localizado');
      }

      if (!$token) {
        return [
          'success' => true,
          'message' => 'Cliente/Pedido não encontrado ou Token do dispositivo não disponível',
        ];
      }

      $firebase = (new Factory())->withServiceAccount(config('firebase.credentials.file'));
      $messaging = $firebase->createMessaging();

      $message = [
        'token' => $token,
        'notification' => [
          'title' => 'Seu pedido foi cancelado!',
          'body' => "O pedido #{$pedido->id} foi cancelado",
        ],
        'data' => [
          'pedido_id' => (string) $pedido->id,
          'status' => 'cancelado',
        ],
      ];

      $messaging->send($message);

      return [
        'success' => true,
        'message' => 'Notificação enviada com sucesso',
      ];
    } catch (\Kreait\Firebase\Exception\Messaging\NotFound $e) {
      return [
        'success' => false,
        'message' => 'Token de notificação inválido ou expirado.',
      ];
    } catch (\Exception $e) {
      throw new \Exception('FALHA AO ENVIAR A NOTIFICAÇÃO DO PEDIDO: ' . $e->getMessage());
    }
  }

  public function enviaPushNotificationAgendaAdmin($empresa, $mensagem, $titulo)
  {
    try {
      if (!$empresa) {
        throw new \Exception('EMPRESA NÃO FOI LOCALIZADO');
      }

      if (!$empresa->token_notificacao) {
        return [
          'success' => false,
          'message' => 'EMPRESA NÃO ENCONTRADO OU TOKEN DO DISPOSITIVO NÃO DISPONÍVEL',
        ];
      }

      $firebase = (new Factory())->withServiceAccount(config('firebase.credentials.file'));
      $messaging = $firebase->createMessaging();

      $message = [
        'token' => $empresa->token_notificacao,
        'notification' => [
          'title' => $titulo,
          'body' => $mensagem,
        ],
        'data' => [
          'pedido_id' => '2',
          'status' => 'saiu para entrega',
        ],
      ];

      $messaging->send($message);

      return [
        'success' => true,
        'message' => 'NOTIFICAÇÃO ENVIADA COM SUCESSO',
      ];
    } catch (\Exception $e) {
      Log::error('Erro ao enviar notificação: ' . $e->getMessage());

      // Retorna erro sem interromper o fluxo
      return [
        'success' => false,
        'message' => 'FALHA AO ENVIAR A NOTIFICAÇÃO',
      ];
    }
  }
}
