<?php

use App\Enums\TipoCondutor;
use App\Livewire\Portaria\Visitantes;
use App\Models\Condutor;
use App\Models\RegistroAcesso;
use App\Models\Usuario;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->operador = Usuario::factory()->create();
    $this->actingAs($this->operador);
});

it('registra novo visitante e libera entrada no patio', function () {
    Livewire::test(Visitantes::class)
        ->call('openCreateModal')
        ->set('cpf', '529.982.247-25')
        ->set('plate', 'vis1234')
        ->set('visitReason', 'Reunião com a coordenação')
        ->set('visitorName', 'Lucas Almeida')
        ->set('vehicleModel', 'Chevrolet Onix')
        ->set('vehicleColor', 'Prata')
        ->call('registerAndReleaseGate')
        ->assertHasNoErrors()
        ->assertSet('showFormModal', false)
        ->assertSee('Lucas Almeida')
        ->assertSee('VIS-1234');

    expect(Condutor::where('cpf', '529.982.247-25')->first()?->tipo)->toBe(TipoCondutor::Visitante)
        ->and(RegistroAcesso::where('placa_registro', 'VIS-1234')->exists())->toBeTrue();
});

it('impede sobrescrever um condutor institucional existente pelo cadastro de visitante', function () {
    $professor = Condutor::create([
        'nome' => 'Prof. Dr. Marcos Souza',
        'cpf' => '529.982.247-25',
        'tipo' => TipoCondutor::Professor,
        'ativo' => true,
    ]);

    Livewire::test(Visitantes::class)
        ->call('openCreateModal')
        ->set('cpf', '529.982.247-25')
        ->set('plate', 'XYZ-9988')
        ->set('visitReason', 'Entrega de documento')
        ->set('visitorName', 'Outro Nome')
        ->call('registerAndReleaseGate')
        ->assertHasErrors(['cpf']);

    expect($professor->fresh()->tipo)->toBe(TipoCondutor::Professor)
        ->and($professor->fresh()->nome)->toBe('Prof. Dr. Marcos Souza');
});

it('impede transferir veiculo ativo de outro condutor para um visitante', function () {
    $professor = Condutor::create([
        'nome' => 'Prof. Dr. Marcos Souza',
        'cpf' => '111.444.777-35',
        'tipo' => TipoCondutor::Professor,
        'ativo' => true,
    ]);

    $veiculo = Veiculo::create([
        'condutor_id' => $professor->id,
        'placa' => 'PRO-1234',
        'modelo' => 'Corolla',
        'cor' => 'Preto',
        'ativo' => true,
    ]);

    Livewire::test(Visitantes::class)
        ->call('openCreateModal')
        ->set('cpf', '529.982.247-25')
        ->set('plate', 'PRO-1234')
        ->set('visitReason', 'Visita técnica')
        ->set('visitorName', 'Visitante Teste')
        ->call('registerAndReleaseGate')
        ->assertHasErrors(['plate']);

    expect($veiculo->fresh()->condutor_id)->toBe($professor->id);
});
