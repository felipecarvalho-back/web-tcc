<?php

namespace App\Livewire\Portaria;

use App\Enums\StatusAcesso;
use App\Models\RegistroAcesso;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Monitoramento de Portaria - Sentinela FATEC')]
class Monitoramento extends Component
{
    public string $filterDate = 'hoje';

    public string $filterPlate = '';

    public string $filterCategory = 'todos';

    public string $filterStatus = 'todos';

    // Estados dos Modais Operacionais
    public bool $showVerificationModal = false;

    public bool $showDriverConfirmationModal = false;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $selectedRecord = null;

    // Campos da Dupla Verificação (Placa não identificada / Não cadastrada)
    public string $correctedPlate = '';

    public string $driverAccessCode = '';

    public string $justification = 'Correção manual de leitura OCR';

    // Campos da Confirmação de Condutor (Veículo identificado)
    public string $confirmDriverCode = '';

    /**
     * Lista de códigos funcionais de caronas/passageiros (máx. 4 passageiros adicionais).
     *
     * @var array<int, string>
     */
    public array $passengers = [];

    public string $newPassengerCode = '';

    // Notificações Toast
    public string $toastMessage = '';

    public string $toastType = 'success';

    // Indicadores do topo da tela
    public int $totalPassages = 0;

    public int $automaticPassages = 0;

    public int $manualCorrections = 0;

