<?php

namespace App\Services\Here;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class HereService
{
  public function findLatLng($logradouro, $numero, $bairro, $cidade, $uf)
  {
    try {
      $params = [
        'q' => $logradouro . '+' . $numero . '+' . $bairro . '+' . $cidade . '+' . $uf,
        'apikey' => config('app.here_key'),
      ];
      // https://geocode.search.hereapi.com/v1/geocode
      $geocode_url = config('app.url_here_geo');

      $geocode_response = Http::get($geocode_url, $params);

      if ($geocode_response->failed() || empty($geocode_response->json('items'))) {
        throw new \Exception('Endereço não encontrado');
      }

      $position = $geocode_response->json('items')[0]['position'];
      $latitude = Str::replace('-', '', $position['lat']);
      $longitude = Str::replace('-', '', $position['lng']);
      return [
        'latitude' => $latitude,
        'longitude' => $longitude,
      ];
    } catch (\Exception $e) {
      throw new \Exception('Erro ao consultar a LAT e LNG de ' . $logradouro);
    }
  }

  public function findDistance($empresaLatLng, $clienteLatLng)
  {
    try {
      $route_url = config('app.url_here_route');
      $params = [
        'transportMode' => 'car',
        'origin' => $empresaLatLng['longitude'] . ',' . $empresaLatLng['latitude'],
        'destination' => $clienteLatLng['longitude'] . ',' . $clienteLatLng['latitude'],
        'return' => 'summary',
        'apikey' => config('app.here_key'),
      ];
      // https://router.hereapi.com/v8/routes?transportMode=car&origin=47.8391122,21.1806688&destination=47.8411782,21.1979377&return=summary&apikey=O8pWUYbh9zc5PIs445aZrmJk53tRbT6p18ov7beLv4g
      $route_response = Http::get($route_url, $params);

      if ($route_response->failed() || empty($route_response->json('routes'))) {
        throw new \Exception('Endereço não encontrado');
      }

      $distance = $route_response['routes'][0]['sections'][0]['summary']['length'] ?? null;

      return [
        'distancia' => $distance,
      ];
    } catch (\Exception $e) {
      throw new \Exception('Erro ao consultar a distância');
    }
  }
}
