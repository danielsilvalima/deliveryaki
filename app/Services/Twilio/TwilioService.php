<?php

namespace App\Services\Twilio;

use Illuminate\Http\Request;

class TwilioService
{
  private $TOKEN;
  private $API_URL;
  public function __construct()
  {
    $this->TOKEN = config('app.telegram_bot');
    $this->API_URL = "https://api.telegram.org/bot$this->TOKEN/";
  }

  public function start(Request $request)
  {
    file_put_contents("log_telegram.txt", json_encode($update, JSON_PRETTY_PRINT));
    $update = json_decode(file_get_contents('php://input'), true);
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
      $qrcode_url = $this->gerarQRCodePix(15.0, $chat_id);
      $this->sendMessage(
        $chat_id,
        "Escaneie este QR Code para pagamento:\n$qrcode_url\nApós o pagamento, digite 'confirmar pagamento'."
      );
    } elseif ($text == 'confirmar pagamento') {
      if ($this->verificarPagamento($chat_id)) {
        $numero_virtual = $this->comprarNumeroTwilio();
        $this->sendMessage($chat_id, "Pagamento confirmado! Seu número virtual é: $numero_virtual");
      } else {
        $this->sendMessage($chat_id, 'Ainda não identificamos o pagamento. Tente novamente mais tarde.');
      }
    }
  }

  // Função para enviar mensagens no Telegram
  public function sendMessage($chat_id, $message)
  {
    //global $API_URL;
    $url = $this->API_URL . "sendMessage?chat_id=$chat_id&text=" . urlencode($message);
    file_get_contents($url);
  }

  public function comprarNumeroTwilio()
  {
    $account_sid = "SEU_TWILIO_ACCOUNT_SID";
    $auth_token = "SEU_TWILIO_AUTH_TOKEN";
    $twilio_url = "https://api.twilio.com/2010-04-01/Accounts/$account_sid/IncomingPhoneNumbers.json";

    $data = [
      "PhoneNumber" => "+5582999999999" // Aqui você pode automatizar para buscar números disponíveis
    ];

    $options = [
      "http" => [
        "header"  => "Authorization: Basic " . base64_encode("$account_sid:$auth_token"),
        "method"  => "POST",
        "content" => http_build_query($data)
      ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($twilio_url, false, $context);
    $response = json_decode($result, true);

    return $response["phone_number"] ?? "Erro ao gerar número";
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
