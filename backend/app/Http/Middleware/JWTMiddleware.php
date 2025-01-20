<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JWTMiddleware extends BaseMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json(['status' => 'Usuario no encontrado'], 404);
            }
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token inválido'
                ], 401);
            }
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token expirado'
                ], 401);
            }
            return response()->json([
                'status' => 'error',
                'message' => 'Token de autorización no encontrado'
            ], 401);
        }
        return $next($request);
    }
}