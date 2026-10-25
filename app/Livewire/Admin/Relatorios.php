<?php

namespace App\Livewire\Admin;

use App\Models\RegistroAcesso;
use App\Models\Usuario;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @property-read array<int, array<string, mixed>> $filteredData
 * @property-read array{total: int, entradas: int, saidas: int, docentes: int} $reportStats
 * @property-read array<int, array{id: string, nome: string}> $operators
 */
#[Layout('layouts.app')]
#[Title('Admin - Relatórios e Exportação PDF - Sentinela FATEC')]
class Relatorios extends Component
{
    public string $startDate = '';

    public string $endDate = '';

    public string $categoryFilter = 'todos';

    public string $eventType = 'todos';

    public string $operatorFilter = 'todos';

    // Filtros de Assiduidade e Frequência Docente
    public string $dayOfWeekFilter = 'todos';

    public string $driverSearch = '';

    public bool $showPreviewModal = false;

    public function mount(): void
    {
        // Define o intervalo padrão cobrindo o ano letivo corrente
        $this->startDate = date('Y-01-01');
        $this->endDate = date('Y-m-d');
    }

    public function clearAssiduidadeFilters(): void
    {
        $this->dayOfWeekFilter = 'todos';
        $this->driverSearch = '';
        $this->categoryFilter = 'todos';
        $this->eventType = 'todos';
        $this->operatorFilter = 'todos';
        $this->startDate = date('Y-01-01');
        $this->endDate = date('Y-m-d');
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
            ->map(fn ($u) => ['id' => (string) $u->id, 'nome' => $u->nome])
            ->toArray();
    }

    /**
     * Constrói a query Eloquent dinâmica de acordo com os filtros.
     *
     * @return Builder<RegistroAcesso>
     */
    public function buildQuery(): Builder
    {
        $query = RegistroAcesso::query()
            ->with([
                'condutor.professor',
                'condutor.funcionario',
                'condutor.prestador',
                'condutor.visitante',
                'veiculo',
                'operador:id,nome',
            ]);

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

        // 3. Busca por Condutor, Professor ou Placa
        if (! empty(trim($this->driverSearch))) {
            $term = strtoupper(trim($this->driverSearch));
            $query->where(function (Builder $sub) use ($term) {
                $sub->whereLike('placa_registro', "%{$term}%")
                    ->orWhereLike('placa_corrigida', "%{$term}%")
                    ->orWhereHas('condutor', fn ($c) => $c->whereLike('nome', "%{$term}%"));
            });
        }

        // 4. Categoria
        if ($this->categoryFilter !== 'todos') {
            if ($this->categoryFilter === 'visitante') {
                $query->where(function (Builder $q) {
                    $q->whereNull('condutor_id')
                        ->orWhereHas('condutor', fn ($c) => $c->where('tipo', 'visitante'));
                });
            } else {
                $query->whereHas('condutor', fn ($c) => $c->where('tipo', $this->categoryFilter));
            }
        }

        // 5. Tipo de Evento
        if ($this->eventType === 'entrada') {
            $query->whereNull('data_hora_saida');
        } elseif ($this->eventType === 'saida') {
            $query->whereNotNull('data_hora_saida');
        }

        // 6. Operador
        if ($this->operatorFilter !== 'todos') {
            if (is_numeric($this->operatorFilter)) {
                $query->where('operador_id', (int) $this->operatorFilter);
            } else {
                $query->whereHas('operador', fn ($o) => $o->where('nome', $this->operatorFilter));
            }
        }

        return $query->orderByDesc('data_hora_entrada');
    }

