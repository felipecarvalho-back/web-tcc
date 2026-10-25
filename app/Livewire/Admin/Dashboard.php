<?php

namespace App\Livewire\Admin;

use App\Enums\StatusAcesso;
use App\Models\Condutor;
use App\Models\RegistroAcesso;
use App\Models\Usuario;
use App\Models\Veiculo;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * @property-read array{
 *     active_users: int,
 *     operators_count: int,
 *     admins_count: int,
 *     total_vehicles: int,
 *     active_drivers: int,
 *     entries_count: int,
 *     exits_count: int,
 *     in_campus_count: int,
 *     total_passage_records: int,
 *     manual_releases_count: int,
 *     identified_plates_count: int,
 *     peak_hour_label: string
 * } $dashboardMetrics
 * @property-read array<int, array{time: string, in: int, out: int, in_pct: int, out_pct: int}> $hourlyTraffic
 * @property-read array<int, array<string, mixed>> $recentAuditLogs
 */
#[Layout('layouts.app')]
#[Title('Admin - Dashboard de Acesso e Indicadores')]
class Dashboard extends Component
{
    public string $periodFilter = 'hoje';

    public function setPeriod(string $period): void
    {
        if (in_array($period, ['hoje', 'ontem', '7dias', 'mes', 'todos'], true)) {
            $this->periodFilter = $period;
        }
    }

