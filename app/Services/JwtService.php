<?php

namespace App\Services;

use App\Models\Usuario;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Config;

class JwtService
{
    /**
     * Gera um token JWT assinado para o usuário.
     */
    public function generateToken(Usuario $usuario, ?int $ttlMinutes = null): string
    {
        $secret = $this->getSecretKey();
        $algo = Config::get('jwt.algo', 'HS256');
        $ttl = ($ttlMinutes ?? (int) Config::get('jwt.ttl', 60)) * 60;

        $currentTime = time();
        $expirationTime = $currentTime + $ttl;

        $payload = [
            'iss' => Config::get('app.url', 'http://localhost'),
            'sub' => (string) $usuario->id,
            'iat' => $currentTime,
            'nbf' => $currentTime,
            'exp' => $expirationTime,
            'usuario' => [
                'id' => $usuario->id,
                'nome' => $usuario->nome,
                'email' => $usuario->email,
                'codigo_operador' => $usuario->codigo_operador,
                'perfil' => $usuario->perfil,
            ],
        ];

        return JWT::encode($payload, $secret, $algo);
    }

    /**
     * Decodifica e valida o token JWT.
     * Retorna o payload como objeto ou null se inválido/expirado.
     */
    public function decodeToken(string $token): ?object
    {
        try {
            $secret = $this->getSecretKey();
            $algo = Config::get('jwt.algo', 'HS256');

            return JWT::decode($token, new Key($secret, $algo));
        } catch (Exception) {
            return null;
        }
    }

    /**
     * Obtém a instância do usuário a partir do token JWT.
     */
    public function getUserFromToken(string $token): ?Usuario
    {
        $payload = $this->decodeToken($token);

        if (! $payload || empty($payload->sub)) {
            return null;
        }

        return Usuario::query()->whereKey($payload->sub)->first();
    }

    /**
     * Retorna o TTL configurado em segundos.
     */
    public function getTTLInSeconds(): int
    {
        return ((int) Config::get('jwt.ttl', 60)) * 60;
    }

    /**
     * Obtém a chave secreta usada para assinar/decodificar.
     */
    protected function getSecretKey(): string
    {
        $secret = Config::get('jwt.secret');

        if (empty($secret)) {
            $secret = Config::get('app.key', 'base64:default-insecure-secret-key-32chars');
        }

        if (str_starts_with($secret, 'base64:')) {
            $decoded = base64_decode(substr($secret, 7), true);
            if ($decoded !== false) {
                return $decoded;
            }
        }

        return $secret;
    }
}
