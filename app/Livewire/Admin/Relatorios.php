<?php

namespace App\Livewire\Admin;

use App\Enums\StatusAcesso;
use App\Enums\TipoOcupante;
use App\Models\RegistroAcesso;
use App\Models\Usuario;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @property-read LengthAwarePaginator $paginatedRecords
 * @property-read array<int, array<string, mixed>> $filteredData
 * @property-read array{total: int, entradas: int, saidas: int, docentes: int} $reportStats
 * @property-read array<int, array{id: string, nome: string}> $operators
 */
#[Layout('layouts.app')]
#[Title('Admin - Relatórios e Exportação PDF - Sentinela FATEC')]
class Relatorios extends Component
{
    use WithPagination;

    public string $startDate = '';

    public string $endDate = '';

    public string $quickPeriod = 'ano';

    public string $categoryFilter = 'todos';

    public string $eventType = 'todos';

    public string $operatorFilter = 'todos';

    public string $dayOfWeekFilter = 'todos';

    public string $driverSearch = '';

    public bool $showPreviewModal = false;

    public function mount(): void
    {
        $this->startDate = Carbon::now()->startOfYear()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
    }

    public function updatedStartDate(): void
    {
        $this->quickPeriod = 'personalizado';
        $this->resetPage();
    }

    public function updatedEndDate(): void
    {
        $this->quickPeriod = 'personalizado';
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedEventType(): void
    {
        $this->resetPage();
    }

    public function updatedOperatorFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDayOfWeekFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDriverSearch(): void
    {
        $this->resetPage();
    }

    public function setQuickPeriod(string $period): void
    {
        $this->quickPeriod = $period;
        $hoje = Carbon::now();

        match ($period) {
            'hoje' => [
                $this->startDate = $hoje->format('Y-m-d'),
                $this->endDate = $hoje->format('Y-m-d'),
            ],
            '7dias' => [
                $this->startDate = $hoje->copy()->subDays(7)->format('Y-m-d'),
                $this->endDate = $hoje->format('Y-m-d'),
            ],
            'mes' => [
                $this->startDate = $hoje->copy()->startOfMonth()->format('Y-m-d'),
                $this->endDate = $hoje->format('Y-m-d'),
            ],
            default => [
                $this->startDate = $hoje->copy()->startOfYear()->format('Y-m-d'),
                $this->endDate = $hoje->format('Y-m-d'),
            ],
        };

        $this->resetPage();
    }

    public function clearAssiduidadeFilters(): void
    {
        $this->dayOfWeekFilter = 'todos';
        $this->driverSearch = '';
        $this->categoryFilter = 'todos';
        $this->eventType = 'todos';
        $this->operatorFilter = 'todos';
        $this->quickPeriod = 'ano';
        $this->startDate = Carbon::now()->startOfYear()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
        $this->resetPage();
    }

    /**
     * Retorna a lista de operadores para o filtro de usuários.
     *
     * @return array<int, array{id: string, nome: string}>
     */
    #[Computed]
    public function operators(): array
    {
        return Usuario::query()
            ->orderBy('nome')
            ->get(['id', 'nome'])
            ->map(function ($u): array {
                /** @var Usuario $u */
                return ['id' => (string) $u->id, 'nome' => $u->nome];
            })
            ->toArray();
    }

    /**
     * Constrói a query base com todos os filtros aplicados (sem ordenação nem eager loading).
     *
     * @return Builder<RegistroAcesso>
     */
    protected function buildBaseQuery(): Builder
    {
        $query = RegistroAcesso::query();

        // 1. Filtro por Período
        if (! empty($this->startDate)) {
            $query->where('data_hora_entrada', '>=', Carbon::parse($this->startDate)->startOfDay());
        }

        if (! empty($this->endDate)) {
            $query->where('data_hora_entrada', '<=', Carbon::parse($this->endDate)->endOfDay());
        }

        // 2. Filtro por Dia da Semana (PostgreSQL, MySQL, SQLite)
        if ($this->dayOfWeekFilter !== 'todos') {
            $driver = DB::connection()->getDriverName();

            if ($driver === 'pgsql') {
                $mapPg = [
                    'domingo' => 0, 'segunda' => 1, 'terca' => 2,
                    'quarta' => 3, 'quinta' => 4, 'sexta' => 5, 'sabado' => 6,
                ];
                if (isset($mapPg[$this->dayOfWeekFilter])) {
                    $query->whereRaw('EXTRACT(DOW FROM data_hora_entrada) = ?', [$mapPg[$this->dayOfWeekFilter]]);
                }
            } elseif ($driver === 'sqlite') {
                $mapSqlite = [
                    'domingo' => '0', 'segunda' => '1', 'terca' => '2',
                    'quarta' => '3', 'quinta' => '4', 'sexta' => '5', 'sabado' => '6',
                ];
                if (isset($mapSqlite[$this->dayOfWeekFilter])) {
                    $query->whereRaw("strftime('%w', data_hora_entrada) = ?", [$mapSqlite[$this->dayOfWeekFilter]]);
                }
            } else {
                $mapMysql = [
                    'domingo' => 1, 'segunda' => 2, 'terca' => 3,
                    'quarta' => 4, 'quinta' => 5, 'sexta' => 6, 'sabado' => 7,
                ];
                if (isset($mapMysql[$this->dayOfWeekFilter])) {
                    $query->whereRaw('DAYOFWEEK(data_hora_entrada) = ?', [$mapMysql[$this->dayOfWeekFilter]]);
                }
            }
        }

        // 3. Busca por Condutor, Professor, Placa ou CPF
        if (! empty(trim($this->driverSearch))) {
            $term = trim($this->driverSearch);
            $upperTerm = strtoupper($term);
            $semHifen = str_replace('-', '', $upperTerm);

            $query->where(function (Builder $sub) use ($term, $upperTerm, $semHifen) {
                $sub->whereLike('placa_registro', "%{$upperTerm}%")
                    ->orWhereLike('placa_corrigida', "%{$upperTerm}%")
                    ->orWhereLike('placa_registro', "%{$semHifen}%")
                    ->orWhereHas('condutor', function (Builder $c) use ($term) {
                        $c->whereLike('nome', "%{$term}%")
                            ->orWhereLike('cpf', "%{$term}%");
                    });
            });
        }

        // 4. Categoria de Vínculo
        $query->filtrarCategoria($this->categoryFilter);

        // 5. Tipo de Evento / Status de Permanência
        if ($this->eventType === 'entrada') {
            $query->whereNull('data_hora_saida');
        } elseif ($this->eventType === 'saida') {
            $query->whereNotNull('data_hora_saida');
        }

        // 6. Operador da Guarita
        if ($this->operatorFilter !== 'todos') {
            if (is_numeric($this->operatorFilter)) {
                $query->where('operador_id', (int) $this->operatorFilter);
            } else {
                $query->whereHas('operador', fn (Builder $o) => $o->where('nome', $this->operatorFilter));
            }
        }

        return $query;
    }

    /**
     * Constrói a query Eloquent com relações carregadas e ordenação cronológica reversa.
     *
     * @return Builder<RegistroAcesso>
     */
    public function buildQuery(): Builder
    {
        $query = $this->buildBaseQuery()
            ->select([
                'id',
                'veiculo_id',
                'condutor_id',
                'operador_id',
                'placa_registro',
                'placa_corrigida',
                'cancela',
                'data_hora_entrada',
                'data_hora_saida',
                'status',
                'tipo_ocupante',
                'justificativa',
            ])
            ->with([
                'condutor:id,nome,cpf,tipo',
                'condutor.professor:condutor_id,departamento',
                'condutor.funcionario:condutor_id,setor',
                'condutor.prestador:condutor_id,empresa',
                'condutor.visitante:condutor_id,motivo_visita',
                'veiculo:id,placa,modelo',
                'operador:id,nome,codigo_operador',
            ]);

        $query->orderByDesc('data_hora_entrada');

        return $query;
    }

    /**
     * Formata um RegistroAcesso para visualização na tabela, documento de impressão e CSV.
     *
     * @return array<string, mixed>
     */
    public function formatRecord(RegistroAcesso $reg): array
    {
        $entrada = $reg->data_hora_entrada;
        $saida = $reg->data_hora_saida;

        $diaSemanaIngles = $entrada ? strtolower($entrada->format('l')) : 'monday';
        $diasMap = [
            'sunday' => ['key' => 'domingo', 'label' => 'Domingo'],
            'monday' => ['key' => 'segunda', 'label' => 'Segunda-feira'],
            'tuesday' => ['key' => 'terca', 'label' => 'Terça-feira'],
            'wednesday' => ['key' => 'quarta', 'label' => 'Quarta-feira'],
            'thursday' => ['key' => 'quinta', 'label' => 'Quinta-feira'],
            'friday' => ['key' => 'sexta', 'label' => 'Sexta-feira'],
            'saturday' => ['key' => 'sabado', 'label' => 'Sábado'],
        ];
        $diaInfo = $diasMap[$diaSemanaIngles] ?? ['key' => 'segunda', 'label' => 'Segunda-feira'];

        $condutor = $reg->condutor;
        $tipo = $condutor?->tipo ?? 'visitante';
        $temPlacaCorrigida = ! empty($reg->placa_corrigida);

        $temSaida = $saida !== null || $reg->status === StatusAcesso::Finalizado;
        $evento = $temSaida ? 'saida' : 'entrada';

        $statusEnum = $reg->status instanceof StatusAcesso
            ? $reg->status
            : (StatusAcesso::tryFrom((string) $reg->status) ?? StatusAcesso::Autorizado);

        $eventoLabel = $temSaida
            ? 'Saída Registrada'
            : match ($statusEnum) {
                StatusAcesso::LiberadoManual => $temPlacaCorrigida ? 'Entrada (Placa Corrigida)' : 'Entrada Manual Liberada',
                StatusAcesso::Pendente => 'Entrada Aguardando Guarita',
                StatusAcesso::NaoCadastrado, StatusAcesso::Bloqueado => 'Entrada Não Credenciada',
                default => 'Entrada Autorizada OCR',
            };

        $permanencia = $temSaida && $entrada && $saida
            ? $entrada->locale('pt_BR')->diffForHumans($saida, true)
            : 'Em Pátio';

        $ocupanteEnum = $reg->tipo_ocupante instanceof TipoOcupante
            ? $reg->tipo_ocupante
            : (TipoOcupante::tryFrom((string) $reg->tipo_ocupante) ?? TipoOcupante::Condutor);

        return [
            'id' => 'REG-'.str_pad((string) $reg->id, 5, '0', STR_PAD_LEFT),
            'raw_id' => $reg->id,
            'date_only' => $entrada?->format('d/m/Y') ?? '--/--/----',
            'entry_hour' => $entrada?->format('H:i:s') ?? '--:--:--',
            'date_time' => $entrada?->format('d/m/Y H:i:s') ?? '--/--/---- --:--:--',
            'exit_time' => $saida?->format('d/m/Y H:i:s'),
            'exit_hour' => $saida?->format('H:i:s'),
            'day_of_week' => $diaInfo['key'],
            'day_of_week_label' => $diaInfo['label'],
            'plate' => $reg->placaExibida(),
            'vehicle_model' => $reg->veiculo?->modelo,
            'driver' => $reg->nomeCondutorExibicao(),
            'driver_cpf' => $condutor?->cpf,
            'category' => $tipo,
            'category_label' => $reg->descricaoVinculo(),
            'event' => $evento,
            'event_label' => $eventoLabel,
            'gate' => $reg->cancela ?: 'Cancela 01 (Principal)',
            'operator' => $reg->operador?->nome ?? 'Sistema OCR Auto',
            'operator_code' => $reg->operador?->codigo_operador,
            'occupant_type' => $ocupanteEnum->value,
            'permanencia' => $permanencia,
            'justificativa' => $reg->justificativa,
        ];
    }

    /**
     * Retorna os registros paginados para a tabela interativa da tela.
     */
    #[Computed]
    public function paginatedRecords(): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator $paginator */
        $paginator = $this->buildQuery()->paginate(10);

        return $paginator->through(function ($reg): array {
            /** @var RegistroAcesso $reg */
            return $this->formatRecord($reg);
        });
    }

