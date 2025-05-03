<?php

namespace App\Http\Controllers\Instagram;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class InstagramAuthController extends Controller
{
  public function exchangeCode(Request $request)
  {
    $code = $request->input('code');

    if (!$code) {
      return response()->json(['error' => 'Código não fornecido.'], 400);
    }

    $clientId = config('instagram_client_id');
    $clientSecret = config('instagram_client_secret');
    $redirectUri = 'https://billevo.guitecnology.com.br/auth/return';

    $response = Http::asForm()->post('https://api.instagram.com/oauth/access_token', [
      'client_id' => $clientId,
      'client_secret' => $clientSecret,
      'grant_type' => 'authorization_code',
      'redirect_uri' => $redirectUri,
      'code' => $code,
    ]);

    if ($response->failed()) {
      return response()->json(['error' => 'Erro ao obter token.'], 500);
    }

    return response()->json($response->json());
  }
}
