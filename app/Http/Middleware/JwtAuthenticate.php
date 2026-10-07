<?php

namespace App\Http\Middleware;

use App\Services\JwtService;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthenticate
{
    public function __construct(
        protected JwtService $jwtService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Token de autenticação não fornecido no cabeçalho Authorization.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $usuario = $this->jwtService->getUserFromToken($token);

        if (! $usuario) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Token JWT inválido ou expirado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (! $usuario->ativo) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Usuário inativo no sistema.',
            ], Response::HTTP_FORBIDDEN);
        }

        // Define o usuário autenticado na requisição
        $request->setUserResolver(fn () => $usuario);

        return $next($request);
    }
}
