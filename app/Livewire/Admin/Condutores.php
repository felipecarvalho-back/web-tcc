<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Admin - Gestão de Condutores e Veículos')]
class Condutores extends Component
{
    public string $activeTab = 'professores'; // professores, funcionarios, prestadores

    public string $search = '';

    public string $statusFilter = 'todos';

    public bool $showFormModal = false;

    public ?int $editingId = null;

    // Form fields
    public string $name = '';

    public string $category = 'professor';

    public string $code = ''; // Código funcional / Matrícula

    public string $department = '';

    public string $accessValidity = 'Indeterminado';

    public string $status = 'ativo';

    // Professor pode ter N carros
    public array $vehicles = [];

    public string $toastMessage = '';

    public string $toastType = 'success';

    public array $people = [];

    public function mount(): void
    {
        $this->people = [
            // Professores (com N veículos vinculados)
            [
                'id' => 1,
                'name' => 'Prof. Dr. Marcos Souza',
                'category' => 'professor',
                'code' => 'DOC-94281',
                'department' => 'DSM - Desenvolvimento de Software Multiplataforma',
                'vehicles' => [
                    ['plate' => 'BRA-2E19', 'model' => 'Toyota Corolla', 'color' => 'Cinza'],
                    ['plate' => 'FTC-1090', 'model' => 'Honda Civic', 'color' => 'Preto'],
                ],
                'validity' => 'Indeterminado',
                'status' => 'ativo',
            ],
            [
                'id' => 2,
                'name' => 'Profa. Dra. Juliana Rezende',
                'category' => 'professor',
                'code' => 'DOC-88312',
                'department' => 'GTI - Gestão da Tecnologia da Informação',
                'vehicles' => [
                    ['plate' => 'GTR-4C88', 'model' => 'Honda HR-V', 'color' => 'Prata'],
                ],
                'validity' => 'Indeterminado',
                'status' => 'ativo',
            ],
            [
                'id' => 3,
                'name' => 'Prof. Me. André Cavalcante',
                'category' => 'professor',
                'code' => 'DOC-74192',
                'department' => 'Banco de Dados & IA',
                'vehicles' => [
                    ['plate' => 'KPL-3390', 'model' => 'VW T-Cross', 'color' => 'Branco'],
                    ['plate' => 'AND-7700', 'model' => 'Yamaha MT-07', 'color' => 'Azul'],
                    ['plate' => 'SPX-9122', 'model' => 'Fiat Pulse', 'color' => 'Cinza'],
                ],
                'validity' => 'Indeterminado',
                'status' => 'ativo',
            ],

            // Funcionários Administrativos
            [
                'id' => 4,
                'name' => 'Fernanda Guimarães',
                'category' => 'funcionario',
                'code' => 'ADM-10293',
                'department' => 'Secretaria Acadêmica Central',
                'vehicles' => [
                    ['plate' => 'ADM-9021', 'model' => 'Hyundai HB20', 'color' => 'Preto'],
                ],
                'validity' => 'Indeterminado',
                'status' => 'ativo',
            ],
            [
                'id' => 5,
                'name' => 'Marcelo Andrade',
                'category' => 'funcionario',
                'code' => 'ADM-23910',
                'department' => 'Diretoria de Serviços e Finanças',
                'vehicles' => [
                    ['plate' => 'MCA-5544', 'model' => 'Chevrolet Tracker', 'color' => 'Azul'],
                ],
                'validity' => 'Indeterminado',
                'status' => 'ativo',
            ],

            // Prestadores de Serviço
            [
                'id' => 6,
                'name' => 'Carlos Silva',
                'category' => 'prestador',
                'code' => 'PST-5512',
                'department' => 'Manutenção Predial / Ar-Condicionado',
                'vehicles' => [
                    ['plate' => 'ABC-1234', 'model' => 'Fiat Fiorino', 'color' => 'Branca'],
                    ['plate' => 'FIO-8822', 'model' => 'Fiat Strada', 'color' => 'Branca'],
                ],
                'validity' => '31/12/2026',
                'status' => 'ativo',
            ],
            [
                'id' => 7,
                'name' => 'Elétrica Volt Manutenção',
                'category' => 'prestador',
                'code' => 'PST-9901',
                'department' => 'Infraestrutura Elétrica',
                'vehicles' => [
                    ['plate' => 'VLT-7711', 'model' => 'Renault Master', 'color' => 'Cinza'],
                ],
                'validity' => '15/11/2026',
                'status' => 'inativo',
            ],
        ];

        $this->vehicles = [
            ['plate' => '', 'model' => '', 'color' => 'Prata'],
        ];
    }

    public function addVehicle(): void
    {
        $this->vehicles[] = ['plate' => '', 'model' => '', 'color' => 'Prata'];
    }

