<?php

use App\Livewire\Admin\Condutores;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Relatorios;
use App\Livewire\Admin\Usuarios;
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

test('a tela inicial de monitoramento da portaria renderiza os registros e os botoes operacionais da guarita', function () {
    $this->get('/portaria')
        ->assertOk()
        ->assertSee('Controle de Passagem de Veículos')
        ->assertSee('Verificar e Liberar')
        ->assertSee('Confirmar Condutor')
        ->assertSee('Marcar Saída');

    Livewire::test(Monitoramento::class)
        ->assertSee('BRA-2819')
        ->call('openVerificationModal', 1084)
        ->assertSet('showVerificationModal', true)
        ->set('correctedPlate', 'BRA-2E19')
        ->call('confirmVerification')
        ->assertSet('showVerificationModal', false)
        ->assertSee('BRA-2E19')
        ->assertSee('Autorizado (Placa Corrigida)');
});

test('o modal de dupla verificacao permite autorizar acesso por codigo de acesso do condutor', function () {
    Livewire::test(Monitoramento::class)
        ->call('openVerificationModal', 1082)
        ->assertSet('showVerificationModal', true)
        ->set('driverAccessCode', 'DOC-94281')
        ->call('confirmVerification')
        ->assertSet('showVerificationModal', false)
        ->assertSee('Liberado por Código')
        ->assertSee('DOC-94281');
});

test('o modal de confirmacao de condutor valida o motorista no veiculo identificado', function () {
    Livewire::test(Monitoramento::class)
        ->call('openDriverConfirmationModal', 1083)
        ->assertSet('showDriverConfirmationModal', true)
        ->set('confirmDriverCode', 'DOC-88312')
        ->call('confirmDriver')
        ->assertSet('showDriverConfirmationModal', false)
        ->assertSee('Condutor Confirmado');
});

test('o operador pode marcar a saida de um veiculo', function () {
    Livewire::test(Monitoramento::class)
        ->call('markExit', 1083)
        ->assertSee('Saída Registrada');
});

test('o porteiro pode registrar caronas e multiplos docentes no mesmo veiculo', function () {
    Livewire::test(Monitoramento::class)
        ->call('openDriverConfirmationModal', 1083)
        ->assertSet('showDriverConfirmationModal', true)
        ->set('confirmDriverCode', 'DOC-88312')
        ->set('newPassengerCode', 'DOC-94281')
        ->call('addPassenger')
        ->assertCount('passengers', 1)
        ->assertSee('DOC-94281')
        ->set('newPassengerCode', 'DOC-74192')
        ->call('addPassenger')
        ->assertCount('passengers', 2)
        ->assertSee('DOC-74192')
        ->call('removePassenger', 0)
        ->assertCount('passengers', 1)
        ->call('confirmDriver')
        ->assertSet('showDriverConfirmationModal', false)
        ->assertSee('carona(s) registrada(s)');
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
        ->assertSee('Gerar Relatório em PDF')
        ->assertSee('Dia da Semana');

    Livewire::test(Relatorios::class)
        ->assertSee('REG-10492')
        ->set('eventType', 'entrada')
        ->assertSee('Entrada');
});

test('a tela de relatorios permite filtrar por dia da semana e assiduidade docente', function () {
    Livewire::test(Relatorios::class)
        ->set('dayOfWeekFilter', 'segunda')
        ->assertSee('Segunda-feira')
        ->set('driverSearch', 'Marcos')
        ->assertSee('Prof. Dr. Marcos Souza')
        ->call('clearAssiduidadeFilters')
        ->assertSet('dayOfWeekFilter', 'todos')
        ->assertSet('driverSearch', '');
});

test('a tela de usuarios do sistema permite listar, cadastrar e ativar/desativar usuarios', function () {
    $this->get('/admin/usuarios')
        ->assertOk()
        ->assertSee('Gestão de Usuários do Sistema')
        ->assertSee('Novo Usuário');

    Livewire::test(Usuarios::class)
        ->assertSee('Carlos Eduardo Silva')
        ->assertSee('ADM-001')
        ->call('openCreateModal')
        ->assertSet('showFormModal', true)
        ->set('nome', 'Renato Augusto Guimarães')
        ->set('cpf', '111.222.333-44')
        ->set('email', 'renato.guimaraes@fatec.sp.gov.br')
        ->set('perfil', 'operador')
        ->set('codigo_operador', 'GDA-109')
        ->set('senha', 'senha123')
        ->set('senha_confirmation', 'senha123')
        ->call('save')
        ->assertSet('showFormModal', false)
        ->assertSee('Renato Augusto Guimarães')
        ->assertSee('GDA-109')
        ->call('toggleStatus', 1)
        ->assertSee('desativado');
});
