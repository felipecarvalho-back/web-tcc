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
        $identificador = $request->input('login') ?? $request->input('email') ?? $request->input('username');
        $senha = $request->input('senha') ?? $request->input('password');

        $validator = Validator::make([
            'login' => $identificador,
            'senha' => $senha,
        ], [
            'login' => ['required', 'string'],
            'senha' => ['required', 'string'],
        ], [
            'login.required' => 'Informe o e-mail ou código de operador.',
            'senha.required' => 'A senha é obrigatória.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dados de autenticação inválidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $identificador = trim((string) $identificador);

        // 2. Busca o usuário por email, codigo_operador ou cpf
        $usuario = Usuario::query()
            ->where(function ($query) use ($identificador) {
                $query->where('email', $identificador)
                    ->orWhere('codigo_operador', $identificador)
                    ->orWhere('cpf', $identificador);
            })
            ->first();

        // 3. Validação de existência e verificação de senha
        if (! $usuario || ! Hash::check($senha, $usuario->senha)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Credenciais inválidas. Verifique seu usuário e senha.',
            ], 401);
        }

        // 4. Verificação de status ativo
        if (! $usuario->ativo) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado. Usuário inativo no sistema.',
            ], 403);
        }

        // 5. Geração do token JWT
        $token = $this->jwtService->generateToken($usuario);

        // 6. Retorno padronizado
        return response()->json([
            'status' => 'success',
            'message' => 'Autenticação realizada com sucesso.',
            'access_token' => $token,
            'token_type' => 'bearer',
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
     * Retorna os dados do usuário autenticado no token atual.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        return response()->json([
            'status' => 'success',
            'usuario' => [
                'id' => $usuario->id,
                'nome' => $usuario->nome,
                'email' => $usuario->email,
                'codigo_operador' => $usuario->codigo_operador,
                'perfil' => $usuario->perfil,
                'ativo' => (bool) $usuario->ativo,
            ],
        ]);
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

    /**
     * Finaliza a sessão / logout do token.
     */
    public function logout(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Desconectado com sucesso.',
        ]);
    }
}
