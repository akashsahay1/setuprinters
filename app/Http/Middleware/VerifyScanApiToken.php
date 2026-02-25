<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyScanApiToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = config('services.scan_api.token');

        if (!$token || $request->bearerToken() !== $token) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
