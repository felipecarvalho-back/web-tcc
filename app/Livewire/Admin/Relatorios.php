<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Admin - Relatórios e Exportação PDF - Sentinela FATEC')]
class Relatorios extends Component
{
    public string $startDate = '';

    public string $endDate = '';

    public string $categoryFilter = 'todos';

    public string $eventType = 'todos';

    public string $operatorFilter = 'todos';

    // Novos Filtros de Assiduidade e Frequência Docente
    public string $dayOfWeekFilter = 'todos';

    public string $driverSearch = '';

    public bool $showPreviewModal = false;

    public array $reportData = [];

    public function mount(): void
    {
        $this->startDate = date('Y-m-01');
        $this->endDate = date('Y-m-d');

        // TODO [Backend]:
        // Substituir esta lista de demonstração por uma consulta Eloquent dinâmica:
        // Exemplo:
        // $query = RegistroAcesso::with(['condutor', 'veiculo', 'operador'])
        //     ->whereBetween('data_hora_entrada', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
        //
        // if ($this->dayOfWeekFilter !== 'todos') {
        //     // Mapeamento MySQL: 1 = Domingo, 2 = Segunda, 3 = Terça, 4 = Quarta, 5 = Quinta, 6 = Sexta, 7 = Sábado
        //     $mapDays = ['domingo' => 1, 'segunda' => 2, 'terca' => 3, 'quarta' => 4, 'quinta' => 5, 'sexta' => 6, 'sabado' => 7];
        //     $query->whereRaw('DAYOFWEEK(data_hora_entrada) = ?', [$mapDays[$this->dayOfWeekFilter]]);
        // }
        //
        // if (!empty($this->driverSearch)) {
        //     $query->whereHas('condutor', fn($q) => $q->where('nome', 'like', "%{$this->driverSearch}%"))
        //           ->orWhere('placa_registro', 'like', "%{$this->driverSearch}%");
        // }
        //
        // $this->reportData = $query->orderBy('data_hora_entrada', 'desc')->get()->toArray();

        $this->reportData = [
            [
                'id' => 'REG-10504',
                'date_time' => '22/09/2026 22:35:10',
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
            ],
            [
                'id' => 'REG-10503',
                'date_time' => '22/09/2026 18:42:05',
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
            ],
            [
                'id' => 'REG-10499',
                'date_time' => '15/09/2026 22:40:00',
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
            ],
            [
                'id' => 'REG-10498',
                'date_time' => '15/09/2026 18:48:30',
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
            ],
            [
                'id' => 'REG-10492',
                'date_time' => '08/09/2026 22:38:40',
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
            ],
            [
                'id' => 'REG-10491',
                'date_time' => '08/09/2026 18:50:15',
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
            ],
            [
                'id' => 'REG-10488',
                'date_time' => '01/09/2026 18:45:00',
                'day_of_week' => 'segunda',
                'day_of_week_label' => 'Segunda-feira',
                'plate' => 'BRA-2819',
                'driver' => 'Prof. Dr. Marcos Souza',
                'category' => 'professor',
                'category_label' => 'Docente DSM',
                'event' => 'entrada',
                'event_label' => 'Entrada Regular Registrada',
                'gate' => 'Cancela 01 (Principal)',
                'operator' => 'Guarda Silva',
                'occupant_type' => 'condutor',
            ],
            [
                'id' => 'REG-10485',
                'date_time' => '23/09/2026 14:14:02',
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
            ],
            [
                'id' => 'REG-10480',
                'date_time' => '16/09/2026 14:10:12',
                'day_of_week' => 'terca',
                'day_of_week_label' => 'Terça-feira',
                'plate' => 'GTR-4C88',
                'driver' => 'Profa. Dra. Juliana Rezende',
                'category' => 'professor',
                'category_label' => 'Docente GTI',
                'event' => 'entrada',
                'event_label' => 'Entrada Regular',
                'gate' => 'Cancela 01 (Principal)',
                'operator' => 'Guarda Silva',
                'occupant_type' => 'condutor',
            ],
            [
                'id' => 'REG-10475',
                'date_time' => '24/09/2026 14:28:40',
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
            ],
            [
                'id' => 'REG-10470',
                'date_time' => '25/09/2026 13:45:00',
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
            ],
        ];
    }

    public function clearAssiduidadeFilters(): void
    {
        $this->dayOfWeekFilter = 'todos';
        $this->driverSearch = '';
        $this->categoryFilter = 'todos';
        $this->eventType = 'todos';
        $this->operatorFilter = 'todos';
    }

    public function getFilteredDataProperty(): array
    {
        return array_filter($this->reportData, function ($item) {
            // Filtro por dia da semana (ex: segunda-feira)
            if ($this->dayOfWeekFilter !== 'todos' && ($item['day_of_week'] ?? '') !== $this->dayOfWeekFilter) {
                return false;
            }

            // Filtro por nome do professor / condutor ou placa
            if (! empty(trim($this->driverSearch))) {
                $term = trim($this->driverSearch);
                $matchDriver = stripos($item['driver'], $term) !== false;
                $matchPlate = stripos($item['plate'], $term) !== false;

                if (! $matchDriver && ! $matchPlate) {
                    return false;
                }
            }

            // Filtro por categoria
            if ($this->categoryFilter !== 'todos' && $item['category'] !== $this->categoryFilter) {
                return false;
            }

            // Filtro por tipo de evento
            if ($this->eventType !== 'todos' && $item['event'] !== $this->eventType) {
                return false;
            }

            // Filtro por operador
            if ($this->operatorFilter !== 'todos' && $item['operator'] !== $this->operatorFilter) {
                return false;
            }

            return true;
        });
    }

    public function openPreview(): void
    {
        $this->showPreviewModal = true;
    }

    public function closePreview(): void
    {
        $this->showPreviewModal = false;
    }

    public function render()
    {
        return view('livewire.admin.relatorios', [
            'filteredData' => $this->filteredData,
        ]);
    }
}
