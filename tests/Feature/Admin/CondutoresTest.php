<?php

use App\Livewire\Admin\Condutores;
use App\Models\Condutor;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
