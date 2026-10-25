<?php

use App\Enums\StatusAcesso;
use App\Enums\TipoOcupante;
use App\Livewire\Admin\Relatorios;
use App\Models\Condutor;
use App\Models\CondutorProfessor;
use App\Models\RegistroAcesso;
use App\Models\Usuario;
use App\Models\Veiculo;
use Illuminate\Support\Carbon;
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

    $condutor = Condutor::query()->create([
        'nome' => 'Prof. Dr. Marcos Souza',
        'cpf' => '529.982.247-25',
        'tipo' => 'professor',
        'ativo' => true,
    ]);

    CondutorProfessor::query()->create([
        'condutor_id' => $condutor->id,
        'codigo_acesso' => 'DOC-202601',
        'departamento' => 'DSM',
    ]);

    $veiculo = Veiculo::query()->create([
        'condutor_id' => $condutor->id,
        'placa' => 'BRA-2819',
        'modelo' => 'Honda Civic',
        'cor' => 'Prata',
    ]);

    $segundaFeira = Carbon::now()->startOfWeek(Carbon::MONDAY)->setTime(18, 45, 0);
    if ($segundaFeira->greaterThan(Carbon::now())) {
        $segundaFeira = $segundaFeira->subWeek();
    }

    $this->registro = RegistroAcesso::query()->create([
        'veiculo_id' => $veiculo->id,
        'condutor_id' => $condutor->id,
        'placa_registro' => 'BRA-2819',
        'cancela' => 'Cancela 01 (Principal)',
        'data_hora_entrada' => $segundaFeira,
        'data_hora_saida' => null,
        'operador_id' => $this->admin->id,
        'status' => StatusAcesso::Autorizado,
        'tipo_ocupante' => TipoOcupante::Condutor,
    ]);
});

test('administrador autenticado consegue acessar a tela de relatorios', function () {
    $this->actingAs($this->admin)
        ->get('/admin/relatorios')
        ->assertOk()
        ->assertSee('Relatórios Gerenciais de Tráfego')
        ->assertSee('Visualizar Documento')
        ->assertSee('Gerar Relatório em PDF')
        ->assertSee('Exportar Planilha (CSV)')
        ->assertSee('Filtros de Tráfego e Assiduidade Docente');
});

test('o componente livewire de relatorios renderiza registros reais do banco e estatisticas', function () {
    $protocoloEsperado = 'REG-'.str_pad((string) $this->registro->id, 5, '0', STR_PAD_LEFT);

    Livewire::actingAs($this->admin)
        ->test(Relatorios::class)
        ->assertSee($protocoloEsperado)
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

test('o componente permite filtrar por tipo de evento, categoria e abrir previa do relatorio base', function () {
    Livewire::actingAs($this->admin)
        ->test(Relatorios::class)
        ->set('categoryFilter', 'professor')
        ->assertSee('Prof. Dr. Marcos Souza')
        ->set('eventType', 'entrada')
        ->assertSee('Entrada')
        ->call('openPreview')
        ->assertSet('showPreviewModal', true)
        ->assertSee('Relatório Oficial de Tráfego e Assiduidade')
        ->call('closePreview')
        ->assertSet('showPreviewModal', false);
});

test('o componente exporta os registros filtrados em csv compativel com excel', function () {
    Livewire::actingAs($this->admin)
        ->test(Relatorios::class)
        ->call('exportCsv')
        ->assertFileDownloaded();
});
