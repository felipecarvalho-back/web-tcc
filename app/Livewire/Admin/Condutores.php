<?php

namespace App\Livewire\Admin;

use App\Models\Condutor;
use App\Models\CondutorFuncionario;
use App\Models\CondutorProfessor;
use App\Models\CondutorPrestador;
use App\Models\Veiculo;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Admin - Gestão de Condutores e Veículos')]
class Condutores extends Component
{
    public string $activeTab = 'professores';
    public string $search = '';
    public string $statusFilter = 'todos';
    public bool $showFormModal = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $cpf = '';
    public string $category = 'professor';
    public string $code = '';
    public string $department = '';
    public string $accessValidity = 'Indeterminado';
    public string $status = 'ativo';

    public array $vehicles = [];

    public string $toastMessage = '';
    public string $toastType = 'success';

    public function mount(): void
    {
        $this->vehicles = [
            ['id' => null, 'plate' => '', 'model' => '', 'color' => ''],
        ];
    }

    public function addVehicle(): void
    {
        $this->vehicles[] = [
            'id' => null,
            'plate' => '',
            'model' => '',
            'color' => '',
        ];
    }

    public function removeVehicle(int $index): void
    {
        if (count($this->vehicles) > 1 && isset($this->vehicles[$index])) {
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

        $this->showFormModal = true;
    }

    public function openEditModal(int $id): void
    {
        $condutor = Condutor::with('veiculos')->findOrFail($id);

        $this->resetForm();

        $this->editingId = $condutor->id;
        $this->name = $condutor->nome;
        $this->cpf = $condutor->cpf;
        $this->category = $condutor->tipo;
        $this->accessValidity = $condutor->validade_acesso ?? 'Indeterminado';
        $this->status = $condutor->ativo ? 'ativo' : 'inativo';

        $especializacao = match ($condutor->tipo) {
            'professor' => CondutorProfessor::where(
                'condutor_id', $condutor->id
            )->first(),

            'funcionario' => CondutorFuncionario::where(
                'condutor_id', $condutor->id
            )->first(),

            'prestador' => CondutorPrestador::where(
                'condutor_id', $condutor->id
            )->first(),

            default => null,
        };

        $this->code = $especializacao?->codigo_acesso ?? '';

        $this->department = match ($condutor->tipo) {
            'professor' => $especializacao?->departamento ?? '',
            'funcionario' => $especializacao?->setor ?? '',
            'prestador' => $especializacao?->empresa ?? '',
            default => '',
        };

        $this->vehicles = $condutor->veiculos->map(fn ($veiculo) => [
            'id' => $veiculo->id,
            'plate' => $veiculo->placa,
            'model' => $veiculo->modelo ?? '',
            'color' => $veiculo->cor ?? '',
        ])->values()->all();

        if (empty($this->vehicles)) {
            $this->vehicles = [
                ['id' => null, 'plate' => '', 'model' => '', 'color' => ''],
            ];
        }
        $this->showFormModal = true;
    }

    public function closeFormModal(): void
    {
        $this->showFormModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $this->cpf = trim($this->cpf);
        $this->code = strtoupper(trim($this->code));

        foreach ($this->vehicles as $index => $vehicle) {
            $this->vehicles[$index]['plate'] = strtoupper(
                trim($vehicle['plate'] ?? '')
            );
        }

        $this->validate([
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'cpf' => [
                'required',
                'string',
                'max:14',
                Rule::unique('condutores', 'cpf')->ignore($this->editingId),
            ],
            'category' => [
                'required',
                Rule::in(['professor', 'funcionario', 'prestador']),
            ],
            'code' => ['required', 'string', 'min:3', 'max:20'],
            'department' => [
                Rule::requiredIf(
                    in_array($this->category, ['prestador'])
                ),
                'nullable',
                'string',
                'max:100',
            ],
            'accessValidity' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in(['ativo', 'inativo'])],
            'vehicles' => ['array'],
            'vehicles.*.plate' => ['required', 'string', 'min:7', 'max:10'],
            'vehicles.*.model' => ['required', 'string', 'max:50'],
            'vehicles.*.color' => ['required', 'string', 'max:30'],
        ], [
            'name.required' => 'O nome é obrigatório.',
            'name.min' => 'O nome deve ter pelo menos 3 caracteres.',
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.unique' => 'Este CPF já está cadastrado.',
            'code.required' => 'O código de acesso/matrícula é obrigatório.',
            'department.required' => 'Informe a empresa do prestador.',
            'vehicles.*.plate.min' => 'A placa deve ter pelo menos 7 caracteres.',
        ]);

        $tabelaCodigo = match ($this->category) {
            'professor' => 'condutores_professores',
            'funcionario' => 'condutores_funcionarios',
            'prestador' => 'condutores_prestadores',
        };

        $this->validate([
            'code' => [
                'required',
                'string',
                'min:3',
                'max:20',
                Rule::unique($tabelaCodigo, 'codigo_acesso')
                    ->ignore($this->editingId, 'condutor_id'),
            ],
        ], [
            'code.unique' => 'Este código já está cadastrado para essa categoria.',
        ]);

        $veiculosValidos = collect($this->vehicles)
            ->filter(fn ($vehicle) => trim($vehicle['plate'] ?? '') !== '')
            ->values();

        // Confere as placas, permitindo manter a placa do próprio veículo.
        foreach ($veiculosValidos as $vehicle) {
            $regraPlaca = Rule::unique('veiculos', 'placa');

            if (! empty($vehicle['id'])) {
                $regraPlaca->ignore($vehicle['id']);
            }

            validator(
    ['placa' => $vehicle['plate']],
    ['placa' => [$regraPlaca]]
)->validate();
        }

        DB::transaction(function () use ($veiculosValidos) {
            $categoriaAnterior = null;

            if ($this->editingId) {
                $condutor = Condutor::with('veiculos')->findOrFail(
                    $this->editingId
                );

                $categoriaAnterior = $condutor->tipo;
            } else {
                $condutor = new Condutor;
            }

            $condutor->nome = trim($this->name);
            $condutor->cpf = trim($this->cpf);
            $condutor->tipo = $this->category;
            $condutor->validade_acesso = $this->accessValidity ?: 'Indeterminado';
            $condutor->ativo = $this->status === 'ativo';
            $condutor->save();

            // Se a categoria mudou, remove a especialização anterior.
            if (
                $categoriaAnterior !== null
                && $categoriaAnterior !== $this->category
            ) {
                CondutorProfessor::where('condutor_id', $condutor->id)->delete();
                CondutorFuncionario::where('condutor_id', $condutor->id)->delete();
                CondutorPrestador::where('condutor_id', $condutor->id)->delete();
            }

            // Salva os dados específicos da categoria selecionada.
            match ($this->category) {
                'professor' => CondutorProfessor::updateOrCreate(
                    ['condutor_id' => $condutor->id],
                    [
                        'codigo_acesso' => $this->code,
                        'departamento' => $this->department ?: null,
                    ]
                ),

                'funcionario' => CondutorFuncionario::updateOrCreate(
                    ['condutor_id' => $condutor->id],
                    [
                        'codigo_acesso' => $this->code,
                        'setor' => $this->department ?: null,
                    ]
                ),

                'prestador' => CondutorPrestador::updateOrCreate(
                    ['condutor_id' => $condutor->id],
                    [
                        'codigo_acesso' => $this->code,
                        'empresa' => $this->department,
                    ]
                ),
            };

            $idsEnviados = [];

            foreach ($veiculosValidos as $vehicle) {
                $dadosVeiculo = [
                    'condutor_id' => $condutor->id,
                    'placa' => strtoupper(trim($vehicle['plate'])),
                    'modelo' => trim($vehicle['model'] ?? '') ?: null,
                    'cor' => trim($vehicle['color'] ?? '') ?: null,
                    'ativo' => $condutor->ativo,
                ];

                if (! empty($vehicle['id'])) {
                    $veiculo = Veiculo::where('condutor_id', $condutor->id)
                        ->findOrFail($vehicle['id']);

                    $veiculo->update($dadosVeiculo);
                } else {
                    $veiculo = Veiculo::create($dadosVeiculo);
                }

                $idsEnviados[] = $veiculo->id;
            }

            // Veículos removidos do formulário recebem exclusão lógica.
            $condutor->veiculos()
                ->whereNotIn('id', $idsEnviados)
                ->delete();
        });

        $mensagem = $this->editingId
            ? 'Condutor atualizado com sucesso!'
            : 'Condutor cadastrado com sucesso!';

        $this->closeFormModal();
        $this->triggerToast($mensagem, 'success');
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $condutor = Condutor::findOrFail($id);

            $condutor->veiculos()->delete();

            CondutorProfessor::where('condutor_id', $id)->delete();
            CondutorFuncionario::where('condutor_id', $id)->delete();
            CondutorPrestador::where('condutor_id', $id)->delete();

            $condutor->delete();
        });

        $this->triggerToast('Condutor excluído com sucesso!', 'info');
    }

    public function toggleStatus(int $id): void
    {
        $condutor = Condutor::findOrFail($id);
        $condutor->ativo = ! $condutor->ativo;
        $condutor->save();

        $condutor->veiculos()->update([
            'ativo' => $condutor->ativo,
        ]);

        $this->triggerToast(
            'Status alterado para '.($condutor->ativo ? 'ativo' : 'inativo').'!',
            'info'
        );
    }

    private function resetForm(): void
    {
        $this->resetValidation();

        $this->editingId = null;
        $this->name = '';
        $this->cpf = '';
        $this->category = 'professor';
        $this->code = '';
        $this->department = '';
        $this->accessValidity = 'Indeterminado';
        $this->status = 'ativo';

        $this->vehicles = [
            ['id' => null, 'plate' => '', 'model' => '', 'color' => ''],
        ];
    }

    public function triggerToast(
        string $message,
        string $type = 'success'
    ): void {
        $this->toastMessage = $message;
        $this->toastType = $type;
    }

    public function clearToast(): void
    {
        $this->toastMessage = '';
    }

    #[Computed]
    public function filteredPeople(): array
    {
        $condutores = Condutor::with([
            'veiculos',
            'professor',
            'funcionario',
            'prestador',
        ])->get();

        return $condutores
            ->map(function ($condutor) {
                $especializacao = match ($condutor->tipo) {
                    'professor' => $condutor->professor,
                    'funcionario' => $condutor->funcionario,
                    'prestador' => $condutor->prestador,
                    default => null,
                };

                $departamento = match ($condutor->tipo) {
                    'professor' => $especializacao?->departamento ?? '',
                    'funcionario' => $especializacao?->setor ?? '',
                    'prestador' => $especializacao?->empresa ?? '',
                    default => '',
                };

                return [
                    'id' => $condutor->id,
                    'name' => $condutor->nome,
                    'cpf' => $condutor->cpf,
                    'category' => $condutor->tipo,
                    'code' => $especializacao?->codigo_acesso ?? '',
                    'department' => $departamento,
                    'vehicles' => $condutor->veiculos->map(fn ($vehicle) => [
                        'id' => $vehicle->id,
                        'plate' => $vehicle->placa,
                        'model' => $vehicle->modelo ?? '',
                        'color' => $vehicle->cor ?? '',
                    ])->all(),
                    'validity' => $condutor->validade_acesso ?? 'Indeterminado',
                    'status' => $condutor->ativo ? 'ativo' : 'inativo',
                ];
            })
            ->filter(function ($person) {
                $categoria = match ($this->activeTab) {
                    'professores' => 'professor',
                    'funcionarios' => 'funcionario',
                    'prestadores' => 'prestador',
                    default => null,
                };

                if ($categoria && $person['category'] !== $categoria) {
                    return false;
                }

                if (
                    $this->statusFilter !== 'todos'
                    && $person['status'] !== $this->statusFilter
                ) {
                    return false;
                }

                if (trim($this->search) !== '') {
                    $term = trim($this->search);

                    $found = str_contains(
                        mb_strtolower($person['name']),
                        mb_strtolower($term)
                    ) || str_contains(
                        mb_strtolower($person['department']),
                        mb_strtolower($term)
                    ) || str_contains(
                        mb_strtolower($person['code']),
                        mb_strtolower($term)
                    ) || str_contains(
                        mb_strtolower($person['cpf']),
                        mb_strtolower($term)
                    );

                    if (! $found) {
                        foreach ($person['vehicles'] as $vehicle) {
                            if (
                                str_contains(
                                    mb_strtolower($vehicle['plate']),
                                    mb_strtolower($term)
                                )
                            ) {
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
            })
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.admin.condutores', [
            'filteredPeople' => $this->filteredPeople,
        ]);
    }
}
