<?php

use App\Enums\StatusAcesso;
use App\Enums\TipoOcupante;
use App\Livewire\Portaria\Monitoramento;
use App\Models\Condutor;
use App\Models\CondutorFuncionario;
use App\Models\CondutorPrestador;
use App\Models\CondutorProfessor;
use App\Models\RegistroAcesso;
use App\Models\Usuario;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->operador = Usuario::factory()->create();
    $this->actingAs($this->operador);

    // Professor com carro cadastrado
    $this->professor = Condutor::create([
        'nome' => 'Prof. Dr. Marcos Souza',
        'cpf' => '529.982.247-25',
        'tipo' => 'professor',
        'validade_acesso' => 'Indeterminado',
        'ativo' => true,
    ]);
    CondutorProfessor::create([
        'condutor_id' => $this->professor->id,
        'codigo_acesso' => 'DOC-88312',
        'departamento' => 'DSM - Coordenação DSM',
    ]);
    $this->veiculoProfessor = Veiculo::create([
        'condutor_id' => $this->professor->id,
        'placa' => 'BRA-2819',
        'modelo' => 'Toyota Corolla',
        'cor' => 'Prata',
        'ativo' => true,
    ]);

    // Funcionário
    $this->funcionario = Condutor::create([
        'nome' => 'Fernanda Guimarães',
        'cpf' => '111.444.777-35',
        'tipo' => 'funcionario',
        'validade_acesso' => 'Indeterminado',
        'ativo' => true,
    ]);
    CondutorFuncionario::create([
        'condutor_id' => $this->funcionario->id,
        'codigo_acesso' => 'ADM-10293',
        'setor' => 'Secretaria Acadêmica',
    ]);

    // Prestador de Serviço com veículo cadastrado
    $this->prestador = Condutor::create([
        'nome' => 'Carlos Silva',
        'cpf' => '123.456.789-09',
        'tipo' => 'prestador',
        'validade_acesso' => 'Semestre Atual',
        'ativo' => true,
    ]);
    CondutorPrestador::create([
        'condutor_id' => $this->prestador->id,
        'codigo_acesso' => 'PST-5512',
        'empresa' => 'Manutenção Predial / Infraestrutura',
    ]);
    $this->veiculoPrestador = Veiculo::create([
        'condutor_id' => $this->prestador->id,
        'placa' => 'PST-1234',
        'modelo' => 'Fiat Fiorino',
        'cor' => 'Branca',
        'ativo' => true,
    ]);
});

it('caso 1: libera veiculo identificado no banco validando apenas o codigo de acesso do condutor', function () {
    $registro = RegistroAcesso::create([
        'veiculo_id' => $this->veiculoProfessor->id,
        'condutor_id' => $this->professor->id,
        'tipo_ocupante' => TipoOcupante::Condutor,
        'placa_registro' => 'BRA-2819',
        'cancela' => 'Cancela 01 (Principal)',
        'data_hora_entrada' => Carbon::now(),
        'status' => StatusAcesso::Pendente,
    ]);

    Livewire::test(Monitoramento::class)
        ->call('openDriverConfirmationModal', $registro->id)
        ->assertSet('showDriverConfirmationModal', true)
        ->set('confirmDriverCode', 'CODIGO-ERRADO')
        ->call('confirmDriver')
        ->assertHasErrors(['confirmDriverCode'])
        ->set('confirmDriverCode', 'DOC-88312')
        ->set('newPassengerCode', 'ADM-10293')
        ->call('addPassenger')
        ->assertCount('passengers', 1)
        ->call('confirmDriver')
        ->assertHasNoErrors()
        ->assertSet('showDriverConfirmationModal', false);

    expect($registro->fresh()->status)->toBe(StatusAcesso::Autorizado)
        ->and(RegistroAcesso::where('passagem_origem_id', $registro->id)->count())->toBe(1);
});

it('caso 2: exige corrigir a placa e informar o codigo de acesso quando o ocr nao identificou no banco', function () {
    $registro = RegistroAcesso::create([
        'veiculo_id' => null,
        'condutor_id' => null,
        'tipo_ocupante' => TipoOcupante::Condutor,
        'placa_registro' => 'SEM-PLACA',
        'cancela' => 'Cancela 01 (Principal)',
        'data_hora_entrada' => Carbon::now(),
        'status' => StatusAcesso::NaoCadastrado,
    ]);

    Livewire::test(Monitoramento::class)
        ->call('openVerificationModal', $registro->id)
        ->assertSet('showVerificationModal', true)
        ->assertSet('verificationMode', 'corrigir_placa')
        ->set('correctedPlate', 'pst1234')
        ->set('driverAccessCode', 'PST-5512')
        ->call('confirmVerification')
        ->assertHasNoErrors()
        ->assertSet('showVerificationModal', false);

    $atualizado = $registro->fresh();

    expect($atualizado->status)->toBe(StatusAcesso::LiberadoManual)
        ->and($atualizado->placa_corrigida)->toBe('PST-1234')
        ->and($atualizado->condutor_id)->toBe($this->prestador->id)
        ->and($atualizado->veiculo_id)->toBe($this->veiculoPrestador->id);
});

