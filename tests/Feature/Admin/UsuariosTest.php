<?php

use App\Enums\PerfilUsuario;
use App\Livewire\Admin\Usuarios;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = Usuario::factory()->admin()->create([
        'cpf' => '529.982.247-25',
        'email' => 'admin.gestao@fatec.sp.gov.br',
    ]);

    $this->actingAs($this->admin);
});

it('permite cadastrar, editar, alternar status e excluir outro usuario', function () {
    Livewire::test(Usuarios::class)
        ->call('openCreateModal')
        ->assertSet('showFormModal', true)
        ->set('nome', 'Renato Augusto')
        ->set('cpf', '111.444.777-35')
        ->set('email', 'renato@fatec.sp.gov.br')
        ->set('perfil', PerfilUsuario::Operador->value)
        ->set('senha', 'senha123')
        ->set('senha_confirmation', 'senha123')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showFormModal', false)
        ->assertSee('Renato Augusto');

    $operador = Usuario::where('email', 'renato@fatec.sp.gov.br')->firstOrFail();

    Livewire::test(Usuarios::class)
        ->call('toggleStatus', $operador->id);

    expect($operador->fresh()->ativo)->toBeFalse();

    Livewire::test(Usuarios::class)
        ->call('delete', $operador->id);

    expect(Usuario::find($operador->id))->toBeNull();
});

it('impede o administrador logado de desativar, rebaixar ou excluir a propria conta', function () {
    Livewire::test(Usuarios::class)
        ->call('toggleStatus', $this->admin->id)
        ->call('delete', $this->admin->id);

    expect($this->admin->fresh()->ativo)->toBeTrue()
        ->and(Usuario::find($this->admin->id))->not->toBeNull();

    Livewire::test(Usuarios::class)
        ->call('openEditModal', $this->admin->id)
        ->set('ativo', false)
        ->call('save')
        ->assertHasErrors(['ativo']);

    Livewire::test(Usuarios::class)
        ->call('openEditModal', $this->admin->id)
        ->set('ativo', true)
        ->set('perfil', PerfilUsuario::Operador->value)
        ->call('save')
        ->assertHasErrors(['perfil']);

    expect($this->admin->fresh()->perfil)->toBe(PerfilUsuario::Admin)
        ->and($this->admin->fresh()->ativo)->toBeTrue();
});
