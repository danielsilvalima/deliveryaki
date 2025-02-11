<?php

namespace App\Services\NumeroVirtual;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NumeroVirtualService
{
  private $TOKEN;
  private $API_URL;
  private $TOKEN_SMS;
  public function __construct()
  {
    $this->TOKEN = config('app.telegram_bot');
    $this->API_URL = "https://api.telegram.org/$this->TOKEN/";
    $this->TOKEN_SMS = config('app.telegram_sms');
  }

  public function start(Request $request)
  {
    $update = json_decode(file_get_contents('php://input'), true);
    Log::info('COMECANDO AKI');
    Log::info(json_encode($update));
    if (!isset($update['message'])) {
      return;
    }
    $chat_id = $update['message']['chat']['id'];
    $text = strtolower($update['message']['text']);

    // Opções do menu
    if ($text == '/start') {
      $this->sendMessage(
        $chat_id,
        "Olá! Escolha o tipo de número virtual que deseja:\n1️⃣ WhatsApp\n2️⃣ Telegram\nDigite o número da opção desejada."
      );
    } elseif ($text == '1') {
      $this->sendMessage($chat_id, "O número virtual para WhatsApp custa R$ 15,00. Digite 'comprar' para prosseguir.");
    } elseif ($text == '2') {
      $this->sendMessage($chat_id, "O número virtual para Telegram custa R$ 12,00. Digite 'comprar' para prosseguir.");
    } elseif ($text == 'comprar') {
      $qrcode_url = $this->gerarQRCodePix(9.0, $chat_id);
      $this->sendMessage(
        $chat_id,
        "Escaneie este QR Code para pagamento:\n$qrcode_url\nApós o pagamento, digite 'confirmar pagamento'."
      );
    } elseif ($text == 'confirmar pagamento') {
      if ($this->verificarPagamento($chat_id)) {
        $numero_virtual = $this->comprarNumeroVirtual();
        $this->sendMessage($chat_id, "Pagamento confirmado! Seu número virtual é: $numero_virtual");
      } else {
        $this->sendMessage($chat_id, 'Ainda não identificamos o pagamento. Tente novamente mais tarde.');
      }
    } else {
      $this->sendMessage($chat_id, 'Opção inválida.');
    }
  }

  // Função para enviar mensagens no Telegram
  public function sendMessage($chat_id, $message)
  {
    //global $API_URL;
    $url = $this->API_URL . "sendMessage?chat_id=$chat_id&text=" . urlencode($message);
    file_get_contents($url);
  }

  public function comprarNumeroVirtual()
  {
    $api_key = $this->TOKEN_SMS;
    $url = "https://api.sms-activate.org/stubs/handler_api.php";

    // Solicita um número disponível para WhatsApp
    $params = [
      "api_key" => $api_key,
      "action" => "getNumber",
      "service" => "wa", // 'wa' para WhatsApp, 'tg' para Telegram
      "country" => "BR" // Código do país (Brasil)
    ];

    $query = http_build_query($params);
    $response = file_get_contents("$url?$query");

    if (strpos($response, "ACCESS_NUMBER") !== false) {
      list(, $id, $number) = explode(":", $response);
      return [
        "id" => $id,
        "numero" => $number
      ];
    }

    return "Erro ao obter número virtual";
  }

  public function gerarQRCodePix($valor, $chat_id)
  {
    // Aqui você chamaria a API do Gerencianet/Mercado Pago para gerar o QR Code
    // Exemplo fictício:
    return "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=PIX_CODE_DE_EXEMPLO";
  }

  public function verificarPagamento($chat_id)
  {
    // Aqui você verificaria a API do Gerencianet/Mercado Pago para ver se o pagamento foi recebido
    // Exemplo fictício, retornando true para simular um pagamento confirmado
    return true;
  }
}
