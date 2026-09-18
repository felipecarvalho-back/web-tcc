<?php

namespace App\Livewire\Portaria;

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

    public bool $showCorrectionModal = false;

    public ?array $selectedRecord = null;

    public string $correctedPlate = '';

    public string $correctionJustification = 'Reflexo solar sobre o caractere da placa';

    public string $correctionCategory = 'professor';

    public string $toastMessage = '';

    public string $toastType = 'success';

    public array $records = [];

    public int $totalPassages = 412;

    public int $automaticPassages = 389;

    public int $manualCorrections = 17;

    public function mount(): void
    {
        $this->records = [
            [
                'id' => 1084,
                'plate' => 'BRA-2819',
                'raw_ocr' => 'BRA-2819',
                'confidence' => 72,
                'driver_name' => 'Possível Prof. Dr. Marcos Souza',
                'category' => 'professor',
                'category_label' => 'Docente DSM (Desenvolvimento de Software)',
                'registered_at' => '14:32:05',
                'time_ago' => 'Há 3 minutos',
                'status' => 'pendente',
                'status_label' => 'Aguardando Liberação',
                'image_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBwm07qkZIJd-rbpEzJBASevnBBdTcqc7Br___et9oQw4CM_S4fITpgE-g2TgcBm9gllhPwl2I3jgKriISCCqhE479dLHJZNh923e02v2Pca-I29JvSJVGmD84ofuwRf3RFt3GPCGneh7kCVritsK654A3EG2w8930I68dlRtbh3ezUhLlPr5-xs1H7rV4KVqp7e4h-89Tu9giFBQO-cAhi5ao4XhOauIJuYF3BSTIIcFgao8jmzA_-',
                'is_registered' => false,
            ],
            [
                'id' => 1083,
                'plate' => 'ABC-1234',
                'raw_ocr' => 'ABC-1234',
                'confidence' => 99,
                'driver_name' => 'Carlos Silva',
                'category' => 'prestador',
                'category_label' => 'Manutenção Predial (Prestador de Serviço) • Tag Ativa',
                'registered_at' => '14:28:40',
                'time_ago' => 'Há 6 minutos',
                'status' => 'autorizado',
                'status_label' => 'Autorizado',
                'image_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDAubQZwTAIxcX5dVzVCdSpZ43moQ0KgfBfG_ocPvPKtuygXT0dM8Z2gmiWo_XcLhYgxmZq0pv0vnyPEtAOpACViX7civEu2_2Zo79hcY7jFBjDlYp9NGX2APBvvPZ_1a4dikxSNJx3caBqkO2XuDusqQ6e8rGEzu3ofu-HTSFVpu7NUirJiOhK6gzU0M6tkoN6hvrwfej1Qkxr4Mn-1uLfV7RoOTsldOAYoOqoYm4EkDirMMaeyD4g',
                'is_registered' => true,
            ],
            [
                'id' => 1082,
                'plate' => 'FKX-9A42',
                'raw_ocr' => 'FKX-9A42',
                'confidence' => 88,
                'driver_name' => 'Veículo Não Cadastrado',
                'category' => 'visitante',
                'category_label' => 'Motocicleta Entregador / Visitante Eventual',
                'registered_at' => '14:21:12',
                'time_ago' => 'Há 13 minutos',
                'status' => 'nao_cadastrado',
                'status_label' => 'Não Cadastrado',
                'image_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuB_UZgErWBuOuiVNK2ZREnBJK82IyqrYm1jzqm2EDVrPN1uyUsRsxWnFnUFDLij9AOdskteOXpFQcakcJ4XVDGKfIyDcfvLiJBNSmpncuzwEG3h95ym_0ArMlRNQnN9eIx5C1FklPjMx5gc36xgMYoiDgsyXj10LzONMJLCKzeoYiVIkVqa92xlAJekXC12q5652tAYQQk53eCxlDIIXeZkFFWogS_tsDW2sZNRsgW5wkJrH6OKR7rG',
                'is_registered' => false,
            ],
            [
                'id' => 1081,
                'plate' => 'GTR-4C88',
                'raw_ocr' => 'GTR-4C88',
                'confidence' => 98,
                'driver_name' => 'Profa. Dra. Juliana Rezende',
                'category' => 'professor',
                'category_label' => 'Docente GTI (Gestão de TI) • Vaga Docente Reservada',
                'registered_at' => '14:14:02',
                'time_ago' => 'Há 20 minutos',
                'status' => 'autorizado',
                'status_label' => 'Autorizado',
                'image_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCApIKZcczrExQtByFs0AuBB4oheMxnqtOqqG-lpkehXqm-D0VqOaGSCPpDJtQ3sgoryRnxOJGlXmr_dhAUjFi0Bvnc03XAd4M5eu3Oqx__ODYjYn9al6DPZgsnmEu9QOpEvI66b43KXmRJFoQbdLM8HhX_xvLCz6mnjU1LhNldBViLi66klsRE56iT5mlsMkzXJKHJmJVAjbhG-OIvemnHBU4ZHLwyNIkrZz4Lb90U7QpMVqcwzfPX',
                'is_registered' => true,
            ],
        ];
    }

    public function openCorrectionModal(int $id): void
    {
        foreach ($this->records as $record) {
            if ($record['id'] === $id) {
                $this->selectedRecord = $record;
                $this->correctedPlate = $record['plate'];
                $this->correctionCategory = $record['category'];
                $this->correctionJustification = 'Correção manual de caractere ilegível no OCR';
                $this->showCorrectionModal = true;

                return;
            }
        }
    }

    public function closeCorrectionModal(): void
    {
        $this->showCorrectionModal = false;
        $this->selectedRecord = null;
    }

    public function confirmCorrection(): void
    {
        if (! $this->selectedRecord) {
            return;
        }

        $targetId = $this->selectedRecord['id'];

        foreach ($this->records as &$record) {
            if ($record['id'] === $targetId) {
                $record['plate'] = strtoupper(trim($this->correctedPlate));
                $record['status'] = 'autorizado';
                $record['status_label'] = 'Autorizado (Corrigido)';
                $record['driver_name'] = 'Prof. Dr. Marcos Souza (Validado)';
                $record['confidence'] = 100;
                break;
            }
        }

        $this->manualCorrections++;
        $this->showCorrectionModal = false;
        $this->triggerToast("Placa #{$targetId} corrigida para {$this->correctedPlate}. Cancela liberada!", 'success');
    }

    public function markExit(int $id): void
    {
        foreach ($this->records as &$record) {
            if ($record['id'] === $id) {
                $record['status'] = 'saida';
                $record['status_label'] = 'Saída Registrada';
                $this->triggerToast("Saída registrada para o veículo {$record['plate']}!", 'info');
                break;
            }
        }
    }

    public function allowManualEntry(int $id): void
    {
        foreach ($this->records as &$record) {
            if ($record['id'] === $id) {
                $record['status'] = 'autorizado';
                $record['status_label'] = 'Liberado Manualmente';
                $record['driver_name'] = 'Prof. FATEC (Acesso Concedido por Exceção)';
                $this->totalPassages++;
                $this->triggerToast('Entrada permitida para docente c/ acesso! Cancela #01 aberta.', 'success');
                break;
            }
        }
    }

    public function simulateNewCapture(): void
    {
        $newId = count($this->records) + 1085;
        $newRecord = [
            'id' => $newId,
            'plate' => 'FTC-'.rand(1000, 9999),
            'raw_ocr' => 'FTC-'.rand(1000, 9999),
            'confidence' => rand(85, 99),
            'driver_name' => 'Prof. Dr. Ricardo Alencar',
            'category' => 'professor',
            'category_label' => 'Docente ADS • Vaga Reservada',
            'registered_at' => date('H:i:s'),
            'time_ago' => 'Agora mesmo',
            'status' => 'autorizado',
            'status_label' => 'Autorizado',
            'image_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAbf2RHooVfcHdtDDW2DVRHqWC1MkeGE1em40bZklihlVeby_dmWboB5Zt-qVbgC0OWQIfhr0NRwLWUAkQP63gi49ZkFN_Pdc5HgLor36CbPwgDiKHkUQYc7n4fgy4jn_Tv4tEmb_pk9sVy9pZznHLYEwVDJVo24Zxhvd1B5GVrwHbBeAmN3yhQrNUwfdkWq9sRpJZBiQesH-Db2hZpuhLkxUU2OooXsLDMImMIZKSOJAoxqjacCTFd',
            'is_registered' => true,
        ];

        array_unshift($this->records, $newRecord);
        $this->totalPassages++;
        $this->automaticPassages++;
        $this->triggerToast('Novo veículo identificado pela câmera da guarita!', 'success');
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

    public function getFilteredRecordsProperty(): array
    {
        return array_filter($this->records, function ($record) {
            if ($this->filterPlate && stripos($record['plate'], trim($this->filterPlate)) === false) {
                return false;
            }
            if ($this->filterCategory !== 'todos' && $record['category'] !== $this->filterCategory) {
                return false;
            }
            if ($this->filterStatus !== 'todos' && $record['status'] !== $this->filterStatus) {
                return false;
            }

            return true;
        });
    }

    public function render()
    {
        return view('livewire.portaria.monitoramento', [
            'filteredRecords' => $this->filteredRecords,
        ]);
    }
}