    /**
     * Retorna todos os registros filtrados para o documento de impressão/PDF e exportação CSV.
     *
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function filteredData(): array
    {
        return $this->buildQuery()
            ->limit(500)
            ->get()
            ->map(function ($reg): array {
                /** @var RegistroAcesso $reg */
                return $this->formatRecord($reg);
            })
            ->toArray();
    }

    /**
     * Retorna os 4 totalizadores do sumário executivo diretamente via agregação SQL no período filtrado.
     *
     * @return array{total: int, entradas: int, saidas: int, docentes: int}
     */
    #[Computed]
    public function reportStats(): array
    {
        $base = $this->buildBaseQuery();

        $totais = (clone $base)
            ->selectRaw('
                COUNT(*) as total,
                COALESCE(SUM(CASE WHEN data_hora_saida IS NULL THEN 1 ELSE 0 END), 0) as entradas,
                COALESCE(SUM(CASE WHEN data_hora_saida IS NOT NULL THEN 1 ELSE 0 END), 0) as saidas
            ')
            ->first();

        $docentes = (clone $base)
            ->whereHas('condutor', fn (Builder $q) => $q->where('tipo', 'professor'))
            ->count();

        return [
            'total' => (int) ($totais->total ?? 0),
            'entradas' => (int) ($totais->entradas ?? 0),
            'saidas' => (int) ($totais->saidas ?? 0),
            'docentes' => $docentes,
        ];
    }

    /**
     * Exporta os registros filtrados para download direto em planilha CSV/Excel.
     */
    public function exportCsv(): StreamedResponse
    {
        $filename = 'relatorio_acessos_sentinela_'.Carbon::now()->format('Ymd_His').'.csv';
        $records = $this->filteredData;

        return response()->streamDownload(function () use ($records): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            // Injeta o Byte Order Mark (BOM) UTF-8 para o Excel abrir com acentuação correta
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'Protocolo',
                'Data Entrada',
                'Horário Entrada',
                'Horário Saída',
                'Dia da Semana',
                'Placa',
                'Condutor',
                'Categoria & Vínculo',
                'Status da Movimentação',
                'Cancela',
                'Operador Responsável',
                'Permanência',
            ], ';', '"', '\\');

            foreach ($records as $item) {
                fputcsv($handle, [
                    $item['id'] ?? '',
                    $item['date_only'] ?? '',
                    $item['entry_hour'] ?? '',
                    $item['exit_hour'] ?? 'Em Pátio',
                    $item['day_of_week_label'] ?? '',
                    $item['plate'] ?? '',
                    $item['driver'] ?? '',
                    $item['category_label'] ?? '',
                    $item['event_label'] ?? '',
                    $item['gate'] ?? '',
                    $item['operator'] ?? '',
                    $item['permanencia'] ?? '',
                ], ';', '"', '\\');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function openPreview(): void
    {
        $this->showPreviewModal = true;
    }

    public function closePreview(): void
    {
        $this->showPreviewModal = false;
    }

    public function render(): View
    {
        return view('livewire.admin.relatorios', [
            'records' => $this->paginatedRecords,
            'filteredData' => $this->filteredData,
            'reportStats' => $this->reportStats,
            'operators' => $this->operators,
        ]);
    }
}
