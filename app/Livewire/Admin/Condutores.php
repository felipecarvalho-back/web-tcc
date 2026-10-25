<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\HasToast;
use App\Models\Condutor;
use App\Models\CondutorFuncionario;
use App\Models\CondutorPrestador;
use App\Models\CondutorProfessor;
use App\Models\RegistroAcesso;
use App\Models\Veiculo;
use App\Rules\CpfValido;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * @property-read LengthAwarePaginator $filteredPeople
 */
#[Layout('layouts.app')]
#[Title('Admin - Gestão de Condutores e Veículos')]
class Condutores extends Component
{
    use HasToast, WithPagination;

    public string $activeTab = 'professores';

    public string $search = '';

    public string $statusFilter = 'todos';

    public bool $showFormModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $cpf = '';

    public string $category = 'professor';

    public string $code = '';

    public string $department = 'DSM - Coordenação DSM';

    public string $accessValidity = 'Indeterminado';

    public string $status = 'ativo';

    /**
     * @var array<int, array{id: int|null, plate: string, model: string, color: string}>
     */
    public array $vehicles = [];

    public function mount(): void
    {
        $this->vehicles = [
            ['id' => null, 'plate' => '', 'model' => '', 'color' => ''],
        ];
    }

    public function updatedActiveTab(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        if ($this->editingId === null) {
            $this->department = $this->defaultDepartmentForCategory($this->category);
            $this->accessValidity = $this->category === 'prestador' ? 'Semestre Atual' : 'Indeterminado';
        }
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

        $this->department = $this->defaultDepartmentForCategory($this->category);
        $this->accessValidity = $this->category === 'prestador' ? 'Semestre Atual' : 'Indeterminado';

        $this->showFormModal = true;
    }

