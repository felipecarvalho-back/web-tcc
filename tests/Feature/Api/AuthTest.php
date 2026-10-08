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

test('retorna token jwt e dados do usuario ao fazer login por codigo_operador', function () {
    $response = $this->postJson('/api/login', [
        'codigo' => 'OP-100',
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

test('retorna erro 401 para credenciais incorretas', function () {
    $response = $this->postJson('/api/login', [
        'codigo' => 'OP-100',
        'senha' => 'senha_errada',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'status' => 'error',
            'message' => 'Credenciais inválidas. Verifique seu código de operador e senha.',
        ]);
});

test('retorna erro 403 quando o usuario esta inativo', function () {
    $this->usuario->update(['ativo' => false]);

    $response = $this->postJson('/api/login', [
        'codigo' => 'OP-100',
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
        ->assertJsonValidationErrors(['codigo', 'senha']);
});
