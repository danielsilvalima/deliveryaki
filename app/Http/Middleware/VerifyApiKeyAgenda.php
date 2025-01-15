<?php
namespace App\Http\Middleware;

use Closure;

class VerifyApiKeyAgenda
{
    public function handle($request, Closure $next)
    {
        $apiKey = $request->header('X-AGENDA');
        $validApiKey = config('app.agenda_key');

        if ($apiKey !== $validApiKey) {
            return response()->json(['error' => 'Unauthorized' ], 401);
        }

        return $next($request);
    }
}