    public function removeVehicle(int $index): void
    {
        if (count($this->vehicles) > 1) {
            unset($this->vehicles[$index]);
            $this->vehicles = array_values($this->vehicles);
        }
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->category = match ($this->activeTab) {
            'funcionarios' => 'funcionario',
            'prestadores' => 'prestador',
            default => 'professor',
        };
        $this->code = match ($this->category) {
            'professor' => 'DOC-'.rand(10000, 99999),
            'funcionario' => 'ADM-'.rand(10000, 99999),
            'prestador' => 'PST-'.rand(1000, 9999),
        };
        $this->vehicles = [
            ['plate' => '', 'model' => '', 'color' => 'Prata'],
        ];
        $this->showFormModal = true;
    }

    public function openEditModal(int $id): void
    {
        foreach ($this->people as $person) {
            if ($person['id'] === $id) {
                $this->editingId = $id;
                $this->name = $person['name'];
                $this->category = $person['category'];
                $this->code = $person['code'];
                $this->department = $person['department'];
                $this->vehicles = $person['vehicles'];
                $this->accessValidity = $person['validity'];
                $this->status = $person['status'];
                $this->showFormModal = true;

                return;
            }
        }
    }

    public function closeFormModal(): void
    {
        $this->showFormModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|min:3',
            'code' => 'required|min:3',
            'vehicles.0.plate' => 'required|min:7',
        ], [
            'name.required' => 'O nome é obrigatório.',
            'code.required' => 'O código de acesso/matrícula é obrigatório.',
            'vehicles.0.plate.required' => 'Cadastre ao menos a placa do primeiro veículo.',
        ]);

        // Formatar placas em maiúsculo
        $formattedVehicles = array_map(function ($veh) {
            return [
                'plate' => strtoupper(trim($veh['plate'])),
                'model' => $veh['model'] ?: 'Não informado',
                'color' => $veh['color'] ?: 'Padrão',
            ];
        }, array_filter($this->vehicles, fn ($v) => ! empty(trim($v['plate']))));

        if (empty($formattedVehicles)) {
            $formattedVehicles[] = [
                'plate' => 'SEM-PLACA',
                'model' => 'Não informado',
                'color' => 'Padrão',
            ];
        }

        if ($this->editingId) {
            foreach ($this->people as &$person) {
                if ($person['id'] === $this->editingId) {
                    $person['name'] = $this->name;
                    $person['category'] = $this->category;
                    $person['code'] = $this->code;
                    $person['department'] = $this->department;
                    $person['vehicles'] = $formattedVehicles;
                    $person['validity'] = $this->accessValidity;
                    $person['status'] = $this->status;
                    break;
                }
            }
            $this->triggerToast('Cadastro e veículos atualizados com sucesso!', 'success');
        } else {
            $newId = count($this->people) + 1;
            $this->people[] = [
                'id' => $newId,
                'name' => $this->name,
                'category' => $this->category,
                'code' => strtoupper(trim($this->code)),
                'department' => $this->department ?: 'Geral',
                'vehicles' => $formattedVehicles,
                'validity' => $this->accessValidity ?: 'Indeterminado',
                'status' => $this->status,
            ];
            $this->triggerToast('Novo condutor cadastrado com '.count($formattedVehicles).' veículo(s)!', 'success');
        }

        $this->closeFormModal();
    }

    public function delete(int $id): void
    {
        $this->people = array_filter($this->people, fn ($item) => $item['id'] !== $id);
        $this->triggerToast('Registro excluído com sucesso!', 'info');
    }

    public function toggleStatus(int $id): void
    {
        foreach ($this->people as &$person) {
            if ($person['id'] === $id) {
                $person['status'] = $person['status'] === 'ativo' ? 'inativo' : 'ativo';
                $this->triggerToast("Status alterado para {$person['status']}!", 'info');
                break;
            }
        }
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->code = '';
        $this->department = '';
        $this->vehicles = [
            ['plate' => '', 'model' => '', 'color' => 'Prata'],
        ];
        $this->accessValidity = 'Indeterminado';
        $this->status = 'ativo';
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

    public function getFilteredPeopleProperty(): array
    {
        return array_filter($this->people, function ($item) {
            // Tab filter
            $categoryMatch = match ($this->activeTab) {
                'professores' => $item['category'] === 'professor',
                'funcionarios' => $item['category'] === 'funcionario',
                'prestadores' => $item['category'] === 'prestador',
                default => true,
            };

            if (! $categoryMatch) {
                return false;
            }

            // Status filter
            if ($this->statusFilter !== 'todos' && $item['status'] !== $this->statusFilter) {
                return false;
            }

            // Search filter
            if ($this->search) {
                $term = trim($this->search);
                $found = (stripos($item['name'], $term) !== false) ||
                         (stripos($item['department'], $term) !== false) ||
                         (stripos($item['code'], $term) !== false);

                if (! $found) {
                    // Check within all vehicles plates
                    foreach ($item['vehicles'] as $v) {
                        if (stripos($v['plate'], $term) !== false) {
                            $found = true;
                            break;
                        }
                    }
                }

                if (! $found) {
                    return false;
                }
            }

            return true;
        });
    }

    public function render()
    {
        return view('livewire.admin.condutores', [
            'filteredPeople' => $this->filteredPeople,
        ]);
    }
}
