<?php

use App\Livewire\Portaria\Monitoramento;
use App\Models\RegistroAcesso;
use App\Models\Usuario;
use App\Services\CloudinaryService;
use App\Services\JwtService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

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
    Config::set('services.python_api.url', 'http://127.0.0.1:5000');
    Config::set('services.python_api.token', 'token_teste');

    Http::fake([
        '*/detectar-placa' => Http::response(['placa' => 'BRA2E19'], 200),
    ]);

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
            'placa' => 'BRA-2E19',
            'placa_identificada' => true,
        ]);

    $nome = $response->json('nome');
    Storage::disk('public')->assertExists('perfis/'.$nome);

    $this->assertDatabaseHas('registros_acesso', [
        'id' => $response->json('registro_id'),
        'placa_registro' => 'BRA-2E19',
        'operador_id' => $this->usuario->id,
        'status' => 'pendente',
    ]);
});

test('salva na nuvem quando variaveis do cloudinary estao configuradas e exibe no monitoramento livewire', function () {
    Config::set('services.cloudinary.cloud_name', 'fake_cloud');
    Config::set('services.cloudinary.api_key', 'fake_key');
    Config::set('services.cloudinary.api_secret', 'fake_secret');
    Config::set('services.python_api.url', 'http://127.0.0.1:5000');
    Config::set('services.python_api.token', 'token_teste');

    Http::fake([
        '*/detectar-placa' => Http::response(['placa' => 'ABC1234'], 200),
    ]);

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
            'placa' => 'ABC-1234',
            'placa_identificada' => true,
        ]);

    $registro = RegistroAcesso::find($response->json('registro_id'));
    expect($registro)->not->toBeNull()
        ->and($registro->placa_registro)->toBe('ABC-1234')
        ->and($registro->veiculo_id)->toBeNull()
        ->and($registro->condutor_id)->toBeNull();

    Livewire::test(Monitoramento::class)
        ->assertSee('ABC-1234')
        ->assertSee('https://res.cloudinary.com/fake_cloud/image/upload/v12345/sentinela/placas/placa-teste.jpg')
        ->assertSee('Aguardando Liberação');
});

test('registra como nao cadastrado quando a api python nao identifica placa na imagem', function () {
    Config::set('services.cloudinary.cloud_name', null);
    Config::set('services.python_api.url', 'http://127.0.0.1:5000');
    Config::set('services.python_api.token', 'token_teste');

    Http::fake([
        '*/detectar-placa' => Http::response([
            'detail' => 'Nenhuma placa veicular valida foi identificada na imagem.',
        ], 404),
    ]);

    $file = UploadedFile::fake()->image('sem_placa.jpg');

    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$this->token,
    ])->postJson('/api/enviar', [
        'foto' => $file,
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'placa' => 'SEM-PLACA',
            'placa_identificada' => false,
        ]);

    $this->assertDatabaseHas('registros_acesso', [
        'id' => $response->json('registro_id'),
        'placa_registro' => 'SEM-PLACA',
        'status' => 'nao_cadastrado',
    ]);
});

test('retorna erro de validacao quando arquivo nao for enviado ou invalido', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$this->token,
    ])->postJson('/api/enviar', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['foto']);
});