    public function openEditModal(int $id): void
    {
        $condutor = Condutor::with([
            'veiculos',
            'professor',
            'funcionario',
            'prestador',
        ])->findOrFail($id);

        $this->resetForm();

        $this->editingId = $condutor->id;
        $this->name = $condutor->nome;
        $this->cpf = $condutor->cpf;
        $this->category = $condutor->tipo;
        $this->accessValidity = $condutor->validade_acesso ?? 'Indeterminado';
        $this->status = $condutor->ativo ? 'ativo' : 'inativo';

        $especializacao = match ($condutor->tipo) {
            'professor' => $condutor->professor,
            'funcionario' => $condutor->funcionario,
            'prestador' => $condutor->prestador,
            default => null,
        };

        $this->code = Condutor::formatarCodigoAcesso($especializacao?->codigo_acesso ?? '');

        $this->department = match ($condutor->tipo) {
            'professor' => $condutor->professor?->departamento ?? '',
            'funcionario' => $condutor->funcionario?->setor ?? '',
            'prestador' => $condutor->prestador?->empresa ?? '',
            default => '',
        };

        $this->vehicles = $condutor->veiculos->map(function ($veiculo): array {
            /** @var Veiculo $veiculo */
            return [
                'id' => $veiculo->id,
                'plate' => $veiculo->placa,
                'model' => $veiculo->modelo ?? '',
                'color' => ($veiculo->cor && $veiculo->cor !== 'Não informada') ? $veiculo->cor : '',
            ];
        })->values()->all();

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
        $this->code = Condutor::formatarCodigoAcesso($this->code);

        foreach ($this->vehicles as $index => $vehicle) {
            $placaBruta = trim((string) ($vehicle['plate'] ?? ''));
            $this->vehicles[$index]['plate'] = $placaBruta !== ''
                ? RegistroAcesso::formatarPlaca($placaBruta)
                : '';
        }

        $tabelaCodigo = match ($this->category) {
            'funcionario' => 'condutores_funcionarios',
            'prestador' => 'condutores_prestadores',
            default => 'condutores_professores',
        };

        $rules = [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'cpf' => [
                'required',
                'string',
                'max:14',
                new CpfValido,
                Rule::unique('condutores', 'cpf')
                    ->ignore($this->editingId)
                    ->whereNull('deleted_at'),
            ],
            'category' => [
                'required',
                Rule::in(['professor', 'funcionario', 'prestador']),
            ],
            'code' => [
                'required',
                'string',
                'min:3',
                'max:20',
                Rule::unique($tabelaCodigo, 'codigo_acesso')
                    ->ignore($this->editingId, 'condutor_id'),
            ],
            'department' => [
                Rule::requiredIf($this->category === 'prestador'),
                'nullable',
                'string',
                'max:100',
            ],
            'accessValidity' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in(['ativo', 'inativo'])],
            'vehicles' => ['required', 'array', 'min:1'],
            'vehicles.*.plate' => [
                'required',
                'string',
                'min:7',
                'max:10',
                'distinct',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $partes = explode('.', $attribute);
                    $index = (int) ($partes[1] ?? 0);
                    $veiculoId = $this->vehicles[$index]['id'] ?? null;

                    $existe = Veiculo::query()
                        ->where('placa', (string) $value)
                        ->when($veiculoId, fn (Builder $q) => $q->where('id', '!=', $veiculoId))
                        ->exists();

                    if ($existe) {
                        $fail('Esta placa já pertence a outro veículo ativo.');
                    }
                },
            ],
            'vehicles.*.model' => ['required', 'string', 'max:50'],
            'vehicles.*.color' => ['nullable', 'string', 'max:30'],
        ];

        $this->validate($rules, [
            'name.required' => 'O nome do condutor é obrigatório.',
            'name.min' => 'O nome deve ter pelo menos 3 caracteres.',
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.unique' => 'Este CPF já está cadastrado para outro condutor.',
            'code.required' => 'O código de acesso/matrícula é obrigatório.',
            'code.unique' => 'Este código já está cadastrado para essa categoria.',
            'department.required' => 'Informe a empresa do prestador de serviço.',
            'vehicles.*.plate.required' => 'Informe a placa do veículo.',
            'vehicles.*.plate.min' => 'A placa deve ter pelo menos 7 caracteres.',
            'vehicles.*.plate.distinct' => 'Você informou placas repetidas para o mesmo condutor.',
            'vehicles.*.plate.unique' => 'Esta placa já pertence a outro veículo ativo.',
            'vehicles.*.model.required' => 'Informe o modelo do veículo.',
        ]);

        $veiculosValidos = collect($this->vehicles)
            ->filter(fn (array $vehicle): bool => trim($vehicle['plate'] ?? '') !== '')
            ->values();

        $wasEditing = $this->editingId !== null;

        DB::transaction(function () use ($veiculosValidos): void {
            $categoriaAnterior = null;

            if ($this->editingId) {
                $condutor = Condutor::with('veiculos')->findOrFail($this->editingId);
                $categoriaAnterior = $condutor->tipo;
                $condutor->update([
                    'nome' => trim($this->name),
                    'cpf' => trim($this->cpf),
                    'tipo' => $this->category,
                    'validade_acesso' => $this->accessValidity ?: 'Indeterminado',
                    'ativo' => $this->status === 'ativo',
                ]);
            } else {
                $condutor = Condutor::withTrashed()->updateOrCreate(
                    ['cpf' => trim($this->cpf)],
                    [
                        'nome' => trim($this->name),
                        'tipo' => $this->category,
                        'validade_acesso' => $this->accessValidity ?: 'Indeterminado',
                        'ativo' => $this->status === 'ativo',
                        'deleted_at' => null,
                    ]
                );
            }

            if ($categoriaAnterior !== null && $categoriaAnterior !== $this->category) {
                CondutorProfessor::where('condutor_id', $condutor->id)->delete();
                CondutorFuncionario::where('condutor_id', $condutor->id)->delete();
                CondutorPrestador::where('condutor_id', $condutor->id)->delete();
            }

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
                $placaFormatada = RegistroAcesso::formatarPlaca($vehicle['plate']);
                $corFormatada = trim((string) ($vehicle['color'] ?? '')) !== ''
                    ? trim((string) $vehicle['color'])
                    : 'Não informada';

                $dadosVeiculo = [
                    'condutor_id' => $condutor->id,
                    'placa' => $placaFormatada,
                    'modelo' => trim((string) ($vehicle['model'] ?? '')) ?: 'Veículo de Passeio',
                    'cor' => $corFormatada,
                    'ativo' => $condutor->ativo,
                    'deleted_at' => null,
                ];

                if (! empty($vehicle['id'])) {
                    $veiculo = Veiculo::where('condutor_id', $condutor->id)
                        ->findOrFail($vehicle['id']);

                    $veiculo->update($dadosVeiculo);
                } else {
                    $veiculo = Veiculo::withTrashed()->updateOrCreate(
                        ['placa' => $placaFormatada],
                        $dadosVeiculo
                    );
                }

                $idsEnviados[] = $veiculo->id;
            }

            $condutor->veiculos()
                ->whereNotIn('id', $idsEnviados)
                ->delete();
        });

        $mensagem = $wasEditing
            ? 'Condutor atualizado com sucesso!'
            : 'Condutor cadastrado com sucesso!';

        $this->closeFormModal();
        unset($this->filteredPeople);
        $this->triggerToast($mensagem, 'success');
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $condutor = Condutor::findOrFail($id);

            $condutor->veiculos()->delete();

            CondutorProfessor::where('condutor_id', $id)->delete();
            CondutorFuncionario::where('condutor_id', $id)->delete();
            CondutorPrestador::where('condutor_id', $id)->delete();

            $condutor->delete();
        });

        unset($this->filteredPeople);
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

        unset($this->filteredPeople);
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
        $this->department = 'DSM - Coordenação DSM';
        $this->accessValidity = 'Indeterminado';
        $this->status = 'ativo';

        $this->vehicles = [
            ['id' => null, 'plate' => '', 'model' => '', 'color' => ''],
        ];
    }

    private function defaultDepartmentForCategory(string $category): string
    {
        return match ($category) {
            'funcionario' => 'Secretaria Acadêmica',
            'prestador' => 'Manutenção Predial / Infraestrutura',
            default => 'DSM - Coordenação DSM',
        };
    }

    /**
     * Consulta paginada e filtrada diretamente no banco de dados (SQL).
     */
    #[Computed]
    public function filteredPeople(): LengthAwarePaginator
    {
        $categoria = match ($this->activeTab) {
            'funcionarios' => 'funcionario',
            'prestadores' => 'prestador',
            default => 'professor',
        };

        $query = Condutor::query()
            ->select(['id', 'nome', 'cpf', 'tipo', 'validade_acesso', 'ativo'])
            ->with([
                'veiculos:id,condutor_id,placa,modelo,cor',
                'professor:condutor_id,codigo_acesso,departamento',
                'funcionario:condutor_id,codigo_acesso,setor',
                'prestador:condutor_id,codigo_acesso,empresa',
            ])
            ->where('tipo', $categoria);

        if ($this->statusFilter === 'ativo') {
            $query->where('ativo', true);
        } elseif ($this->statusFilter === 'inativo') {
            $query->where('ativo', false);
        }

        $termo = trim($this->search);
        if ($termo !== '') {
            $termoUpper = strtoupper($termo);
            $semHifen = str_replace('-', '', $termoUpper);

            $query->where(function (Builder $q) use ($termo, $termoUpper, $semHifen): void {
                $q->whereLike('nome', "%{$termo}%")
                    ->orWhereLike('cpf', "%{$termo}%")
                    ->orWhereHas('veiculos', function (Builder $vq) use ($termoUpper, $semHifen): void {
                        $vq->whereLike('placa', "%{$termoUpper}%")
                            ->orWhereLike('placa', "%{$semHifen}%");
                    })
                    ->orWhereHas('professor', function (Builder $pq) use ($termo, $termoUpper): void {
                        $pq->whereLike('codigo_acesso', "%{$termoUpper}%")
                            ->orWhereLike('departamento', "%{$termo}%");
                    })
                    ->orWhereHas('funcionario', function (Builder $fq) use ($termo, $termoUpper): void {
                        $fq->whereLike('codigo_acesso', "%{$termoUpper}%")
                            ->orWhereLike('setor', "%{$termo}%");
                    })
                    ->orWhereHas('prestador', function (Builder $prq) use ($termo, $termoUpper): void {
                        $prq->whereLike('codigo_acesso', "%{$termoUpper}%")
                            ->orWhereLike('empresa', "%{$termo}%");
                    });
            });
        }

        $query->orderByDesc('id');

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate(10);

        return $paginator->through(function ($condutor): array {
            /** @var Condutor $condutor */
            $especializacao = match ($condutor->tipo) {
                'professor' => $condutor->professor,
                'funcionario' => $condutor->funcionario,
                'prestador' => $condutor->prestador,
                default => null,
            };

            $departamento = match ($condutor->tipo) {
                'professor' => $condutor->professor?->departamento ?? 'Não informado',
                'funcionario' => $condutor->funcionario?->setor ?? 'Não informado',
                'prestador' => $condutor->prestador?->empresa ?? 'Não informada',
                default => '',
            };

            return [
                'id' => $condutor->id,
                'name' => $condutor->nome,
                'cpf' => $condutor->cpf,
                'category' => $condutor->tipo,
                'code' => $condutor->codigoAcesso() ?? '--',
                'department' => $departamento,
                'vehicles' => $condutor->veiculos->map(function ($vehicle): array {
                    /** @var Veiculo $vehicle */
                    return [
                        'id' => $vehicle->id,
                        'plate' => RegistroAcesso::formatarPlaca($vehicle->placa),
                        'model' => $vehicle->modelo ?? '',
                        'color' => $vehicle->cor ?? '',
                    ];
                })->all(),
                'validity' => $condutor->validade_acesso ?? 'Indeterminado',
                'status' => $condutor->ativo ? 'ativo' : 'inativo',
            ];
        });
    }

    public function render(): View
    {
        return view('livewire.admin.condutores', [
            'filteredPeople' => $this->filteredPeople,
        ]);
    }
}
