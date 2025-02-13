<?php

namespace App\Services\NumeroVirtual;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class NumeroVirtualService
{
  private $TOKEN;
  private $API_URL;
  private $TOKEN_SMS;
  private $TOKEN_GN_ID;
  private $TOKEN_GN_SEC;
  private $URL_GN;
  public function __construct()
  {
    $this->TOKEN = config('app.telegram_bot');
    $this->API_URL = "https://api.telegram.org/$this->TOKEN/";
    $this->TOKEN_SMS = config('app.telegram_sms');
    $this->TOKEN_GN_ID = config('app.telegram_gn_id');
    $this->TOKEN_GN_SEC = config('app.telegram_gn_sec');
    $this->URL_GN = 'https://pix.api.efipay.com.br';
  }

  public function start(Request $request)
  {
    try {
      $update = json_decode(file_get_contents('php://input'), true);
      Log::info('COMECANDO');
      //Log::info($update);
      $this->gravaUsername($update);

      if (isset($update['callback_query'])) {
        Log::info('Callback detectado!');
        $callback_data = $update['callback_query']['data'];
        $chat_id = $update['callback_query']['message']['chat']['id'];

        if ($callback_data === 'comprar_whatsapp') {
          $this->sendMessage($chat_id, 'Você escolheu comprar um número para WhatsApp!');
        } elseif ($callback_data === 'comprar_telegram') {
          $this->sendMessage($chat_id, 'Você escolheu comprar um número para Telegram!');
        }

        $this->responderCallbackQuery($update['callback_query']['id']);
        return;
      }

      if (!isset($update['message'])) {
        return;
      }
      $chat_id = $update['message']['chat']['id'];
      $text = strtolower($update['message']['text']);
      Log::info($text);

      if (isset($update['callback_query'])) {
        Log::info('ENTROU');
        $callback_data = $update['callback_query']['data'];
        $chat_id = $update['callback_query']['message']['chat']['id'];

        if ($callback_data === 'comprar_whatsapp' || $callback_data === 'comprar_telegram') {
          //$this->sendMessage($chat_id, "Você escolheu comprar um número para WhatsApp!");
          $numero_virtual = $this->comprarNumeroVirtual($callback_data);
          //$this->sendMessage($chat_id, "Pagamento confirmado! Seu número virtual é: $numero_virtual");
        } /*elseif ($callback_data === "comprar_telegram") {
            $this->sendMessage($chat_id, "Você escolheu comprar um número para Telegram!");
            $numero_virtual = $this->comprarNumeroVirtual();
            $this->sendMessage($chat_id, "Pagamento confirmado! Seu número virtual é: $numero_virtual");
        }*/
      }
      if ($text == '/start') {
        $keyboard = [
          'keyboard' => [
            [['text' => '📲 Comprar Número']],
            [['text' => 'Adicionar Saldo']],
            [['text' => '🔗 Link de Indicação']],
            [['text' => '❓ Ajuda']],
          ],
          'resize_keyboard' => true,
          'one_time_keyboard' => false,
        ];

        $this->sendMessage($chat_id, 'Bem-vindo! Escolha uma opção abaixo:', $keyboard);
      } elseif ($text == '1') {
        $this->sendMessage($chat_id, "O número virtual para WhatsApp custa R$ 7,50. Digite 'comprar' para prosseguir.");
      } elseif ($text == '2') {
        $this->sendMessage($chat_id, "O número virtual para Telegram custa R$ 7,50. Digite 'comprar' para prosseguir.");
      } elseif ($text == '/recarregar') {
        $this->mostrarOpcoesNumeros($chat_id);
      } elseif ($text == '/recarregar') {
        $pix_copia_e_cola = $this->gerarPixCopiaCola(7.5);
        $this->sendMessage(
          $chat_id,
          "🔹 *Pagamento via PIX*\n\n" .
            "📌 Copie o código abaixo e cole no seu app bancário para pagar:\n\n`$pix_copia_e_cola`"
        );
      } elseif ($text == 'confirmar pagamento') {
        if ($this->verificarPagamento($chat_id)) {
          //$numero_virtual = $this->comprarNumeroVirtual();
          //$this->sendMessage($chat_id, "Pagamento confirmado! Seu número virtual é: $numero_virtual");
        } else {
          $this->sendMessage($chat_id, 'Ainda não identificamos o pagamento. Tente novamente mais tarde.');
        }
      } else {
        $this->sendMessage($chat_id, 'Opção inválida.');
      }
    } catch (Exception $e) {
      Log::error($e->getMessage());
      return [$e->getMessage()];
    }
  }

  public function mostrarOpcoesNumeros($chat_id)
  {
    try {
      $keyboard = [
        'inline_keyboard' => [
          [['text' => 'WhatsApp | R$ 7,50', 'callback_data' => 'comprar_whatsapp']],
          [['text' => 'Telegram | R$ 7,50', 'callback_data' => 'comprar_telegram']],
        ],
      ];

      $dados = [
        'chat_id' => $chat_id,
        'text' => 'Escolha um número virtual:',
        'reply_markup' => $keyboard,
      ];

      $url = $this->API_URL . 'sendMessage';
      $response = Http::withHeaders(['Content-Type' => 'application/json'])->post($url, $dados);
    } catch (Exception $e) {
      Log::error($e->getMessage());
      return [$e->getMessage()];
    }
  }

