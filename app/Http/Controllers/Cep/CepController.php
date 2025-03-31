<?php

namespace App\Http\Controllers\Cep;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Cep\CepService;
use App\Services\ViaCep\ViaCepService;
use App\Services\Here\HereService;
use App\Services\Empresa\EmpresaService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class CepController extends Controller
{
  private $header = array(
    //'Content-Type' => 'text/html; charset=UTF-8',
    'Content-Type' => 'application/json; charset=UTF-8',
    'charset' => 'utf-8'
  );
  private $options = JSON_UNESCAPED_UNICODE;

  public function get(Request $request, CepService $cepService, ViaCepService $viaCepService, HereService $hereService, EmpresaService $empresaService)
  {
    try {
      $request->validate([
        'empresa_id' => 'required',
        'numero' => 'required|string',
        'cep' => 'required|string',
      ], [
        'empresa_id.required' => __('CAMPO empresa_id É OBRIGATÓRIO'),
        'numero.required' => __('CAMPO numero É OBRIGATÓRIO'),
        'cep.required' => __('CAMPO cep É OBRIGATÓRIO'),
      ]);

      $empresa_hash = $request->empresa_id;
      $numero = $request->numero;
      $numero_cep = $request->cep;

      $cep = $cepService->findByCEP($numero_cep);

      if (!$cep) {
        $cep = $viaCepService->findViaCep($numero_cep);
      }

      if ($cep) {
        $clienteLatLng = $hereService->findLatLng($cep['logradouro'], $numero, $cep['bairro'], $cep['cidade'], $cep['uf']);

        $empresa = $empresaService->findByHash($empresa_hash);
        if (!$empresa) {
          //return ResponseHelper::error('EMPRESA NÃO ENCONTRADA');
          return response()->json(['error' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);
        }

        if (!$empresa->lat || !$empresa->lng) {
          $empresaLatLng = $hereService->findLatLng($empresa->logradouro, $empresa->numero, $empresa->bairro, $empresa->cidade, $empresa->uf);
          if (!$empresaLatLng) {
            //return ResponseHelper::error('NÃO FOI POSSÍVEL ENCONTRAR AS COORDENADAS DA EMPRESA');
            return response()->json(['error' => 'Não foi possível encontrar as coordenadas da empresa.'], Response::HTTP_FORBIDDEN);
          }

          $empresa->update([
            'lat' => $empresaLatLng['latitude'],
            'lng' => $empresaLatLng['longitude'],
          ]);
        } else {
          $empresaLatLng = ['latitude' => $empresa->lat, 'longitude' => $empresa->lng];
        }

        $distancia = $hereService->findDistance($empresaLatLng, $clienteLatLng);
        if (!$distancia || !isset($distancia['distancia'])) {
          //return ResponseHelper::error('ERRO AO CALCULAR A DISTÂNCIA');
          return response()->json(['error' => 'Erro ao calcular a distância.'], Response::HTTP_FORBIDDEN);
        }

        $distancia_km = $distancia['distancia'] / 1000; // Converte a distância para km
        $vlr_km = $empresa->vlr_km ?? 0;              // Valor por km, padrão 0
        $vlr_taxa = 0;

        // Verifica se o tipo de taxa é baseado em distância ('D') ou fixa ('F')
        if ($empresa->tipo_taxa === 'D') {
          // Taxa com base na distância
          if ($empresa->inicio_distancia !== null && $distancia_km > $empresa->inicio_distancia) {
            // Aplica a fórmula apenas se a distância ultrapassar o limite inicial
            $vlr_taxa = round($distancia_km * $vlr_km, 2);
          } elseif ($empresa->inicio_distancia === null) {
            // Sem limite inicial, calcula diretamente
            $vlr_taxa = round($distancia_km * $vlr_km, 2);
          }
        } elseif ($empresa->tipo_taxa === 'F') {
          // Taxa fixa
          if ($empresa->inicio_distancia !== null && $distancia_km > $empresa->inicio_distancia) {
            // Aplica a taxa fixa apenas se a distância ultrapassar o limite inicial
            $vlr_taxa = $vlr_km;
          } elseif ($empresa->inicio_distancia === null) {
            // Sem limite inicial, usa a taxa fixa diretamente
            $vlr_taxa = $vlr_km;
          }
        }
      }

      return response()->json(
        [
          'distancia_km' => $distancia_km,
          'vlr_taxa' => $vlr_taxa,
          'cep' => $cep['cep'],
          'logradouro' => $cep['logradouro'],
          'bairro' => $cep['bairro'],
          'complemento' => $cep['complemento'],
          'cidade' => $cep['cidade'],
          'uf' => $cep['uf']
        ],
        //['cep' => $clienteLatLng],
        Response::HTTP_OK,
        $this->header,
        $this->options
      );
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getCEP(Request $request, CepService $cepService, ViaCepService $viaCepService, HereService $hereService, EmpresaService $empresaService)
  {
    try {
      $request->validate([
        'cep' => 'required|string',
      ], [
        'cep.required' => __('CAMPO cep é obrigatório'),
      ]);

      $numero_cep = $request->cep;

      $cep = $cepService->findByCEP($numero_cep);

      if (!$cep) {
        $cep = $viaCepService->findViaCep($numero_cep);
      }

      return response()->json(
        [
          'cep' => $cep['cep'],
          'logradouro' => $cep['logradouro'],
          'bairro' => $cep['bairro'],
          'complemento' => $cep['complemento'],
          'cidade' => $cep['cidade'],
          'uf' => $cep['uf']
        ],
        Response::HTTP_OK,
        $this->header,
        $this->options
      );
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }
}
