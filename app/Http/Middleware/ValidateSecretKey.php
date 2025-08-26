<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateSecretKey
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
        $secretKey = env('SECRATE_KEY'); // Secret key from .env
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized: Missing or invalid Bearer token'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $matches[1];

        if ($token !== $secretKey) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized: Invalid secret key'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
