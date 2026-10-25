<?php

use App\Enums\StatusAcesso;
use App\Enums\TipoOcupante;
use App\Livewire\Portaria\Monitoramento;
use App\Models\Condutor;
use App\Models\RegistroAcesso;
use App\Models\Usuario;
use App\Models\Veiculo;
use App\Services\CloudinaryService;
use App\Services\JwtService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
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

test('retorna historico de fotos tiradas no dia atual com horario exato, placa e condutor', function () {
    $condutor = Condutor::create([
        'nome' => 'Prof. Dr. Marcos Souza',
        'cpf' => '111.222.333-44',
        'tipo' => 'professor',
        'ativo' => true,
    ]);

    Veiculo::create([
        'condutor_id' => $condutor->id,
        'placa' => 'BRA-2E19',
        'modelo' => 'Honda Civic',
        'cor' => 'Prata',
        'ativo' => true,
    ]);

    $horarioComPlaca = Carbon::today()->setTime(14, 32, 5);
    $horarioSemPlaca = Carbon::today()->setTime(15, 10, 20);

    // Registro de ontem (não deve aparecer no histórico de hoje)
    RegistroAcesso::create([
        'placa_registro' => 'OLD-9999',
        'data_hora_entrada' => Carbon::yesterday()->setTime(10, 0, 0),
        'foto_entrada_path' => 'https://exemplo.com/ontem.jpg',
        'status' => StatusAcesso::Finalizado,
        'tipo_ocupante' => TipoOcupante::Condutor,
    ]);

    // Registro 1 de hoje: placa identificada e vinculada ao veículo do condutor
    RegistroAcesso::create([
        'placa_registro' => 'BRA-2E19',
        'data_hora_entrada' => $horarioComPlaca,
        'foto_entrada_path' => 'https://exemplo.com/foto1.jpg',
        'status' => StatusAcesso::Pendente,
        'tipo_ocupante' => TipoOcupante::Condutor,
    ]);

    // Registro 2 de hoje: placa não identificada
    RegistroAcesso::create([
        'placa_registro' => RegistroAcesso::PLACA_NAO_IDENTIFICADA,
        'data_hora_entrada' => $horarioSemPlaca,
        'foto_entrada_path' => 'https://exemplo.com/foto2.jpg',
        'status' => StatusAcesso::NaoCadastrado,
        'tipo_ocupante' => TipoOcupante::Condutor,
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$this->token,
    ])->getJson('/api/historico');

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
            'total' => 2,
            'historico' => [
                [
                    'foto' => 'https://exemplo.com/foto2.jpg',
                    'horario' => '15:10:20',
                    'placa' => null,
                    'placa_identificada' => false,
                    'condutor' => 'Condutor não identificado',
                ],
                [
                    'foto' => 'https://exemplo.com/foto1.jpg',
                    'horario' => '14:32:05',
                    'placa' => 'BRA-2E19',
                    'placa_identificada' => true,
                    'condutor' => 'Prof. Dr. Marcos Souza',
                ],
            ],
        ]);
});