    /**
     * Formata um RegistroAcesso para a visualização na tabela e exportações.
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

        $categoriaLabel = match ($tipo) {
            'professor' => 'Docente '.($condutor?->professor?->departamento ?: 'Geral'),
            'funcionario' => 'Funcionário '.($condutor?->funcionario?->setor ?: 'Administrativo'),
            'prestador' => 'Prestador - '.($condutor?->prestador?->empresa ?: 'Serviços'),
            'visitante' => 'Visitante - '.($condutor?->visitante?->motivo_visita ?: 'Visita'),
            default => 'Não Cadastrado',
        };

        $placaExibida = $reg->placa_corrigida ?: $reg->placa_registro;
        $semPlaca = $reg->placa_registro === RegistroAcesso::PLACA_NAO_IDENTIFICADA && empty($reg->placa_corrigida);
        $nomeCondutor = $condutor?->nome ?? ($semPlaca ? 'Placa Não Identificada' : 'Condutor Não Vinculado');

        $temSaida = $saida !== null;
        $evento = $temSaida ? 'saida' : 'entrada';
        $eventoLabel = $temSaida
            ? 'Saída Registrada'
            : (! empty($reg->placa_corrigida) ? 'Entrada c/ Placa Corrigida' : (! empty($reg->justificativa) ? 'Entrada c/ Validação de Código' : 'Entrada Automática OCR'));

        $permanencia = $temSaida && $entrada
            ? $entrada->diffForHumans($saida, true)
            : 'Em Pátio';

        return [
            'id' => 'REG-'.str_pad((string) $reg->id, 5, '0', STR_PAD_LEFT),
            'raw_id' => $reg->id,
            'date_time' => $entrada?->format('d/m/Y H:i:s') ?? '--/--/---- --:--:--',
            'exit_time' => $saida?->format('d/m/Y H:i:s'),
            'day_of_week' => $diaInfo['key'],
            'day_of_week_label' => $diaInfo['label'],
            'plate' => $placaExibida,
            'driver' => $nomeCondutor,
            'category' => $tipo,
            'category_label' => $categoriaLabel,
            'event' => $evento,
            'event_label' => $eventoLabel,
            'gate' => $reg->cancela ?: 'Cancela 01 (Principal)',
            'operator' => $reg->operador?->nome ?? 'Sistema Auto',
            'occupant_type' => $reg->tipo_ocupante?->value ?? 'condutor',
            'permanencia' => $permanencia,
        ];
    }

    /**
     * Retorna os registros filtrados do relatório.
     *
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function filteredData(): array
    {
        if (RegistroAcesso::query()->exists()) {
            return $this->buildQuery()
                ->get()
                ->map(fn (RegistroAcesso $reg) => $this->formatRecord($reg))
                ->toArray();
        }

        return $this->filterMockData();
    }

    /**
     * Aplica os filtros em memória aos registros de demonstração quando o banco estiver vazio.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function filterMockData(): array
    {
        return array_values(array_filter($this->mockRecords(), function (array $item): bool {
            if ($this->dayOfWeekFilter !== 'todos' && ($item['day_of_week'] ?? '') !== $this->dayOfWeekFilter) {
                return false;
            }

            if (! empty(trim($this->driverSearch))) {
                $term = trim($this->driverSearch);
                $matchDriver = stripos((string) $item['driver'], $term) !== false;
                $matchPlate = stripos((string) $item['plate'], $term) !== false;

                if (! $matchDriver && ! $matchPlate) {
                    return false;
                }
            }

            if ($this->categoryFilter !== 'todos' && ($item['category'] ?? '') !== $this->categoryFilter) {
                return false;
            }

            if ($this->eventType !== 'todos' && ($item['event'] ?? '') !== $this->eventType) {
                return false;
            }

            if ($this->operatorFilter !== 'todos' && ($item['operator'] ?? '') !== $this->operatorFilter) {
                return false;
            }

            return true;
        }));
    }

    /**
     * Registros de demonstração caso o banco de dados esteja vazio (ex: testes em memória SQLite).
     *
     * @return array<int, array<string, mixed>>
     */
    public function mockRecords(): array
    {
        return [
            [
                'id' => 'REG-10504',
                'raw_id' => 10504,
                'date_time' => '22/09/2026 22:35:10',
                'exit_time' => '22/09/2026 22:35:10',
                'day_of_week' => 'segunda',
                'day_of_week_label' => 'Segunda-feira',
                'plate' => 'BRA-2819',
                'driver' => 'Prof. Dr. Marcos Souza',
                'category' => 'professor',
                'category_label' => 'Docente DSM',
                'event' => 'saida',
                'event_label' => 'Saída Noturna Registrada',
                'gate' => 'Cancela 03 (Saída)',
                'operator' => 'Guarda Silva',
                'occupant_type' => 'condutor',
                'permanencia' => '3h 53min',
            ],
            [
                'id' => 'REG-10503',
                'raw_id' => 10503,
                'date_time' => '22/09/2026 18:42:05',
                'exit_time' => null,
                'day_of_week' => 'segunda',
                'day_of_week_label' => 'Segunda-feira',
                'plate' => 'BRA-2819',
                'driver' => 'Prof. Dr. Marcos Souza',
                'category' => 'professor',
                'category_label' => 'Docente DSM',
                'event' => 'entrada',
                'event_label' => 'Entrada c/ Validação de Código',
                'gate' => 'Cancela 01 (Principal)',
                'operator' => 'Guarda Silva',
                'occupant_type' => 'condutor',
                'permanencia' => 'Em Pátio',
            ],
            [
                'id' => 'REG-10499',
                'raw_id' => 10499,
                'date_time' => '15/09/2026 22:40:00',
                'exit_time' => '15/09/2026 22:40:00',
                'day_of_week' => 'segunda',
                'day_of_week_label' => 'Segunda-feira',
                'plate' => 'BRA-2819',
                'driver' => 'Prof. Dr. Marcos Souza',
                'category' => 'professor',
                'category_label' => 'Docente DSM',
                'event' => 'saida',
                'event_label' => 'Saída Noturna Registrada',
                'gate' => 'Cancela 03 (Saída)',
                'operator' => 'Guarda Ribeiro',
                'occupant_type' => 'condutor',
                'permanencia' => '3h 51min',
            ],
            [
                'id' => 'REG-10498',
                'raw_id' => 10498,
                'date_time' => '15/09/2026 18:48:30',
                'exit_time' => null,
                'day_of_week' => 'segunda',
                'day_of_week_label' => 'Segunda-feira',
                'plate' => 'BRA-2819',
                'driver' => 'Prof. Dr. Marcos Souza',
                'category' => 'professor',
                'category_label' => 'Docente DSM',
                'event' => 'entrada',
                'event_label' => 'Entrada Automática OCR',
                'gate' => 'Cancela 01 (Principal)',
                'operator' => 'Sistema Auto',
                'occupant_type' => 'condutor',
                'permanencia' => 'Em Pátio',
            ],
            [
                'id' => 'REG-10492',
                'raw_id' => 10492,
                'date_time' => '08/09/2026 22:38:40',
                'exit_time' => '08/09/2026 22:38:40',
                'day_of_week' => 'segunda',
                'day_of_week_label' => 'Segunda-feira',
                'plate' => 'BRA-2819',
                'driver' => 'Prof. Dr. Marcos Souza',
                'category' => 'professor',
                'category_label' => 'Docente DSM',
                'event' => 'saida',
                'event_label' => 'Saída Noturna Registrada',
                'gate' => 'Cancela 03 (Saída)',
                'operator' => 'Guarda Silva',
                'occupant_type' => 'condutor',
                'permanencia' => '3h 48min',
            ],
            [
                'id' => 'REG-10491',
                'raw_id' => 10491,
                'date_time' => '08/09/2026 18:50:15',
                'exit_time' => null,
                'day_of_week' => 'segunda',
                'day_of_week_label' => 'Segunda-feira',
                'plate' => 'BRA-2819',
                'driver' => 'Prof. Dr. Marcos Souza',
                'category' => 'professor',
                'category_label' => 'Docente DSM',
                'event' => 'entrada',
                'event_label' => 'Entrada Automática OCR',
                'gate' => 'Cancela 01 (Principal)',
                'operator' => 'Guarda Silva',
                'occupant_type' => 'condutor',
                'permanencia' => 'Em Pátio',
            ],
            [
                'id' => 'REG-10485',
                'raw_id' => 10485,
                'date_time' => '23/09/2026 14:14:02',
                'exit_time' => null,
                'day_of_week' => 'terca',
                'day_of_week_label' => 'Terça-feira',
                'plate' => 'GTR-4C88',
                'driver' => 'Profa. Dra. Juliana Rezende',
                'category' => 'professor',
                'category_label' => 'Docente GTI',
                'event' => 'entrada',
                'event_label' => 'Entrada Automática OCR',
                'gate' => 'Cancela 01 (Principal)',
                'operator' => 'Guarda Ribeiro',
                'occupant_type' => 'condutor',
                'permanencia' => 'Em Pátio',
            ],
            [
                'id' => 'REG-10475',
                'raw_id' => 10475,
                'date_time' => '24/09/2026 14:28:40',
                'exit_time' => null,
                'day_of_week' => 'quarta',
                'day_of_week_label' => 'Quarta-feira',
                'plate' => 'ABC-1234',
                'driver' => 'Carlos Silva',
                'category' => 'prestador',
                'category_label' => 'Manutenção Predial',
                'event' => 'entrada',
                'event_label' => 'Entrada Automática OCR',
                'gate' => 'Cancela 01 (Principal)',
                'operator' => 'Guarda Ribeiro',
                'occupant_type' => 'condutor',
                'permanencia' => 'Em Pátio',
            ],
            [
                'id' => 'REG-10470',
                'raw_id' => 10470,
                'date_time' => '25/09/2026 13:45:00',
                'exit_time' => '25/09/2026 18:15:00',
                'day_of_week' => 'quinta',
                'day_of_week_label' => 'Quinta-feira',
                'plate' => 'KPL-3390',
                'driver' => 'Prof. Me. André Cavalcante',
                'category' => 'professor',
                'category_label' => 'Docente IA / BD',
                'event' => 'saida',
                'event_label' => 'Saída Regular Registrada',
                'gate' => 'Cancela 03 (Saída)',
                'operator' => 'Guarda Silva',
                'occupant_type' => 'condutor',
                'permanencia' => '4h 30min',
            ],
        ];
    }

