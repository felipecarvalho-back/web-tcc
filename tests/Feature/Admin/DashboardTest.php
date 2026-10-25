<?php

use App\Enums\StatusAcesso;
use App\Enums\TipoOcupante;
use App\Livewire\Admin\Dashboard;
use App\Models\Condutor;
use App\Models\CondutorProfessor;
use App\Models\RegistroAcesso;
use App\Models\Usuario;
use App\Models\Veiculo;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = Usuario::query()->create([
        'codigo_operador' => 'ADM-900',
        'nome' => 'Admin Dashboard',
        'cpf' => '529.982.247-25',
        'email' => 'admin.dash@fatec.sp.gov.br',
        'senha' => 'senha123',
        'perfil' => 'admin',
        'ativo' => true,
    ]);

    $this->operador = Usuario::query()->create([
        'codigo_operador' => 'GDA-901',
        'nome' => 'Guarda Operacional',
        'cpf' => '111.444.777-35',
        'email' => 'guarda.dash@fatec.sp.gov.br',
        'senha' => 'senha123',
        'perfil' => 'operador',
        'ativo' => true,
    ]);

    $condutor = Condutor::query()->create([
        'nome' => 'Profa. Dra. Helena Costa',
        'cpf' => '123.456.789-09',
        'tipo' => 'professor',
        'ativo' => true,
    ]);

    CondutorProfessor::query()->create([
        'condutor_id' => $condutor->id,
        'codigo_acesso' => 'DOC-9001',
        'departamento' => 'ADS',
    ]);

    $veiculo = Veiculo::query()->create([
        'condutor_id' => $condutor->id,
        'placa' => 'HEL-2026',
        'modelo' => 'Toyota Corolla',
        'cor' => 'Branco',
    ]);

    RegistroAcesso::query()->create([
        'veiculo_id' => $veiculo->id,
        'condutor_id' => $condutor->id,
        'placa_registro' => 'HEL-2026',
        'cancela' => 'Cancela 01 (Principal)',
        'data_hora_entrada' => Carbon::now()->setTime(8, 15, 0),
        'data_hora_saida' => null,
        'operador_id' => $this->operador->id,
        'status' => StatusAcesso::Autorizado,
        'tipo_ocupante' => TipoOcupante::Condutor,
    ]);
});

test('administrador acessa o dashboard e visualiza indicadores reais do banco de dados', function () {
    $this->actingAs($this->admin)
        ->get('/admin/dashboard')
        ->assertOk()
        ->assertSee('Painel de Indicadores e Telemetria')
        ->assertSee('Usuários Ativos no Sistema')
        ->assertSee('Quantidade de Veículos')
        ->assertSee('Entradas & Saídas', false)
        ->assertSee('Total de Registros');

    Livewire::actingAs($this->admin)
        ->test(Dashboard::class)
        ->assertSee('Profa. Dra. Helena Costa')
        ->assertSee('HEL-2026')
        ->assertSee('1 operador(es) + 1 admin(s)')
        ->call('setPeriod', 'mes')
        ->assertSet('periodFilter', 'mes')
        ->assertSee('HEL-2026');
});
