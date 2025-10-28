<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('Api-Key');

        if (!$apiKey || $apiKey !== config('app.api_key')) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        return $next($request);
    }
}
