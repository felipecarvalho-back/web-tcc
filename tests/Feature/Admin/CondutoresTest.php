<?php

use App\Livewire\Admin\Condutores;
use App\Models\Condutor;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = Usuario::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('cadastra um professor com multiplos veiculos e normaliza placas com hifen', function () {
    Livewire::test(Condutores::class)
        ->set('activeTab', 'professores')
        ->call('openCreateModal')
        ->assertSet('showFormModal', true)
        ->set('name', 'Prof. Dr. Roberto Valente')
        ->set('cpf', '529.982.247-25')
        ->set('code', 'DOC-99112')
        ->set('department', 'Tecnologia da Informação')
        ->set('vehicles.0.plate', 'rob1111')
        ->set('vehicles.0.model', 'Cruze')
        ->call('addVehicle')
        ->set('vehicles.1.plate', 'ROB-2222')
        ->set('vehicles.1.model', 'Tracker')
        ->set('vehicles.1.color', 'Prata')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showFormModal', false)
        ->assertSee('Prof. Dr. Roberto Valente')
        ->assertSee('ROB-1111')
        ->assertSee('ROB-2222');

    $condutor = Condutor::with(['veiculos', 'professor'])->first();

    expect($condutor)->not->toBeNull()
        ->and($condutor->cpf)->toBe('529.982.247-25')
        ->and($condutor->professor->codigo_acesso)->toBe('DOC-99112')
        ->and($condutor->veiculos->pluck('placa')->all())->toEqualCanonicalizing(['ROB-1111', 'ROB-2222']);
});

it('rejeita cadastro com cpf invalido ou placa duplicada', function () {
    Livewire::test(Condutores::class)
        ->call('openCreateModal')
        ->set('name', 'Condutor Teste')
        ->set('cpf', '111.111.111-11')
        ->set('code', 'DOC-100')
        ->set('vehicles.0.plate', 'DUP-1234')
        ->set('vehicles.0.model', 'Onix')
        ->call('addVehicle')
        ->set('vehicles.1.plate', 'dup1234')
        ->set('vehicles.1.model', 'Civic')
        ->call('save')
        ->assertHasErrors(['cpf', 'vehicles.0.plate', 'vehicles.1.plate']);
});

it('permite editar, alternar status e excluir um condutor', function () {
    Livewire::test(Condutores::class)
        ->set('activeTab', 'funcionarios')
        ->call('openCreateModal')
        ->set('name', 'Maria Oliveira')
        ->set('cpf', '529.982.247-25')
        ->set('code', 'MAT-501')
        ->set('department', 'Secretaria Acadêmica')
        ->set('vehicles.0.plate', 'MAR-1020')
        ->set('vehicles.0.model', 'HB20')
        ->call('save')
        ->assertHasNoErrors();

    $condutor = Condutor::firstOrFail();

    Livewire::test(Condutores::class)
        ->set('activeTab', 'funcionarios')
        ->call('openEditModal', $condutor->id)
        ->assertSet('name', 'Maria Oliveira')
        ->assertSet('cpf', '529.982.247-25')
        ->set('name', 'Maria Oliveira Santos')
        ->call('save')
        ->assertHasNoErrors()
        ->call('toggleStatus', $condutor->id);

    expect($condutor->fresh()->ativo)->toBeFalse();

    Livewire::test(Condutores::class)
        ->set('activeTab', 'funcionarios')
        ->call('delete', $condutor->id);

    expect(Condutor::find($condutor->id))->toBeNull();
});

it('permite aplicar opcoes pre-configuradas e determinar data customizada de validade', function () {
    $hoje = Carbon::today();
    $dataEsperada30Dias = $hoje->copy()->addDays(30)->format('Y-m-d');

    // 1. Testa aplicação de preset de 30 dias e depois altera para data personalizada
    $test = Livewire::test(Condutores::class)
        ->set('activeTab', 'prestadores')
        ->call('openCreateModal')
        ->assertSet('category', 'prestador')
        ->call('applyValidityPreset', '30_dias')
        ->assertSet('validityType', 'data')
        ->assertSet('validityDate', $dataEsperada30Dias)
        ->set('name', 'Prestador Infra')
        ->set('cpf', '529.982.247-25')
        ->set('code', 'PST-101')
        ->set('department', 'Manutenção Predial / Infraestrutura')
        ->set('validityDate', '2027-12-31')
        ->set('vehicles.0.plate', 'PRE-9988')
        ->set('vehicles.0.model', 'Fiorino')
        ->call('save')
        ->assertHasNoErrors();

    $condutor = Condutor::where('cpf', '529.982.247-25')->firstOrFail();
    expect($condutor->validade_acesso)->toBe('2027-12-31')
        ->and($condutor->estaComAcessoExpirado())->toBeFalse()
        ->and($condutor->statusValidade()['status'])->toBe('valido');

    // 2. Altera para Permanente via Preset 'indeterminado'
    Livewire::test(Condutores::class)
        ->set('activeTab', 'prestadores')
        ->call('openEditModal', $condutor->id)
        ->assertSet('validityDate', '2027-12-31')
        ->call('applyValidityPreset', 'indeterminado')
        ->assertSet('validityType', 'indeterminado')
        ->assertSet('validityDate', '')
        ->call('save')
        ->assertHasNoErrors();

    expect($condutor->fresh()->validade_acesso)->toBe('Indeterminado')
        ->and($condutor->fresh()->estaComAcessoExpirado())->toBeFalse()
        ->and($condutor->fresh()->statusValidade()['status'])->toBe('permanente');
});

it('rejeita cadastro com prazo determinado quando a data limite nao for informada', function () {
    Livewire::test(Condutores::class)
        ->call('openCreateModal')
        ->set('name', 'Professor Teste')
        ->set('cpf', '529.982.247-25')
        ->set('code', 'DOC-55443')
        ->set('department', 'Tecnologia da Informação')
        ->call('setValidityType', 'data')
        ->set('validityDate', '')
        ->set('vehicles.0.plate', 'TES-1234')
        ->set('vehicles.0.model', 'Gol')
        ->call('save')
        ->assertHasErrors(['validityDate']);
});

it('identifica corretamente condutor com prazo de validade expirado', function () {
    $ontem = Carbon::yesterday()->format('Y-m-d');

    $condutor = Condutor::create([
        'nome' => 'Carlos Vencido',
        'cpf' => '529.982.247-25',
        'tipo' => 'prestador',
        'validade_acesso' => $ontem,
        'ativo' => true,
    ]);

    expect($condutor->estaComAcessoExpirado())->toBeTrue()
        ->and($condutor->statusValidade()['status'])->toBe('expirado')
        ->and($condutor->statusValidade()['expirado'])->toBeTrue();
});