    /**
     * Calcula os 4 indicadores executivos e métricas operacionais diretamente no banco de dados.
     *
     * @return array{
     *     active_users: int,
     *     operators_count: int,
     *     admins_count: int,
     *     total_vehicles: int,
     *     active_drivers: int,
     *     entries_count: int,
     *     exits_count: int,
     *     in_campus_count: int,
     *     total_passage_records: int,
     *     manual_releases_count: int,
     *     identified_plates_count: int,
     *     peak_hour_label: string
     * }
     */
    #[Computed]
    public function dashboardMetrics(): array
    {
        $usuariosStats = Usuario::query()
            ->where('ativo', true)
            ->selectRaw("
                COUNT(*) as total,
                COALESCE(SUM(CASE WHEN perfil = 'operador' THEN 1 ELSE 0 END), 0) as operadores,
                COALESCE(SUM(CASE WHEN perfil = 'admin' THEN 1 ELSE 0 END), 0) as admins
            ")
            ->first();

        $totalVehicles = Veiculo::query()->count();
        $activeDrivers = Condutor::query()->where('ativo', true)->count();

        $passagensPeriodo = RegistroAcesso::query()
            ->filtrarData($this->periodFilter)
            ->selectRaw('
                COUNT(*) as entradas,
                COALESCE(SUM(CASE WHEN data_hora_saida IS NOT NULL THEN 1 ELSE 0 END), 0) as saidas,
                COALESCE(SUM(CASE WHEN placa_corrigida IS NOT NULL OR status = ? THEN 1 ELSE 0 END), 0) as manuais,
                COALESCE(SUM(CASE WHEN placa_registro != ? THEN 1 ELSE 0 END), 0) as identificadas
            ', [StatusAcesso::LiberadoManual->value, RegistroAcesso::PLACA_NAO_IDENTIFICADA])
            ->first();

        $entriesCount = (int) ($passagensPeriodo->entradas ?? 0);
        $exitsCount = (int) ($passagensPeriodo->saidas ?? 0);
        $manualCount = (int) ($passagensPeriodo->manuais ?? 0);
        $identifiedPlatesCount = (int) ($passagensPeriodo->identificadas ?? 0);

        $totaisGerais = RegistroAcesso::query()
            ->selectRaw('
                COUNT(*) as total_historico,
                COALESCE(SUM(CASE WHEN data_hora_saida IS NULL THEN 1 ELSE 0 END), 0) as em_patio
            ')
            ->first();

        $inCampusCount = (int) ($totaisGerais->em_patio ?? 0);
        $totalPassageRecords = (int) ($totaisGerais->total_historico ?? 0);

        $peakHour = 'Sem movimentações no período';
        $maxMov = 0;
        foreach ($this->hourlyTraffic as $slot) {
            $sumSlot = $slot['in'] + $slot['out'];
            if ($sumSlot > $maxMov) {
                $maxMov = $sumSlot;
                $peakHour = "{$slot['time']} ({$sumSlot} mov.)";
            }
        }

        return [
            'active_users' => (int) ($usuariosStats->total ?? 0),
            'operators_count' => (int) ($usuariosStats->operadores ?? 0),
            'admins_count' => (int) ($usuariosStats->admins ?? 0),
            'total_vehicles' => $totalVehicles,
            'active_drivers' => $activeDrivers,
            'entries_count' => $entriesCount,
            'exits_count' => $exitsCount,
            'in_campus_count' => $inCampusCount,
            'total_passage_records' => $totalPassageRecords,
            'manual_releases_count' => $manualCount,
            'identified_plates_count' => $identifiedPlatesCount,
            'peak_hour_label' => $peakHour,
        ];
    }

    /**
     * Gera a curva horária de entradas e saídas (07h às 22h) com base nos registros do período.
     *
     * @return array<int, array{time: string, in: int, out: int, in_pct: int, out_pct: int}>
     */
    #[Computed]
    public function hourlyTraffic(): array
    {
        $slots = [];
        for ($h = 7; $h <= 22; $h++) {
            $key = str_pad((string) $h, 2, '0', STR_PAD_LEFT);
            $slots[$key] = [
                'time' => "{$key}h",
                'in' => 0,
                'out' => 0,
                'in_pct' => 4,
                'out_pct' => 4,
            ];
        }

        $registros = RegistroAcesso::query()
            ->filtrarData($this->periodFilter)
            ->get(['data_hora_entrada', 'data_hora_saida']);

        foreach ($registros as $reg) {
            /** @var RegistroAcesso $reg */
            if ($reg->data_hora_entrada !== null) {
                $hIn = $reg->data_hora_entrada->format('H');
                if (isset($slots[$hIn])) {
                    $slots[$hIn]['in']++;
                }
            }

            if ($reg->data_hora_saida !== null) {
                $hOut = $reg->data_hora_saida->format('H');
                if (isset($slots[$hOut])) {
                    $slots[$hOut]['out']++;
                }
            }
        }

        $maxVal = 1;
        foreach ($slots as $slot) {
            $maxVal = max($maxVal, $slot['in'], $slot['out']);
        }

        foreach ($slots as $key => $slot) {
            $slots[$key]['in_pct'] = $slot['in'] > 0
                ? max(10, (int) round(($slot['in'] / $maxVal) * 88))
                : 3;
            $slots[$key]['out_pct'] = $slot['out'] > 0
                ? max(10, (int) round(($slot['out'] / $maxVal) * 88))
                : 3;
        }

        return array_values($slots);
    }

    /**
     * Retorna os últimos 8 registros de passagem para a tabela de auditoria recente.
     *
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function recentAuditLogs(): array
    {
        $query = RegistroAcesso::query()
            ->select([
                'id',
                'condutor_id',
                'operador_id',
                'placa_registro',
                'placa_corrigida',
                'cancela',
                'data_hora_entrada',
                'data_hora_saida',
                'status',
                'justificativa',
            ])
            ->filtrarData($this->periodFilter)
            ->with([
                'condutor:id,nome,tipo',
                'condutor.professor:condutor_id,departamento',
                'condutor.funcionario:condutor_id,setor',
                'condutor.prestador:condutor_id,empresa',
                'condutor.visitante:condutor_id,motivo_visita',
                'operador:id,nome,codigo_operador',
            ]);

        $query->orderByDesc('data_hora_entrada');
        $query->limit(8);

        return $query
            ->get()
            ->map(function ($reg): array {
                /** @var RegistroAcesso $reg */
                $condutor = $reg->condutor;
                $tipo = $condutor?->tipo ?? 'visitante';

                $temSaida = $reg->data_hora_saida !== null || $reg->status === StatusAcesso::Finalizado;
                $temPlacaCorrigida = ! empty($reg->placa_corrigida);
                $semPlaca = ! $reg->temPlacaIdentificada();

                $statusEnum = $reg->status instanceof StatusAcesso
                    ? $reg->status
                    : (StatusAcesso::tryFrom((string) $reg->status) ?? StatusAcesso::Autorizado);

                $acao = match (true) {
                    $temPlacaCorrigida => 'Dupla Verificação (Placa Corrigida)',
                    $tipo === 'visitante' && $reg->operador_id !== null => 'Credenciamento e Liberação de Visitante',
                    $statusEnum === StatusAcesso::LiberadoManual => 'Liberação Manual / Código Funcional',
                    $statusEnum === StatusAcesso::Pendente => 'Leitura OCR Aguardando Guarita',
                    default => 'Passagem Automática Liberada via OCR',
                };

                return [
                    'id' => 'REG-'.str_pad((string) $reg->id, 5, '0', STR_PAD_LEFT),
                    'raw_id' => $reg->id,
                    'operator' => $reg->operador?->nome ?? 'Sistema OCR Auto',
                    'operator_code' => $reg->operador?->codigo_operador,
                    'action' => $acao,
                    'plate' => $reg->placaExibida(),
                    'driver' => $reg->nomeCondutorExibicao(),
                    'role' => $reg->descricaoVinculo(),
                    'gate' => $reg->cancela ?: 'Cancela 01 (Principal)',
                    'time' => $reg->data_hora_entrada?->format('d/m H:i') ?? '--:--',
                    'exit_time' => $reg->data_hora_saida?->format('H:i'),
                    'status' => $statusEnum->label($temSaida, $temPlacaCorrigida, $semPlaca),
                    'status_state' => $statusEnum->viewState($temSaida),
                ];
            })
            ->toArray();
    }

    public function render(): View
    {
        return view('livewire.admin.dashboard', [
            'metrics' => $this->dashboardMetrics,
            'hourlyTraffic' => $this->hourlyTraffic,
            'recentAuditLogs' => $this->recentAuditLogs,
        ]);
    }
}
