<?php

namespace App\Livewire\Portaria;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Cadastro Rápido de Visitantes - Sentinela FATEC')]
class Visitantes extends Component
{
    public string $cpf = '';

    public string $plate = '';

    public string $visitReason = '';

    public string $visitorName = '';

    public string $vehicleModel = '';

    public string $department = 'DSM - Coordenação DSM';

    public string $estimatedStay = '2 horas';

    public string $toastMessage = '';

    public string $toastType = 'success';

    public array $recentVisitors = [];

    public function mount(): void
    {
        $this->recentVisitors = [
            [
                'id' => 101,
                'cpf' => '443.829.102-14',
                'name' => 'Mariana Silveira',
                'plate' => 'FTC-8821',
                'model' => 'Honda Civic Prata',
                'reason' => 'Entrevista Acadêmica • RH FATEC',
                'department' => 'Direção / Recursos Humanos',
                'entry_time' => '14:15:20',
                'stay' => '1 hora',
                'status' => 'ativo',
            ],
            [
                'id' => 102,
                'cpf' => '221.734.901-88',
                'name' => 'Roberto Mendes',
                'plate' => 'RBM-4G19',
                'model' => 'Fiat Strada Branca',
                'reason' => 'Entrega de Insumos Laboratoriais',
                'department' => 'Laboratório de Redes',
                'entry_time' => '13:50:12',
                'stay' => '30 minutos',
                'status' => 'ativo',
            ],
            [
                'id' => 103,
                'cpf' => '332.615.820-99',
                'name' => 'Dra. Camila Duarte',
                'plate' => 'CAM-1192',
                'model' => 'Jeep Renegade Preto',
                'reason' => 'Banca Examinadora de TCC',
                'department' => 'Auditório Principal',
                'entry_time' => '12:30:00',
                'stay' => '3 horas',
                'status' => 'finalizado',
            ],
        ];
    }

    public function registerAndReleaseGate(): void
    {
        $this->validate([
            'cpf' => 'required|min:11',
            'plate' => 'required|min:7',
            'visitReason' => 'required|min:3',
        ], [
            'cpf.required' => 'O CPF do visitante é obrigatório.',
            'plate.required' => 'A placa do carro é obrigatória.',
            'visitReason.required' => 'O motivo da visita é obrigatório.',
        ]);

        $newVisitor = [
            'id' => count($this->recentVisitors) + 104,
            'cpf' => $this->cpf,
            'name' => $this->visitorName ?: 'Visitante Identificado',
            'plate' => strtoupper(trim($this->plate)),
            'model' => $this->vehicleModel ?: 'Veículo de Passeio',
            'reason' => $this->visitReason,
            'department' => $this->department,
            'entry_time' => date('H:i:s'),
            'stay' => $this->estimatedStay,
            'status' => 'ativo',
        ];

        array_unshift($this->recentVisitors, $newVisitor);

        $this->reset(['cpf', 'plate', 'visitReason', 'visitorName', 'vehicleModel']);
        $this->triggerToast('Visitante cadastrado com sucesso! Cancela 01 liberada para entrada.', 'success');
    }

    public function markVisitorExit(int $id): void
    {
        foreach ($this->recentVisitors as &$visitor) {
            if ($visitor['id'] === $id) {
                $visitor['status'] = 'finalizado';
                $this->triggerToast("Saída registrada para o visitante {$visitor['name']} ({$visitor['plate']})!", 'info');
                break;
            }
        }
    }

    public function triggerToast(string $message, string $type = 'success'): void
    {
        $this->toastMessage = $message;
        $this->toastType = $type;
    }

    public function clearToast(): void
    {
        $this->toastMessage = '';
    }

    public function render()
    {
        return view('livewire.portaria.visitantes');
    }
}
