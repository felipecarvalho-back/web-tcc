<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct(
        protected JwtService $jwtService
    ) {}

    /**
     * Autentica o usuário recebendo credenciais em JSON e retorna o token JWT + dados do usuário.
     */
    public function login(Request $request): JsonResponse
    {
        // 1. Normaliza as credenciais (suporta 'login', 'email' ou 'username' e 'senha' ou 'password')
        $codigo = $request->input('username');
        $senha = $$request->input('password');

        $validator = Validator::make([
            'login' => $codigo,
            'senha' => $senha,
        ], [
            'login' => ['required', 'string'],
            'senha' => ['required', 'string'],
        ], [
            'login.required' => 'Informe o código de operador.',
            'senha.required' => 'A senha é obrigatória.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dados de autenticação inválidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $codigo = trim((string) $codigo);

        // 2. Busca o usuário por codigo_operador E que esteja ativo (ativo = 1)
        $usuario = Usuario::select('id', 'nome', 'codigo_operador', 'email', 'senha', 'ativo', 'perfil')
            ->where('codigo_operador', $codigo)
            ->where('ativo', 1) // Garante que só traz se estiver ativo
            ->first();

        // 3. Se não achar (ou porque não existe, ou porque está inativo) OU a senha estiver errada
        if (!$usuario || !Hash::check($senha, $usuario->senha)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Credenciais inválidas ou usuário inativo.',
            ], 401);
        }

        // 4. Geração do token JWT
        $token = $this->jwtService->generateToken($usuario);

        // 5. Retorno padronizado
        return response()->json([
            'status' => 'success',
            'message' => 'Autenticação realizada com sucesso.',
            'access_token' => $token,
            'expires_in' => $this->jwtService->getTTLInSeconds(),
            'usuario' => [
                'id' => $usuario->id,
                'nome' => $usuario->nome,
                'email' => $usuario->email,
                'codigo_operador' => $usuario->codigo_operador,
                'perfil' => $usuario->perfil,
                'ativo' => (bool) $usuario->ativo,
            ],
        ], 200);
    }
    
    /**
     * Renova o token JWT emitindo um novo para o usuário autenticado.
     */
    public function refresh(Request $request): JsonResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $token = $this->jwtService->generateToken($usuario);

        return response()->json([
            'status' => 'success',
            'message' => 'Token renovado com sucesso.',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->jwtService->getTTLInSeconds(),
        ]);
    }
}
