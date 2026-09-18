<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Admin - Dashboard de Acesso e Indicadores')]
class Dashboard extends Component
{
    public int $activeUsers = 8;

    public int $totalVehicles = 1248;

    public int $entriesCount = 412;

    public int $exitsCount = 385;

    public int $totalPassageRecords = 18942;

    public int $parkingOccupancy = 162;

    public int $parkingCapacity = 200;

    public string $periodFilter = 'hoje';

    public array $recentAuditLogs = [
        [
            'id' => 8421,
            'operator' => 'Guarda Silva',
            'action' => 'Liberação Manual por Exceção',
            'plate' => 'BRA-2819',
            'role' => 'Prof. Dr. Marcos Souza',
            'time' => '14:32',
            'gate' => 'Cancela 01',
            'status' => 'Concedido',
        ],
        [
            'id' => 8420,
            'operator' => 'Sistema OCR Auto',
            'action' => 'Passagem Automática Liberada',
            'plate' => 'ABC-1234',
            'role' => 'Carlos Silva (Manutenção)',
            'time' => '14:28',
            'gate' => 'Cancela 01',
            'status' => 'Autorizado',
        ],
        [
            'id' => 8419,
            'operator' => 'Guarda Ribeiro',
            'action' => 'Cadastro Rápido de Visitante',
            'plate' => 'FTC-8821',
            'role' => 'Mariana Silveira (Visita)',
            'time' => '14:15',
            'gate' => 'Cancela 02',
            'status' => 'Registrado',
        ],
        [
            'id' => 8418,
            'operator' => 'Sistema OCR Auto',
            'action' => 'Passagem Automática Liberada',
            'plate' => 'GTR-4C88',
            'role' => 'Profa. Dra. Juliana Rezende',
            'time' => '14:14',
            'gate' => 'Cancela 01',
            'status' => 'Autorizado',
        ],
    ];

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