    /**
     * Retorna os 4 totalizadores do sumário executivo.
     *
     * @return array{total: int, entradas: int, saidas: int, docentes: int}
     */
    #[Computed]
    public function reportStats(): array
    {
        $data = $this->filteredData;

        return [
            'total' => count($data),
            'entradas' => count(array_filter($data, fn ($i) => ($i['event'] ?? '') === 'entrada')),
            'saidas' => count(array_filter($data, fn ($i) => ($i['event'] ?? '') === 'saida')),
            'docentes' => count(array_filter($data, fn ($i) => ($i['category'] ?? '') === 'professor')),
        ];
    }

    /**
     * Exporta os registros filtrados para download direto em planilha CSV/Excel.
     */
    public function exportCsv(): StreamedResponse
    {
        $filename = 'relatorio_acessos_sentinela_'.now()->format('Ymd_His').'.csv';
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
                'Data & Horário Entrada',
                'Dia da Semana',
                'Placa',
                'Condutor',
                'Categoria',
                'Movimentação',
                'Cancela',
                'Operador',
                'Permanência',
            ], ';', '"', '\\');

            foreach ($records as $item) {
                fputcsv($handle, [
                    $item['id'] ?? '',
                    $item['date_time'] ?? '',
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
            'filteredData' => $this->filteredData,
            'reportStats' => $this->reportStats,
            'operators' => $this->operators,
        ]);
    }
}
