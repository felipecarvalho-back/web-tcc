<?php

use App\Livewire\Admin\Relatorios;
use App\Models\Usuario;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = Usuario::query()->create([
        'codigo_operador' => 'ADM-999',
        'nome' => 'Admin Teste',
        'cpf' => '000.111.222-33',
        'email' => 'admin.teste@fatec.sp.gov.br',
        'senha' => 'senha123',
        'perfil' => 'admin',
        'ativo' => true,
    ]);
});

test('administrador autenticado consegue acessar a tela de relatorios', function () {
    $this->actingAs($this->admin)
        ->get('/admin/relatorios')
        ->assertOk()
        ->assertSee('Relatórios Gerenciais de Tráfego')
        ->assertSee('Gerar Relatório em PDF')
        ->assertSee('Exportar Planilha (CSV)')
        ->assertSee('Filtros de Tráfego e Assiduidade Docente');
});

test('o componente livewire de relatorios renderiza registros e estatisticas', function () {
    Livewire::actingAs($this->admin)
        ->test(Relatorios::class)
        ->assertSee('REG-10492')
        ->assertSee('Prof. Dr. Marcos Souza')
        ->assertSee('BRA-2819')
        ->assertSet('dayOfWeekFilter', 'todos')
        ->assertSet('categoryFilter', 'todos');
});

test('o componente permite filtrar por dia da semana e assiduidade docente', function () {
    Livewire::actingAs($this->admin)
        ->test(Relatorios::class)
        ->set('dayOfWeekFilter', 'segunda')
        ->assertSee('Segunda-feira')
        ->set('driverSearch', 'Marcos')
        ->assertSee('Prof. Dr. Marcos Souza')
        ->call('clearAssiduidadeFilters')
        ->assertSet('dayOfWeekFilter', 'todos')
        ->assertSet('driverSearch', '');
});

test('o componente permite filtrar por tipo de evento e categoria', function () {
    Livewire::actingAs($this->admin)
        ->test(Relatorios::class)
        ->set('categoryFilter', 'professor')
        ->assertSee('Prof. Dr. Marcos Souza')
        ->set('eventType', 'entrada')
        ->assertSee('Entrada');
});

test('o componente exporta os registros filtrados em csv compativel com excel', function () {
    Livewire::actingAs($this->admin)
        ->test(Relatorios::class)
        ->call('exportCsv')
        ->assertFileDownloaded();
});