it('caso 3: permite carro de terceiro apenas para professor e funcionario pelo codigo de acesso e bloqueia prestador', function () {
    $registro = RegistroAcesso::create([
        'veiculo_id' => null,
        'condutor_id' => null,
        'tipo_ocupante' => TipoOcupante::Condutor,
        'placa_registro' => 'TER-9988',
        'cancela' => 'Cancela 01 (Principal)',
        'data_hora_entrada' => Carbon::now(),
        'status' => StatusAcesso::NaoCadastrado,
    ]);

    Livewire::test(Monitoramento::class)
        ->call('openVerificationModal', $registro->id)
        ->set('verificationMode', 'carro_terceiro')
        ->set('driverAccessCode', 'PST-5512')
        ->call('confirmVerification')
        ->assertHasErrors(['driverAccessCode'])
        ->set('driverAccessCode', 'ADM-10293')
        ->call('confirmVerification')
        ->assertHasNoErrors()
        ->assertSet('showVerificationModal', false);

    $atualizado = $registro->fresh();

    expect($atualizado->status)->toBe(StatusAcesso::LiberadoManual)
        ->and($atualizado->condutor_id)->toBe($this->funcionario->id)
        ->and($atualizado->veiculo_id)->toBeNull();
});

it('permite que o operador da guarita bloqueie o registro quando o veiculo nao for cadastrado e nao tiver codigo', function () {
    $registro = RegistroAcesso::create([
        'veiculo_id' => null,
        'condutor_id' => null,
        'tipo_ocupante' => TipoOcupante::Condutor,
        'placa_registro' => 'XYZ-9999',
        'cancela' => 'Cancela 01 (Principal)',
        'data_hora_entrada' => Carbon::now(),
        'status' => StatusAcesso::NaoCadastrado,
    ]);

    Livewire::test(Monitoramento::class)
        ->call('openBlockModal', $registro->id)
        ->assertSet('showBlockModal', true)
        ->set('blockReason', 'Condutor não possui código de acesso')
        ->set('blockCustomJustification', 'Veículo recusado por falta de cadastro e código')
        ->call('confirmBlock')
        ->assertSet('showBlockModal', false);

    $atualizado = $registro->fresh();
    expect($atualizado->status)->toBe(StatusAcesso::Bloqueado)
        ->and($atualizado->justificativa)->toContain('Veículo recusado por falta de cadastro e código');

    // Testar reversão / reabertura do registro
    Livewire::test(Monitoramento::class)
        ->call('unblockRecord', $registro->id);

    expect($registro->fresh()->status)->toBe(StatusAcesso::NaoCadastrado);
});

it('permite acionar o bloqueio de acesso diretamente de dentro do modal de verificacao', function () {
    $registro = RegistroAcesso::create([
        'veiculo_id' => null,
        'condutor_id' => null,
        'tipo_ocupante' => TipoOcupante::Condutor,
        'placa_registro' => 'SEM-PLACA',
        'cancela' => 'Cancela 01 (Principal)',
        'data_hora_entrada' => Carbon::now(),
        'status' => StatusAcesso::NaoCadastrado,
    ]);

    Livewire::test(Monitoramento::class)
        ->call('openVerificationModal', $registro->id)
        ->assertSet('showVerificationModal', true)
        ->call('openBlockModal', $registro->id)
        ->assertSet('showVerificationModal', false)
        ->assertSet('showBlockModal', true)
        ->call('confirmBlock');

    expect($registro->fresh()->status)->toBe(StatusAcesso::Bloqueado);
});

it('bloqueia a liberacao de condutor com prazo de autorizacao expirado', function () {
    $ontem = Carbon::yesterday()->format('Y-m-d');

    $condutorExpirado = Condutor::create([
        'nome' => 'Professor Contrato Vencido',
        'cpf' => '654.321.987-00',
        'tipo' => 'professor',
        'validade_acesso' => $ontem,
        'ativo' => true,
    ]);

    CondutorProfessor::create([
        'condutor_id' => $condutorExpirado->id,
        'codigo_acesso' => 'DOC-00001',
        'departamento' => 'Engenharia',
    ]);

    $veiculo = Veiculo::create([
        'condutor_id' => $condutorExpirado->id,
        'placa' => 'VNC-9999',
        'modelo' => 'Focus',
        'cor' => 'Branco',
        'ativo' => true,
    ]);

    $registro = RegistroAcesso::create([
        'veiculo_id' => $veiculo->id,
        'condutor_id' => $condutorExpirado->id,
        'tipo_ocupante' => TipoOcupante::Condutor,
        'placa_registro' => 'VNC-9999',
        'cancela' => 'Cancela 01 (Principal)',
        'data_hora_entrada' => Carbon::now(),
        'status' => StatusAcesso::Pendente,
    ]);

    Livewire::test(Monitoramento::class)
        ->call('openDriverConfirmationModal', $registro->id)
        ->assertSet('selectedRecord.is_driver_expired', true)
        ->set('confirmDriverCode', 'DOC-00001')
        ->call('confirmDriver')
        ->assertHasErrors(['confirmDriverCode']);

    expect($registro->fresh()->status)->toBe(StatusAcesso::Pendente);
});
