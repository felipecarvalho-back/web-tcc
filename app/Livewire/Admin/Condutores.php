<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Admin - Gestão e CRUD de Condutores e Veículos')]
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

    public string $registrationNumber = '';

    public string $email = '';

    public string $department = '';

    public string $plate = '';

    public string $vehicleModel = '';

    public string $vehicleColor = 'Prata';

    public string $accessValidity = 'Indeterminado';

    public string $status = 'ativo';

    public string $toastMessage = '';

    public string $toastType = 'success';

    public array $people = [];

    public function mount(): void
    {
        $this->people = [
            // Professores
            [
                'id' => 1,
                'name' => 'Prof. Dr. Marcos Souza',
                'category' => 'professor',
                'registration' => 'DOC-94281',
                'email' => 'marcos.souza@fatec.sp.gov.br',
                'department' => 'DSM - Desenvolvimento de Software Multiplataforma',
                'plate' => 'BRA-2E19',
                'vehicle_model' => 'Toyota Corolla',
                'vehicle_color' => 'Cinza',
                'validity' => 'Indeterminado',
                'status' => 'ativo',
            ],
            [
                'id' => 2,
                'name' => 'Profa. Dra. Juliana Rezende',
                'category' => 'professor',
                'registration' => 'DOC-88312',
                'email' => 'juliana.rezende@fatec.sp.gov.br',
                'department' => 'GTI - Gestão da Tecnologia da Informação',
                'plate' => 'GTR-4C88',
                'vehicle_model' => 'Honda HR-V',
                'vehicle_color' => 'Prata',
                'validity' => 'Indeterminado',
                'status' => 'ativo',
            ],
            [
                'id' => 3,
                'name' => 'Prof. Me. André Cavalcante',
                'category' => 'professor',
                'registration' => 'DOC-74192',
                'email' => 'andre.cavalcante@fatec.sp.gov.br',
                'department' => 'Banco de Dados & Inteligência Artificial',
                'plate' => 'KPL-3390',
                'vehicle_model' => 'VW T-Cross',
                'vehicle_color' => 'Branco',
                'validity' => 'Indeterminado',
                'status' => 'ativo',
            ],

            // Funcionários Administrativos
            [
                'id' => 4,
                'name' => 'Fernanda Guimarães',
                'category' => 'funcionario',
                'registration' => 'ADM-10293',
                'email' => 'fernanda.guimaraes@fatec.sp.gov.br',
                'department' => 'Secretaria Acadêmica Central',
                'plate' => 'ADM-9021',
                'vehicle_model' => 'Hyundai HB20',
                'vehicle_color' => 'Preto',
                'validity' => 'Indeterminado',
                'status' => 'ativo',
            ],
            [
                'id' => 5,
                'name' => 'Marcelo Andrade',
                'category' => 'funcionario',
                'registration' => 'ADM-23910',
                'email' => 'marcelo.andrade@fatec.sp.gov.br',
                'department' => 'Diretoria de Serviços e Finanças',
                'plate' => 'MCA-5544',
                'vehicle_model' => 'Chevrolet Tracker',
                'vehicle_color' => 'Azul',
                'validity' => 'Indeterminado',
                'status' => 'ativo',
            ],

            // Prestadores de Serviço (RF02)
            [
                'id' => 6,
                'name' => 'Carlos Silva',
                'category' => 'prestador',
                'registration' => 'CNPJ: 14.821.902/0001-44',
                'email' => 'contato@climafatec.com.br',
                'department' => 'Manutenção Predial / Ar-Condicionado',
                'plate' => 'ABC-1234',
                'vehicle_model' => 'Fiat Fiorino',
                'vehicle_color' => 'Branca',
                'validity' => '31/12/2026',
                'status' => 'ativo',
            ],
            [
                'id' => 7,
                'name' => 'Elétrica Volt Prestação de Serviços',
                'category' => 'prestador',
                'registration' => 'CNPJ: 08.192.441/0001-90',
                'email' => 'operacoes@eletricavolt.com.br',
                'department' => 'Infraestrutura Elétrica',
                'plate' => 'VLT-7711',
                'vehicle_model' => 'Renault Master',
                'vehicle_color' => 'Cinza',
                'validity' => '15/11/2026',
                'status' => 'inativo',
            ],
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->category = match ($this->activeTab) {
            'funcionarios' => 'funcionario',
            'prestadores' => 'prestador',
            default => 'professor',
        };
        $this->showFormModal = true;
    }

    public function openEditModal(int $id): void
    {
        foreach ($this->people as $person) {
            if ($person['id'] === $id) {
                $this->editingId = $id;
                $this->name = $person['name'];
                $this->category = $person['category'];
                $this->registrationNumber = $person['registration'];
                $this->email = $person['email'];
                $this->department = $person['department'];
                $this->plate = $person['plate'];
                $this->vehicleModel = $person['vehicle_model'];
                $this->vehicleColor = $person['vehicle_color'];
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
            'plate' => 'required|min:7',
            'email' => 'required|email',
        ], [
            'name.required' => 'O nome é obrigatório.',
            'plate.required' => 'A placa do veículo é obrigatória.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
        ]);

        if ($this->editingId) {
            foreach ($this->people as &$person) {
                if ($person['id'] === $this->editingId) {
                    $person['name'] = $this->name;
                    $person['category'] = $this->category;
                    $person['registration'] = $this->registrationNumber;
                    $person['email'] = $this->email;
                    $person['department'] = $this->department;
                    $person['plate'] = strtoupper(trim($this->plate));
                    $person['vehicle_model'] = $this->vehicleModel;
                    $person['vehicle_color'] = $this->vehicleColor;
                    $person['validity'] = $this->accessValidity;
                    $person['status'] = $this->status;
                    break;
                }
            }
            $this->triggerToast('Cadastro atualizado com sucesso!', 'success');
        } else {
            $newId = count($this->people) + 1;
            $this->people[] = [
                'id' => $newId,
                'name' => $this->name,
                'category' => $this->category,
                'registration' => $this->registrationNumber ?: 'MAT-'.rand(10000, 99999),
                'email' => $this->email,
                'department' => $this->department ?: 'Geral',
                'plate' => strtoupper(trim($this->plate)),
                'vehicle_model' => $this->vehicleModel ?: 'Não especificado',
                'vehicle_color' => $this->vehicleColor ?: 'Prata',
                'validity' => $this->accessValidity ?: 'Indeterminado',
                'status' => $this->status,
            ];
            $this->triggerToast('Novo condutor e veículo cadastrados com sucesso!', 'success');
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
        $this->registrationNumber = '';
        $this->email = '';
        $this->department = '';
        $this->plate = '';
        $this->vehicleModel = '';
        $this->vehicleColor = 'Prata';
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
                         (stripos($item['plate'], $term) !== false) ||
                         (stripos($item['department'], $term) !== false) ||
                         (stripos($item['registration'], $term) !== false);

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
