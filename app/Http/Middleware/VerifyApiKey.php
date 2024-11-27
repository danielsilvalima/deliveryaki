<?php
namespace App\Http\Middleware;

use Closure;

class VerifyApiKey
{
    public function handle($request, Closure $next)
    {
        $apiKey = $request->header('X-PEDIDO');
        //$validApiKey = env('API_KEY', '');
        $validApiKey = env('X_PEDIDO', '');

        if ($apiKey !== $validApiKey) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
