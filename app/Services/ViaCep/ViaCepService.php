<?php

namespace App\Services\ViaCep;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ViaCepService
{
  public function findViaCep($cep)
  {
    try {
      $base_url = config('app.url_via_cep');

      if (empty($base_url)) {
        throw new \Exception('Configuração vazia');
      }


      $url_via_cep = "{$base_url}/{$cep}/json/";

      $via_cep_response = Http::get($url_via_cep);

      // Verifica se a resposta falhou ou se não retornou dados
      if ($via_cep_response->failed() || $via_cep_response->json('erro') === true) {
        throw new \Exception('CEP não encontrado');
      }

      $dados = $via_cep_response->json();
      // Retorna os dados formatados
      return [
        'cep' => Str::replace('-', '', $dados['cep']) ?? $cep,
        'logradouro' => strtoupper($dados['logradouro']) ?? '',
        'complemento' => strtoupper($dados['complemento']) ?? '',
        'bairro' => strtoupper($dados['bairro']) ?? '',
        'cidade' => strtoupper($dados['localidade']) ?? '',
        'uf' => strtoupper($dados['uf']) ?? '',
      ];
    } catch (\Exception $e) {
      throw new \Exception($e);
    }
  }
}
