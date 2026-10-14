<?php

use App\Models\Usuario;
use App\Services\CloudinaryService;
use App\Services\JwtService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->usuario = Usuario::create([
        'nome' => 'Operador Teste',
        'cpf' => '123.456.789-00',
        'email' => 'operador@fatec.sp.gov.br',
        'senha' => Hash::make('segredo123'),
        'perfil' => 'operador',
        'codigo_operador' => 'OP-100',
        'ativo' => true,
    ]);

    $jwtService = app(JwtService::class);
    $this->token = $jwtService->generateToken($this->usuario);
});

test('salva localmente quando variaveis de ambiente do cloudinary nao estao configuradas', function () {
    Config::set('services.cloudinary.cloud_name', null);
    Config::set('services.cloudinary.api_key', null);
    Config::set('services.cloudinary.api_secret', null);

    $file = UploadedFile::fake()->image('placa.jpg');

    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$this->token,
    ])->postJson('/api/enviar', [
        'foto' => $file,
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'mensagem' => 'Imagem salva com sucesso localmente!',
            'armazenamento' => 'local',
        ]);

    $nome = $response->json('nome');
    Storage::disk('public')->assertExists('perfis/'.$nome);
});

test('salva na nuvem quando variaveis do cloudinary estao configuradas', function () {
    Config::set('services.cloudinary.cloud_name', 'fake_cloud');
    Config::set('services.cloudinary.api_key', 'fake_key');
    Config::set('services.cloudinary.api_secret', 'fake_secret');

    $mockCloudinary = Mockery::mock(CloudinaryService::class);
    $mockCloudinary->shouldReceive('isConfigured')->once()->andReturn(true);
    $mockCloudinary->shouldReceive('upload')
        ->once()
        ->andReturn('https://res.cloudinary.com/fake_cloud/image/upload/v12345/sentinela/placas/placa-teste.jpg');

    $this->app->instance(CloudinaryService::class, $mockCloudinary);

    $file = UploadedFile::fake()->image('placa.png');

    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$this->token,
    ])->postJson('/api/enviar', [
        'foto' => $file,
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'mensagem' => 'Imagem enviada com sucesso para a nuvem!',
            'url' => 'https://res.cloudinary.com/fake_cloud/image/upload/v12345/sentinela/placas/placa-teste.jpg',
            'armazenamento' => 'cloudinary',
        ]);
});

test('retorna erro de validacao quando arquivo nao for enviado ou invalido', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$this->token,
    ])->postJson('/api/enviar', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['foto']);
});
