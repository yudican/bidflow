<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-Key') ?? $request->input('api_key');
        
        // Get the expected API key from environment or config
        $expectedApiKey = env('API_KEY', 'your-secret-api-key');
        
        if (!$apiKey || $apiKey !== $expectedApiKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or missing API key'
            ], 401);
        }
        
        return $next($request);
    }
}