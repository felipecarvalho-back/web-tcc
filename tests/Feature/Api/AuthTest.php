<?php

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->usuario = Usuario::create([
        'nome' => 'Operador Teste',
        'cpf' => '123.456.789-00',
        'email' => 'operador@fatec.sp.gov.br',
        'senha' => Hash::make('segredo123'),
        'perfil' => 'operador',
        'codigo_operador' => 'OP-100',
        'ativo' => true,
    ]);
});

test('retorna token jwt e dados do usuario ao fazer login por email', function () {
    $response = $this->postJson('/api/login', [
        'login' => 'operador@fatec.sp.gov.br',
        'senha' => 'segredo123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'access_token',
            'token_type',
            'expires_in',
            'usuario' => [
                'id',
                'nome',
                'email',
                'codigo_operador',
                'perfil',
                'ativo',
            ],
        ])
        ->assertJson([
            'status' => 'success',
            'token_type' => 'bearer',
            'usuario' => [
                'id' => $this->usuario->id,
                'nome' => 'Operador Teste',
                'email' => 'operador@fatec.sp.gov.br',
                'codigo_operador' => 'OP-100',
                'perfil' => 'operador',
                'ativo' => true,
            ],
        ]);

    expect($response->json('access_token'))->toBeString()->not->toBeEmpty();
});

test('retorna token jwt e dados do usuario ao fazer login por codigo_operador', function () {
    $response = $this->postJson('/api/login', [
        'login' => 'OP-100',
        'senha' => 'segredo123',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'usuario' => [
                'codigo_operador' => 'OP-100',
            ],
        ]);
});

test('retorna erro 401 para credenciais incorretas', function () {
    $response = $this->postJson('/api/login', [
        'login' => 'operador@fatec.sp.gov.br',
        'senha' => 'senha_errada',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'status' => 'error',
            'message' => 'Credenciais inválidas. Verifique seu usuário e senha.',
        ]);
});

test('retorna erro 403 quando o usuario esta inativo', function () {
    $this->usuario->update(['ativo' => false]);

    $response = $this->postJson('/api/login', [
        'login' => 'OP-100',
        'senha' => 'segredo123',
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'status' => 'error',
            'message' => 'Acesso negado. Usuário inativo no sistema.',
        ]);
});

test('valida campos obrigatorios no login', function () {
    $response = $this->postJson('/api/login', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['login', 'senha']);
});

test('permite acessar rota protegida com o token jwt', function () {
    $loginResponse = $this->postJson('/api/login', [
        'login' => 'OP-100',
        'senha' => 'segredo123',
    ]);

    $token = $loginResponse->json('access_token');

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/me');

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'usuario' => [
                'id' => $this->usuario->id,
                'email' => 'operador@fatec.sp.gov.br',
            ],
        ]);
});

test('bloqueia acesso a rota protegida sem token jwt', function () {
    $response = $this->getJson('/api/me');

    $response->assertStatus(401)
        ->assertJson([
            'status' => 'error',
        ]);
});
