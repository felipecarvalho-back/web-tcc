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
     * Autentica o usuário operador via código de operador e senha, retornando token JWT e dados do usuário.
     */
    public function login(Request $request): JsonResponse
    {
        // Aceita 'codigo' ou 'codigo_operador'
        $codigo = $request->input('codigo') ?? $request->input('codigo_operador');

        // Aceita 'senha' ou 'password'
        $senha = $request->input('senha') ?? $request->input('password');

        $validator = Validator::make([
            'codigo' => $codigo,
            'senha' => $senha,
        ], [
            'codigo' => ['required', 'string'],
            'senha' => ['required', 'string'],
        ], [
            'codigo.required' => 'Informe o código de operador.',
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

        // Busca o usuário por codigo_operador
        $usuario = Usuario::select('id', 'nome', 'codigo_operador', 'email', 'senha', 'ativo', 'perfil')
            ->where('codigo_operador', $codigo)
            ->first();

        // Se não encontrar ou senha incorreta
        if (! $usuario || ! Hash::check((string) $senha, $usuario->senha)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Credenciais inválidas. Verifique seu código de operador e senha.',
            ], 401);
        }

        // Se o usuário estiver inativo
        if (! $usuario->ativo) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado. Usuário inativo no sistema.',
            ], 403);
        }

        // Geração do token JWT
        $token = $this->jwtService->generateToken($usuario);

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
}