    /**
     * Atualiza os 3 indicadores do topo em uma única query SQL otimizada.
     */
    private function loadMetrics(): void
    {
        $metricas = RegistroAcesso::query()
            ->filtrarData('hoje')
            ->selectRaw('
                COUNT(*) as total,
                COALESCE(SUM(CASE WHEN placa_registro != ? AND placa_corrigida IS NULL THEN 1 ELSE 0 END), 0) as automaticas,
                COALESCE(SUM(CASE WHEN placa_corrigida IS NOT NULL THEN 1 ELSE 0 END), 0) as manuais
            ', [RegistroAcesso::PLACA_NAO_IDENTIFICADA])
            ->first();

        $this->totalPassages = (int) ($metricas->total ?? 0);
        $this->automaticPassages = (int) ($metricas->automaticas ?? 0);
        $this->manualCorrections = (int) ($metricas->manuais ?? 0);
    }

    /**
     * Consulta e formata os registros filtrados diretamente no banco de dados usando Query Scopes.
     *
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function filteredRecords(): array
    {
        if ($this->filterCategory !== 'todos' && $this->filterCategory !== 'visitante') {
            return [];
        }

        return RegistroAcesso::query()
            ->select([
                'id',
                'veiculo_id',
                'condutor_id',
                'placa_registro',
                'placa_corrigida',
                'cancela',
                'data_hora_entrada',
                'data_hora_saida',
                'operador_id',
                'foto_entrada_path',
                'status',
                'justificativa',
            ])
            ->with(['operador:id,nome'])
            ->filtrarData($this->filterDate)
            ->filtrarPlaca($this->filterPlate)
            ->filtrarStatus($this->filterStatus)
            ->latest('data_hora_entrada')
            ->latest('id')
            ->limit(50)
            ->get()
            ->map(fn (RegistroAcesso $registro): array => $registro->toMonitoringArray())
            ->all();
    }

    /**
     * Adiciona um código de carona/passageiro à lista de ocupantes (limite de até 4 caronas).
     */
    public function addPassenger(): void
    {
        $code = strtoupper(trim($this->newPassengerCode));
        if ($code === '') {
            return;
        }

        if (count($this->passengers) >= 4) {
            $this->triggerToast('Capacidade máxima de 4 caronas atingida para este veículo.', 'warning');

            return;
        }

        if (in_array($code, $this->passengers, true)) {
            $this->triggerToast('Este código de carona já foi adicionado.', 'warning');

            return;
        }

        $this->passengers[] = $code;
        $this->newPassengerCode = '';
    }

    /**
     * Remove um carona/passageiro da lista de ocupantes.
     */
    public function removePassenger(int $index): void
    {
        if (isset($this->passengers[$index])) {
            unset($this->passengers[$index]);
            $this->passengers = array_values($this->passengers);
        }
    }

    /**
     * Abre o modal de dupla verificação para veículos com placa não identificada ou não cadastrada.
     */
    public function openVerificationModal(int $id): void
    {
        $registro = RegistroAcesso::with(['operador:id,nome'])->find($id);
        if (! $registro) {
            return;
        }

        $record = $registro->toMonitoringArray();
        $this->selectedRecord = $record;
        $this->correctedPlate = $record['plate'] === RegistroAcesso::PLACA_NAO_IDENTIFICADA ? '' : $record['plate'];
        $this->driverAccessCode = '';
        $this->justification = 'Correção manual de leitura OCR';
        $this->passengers = [];
        $this->newPassengerCode = '';
        $this->showVerificationModal = true;
    }

    /**
     * Fecha o modal de dupla verificação.
     */
    public function closeVerificationModal(): void
    {
        $this->showVerificationModal = false;
        $this->selectedRecord = null;
        $this->correctedPlate = '';
        $this->driverAccessCode = '';
        $this->passengers = [];
        $this->newPassengerCode = '';
    }

    /**
     * Processa a validação da dupla verificação e libera a cancela.
     */
    public function confirmVerification(): void
    {
        if (! $this->selectedRecord) {
            return;
        }

        $targetId = $this->selectedRecord['id'];
        $registro = RegistroAcesso::find($targetId);

        if ($registro) {
            $novaPlaca = strtoupper(trim($this->correctedPlate));
            $placaOriginal = $registro->placa_registro;

            if ($novaPlaca !== '' && $novaPlaca !== $placaOriginal) {
                $registro->placa_corrigida = substr($novaPlaca, 0, 10);
            }

            $registro->status = StatusAcesso::LiberadoManual;
            $registro->justificativa = $this->justification;
            $registro->save();
        }

        $passengerCount = count($this->passengers);
        $passengerNote = $passengerCount > 0 ? " (+{$passengerCount} carona(s) registrada(s))" : '';

        $this->closeVerificationModal();
        unset($this->filteredRecords);
        $this->triggerToast("Acesso validado e cancela liberada{$passengerNote}!", 'success');
    }

    /**
     * Abre o modal de confirmação de condutor para veículos que já foram identificados pelo sistema.
     */
    public function openDriverConfirmationModal(int $id): void
    {
        $registro = RegistroAcesso::with(['operador:id,nome'])->find($id);
        if (! $registro) {
            return;
        }

        $this->selectedRecord = $registro->toMonitoringArray();
        $this->confirmDriverCode = '';
        $this->passengers = [];
        $this->newPassengerCode = '';
        $this->showDriverConfirmationModal = true;
    }

    /**
     * Fecha o modal de confirmação de condutor.
     */
    public function closeDriverConfirmationModal(): void
    {
        $this->showDriverConfirmationModal = false;
        $this->selectedRecord = null;
        $this->confirmDriverCode = '';
        $this->passengers = [];
        $this->newPassengerCode = '';
    }

    /**
     * Confirma se o motorista na guarita é de fato o condutor registrado no veículo.
     */
    public function confirmDriver(): void
    {
        if (! $this->selectedRecord) {
            return;
        }

        $targetId = $this->selectedRecord['id'];
        RegistroAcesso::whereKey($targetId)->update(['status' => StatusAcesso::Autorizado]);

        $passengerCount = count($this->passengers);
        $passengerNote = $passengerCount > 0 ? " (+{$passengerCount} carona(s) registrada(s))" : '';

        $this->closeDriverConfirmationModal();
        unset($this->filteredRecords);
        $this->triggerToast("Identidade do condutor confirmada{$passengerNote}! Cancela liberada.", 'success');
    }

    /**
     * Registra a saída de um veículo no sistema.
     */
    public function markExit(int $id): void
    {
        $registro = RegistroAcesso::find($id);

        if ($registro) {
            $registro->data_hora_saida = Carbon::now();
            $registro->status = StatusAcesso::Finalizado;
            $registro->save();

            $placa = $registro->placa_corrigida ?: $registro->placa_registro;
            unset($this->filteredRecords);
            $this->triggerToast("Saída registrada para o veículo {$placa}!", 'info');
        }
    }

    private function triggerToast(string $message, string $type = 'success'): void
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
        $this->loadMetrics();

        return view('livewire.portaria.monitoramento', [
            'filteredRecords' => $this->filteredRecords,
        ]);
    }
}