  public function sendMessage($chat_id, $message, $keyboard = null)
  {
    try {
      $data = [
        'chat_id' => $chat_id,
        'text' => $message,
        //'parse_mode' => 'MarkdownV2'
      ];

      if ($keyboard) {
        $data['reply_markup'] = $keyboard;
      }

      $url = $this->API_URL . 'sendMessage';
      Http::post($url, $data);
    } catch (Exception $e) {
      Log::error($e->getMessage());
      return [$e->getMessage()];
    }
  }

  private function responderCallbackQuery($callbackQueryId)
  {
    try {
      $url = $this->API_URL . 'answerCallbackQuery';

      Http::post($url, [
        'callback_query_id' => $callbackQueryId,
        'text' => 'Processando sua escolha...',
        'show_alert' => false,
      ]);
    } catch (Exception $e) {
      Log::error($e->getMessage());
      return [$e->getMessage()];
    }
  }

  public function comprarNumeroVirtual($service)
  {
    try {
      // Mapeia os serviços corretamente
      $serviceMap = [
        'comprar_whatsapp' => 'wa',
        'comprar_telegram' => 'tg',
      ];

      if (!isset($serviceMap[$service])) {
        return 'Serviço inválido.';
      }

      $api_key = $this->TOKEN_SMS;
      $url = 'https://api.sms-activate.org/stubs/handler_api.php';

      // Parâmetros da requisição
      $params = [
        'api_key' => $api_key,
        'action' => 'getNumber',
        'service' => $serviceMap[$service], // 'wa' para WhatsApp, 'tg' para Telegram
        'country' => 'BR', // Código do país (Brasil)
      ];

      // Requisição usando Http do Laravel
      $response = Http::get($url, $params)->body();
      Log::info('GERAR NUMERO');
      Log::info($response);
      // Verifica se a resposta contém um número válido
      if (str_starts_with($response, 'ACCESS_NUMBER')) {
        list(, $id, $number) = explode(':', $response);
        return [
          'id' => $id,
          'numero' => $number,
        ];
      }

      return "Erro ao obter número virtual: $response";
    } catch (Exception $e) {
      Log::error($e->getMessage());
      return [$e->getMessage()];
    }
  }

  public function gerarPixCopiaCola($valor)
  {
    try {
      // 🔹 Obter o token de acesso
      $access_token = $this->obterAccessToken();
      if (!$access_token) {
        throw new Exception('Falha ao obter token de acesso da Gerencianet.');
      }

      // 🔹 Criar cobrança PIX com valor dinâmico
      $pix_data = [
        'calendario' => ['expiracao' => 3600], // Expira em 1 hora
        'valor' => ['original' => number_format($valor, 2, '.', '')],
        'chave' => 'b6e54434-7ef2-4266-aecd-71f6b5779d27',
        'solicitacaoPagador' => 'Pagamento via PIX',
      ];

      $response = $this->chamarApiGerencianet($this->URL_GN . '/v2/cob', 'POST', $pix_data, $access_token, null);

      if (!isset($response['loc']['id'])) {
        throw new Exception('Erro ao gerar PIX: Resposta inválida.');
      }

      $loc_id = $response['loc']['id'];

      // 🔹 Obter o QR Code e o código Copia e Cola
      $qrcode_response = $this->chamarApiGerencianet(
        $this->URL_GN . "/v2/loc/{$loc_id}/qrcode",
        'GET',
        [],
        $access_token,
        null
      );

      if (!isset($qrcode_response['qrcode'])) {
        throw new Exception('Erro ao obter código Copia e Cola.');
      }

      return $qrcode_response['qrcode'];
    } catch (Exception $e) {
      Log::error($e->getMessage());
      return [$e->getMessage()];
    }
  }

  private function chamarApiGerencianet($url, $method, $data = [], $access_token = null, $headers = [])
  {
    try {
      if ($access_token) {
        $headers['Authorization'] = "Bearer $access_token";
      } else {
        if (isset($data['grant_type']) && $data['grant_type'] === 'client_credentials') {
          $credentials = base64_encode($this->TOKEN_GN_ID . ':' . $this->TOKEN_GN_SEC);
          $headers['Authorization'] = "Basic $credentials";
        }
      }

      $response = Http::withHeaders($headers)
        ->withOptions([
          'cert' => base_path('app/certificate/cert.p12'),
        ])
        ->{$method}($url, $data);

      return $response->json();
    } catch (\Exception $e) {
      Log::error($e->getMessage());
      return ['error' => $e->getMessage()];
    }
  }

  private function obterAccessToken()
  {
    $url = $this->URL_GN . '/oauth/token';
    $credentials = base64_encode($this->TOKEN_GN_ID . ':' . $this->TOKEN_GN_SEC);

    $headers = ['Content-Type: application/json', "Authorization: Basic $credentials"];

    $data = ['grant_type' => 'client_credentials'];

    $response = $this->chamarApiGerencianet($url, 'POST', $data, null, $headers);

    return $response['access_token'] ?? null;
  }

  public function verificarPagamento($chat_id)
  {
    // Aqui você verificaria a API do Gerencianet/Mercado Pago para ver se o pagamento foi recebido
    // Exemplo fictício, retornando true para simular um pagamento confirmado
    return true;
  }

  public function gravaUsername($message)
  {
    try {
      if (isset($message['message']['from'])) {
        $from = $message['message']['from'];
        Log::info($from);

        // Capturar 'username' dentro de 'from'
        if (isset($from['username'])) {
          $username = $from['username'];
          Log::info('Username: ' . $username);
        } else {
          Log::info('Username não encontrado.');
        }
      } else {
        Log::info("Campo 'from' não encontrado.");
      }
    } catch (\Exception $e) {
      Log::error($e->getMessage());
      return ['error' => $e->getMessage()];
    }
  }
}
