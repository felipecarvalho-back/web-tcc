<?php

namespace App\Livewire\Portaria;

use App\Models\RegistroAcesso;
use Illuminate\Database\Eloquent\Builder;
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
        $inicioHoje = Carbon::today()->startOfDay();
        $fimHoje = Carbon::today()->endOfDay();

        $metricas = RegistroAcesso::query()
            ->whereBetween('data_hora_entrada', [$inicioHoje, $fimHoje])
            ->selectRaw('
                COUNT(*) as total,
                COALESCE(SUM(CASE WHEN placa_registro != ? AND placa_corrigida IS NULL THEN 1 ELSE 0 END), 0) as automaticas,
                COALESCE(SUM(CASE WHEN placa_corrigida IS NOT NULL THEN 1 ELSE 0 END), 0) as manuais
            ', ['SEM-PLACA'])
            ->first();

        $this->totalPassages = (int) ($metricas->total ?? 0);
        $this->automaticPassages = (int) ($metricas->automaticas ?? 0);
        $this->manualCorrections = (int) ($metricas->manuais ?? 0);
    }

    /**
     * Consulta e formata os registros filtrados diretamente no banco de dados.
     *
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function filteredRecords(): array
    {
        if ($this->filterCategory !== 'todos' && $this->filterCategory !== 'visitante') {
            return [];
        }

        $query = RegistroAcesso::query()
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
            ->latest('data_hora_entrada')
            ->latest('id');

        // 1. Filtro de Data usando intervalos compatíveis com índices B-Tree (PostgreSQL / MySQL)
        match ($this->filterDate) {
            'hoje' => $query->whereBetween('data_hora_entrada', [
                Carbon::today()->startOfDay(),
                Carbon::today()->endOfDay(),
            ]),
            'ontem' => $query->whereBetween('data_hora_entrada', [
                Carbon::yesterday()->startOfDay(),
                Carbon::yesterday()->endOfDay(),
            ]),
            '7dias' => $query->where('data_hora_entrada', '>=', Carbon::today()->subDays(7)->startOfDay()),
            'mes' => $query->whereBetween('data_hora_entrada', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ]),
            default => null,
        };

        // 2. Filtro por Placa direto no SQL (whereLike usa ILIKE no PostgreSQL e LIKE no MySQL)
        $buscaPlaca = strtoupper(trim($this->filterPlate));
        if ($buscaPlaca !== '') {
            $buscaSemHifen = str_replace('-', '', $buscaPlaca);
            $query->where(function (Builder $q) use ($buscaPlaca, $buscaSemHifen) {
                $q->whereLike('placa_registro', "%{$buscaPlaca}%")
                    ->orWhereLike('placa_corrigida', "%{$buscaPlaca}%")
                    ->orWhereLike('placa_registro', "%{$buscaSemHifen}%");
            });
        }

        // 3. Filtro por Status direto no SQL
        match ($this->filterStatus) {
            'pendente' => $query->where('status', 'pendente')->whereNull('data_hora_saida'),
            'autorizado' => $query->whereIn('status', ['autorizado', 'em_patio', 'liberado_manual'])->whereNull('data_hora_saida'),
            'nao_cadastrado' => $query->whereIn('status', ['nao_cadastrado', 'bloqueado'])->whereNull('data_hora_saida'),
            'saida' => $query->where(function (Builder $q) {
                $q->where('status', 'finalizado')->orWhereNotNull('data_hora_saida');
            }),
            default => null,
        };

        return $query
            ->limit(50)
            ->get()
            ->map(fn (RegistroAcesso $registro): array => $this->formatRecord($registro))
            ->all();
    }

    /**
     * Formata uma instância de RegistroAcesso para a estrutura esperada pela view.
     *
     * @return array<string, mixed>
     */
    private function formatRecord(RegistroAcesso $registro): array
    {
        $placaExibida = $registro->placa_corrigida ?: $registro->placa_registro;

        $statusView = match (true) {
            $registro->data_hora_saida !== null || $registro->status === 'finalizado' => 'saida',
            in_array($registro->status, ['autorizado', 'em_patio', 'liberado_manual'], true) => 'autorizado',
            in_array($registro->status, ['nao_cadastrado', 'bloqueado'], true) => 'nao_cadastrado',
            default => 'pendente',
        };

        $statusLabel = match (true) {
            $registro->data_hora_saida !== null || $registro->status === 'finalizado' => 'Saída Registrada',
            $registro->status === 'liberado_manual' && ! empty($registro->placa_corrigida) => 'Autorizado (Placa Corrigida)',
            $registro->status === 'liberado_manual' => 'Liberado Manualmente',
            in_array($registro->status, ['autorizado', 'em_patio'], true) => 'Autorizado',
            $registro->status === 'bloqueado' => 'Bloqueado',
            $registro->status === 'nao_cadastrado' && $registro->placa_registro === 'SEM-PLACA' => 'Placa Não Identificada',
            $registro->status === 'nao_cadastrado' => 'Não Cadastrado',
            default => 'Aguardando Liberação',
        };

        $driverName = ($registro->placa_registro === 'SEM-PLACA' && empty($registro->placa_corrigida))
            ? 'Placa Não Identificada'
            : 'Condutor Não Vinculado';

        $detalhes = [$registro->cancela];
        if ($registro->operador) {
            $detalhes[] = 'Operador: '.$registro->operador->nome;
        }
        if ($registro->justificativa) {
            $detalhes[] = $registro->justificativa;
        }

        return [
            'id' => $registro->id,
            'plate' => $placaExibida,
            'raw_ocr' => $registro->placa_registro,
            'driver_name' => $driverName,
            'category' => 'visitante',
            'category_label' => implode(' • ', $detalhes),
            'registered_at' => $registro->data_hora_entrada?->format('H:i:s') ?? '--:--:--',
            'time_ago' => $registro->data_hora_entrada?->locale('pt_BR')->diffForHumans() ?? 'Agora mesmo',
            'status' => $statusView,
            'status_label' => $statusLabel,
            'image_url' => $registro->foto_entrada_path ?: 'https://placehold.co/300x200?text=Sem+Imagem',
            'is_registered' => (bool) $registro->veiculo_id,
        ];
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

        $record = $this->formatRecord($registro);
        $this->selectedRecord = $record;
        $this->correctedPlate = $record['plate'] === 'SEM-PLACA' ? '' : $record['plate'];
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

            $registro->status = 'liberado_manual';
            $registro->justificativa = $this->justification;
            $registro->save();
        }

        $passengerCount = count($this->passengers);
        $passengerNote = $passengerCount > 0 ? " (+{$passengerCount} carona(s) registrada(s))" : '';

        $this->showVerificationModal = false;
        $this->selectedRecord = null;
        $this->passengers = [];
        $this->newPassengerCode = '';
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

        $this->selectedRecord = $this->formatRecord($registro);
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
        RegistroAcesso::whereKey($targetId)->update(['status' => 'autorizado']);

        $passengerCount = count($this->passengers);
        $passengerNote = $passengerCount > 0 ? " (+{$passengerCount} carona(s) registrada(s))" : '';

        $this->showDriverConfirmationModal = false;
        $this->selectedRecord = null;
        $this->passengers = [];
        $this->newPassengerCode = '';
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
            $registro->status = 'finalizado';
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
