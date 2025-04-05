<?php

namespace App\Services\NumeroVirtual;

use App\Models\VirtualTransacao;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\VirtualUser;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class NumeroVirtualService
{
  private $TOKEN1;
  private $TOKEN2;
  private $TOKEN3;
  private $API_URL1;
  private $API_URL2;
  private $API_URL3;
  private $TOKEN_SMS;
  private $TOKEN_GN_ID;
  private $TOKEN_GN_SEC;
  private $URL_GN;
  public function __construct()
  {
    $this->TOKEN1 = config('app.telegram_bot1');
    $this->TOKEN2 = config('app.telegram_bot2');
    $this->TOKEN3 = config('app.telegram_bot3');
    $this->API_URL1 = "https://api.telegram.org/$this->TOKEN1/";
    $this->API_URL2 = "https://api.telegram.org/$this->TOKEN2/";
    $this->API_URL3 = "https://api.telegram.org/$this->TOKEN3/";
    $this->TOKEN_SMS = config('app.telegram_sms');
    $this->TOKEN_GN_ID = config('app.telegram_gn_id');
    $this->TOKEN_GN_SEC = config('app.telegram_gn_sec');
    $this->URL_GN = 'https://pix.api.efipay.com.br';
  }

  public function start1(Request $request)
  {
    try {
      $update = json_decode(file_get_contents('php://input'), true);
      $valor_numero = 7.5;

      //Callback
      if (isset($update['callback_query'])) {
        $username = $this->retornaUsername($update['callback_query']);

        $callback_data = $update['callback_query']['data'];
        $chat_id = $update['callback_query']['message']['chat']['id'];

        if ($callback_data === 'comprar_whatsapp') {
          $this->sendMessage($chat_id, 'Você escolheu comprar um número para WhatsApp!', null, null, 1);
          $this->responderCallbackQueryComprar($update['callback_query']['id'], 1);
          $this->mostrarOpcoesValores($chat_id, 1);
        } elseif ($callback_data === 'comprar_telegram') {
          $this->sendMessage($chat_id, 'Você escolheu comprar um número para Telegram!', null, null, 1);
          $this->responderCallbackQueryComprar($update['callback_query']['id'], 1);
          $this->mostrarOpcoesValores($chat_id, 1);
        }

        if (Str::startsWith($callback_data, 'recarregar')) {
          $this->responderCallbackQueryRecarregar($username, $chat_id, $callback_data, 1);
        }

        return;
      }

      if (!isset($update['message'])) {
        return;
      }

      $username = $this->retornaUsername($update);
      $chat_id = $update['message']['chat']['id'];
      $text = strtolower($update['message']['text']);

      /*if (isset($update['callback_query'])) {
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
        }
      }*/
      if ($text == '/start') {
        $keyboard = [
          'keyboard' => [[['text' => '/servico']], [['text' => '/recarregar']], [['text' => '/saldo']]],
          'resize_keyboard' => true,
          'one_time_keyboard' => false,
        ];

        $this->sendMessage($chat_id, 'Bem-vindo! Escolha uma opção abaixo:', $keyboard, null, null, 1);
      } elseif ($text == '1') {
        $this->sendMessage($chat_id, "O número virtual para WhatsApp custa R$ 7,50. Digite 'comprar' para prosseguir.", null, null, 1);
      } elseif ($text == '2') {
        $this->sendMessage($chat_id, "O número virtual para Telegram custa R$ 7,50. Digite 'comprar' para prosseguir.", null, null, 1);
      } elseif ($text == '/servico') {
        $this->mostrarOpcoesNumeros($chat_id, 1);
      } elseif ($text == '/recarregar') {
        $this->mostrarOpcoesValores($chat_id, 1);
      } elseif ($text == '/saldo') {
        $user = $this->retornaSaldoByUsername($username);
        //if (!$user) {
        $this->sendMessage($chat_id, 'Seu saldo é: R$ 0,00.', null, null, 1);
        /*} else {
          $this->sendMessage($chat_id, 'Seu saldo é: R$' . str_replace('.', ',', $user->balance). '.');
        }*/
      } elseif ($text == 'confirmar pagamento') {
        if ($this->verificarPagamento($chat_id)) {
          //$numero_virtual = $this->comprarNumeroVirtual();
          //$this->sendMessage($chat_id, "Pagamento confirmado! Seu número virtual é: $numero_virtual");
        } else {
          $this->sendMessage($chat_id, 'Ainda não identificamos o pagamento. Tente novamente mais tarde.', null, null, 1);
        }
      } else {
        $this->sendMessage($chat_id, 'Opção inválida.', null, null, 1);
      }
    } catch (Exception $e) {
      Log::error($e->getMessage());
      return [$e->getMessage()];
    }
  }

  public function start2(Request $request)
  {
    try {
      $update = json_decode(file_get_contents('php://input'), true);
      $valor_numero = 7.5;

      //Callback
      if (isset($update['callback_query'])) {
        $username = $this->retornaUsername($update['callback_query']);

        $callback_data = $update['callback_query']['data'];
        $chat_id = $update['callback_query']['message']['chat']['id'];

        if ($callback_data === 'comprar_whatsapp') {
          $this->sendMessage($chat_id, 'Você escolheu comprar um número para WhatsApp!', null, null, 2);
          $this->responderCallbackQueryComprar($update['callback_query']['id'], 2);
          $this->mostrarOpcoesValores($chat_id, 2);
        } elseif ($callback_data === 'comprar_telegram') {
          $this->sendMessage($chat_id, 'Você escolheu comprar um número para Telegram!', null, null, 2);
          $this->responderCallbackQueryComprar($update['callback_query']['id'], 2);
          $this->mostrarOpcoesValores($chat_id, 2);
        }

        if (Str::startsWith($callback_data, 'recarregar')) {
          $this->responderCallbackQueryRecarregar($username, $chat_id, $callback_data, 2);
        }

        return;
      }

      if (!isset($update['message'])) {
        return;
      }

      $username = $this->retornaUsername($update);
      $chat_id = $update['message']['chat']['id'];
      $text = strtolower($update['message']['text']);

      /*if (isset($update['callback_query'])) {
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
        }
      }*/
      if ($text == '/start') {
        $keyboard = [
          'keyboard' => [[['text' => '/servico']], [['text' => '/recarregar']], [['text' => '/saldo']]],
          'resize_keyboard' => true,
          'one_time_keyboard' => false,
        ];

        $this->sendMessage($chat_id, 'Bem-vindo! Escolha uma opção abaixo:', $keyboard, null, null, 2);
      } elseif ($text == '1') {
        $this->sendMessage($chat_id, "O número virtual para WhatsApp custa R$ 7,50. Digite 'comprar' para prosseguir.", null, null, 2);
      } elseif ($text == '2') {
        $this->sendMessage($chat_id, "O número virtual para Telegram custa R$ 7,50. Digite 'comprar' para prosseguir.", null, null, 2);
      } elseif ($text == '/servico') {
        $this->mostrarOpcoesNumeros($chat_id, 2);
      } elseif ($text == '/recarregar') {
        $this->mostrarOpcoesValores($chat_id, 2);
      } elseif ($text == '/saldo') {
        $user = $this->retornaSaldoByUsername($username);
        //if (!$user) {
        $this->sendMessage($chat_id, 'Seu saldo é: R$ 0,00.', null, null, 2);
        /*} else {
          $this->sendMessage($chat_id, 'Seu saldo é: R$' . str_replace('.', ',', $user->balance). '.');
        }*/
      } elseif ($text == 'confirmar pagamento') {
        if ($this->verificarPagamento($chat_id)) {
          //$numero_virtual = $this->comprarNumeroVirtual();
          //$this->sendMessage($chat_id, "Pagamento confirmado! Seu número virtual é: $numero_virtual");
        } else {
          $this->sendMessage($chat_id, 'Ainda não identificamos o pagamento. Tente novamente mais tarde.', null, null, 2);
        }
      } else {
        $this->sendMessage($chat_id, 'Opção inválida.', null, null, 2);
      }
    } catch (Exception $e) {
      Log::error($e->getMessage());
      return [$e->getMessage()];
    }
  }

  public function mostrarOpcoesNumeros($chat_id, $numero)
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

      if ($numero === 1) {
        $url = $this->API_URL1 . 'sendMessage';
        $response = Http::withHeaders(['Content-Type' => 'application/json'])->post($url, $dados);
      } else if ($numero === 2) {
        $url = $this->API_URL2 . 'sendMessage';
        $response = Http::withHeaders(['Content-Type' => 'application/json'])->post($url, $dados);
      }
    } catch (Exception $e) {
      Log::error($e->getMessage());
      return [$e->getMessage()];
    }
  }

  public function mostrarOpcoesValores($chat_id, $numero)
  {
    try {

      $keyboard = [
        'inline_keyboard' => [
          [['text' => 'R$ 7,50', 'callback_data' => 'recarregar_7.5']],
          [['text' => 'R$ 14,00', 'callback_data' => 'recarregar_14.0']],
          [['text' => 'R$ 22,50', 'callback_data' => 'recarregar_22.5']],
          [['text' => 'R$ 30,00', 'callback_data' => 'recarregar_30.0']],
          [['text' => 'R$ 50,00', 'callback_data' => 'recarregar_50.0']],
          [['text' => 'R$ 100,00', 'callback_data' => 'recarregar_100.0']],
        ],
      ];

      $dados = [
        'chat_id' => $chat_id,
        'text' => 'Escolha um número virtual:',
        'reply_markup' => $keyboard,
      ];

      if ($numero === 1) {
        $url = $this->API_URL1 . 'sendMessage';
        $response = Http::withHeaders(['Content-Type' => 'application/json'])->post($url, $dados);
      } else if ($numero === 2) {
        $url = $this->API_URL2 . 'sendMessage';
        $response = Http::withHeaders(['Content-Type' => 'application/json'])->post($url, $dados);
      }
    } catch (Exception $e) {
      Log::error($e->getMessage());
      return [$e->getMessage()];
    }
  }

  public function sendMessage($chat_id, $message, $keyboard = null, $parse_mode = null, $numero)
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
      if ($parse_mode) {
        $data['parse_mode'] = 'HTML';
      }

      if ($numero === 1) {
        $url = $this->API_URL1 . 'sendMessage';
        return Http::post($url, $data);
      } else if ($numero === 2) {
        $url = $this->API_URL2 . 'sendMessage';
        return Http::post($url, $data);
      } else if ($numero === 3) {
        $url = $this->API_URL3 . 'sendMessage';
        return Http::post($url, $data);
      }
    } catch (Exception $e) {
      Log::error($e->getMessage());
      return [$e->getMessage()];
    }
  }

  private function responderCallbackQueryComprar($callbackQueryId, $numero)
  {
    try {
      if ($numero === 1) {
        $url = $this->API_URL1 . 'answerCallbackQuery';
        Http::post($url, [
          'callback_query_id' => $callbackQueryId,
          'text' => 'Processando sua escolha...',
          'show_alert' => false,
        ]);
      } else if ($numero === 2) {
        $url = $this->API_URL2 . 'answerCallbackQuery';
        Http::post($url, [
          'callback_query_id' => $callbackQueryId,
          'text' => 'Processando sua escolha...',
          'show_alert' => false,
        ]);
      } else if ($numero === 3) {
        $url = $this->API_URL3 . 'answerCallbackQuery';
        Http::post($url, [
          'callback_query_id' => $callbackQueryId,
          'text' => 'Processing your choice...',
          'show_alert' => false,
        ]);
      }
    } catch (Exception $e) {
      Log::error($e->getMessage());
      return [$e->getMessage()];
    }
  }

  private function responderCallbackQueryRecarregar($username, $chat_id, $valor, $numero)
  {
    try {
      $valor = explode('_', $valor)[1] ?? null;

      if (!$valor) {
        Log::error('Formato inválido: ' . $valor);
        return;
      }

      $pix_copia_e_cola = $this->gerarPixCopiaCola($valor);

      $response = $this->sendMessage(
        $chat_id,
        "🔹 *Pagamento via PIX*\n\n" .
          "📌 Copie o código abaixo e cole no seu app bancário para pagar:\n\n<pre>$pix_copia_e_cola</pre>",
        null,
        'HTML',
        $numero
      );
      $responseArray = json_decode($response, true);
      $message_id = $responseArray['result']['message_id'] ?? null;
      $this->criarTransacao($username, $chat_id, $message_id, $pix_copia_e_cola, $valor);
    } catch (Exception $e) {
      Log::error($e->getMessage());
      return [$e->getMessage()];
    }
  }

  public function comprarNumeroVirtual($service)
  {
    try {
      $serviceMap = [
        'comprar_whatsapp' => 'wa',
        'comprar_telegram' => 'tg',
      ];

      if (!isset($serviceMap[$service])) {
        return 'Serviço inválido.';
      }

      $api_key = $this->TOKEN_SMS;
      $url = 'https://api.sms-activate.org/stubs/handler_api.php';

      $params = [
        'api_key' => $api_key,
        'action' => 'getNumber',
        'service' => $serviceMap[$service],
        'country' => 'BR',
      ];

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
      $access_token = $this->obterAccessToken();
      if (!$access_token) {
        throw new Exception('Falha ao obter token de acesso da Gerencianet.');
      }

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

  public function retornaUsername($message)
  {
    try {
      if (isset($message['message']['from'])) {
        $from = $message['message']['from'];

        if (isset($from['username'])) {
          return $from['username'];
        } else {
          // Fallback para ID
          $fallback = 'user_' . $from['id'];
          Log::info("Username não encontrado. Usando fallback: {$fallback}");
          return $fallback;
        }
      } else {
        Log::info("Campo 'from' não encontrado.");
      }

      return null;
    } catch (\Exception $e) {
      Log::error($e->getMessage());
      return null;
    }
  }

  public function retornaSaldoByUsername($username)
  {
    try {
      $user = VirtualUser::where('username', '=', $username)->first();

      return $user;
    } catch (\Exception $e) {
      Log::error($e->getMessage());
      return ['error' => $e->getMessage()];
    }
  }

  public function retornaUserByUsername($username)
  {
    try {
      $user = VirtualUser::where('username', '=', $username)->first();

      return $user;
    } catch (\Exception $e) {
      Log::error($e->getMessage());
      return ['error' => $e->getMessage()];
    }
  }

  public function criarTransacao($username, $chat_id, $message_id, $qrcode, $balance)
  {
    try {
      DB::beginTransaction();

      $user = $this->retornaUserByUsername($username);
      if (!$user) {
        $user = VirtualUser::create([
          'username' => $username,
          'balance' => 0,
        ]);
      }

      // Atualizar balance de forma segura usando `increment()`
      $user->increment('balance', $balance);

      $transcacao_db = VirtualTransacao::create([
        'virtual_user_id' => $user->id,
        'qrcode' => $qrcode,
        'balance' => $balance,
        'chat_id' => $chat_id,
        'message_id' => $message_id,
      ]);

      DB::commit();
      return $transcacao_db;
    } catch (\Exception $e) {
      Log::error($e->getMessage());
      DB::rollBack();
      return ['error' => $e->getMessage()];
    }
  }

  public function start3(Request $request)
  {
    try {
      Log::info('Iniciando envio de notificações...');
      $update = json_decode(file_get_contents('php://input'), true);
      Log::info($update);

      // Verifica se é um callback
      if (isset($update['callback_query'])) {
        $this->tratarCallback($update['callback_query']);
        return;
      }

      // Verifica se é uma mensagem
      if (!isset($update['message'])) {
        return;
      }
      Log::info('passou');

      $chat_id = $update['message']['chat']['id'];
      Log::info('chat id');
      Log::info($chat_id);
      $username = $this->retornaUsername($update);
      $text = strtolower($update['message']['text']);
      Log::info('text');
      Log::info($text);
      switch ($text) {
        case '/start':
          $keyboard = [
            'keyboard' => [
              //[['text' => '/servico']],
              //[['text' => '/recarregar']],
              //[['text' => '/saldo']],
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
          ];
          Log::info('entrou');
          Log::info($text);

          $this->sendMessage($chat_id, 'Welcome! Did you like the previews?\n\n' .
            '\n\n' .
            'Now, imagine having exclusive access to all my content, the way you ve never seen it before,\n\n' .
            'all just for you.', null, null, 3);

          $this->mostrarOpcoesValoresVip($chat_id, 3);

          // Mensagem de teste adicional
          //$this->sendMessage($chat_id, 'Mensagem de teste recebida com sucesso!', null, null, 3);
          break;

        /*case '1':
          $this->sendMessage($chat_id, "O número virtual para WhatsApp custa R$ 7,50. Digite 'comprar' para prosseguir.", null, null, 3);
          break;

        case '2':
          $this->sendMessage($chat_id, "O número virtual para Telegram custa R$ 7,50. Digite 'comprar' para prosseguir.", null, null, 3);
          break;

        case '/servico':
          $this->mostrarOpcoesNumeros($chat_id, 3);
          break;

        case '/recarregar':
          $this->mostrarOpcoesValores($chat_id, 3);
          break;

        case '/saldo':
          $user = $this->retornaSaldoByUsername($username);
          $saldo = $user ? 'R$ ' . str_replace('.', ',', $user->balance) : 'R$ 0,00';
          $this->sendMessage($chat_id, "Seu saldo é: $saldo.", null, null, 3);
          break;

        case 'confirmar pagamento':
          if ($this->verificarPagamento($chat_id)) {
            // $numero_virtual = $this->comprarNumeroVirtual();
            // $this->sendMessage($chat_id, "Pagamento confirmado! Seu número virtual é: $numero_virtual");
          } else {
            $this->sendMessage($chat_id, 'Ainda não identificamos o pagamento. Tente novamente mais tarde.', null, null, 3);
          }
          break;*/

        default:
          $this->sendMessage($chat_id, 'Opção inválida.', null, null, 3);
          break;
      }
    } catch (Exception $e) {
      Log::error($e->getMessage());
      return [$e->getMessage()];
    }
  }

  private function tratarCallback(array $callback)
  {
    $username = $this->retornaUsername(['message' => $callback['message']]);
    $callback_data = $callback['data'];
    $chat_id = $callback['message']['chat']['id'];
    $callback_id = $callback['id'];

    if ($callback_data === 'acess_40.90') {
      $this->sendMessage($chat_id, 'You chose the Lifetime Access!', null, null, 3);
      $this->responderCallbackQueryComprar($callback_id, 2);
    } elseif ($callback_data === 'acess_26.29') {
      $this->sendMessage($chat_id, 'You chose the VIP access 3 months!', null, null, 3);
      $this->responderCallbackQueryComprar($callback_id, 2);
    } elseif ($callback_data === 'acess_11.68') {
      $this->sendMessage($chat_id, 'You chose the VIP access 1 month!', null, null, 3);
      $this->responderCallbackQueryComprar($callback_id, 2);
    }

    if ($callback_data === 'acess_40.90') {
      $valor = 40.90;
    } else if ($callback_data === 'acess_26.29') {
      $valor = 26.29;
    } else if ($callback_data === 'acess_11.68') {
      $valor = 11.68;
    }
    if ($valor) {
      $pix_copia_e_cola = $this->gerarPixCopiaCola($valor);

      $response = $this->sendMessage(
        $chat_id,
        "🔹 *Payment via PIX*\n\n" .
          "📌 Copy the code below and paste it into your banking app to pay:\n\n<pre>$pix_copia_e_cola</pre>",
        null,
        'HTML',
        3
      );
      $responseArray = json_decode($response, true);
      $message_id = $responseArray['result']['message_id'] ?? null;
      $this->criarTransacao($username, $chat_id, $message_id, $pix_copia_e_cola, $valor);
    }
  }

  public function mostrarOpcoesValoresVip($chat_id, $numero)
  {
    try {
      $keyboard = [
        'inline_keyboard' => [
          [['text' => 'Lifetime access | USD 7,00', 'callback_data' => 'acess_40.90']],
          [['text' => 'VIP access 3 months | USD 4,50', 'callback_data' => 'acess_26.29']],
          [['text' => 'VIP access 1 month | USD 2,00', 'callback_data' => 'acess_11.68']],
        ],
      ];

      $dados = [
        'chat_id' => $chat_id,
        'text' => 'Choose a plan and enjoy:',
        'reply_markup' => $keyboard,
      ];

      if ($numero === 1) {
        $url = $this->API_URL1 . 'sendMessage';
        $response = Http::withHeaders(['Content-Type' => 'application/json'])->post($url, $dados);
      } else if ($numero === 2) {
        $url = $this->API_URL2 . 'sendMessage';
        $response = Http::withHeaders(['Content-Type' => 'application/json'])->post($url, $dados);
      } else if ($numero === 3) {
        $url = $this->API_URL3 . 'sendMessage';
        $response = Http::withHeaders(['Content-Type' => 'application/json'])->post($url, $dados);
      }
    } catch (Exception $e) {
      Log::error($e->getMessage());
      return [$e->getMessage()];
    }
  }
}
