<?php

use App\Livewire\Admin\Condutores;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Relatorios;
use App\Livewire\Auth\Login;
use App\Livewire\Portaria\Monitoramento;
use App\Livewire\Portaria\Visitantes;
use Livewire\Livewire;

test('a tela de login carrega e redireciona para a portaria por codigo', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('Sentinela');

    Livewire::test(Login::class)
        ->set('accessCode', 'GDA-104')
        ->call('login')
        ->assertRedirect(route('portaria.monitoramento'));
});

test('a tela inicial de monitoramento da portaria renderiza os registros e os 3 botoes operacionais', function () {
    $this->get('/portaria')
        ->assertOk()
        ->assertSee('Controle de Passagem de Veículos')
        ->assertSee('Corrigir Placa')
        ->assertSee('Marcar Saída')
        ->assertSee('Permitir Entrada (Prof)');

    Livewire::test(Monitoramento::class)
        ->assertSee('BRA-2819')
        ->call('openCorrectionModal', 1084)
        ->assertSet('showCorrectionModal', true)
        ->set('correctedPlate', 'BRA-2E19')
        ->call('confirmCorrection')
        ->assertSet('showCorrectionModal', false)
        ->assertSee('BRA-2E19');
});

test('o botao de permitir entrada de professor sem cadastro libera o acesso por excecao', function () {
    Livewire::test(Monitoramento::class)
        ->call('allowManualEntry', 1082)
        ->assertSee('Liberado Manualmente');
});

test('a tela de cadastro rapido de visitantes registra novo visitante e valida campos', function () {
    $this->get('/portaria/visitantes')
        ->assertOk()
        ->assertSee('Cadastro Rápido de Visitantes');

    Livewire::test(Visitantes::class)
        ->set('cpf', '123.456.789-00')
        ->set('plate', 'XYZ-9988')
        ->set('visitReason', 'Reunião de Coordenação')
        ->set('visitorName', 'Lucas Almeida')
        ->call('registerAndReleaseGate')
        ->assertHasNoErrors()
        ->assertSee('XYZ-9988')
        ->assertSee('Lucas Almeida');
});

test('o dashboard do administrador exibe os 4 indicadores requeridos', function () {
    $this->get('/admin/dashboard')
        ->assertOk()
        ->assertSee('Usuários Ativos no Sistema')
        ->assertSee('Quantidade de Veículos')
        ->assertSee('Entradas & Saídas (Hoje)', false)
        ->assertSee('Total de Registros');

    Livewire::test(Dashboard::class)
        ->assertSee('8')
        ->assertSee('1.248')
        ->assertSee('412')
        ->assertSee('385')
        ->assertSee('18.942');
});

test('a tela de condutores permite cadastrar N carros para um professor e buscar por codigo', function () {
    $this->get('/admin/condutores')
        ->assertOk()
        ->assertSee('Gestão de Condutores e Veículos')
        ->assertSee('Professores (Docentes)')
        ->assertSee('Funcionários Administrativos')
        ->assertSee('Prestadores de Serviço');

    Livewire::test(Condutores::class)
        ->set('activeTab', 'professores')
        ->call('openCreateModal')
        ->assertSet('showFormModal', true)
        ->set('name', 'Prof. Dr. Roberto Valente')
        ->set('code', 'DOC-99112')
        ->set('vehicles.0.plate', 'ROB-1111')
        ->set('vehicles.0.model', 'Cruze')
        ->call('addVehicle')
        ->set('vehicles.1.plate', 'ROB-2222')
        ->set('vehicles.1.model', 'Tracker')
        ->call('save')
        ->assertSet('showFormModal', false)
        ->assertSee('ROB-1111')
        ->assertSee('ROB-2222');
});

test('a tela de relatorios renderiza filtros e botao para geracao de pdf', function () {
    $this->get('/admin/relatorios')
        ->assertOk()
        ->assertSee('Relatórios Gerenciais de Tráfego')
        ->assertSee('Gerar Relatório em PDF');

    Livewire::test(Relatorios::class)
        ->assertSee('REG-10492')
        ->set('eventType', 'entrada')
        ->assertSee('Entrada');
});
