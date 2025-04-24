<?php

namespace App\Services\Fatura;

use App\Models\Empresa;
use App\Models\Fatura;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Services\Empresa\EmpresaService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FaturaService
{
  protected $empresaService;
  private $URL_GN;
  private $TOKEN_GN_ID;
  private $TOKEN_GN_SEC;

  public function __construct(EmpresaService $empresaService)
  {
    $this->empresaService = $empresaService;
    $this->URL_GN = 'https://pix.api.efipay.com.br';
    $this->TOKEN_GN_ID = config('app.telegram_gn_id');
    $this->TOKEN_GN_SEC = config('app.telegram_gn_sec');
  }

  public function gerarQrCodePix(Fatura $fatura, Empresa $empresa): array
  {
    try {
      $chave = config('app.bank_key'); // Exemplo: 'email@dominio.com'

      $nomeRecebedor = substr('Guitecnology', 0, 25);
      $cidade = substr('Ribeirao Preto', 0, 15);
      $valor = $fatura->valor_a_pagar;

      // Gera o código EMV ("copia e cola")
      return $this->gerarPayloadPix($chave, $fatura->id, $nomeRecebedor, $cidade, $valor);
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  private function gerarPayloadPix(string $chave, $prefixo, string $nomeRecebedor, string $cidade, float $valor): array
  {
    try {
      // Merchant Account Info
      $gui = 'BR.GOV.BCB.PIX';
      $guiField = '00' . str_pad(strlen($gui), 2, '0', STR_PAD_LEFT) . $gui;
      $keyField = '01' . str_pad(strlen($chave), 2, '0', STR_PAD_LEFT) . $chave;
      $merchantAccountInfo = $guiField . $keyField;
      $merchantAccountInfoField = '26' . str_pad(strlen($merchantAccountInfo), 2, '0', STR_PAD_LEFT) . $merchantAccountInfo;

      // Adicionais
      $tx_id = $this->gerarTxid("fatura{$prefixo}");
      $txidField = '05' . str_pad(strlen($tx_id), 2, '0', STR_PAD_LEFT) . $tx_id;
      $additionalDataField = '62' . str_pad(strlen($txidField), 2, '0', STR_PAD_LEFT) . $txidField;

      // Valor
      $valorFormatado = number_format($valor, 2, '.', '');
      $valorField = '54' . str_pad(strlen($valorFormatado), 2, '0', STR_PAD_LEFT) . $valorFormatado;

      // Montagem do payload sem o CRC
      $payloadSemCRC =
        '000201' .
        $merchantAccountInfoField .
        '52040000' .
        '5303986' .
        $valorField .
        '58' . '02BR' .
        '59' . str_pad(strlen($nomeRecebedor), 2, '0', STR_PAD_LEFT) . $nomeRecebedor .
        '60' . str_pad(strlen($cidade), 2, '0', STR_PAD_LEFT) . $cidade .
        $additionalDataField;

      // Cálculo do CRC16
      $crc16 = strtoupper(dechex($this->crc16($payloadSemCRC . '6304')));
      $payloadCompleto = $payloadSemCRC . '6304' . str_pad($crc16, 4, '0', STR_PAD_LEFT);

      return [
        'copia_e_cola' => $payloadCompleto,
        'tx_id' => $tx_id,
      ];
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  private function crc16(string $payload): int
  {
    try {
      $polynomial = 0x1021;
      $result = 0xFFFF;

      for ($i = 0; $i < strlen($payload); $i++) {
        $result ^= (ord($payload[$i]) << 8);
        for ($bit = 0; $bit < 8; $bit++) {
          $result = ($result & 0x8000)
            ? ($result << 1) ^ $polynomial
            : ($result << 1);
        }
      }

      return $result & 0xFFFF;
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  private function gerarTxid(string $prefixo): string
  {
    try {
      $prefixo = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $prefixo), 0, 10));
      $timestamp = round(microtime(true) * 1000);
      $random = strtoupper(Str::random(10));

      return substr($prefixo . $timestamp . $random, 0, 25);
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function consultarPixPorTxid(string $tx_id)
  {
    try {
      $accessToken = $this->obterAccessToken();

      if (!$accessToken) {
        throw new \Exception('Não foi possível obter o access token.');
      }

      //$url = $this->URL_GN . "/v2/pix/{$txid}";
      $url = $this->URL_GN . "/v2/pix?inicio=2025-04-23T00:00:01Z&fim=2025-04-23T23:59:59Z";

      return $this->chamarApiPagamento($url, 'GET', [], $accessToken);
    } catch (\Exception $e) {
      Log::error('Erro ao consultar Pix: ' . $e->getMessage());
      throw new \Exception($e->getMessage());
    }
  }

  public function obterAccessToken(): ?string
  {
    $url = $this->URL_GN . '/oauth/token';

    $data = ['grant_type' => 'client_credentials'];

    $response = $this->chamarApiPagamento($url, 'POST', $data);

    return $response['access_token'] ?? null;
  }

  public function chamarApiPagamento(string $url, string $method, array $data = [], ?string $access_token = null): array
  {
    try {
      // Define headers
      $headers = [];

      if ($access_token) {
        $headers['Authorization'] = "Bearer $access_token";
        $headers['Content-Type'] = 'application/json';
      } elseif (isset($data['grant_type']) && $data['grant_type'] === 'client_credentials') {
        $credentials = base64_encode($this->TOKEN_GN_ID . ':' . $this->TOKEN_GN_SEC);
        $headers['Authorization'] = "Basic $credentials";
        //$headers['Content-Type'] = 'application/x-www-form-urlencoded';
      }

      $http = Http::withHeaders($headers)->withOptions([
        'cert' => base_path('app/certificate/producao.p12'),
      ]);

      $response = $method === 'GET'
        ? $http->get($url)
        : $http->post($url, $data);

      if ($response->failed()) {
        Log::error('Resposta da API Gerencianet com erro: ' . $response->body());
        throw new \Exception("Erro da API: " . $response->body());
      }

      return $response->json();
    } catch (\Exception $e) {
      Log::error('Erro na chamada da API Gerencianet: ' . $e->getMessage());
      throw new \Exception($e->getMessage());
    }
  }
}
