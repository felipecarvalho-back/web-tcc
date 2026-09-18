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

    public bool $showPreviewModal = false;

    public array $reportData = [];

    public function mount(): void
    {
        $this->startDate = date('Y-m-01');
        $this->endDate = date('Y-m-d');

        $this->reportData = [
            [
                'id' => 'REG-10492',
                'date_time' => date('d/m/Y').' 14:32:05',
                'plate' => 'BRA-2819',
                'driver' => 'Prof. Dr. Marcos Souza',
                'category' => 'professor',
                'category_label' => 'Docente DSM',
                'event' => 'entrada',
                'event_label' => 'Entrada c/ Correção Manual',
                'gate' => 'Cancela 01 (Principal)',
                'operator' => 'Guarda Silva',
                'ocr_score' => '72% -> 100%',
            ],
            [
                'id' => 'REG-10491',
                'date_time' => date('d/m/Y').' 14:28:40',
                'plate' => 'ABC-1234',
                'driver' => 'Carlos Silva',
                'category' => 'prestador',
                'category_label' => 'Manutenção Predial',
                'event' => 'entrada',
                'event_label' => 'Entrada Automática OCR',
                'gate' => 'Cancela 01 (Principal)',
                'operator' => 'Sistema OCR Auto',
                'ocr_score' => '99%',
            ],
            [
                'id' => 'REG-10490',
                'date_time' => date('d/m/Y').' 14:21:12',
                'plate' => 'FKX-9A42',
                'driver' => 'Visitante (Entregador)',
                'category' => 'visitante',
                'category_label' => 'Visitante Eventual',
                'event' => 'entrada',
                'event_label' => 'Entrada Não Cadastrada',
                'gate' => 'Cancela 01 (Principal)',
                'operator' => 'Guarda Silva',
                'ocr_score' => '88%',
            ],
            [
                'id' => 'REG-10489',
                'date_time' => date('d/m/Y').' 14:15:20',
                'plate' => 'FTC-8821',
                'driver' => 'Mariana Silveira',
                'category' => 'visitante',
                'category_label' => 'Visitante Autorizado',
                'event' => 'entrada',
                'event_label' => 'Cadastro Rápido Visitante',
                'gate' => 'Cancela 02 (Visitantes)',
                'operator' => 'Guarda Ribeiro',
                'ocr_score' => 'N/A',
            ],
            [
                'id' => 'REG-10488',
                'date_time' => date('d/m/Y').' 14:14:02',
                'plate' => 'GTR-4C88',
                'driver' => 'Profa. Dra. Juliana Rezende',
                'category' => 'professor',
                'category_label' => 'Docente GTI',
                'event' => 'entrada',
                'event_label' => 'Entrada Automática OCR',
                'gate' => 'Cancela 01 (Principal)',
                'operator' => 'Sistema OCR Auto',
                'ocr_score' => '98%',
            ],
            [
                'id' => 'REG-10487',
                'date_time' => date('d/m/Y').' 13:58:10',
                'plate' => 'ADM-9021',
                'driver' => 'Fernanda Guimarães',
                'category' => 'funcionario',
                'category_label' => 'Secretaria Acadêmica',
                'event' => 'saida',
                'event_label' => 'Saída Regular Registrada',
                'gate' => 'Cancela 03 (Saída)',
                'operator' => 'Sistema OCR Auto',
                'ocr_score' => '96%',
            ],
            [
                'id' => 'REG-10486',
                'date_time' => date('d/m/Y').' 13:45:00',
                'plate' => 'KPL-3390',
                'driver' => 'Prof. Me. André Cavalcante',
                'category' => 'professor',
                'category_label' => 'Docente IA / BD',
                'event' => 'saida',
                'event_label' => 'Saída Regular Registrada',
                'gate' => 'Cancela 03 (Saída)',
                'operator' => 'Sistema OCR Auto',
                'ocr_score' => '99%',
            ],
        ];
    }

    public function getFilteredDataProperty(): array
    {
        return array_filter($this->reportData, function ($item) {
            if ($this->categoryFilter !== 'todos' && $item['category'] !== $this->categoryFilter) {
                return false;
            }
            if ($this->eventType !== 'todos' && $item['event'] !== $this->eventType) {
                return false;
            }
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
