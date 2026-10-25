<?php

namespace App\Livewire\Portaria;

use App\Enums\StatusAcesso;
use App\Enums\TipoOcupante;
use App\Models\Condutor;
use App\Models\CondutorVisitante;
use App\Models\RegistroAcesso;
use App\Models\Veiculo;
use App\Rules\CpfValido;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * @property-read LengthAwarePaginator $filteredVisitors
 */
#[Layout('layouts.app')]
#[Title('Gestão e Controle de Visitantes - Sentinela FATEC')]
class Visitantes extends Component
{
    use WithPagination;

    public ?int $selectedId = null;

    public ?int $selectedCondutorId = null;

    // Propriedades do Formulário (Modal)
    public bool $showFormModal = false;

    public bool $isEditing = false;

    public string $cpf = '';

    public string $plate = '';

    public string $visitReason = '';

    public string $visitorName = '';

    public string $vehicleModel = '';

    public string $vehicleColor = '';

    public string $department = 'DSM - Coordenação DSM';

    public string $estimatedStay = '2 horas';

    /**
     * Dados do visitante recorrente encontrado pelo CPF para conferência visual do guarda.
     *
     * @var array<string, string>|null
     */
    public ?array $recurringVisitor = null;

    // Propriedades de Feedback
    public string $toastMessage = '';

    public string $toastType = 'success';

    // Filtros da Consulta
    public string $searchQuery = '';

    public string $dateFilter = 'hoje';

    public string $statusFilter = 'todos';

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $cpfRules = ['required', 'string', new CpfValido];

        if ($this->isEditing && $this->selectedCondutorId) {
            $cpfRules[] = Rule::unique('condutores', 'cpf')->ignore($this->selectedCondutorId);
        }

        return [
            'cpf' => $cpfRules,
            'plate' => ['required', 'string', 'min:7', 'max:10'],
            'visitReason' => ['required', 'string', 'min:3', 'max:255'],
            'visitorName' => ['nullable', 'string', 'max:150'],
            'vehicleModel' => ['nullable', 'string', 'max:100'],
            'vehicleColor' => ['nullable', 'string', 'max:40'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'cpf.required' => 'O CPF do visitante é obrigatório.',
            'cpf.unique' => 'Este CPF já pertence a outro condutor cadastrado.',
            'plate.required' => 'A placa do carro é obrigatória.',
            'plate.min' => 'A placa deve ter no mínimo 7 caracteres.',
            'plate.max' => 'A placa deve ter no máximo 10 caracteres.',
            'visitReason.required' => 'O motivo da visita é obrigatório.',
            'visitReason.min' => 'Informe um motivo com pelo menos 3 caracteres.',
        ];
    }

    /**
     * Abre o modal para novo registro de entrada de visitante.
     */
    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    /**
     * Fecha o modal de cadastro/edição e limpa erros de validação.
     */
    public function closeFormModal(): void
    {
        $this->showFormModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    /**
     * Busca automaticamente os dados do visitante e do último veículo quando o guarda digita os 11 dígitos do CPF.
     */
    public function updatedCpf(string $value): void
    {
        $cpf = trim($value);

        if (strlen($cpf) !== 14) {
            $this->recurringVisitor = null;

            return;
        }

        if ($this->isEditing) {
            return;
        }

        $condutor = Condutor::query()
            ->with([
                'visitante:condutor_id,motivo_visita,setor_destino,permanencia_estimada',
                'veiculos' => fn ($q) => $q->select(['id', 'condutor_id', 'placa', 'modelo', 'cor'])->latest('updated_at'),
            ])
            ->where('cpf', $cpf)
            ->first(['id', 'nome', 'cpf']);

        if (! $condutor) {
            $this->recurringVisitor = null;

            return;
        }

        $ultimoVeiculo = $condutor->veiculos->first();

        $this->visitorName = $condutor->nome;
        if ($ultimoVeiculo) {
            $this->plate = $ultimoVeiculo->placa;
            $this->vehicleModel = $ultimoVeiculo->modelo ?? '';
            $this->vehicleColor = ($ultimoVeiculo->cor && $ultimoVeiculo->cor !== 'Não informada') ? $ultimoVeiculo->cor : '';
        }
        if ($condutor->visitante?->setor_destino) {
            $this->department = $condutor->visitante->setor_destino;
        }

        $this->recurringVisitor = [
            'nome' => $condutor->nome,
            'cpf' => $condutor->cpf,
            'placa' => $ultimoVeiculo?->placa ?? 'Sem veículo anterior',
            'modelo' => $ultimoVeiculo?->modelo ?? 'Não informado',
            'cor' => $ultimoVeiculo?->cor ?? 'Não informada',
            'ultimo_setor' => $condutor->visitante?->setor_destino ?? 'Não informado',
        ];
    }

    /**
     * Persiste ou atualiza o condutor visitante, seus dados de visita e o veículo utilizado.
     *
     * @return array{condutor: Condutor, veiculo: Veiculo}
     */
    private function persistVisitorAndVehicle(string $nomeVisitante, string $modeloVeiculo, string $corVeiculo): array
    {
        if ($this->isEditing && $this->selectedCondutorId) {
            $condutor = Condutor::findOrFail($this->selectedCondutorId);
            $condutor->update([
                'nome' => $nomeVisitante,
                'cpf' => $this->cpf,
            ]);
        } else {
            $condutor = Condutor::withTrashed()->updateOrCreate(
                ['cpf' => $this->cpf],
                [
                    'nome' => $nomeVisitante,
                    'tipo' => 'visitante',
                    'ativo' => true,
                    'deleted_at' => null,
                ]
            );
        }

        CondutorVisitante::updateOrCreate(
            ['condutor_id' => $condutor->id],
            [
                'motivo_visita' => $this->visitReason,
                'setor_destino' => $this->department,
                'permanencia_estimada' => $this->estimatedStay,
            ]
        );

        $veiculo = Veiculo::withTrashed()->updateOrCreate(
            ['placa' => $this->plate],
            [
                'condutor_id' => $condutor->id,
                'modelo' => $modeloVeiculo,
                'cor' => $corVeiculo,
                'ativo' => true,
                'deleted_at' => null,
            ]
        );

        return [
            'condutor' => $condutor,
            'veiculo' => $veiculo,
        ];
    }

    /**
     * Salva o cadastro/edição do visitante e libera a cancela registrando a passagem.
     */
    public function registerAndReleaseGate(): void
    {
        $this->cpf = trim($this->cpf);
        $this->plate = strtoupper(trim($this->plate));
        $this->validate();

        $nomeVisitante = trim($this->visitorName) !== '' ? trim($this->visitorName) : 'Visitante Identificado';
        $modeloVeiculo = trim($this->vehicleModel) !== '' ? trim($this->vehicleModel) : 'Veículo de Passeio';
        $corVeiculo = trim($this->vehicleColor) !== '' ? trim($this->vehicleColor) : 'Não informada';

        if ($this->isEditing && $this->selectedId) {
            DB::transaction(function () use ($nomeVisitante, $modeloVeiculo, $corVeiculo): void {
                $registro = RegistroAcesso::findOrFail($this->selectedId);
                $this->selectedCondutorId = $this->selectedCondutorId ?? $registro->condutor_id;

                $dados = $this->persistVisitorAndVehicle($nomeVisitante, $modeloVeiculo, $corVeiculo);

                $registro->update([
                    'condutor_id' => $dados['condutor']->id,
                    'veiculo_id' => $dados['veiculo']->id,
                    'placa_registro' => $this->plate,
                    'justificativa' => "Visita ({$this->department}): {$this->visitReason}",
                ]);
            });

            $this->closeFormModal();
            unset($this->filteredVisitors);
            $this->triggerToast('Registro de visita atualizado com sucesso!', 'success');

            return;
        }

        DB::transaction(function () use ($nomeVisitante, $modeloVeiculo, $corVeiculo): void {
            $dados = $this->persistVisitorAndVehicle($nomeVisitante, $modeloVeiculo, $corVeiculo);

            RegistroAcesso::create([
                'veiculo_id' => $dados['veiculo']->id,
                'condutor_id' => $dados['condutor']->id,
                'tipo_ocupante' => TipoOcupante::Condutor,
                'placa_registro' => $this->plate,
                'cancela' => 'Cancela 01 (Principal)',
                'data_hora_entrada' => Carbon::now(),
                'operador_id' => Auth::id(),
                'status' => StatusAcesso::EmPatio,
                'justificativa' => "Visita ({$this->department}): {$this->visitReason}",
            ]);
        });

        $this->closeFormModal();
        unset($this->filteredVisitors);
        $this->triggerToast('Entrada registrada! Cancela 01 liberada para o visitante.', 'success');
    }

    /**
     * Carrega os dados da passagem no modal para edição.
     */
    public function editVisitor(int $registroId): void
    {
        $registro = RegistroAcesso::with(['condutor.visitante', 'veiculo'])->find($registroId);

        if (! $registro || ! $registro->condutor) {
            return;
        }

        $condutor = $registro->condutor;
        $veiculo = $registro->veiculo;

        $this->selectedId = $registro->id;
        $this->selectedCondutorId = $condutor->id;
        $this->cpf = $condutor->cpf;
        $this->plate = $registro->placa_registro ?: ($veiculo?->placa ?? '');
        $this->visitReason = $condutor->visitante?->motivo_visita ?? '';
        $this->visitorName = $condutor->nome;
        $this->vehicleModel = $veiculo?->modelo ?? '';
        $this->vehicleColor = ($veiculo?->cor && $veiculo->cor !== 'Não informada') ? $veiculo->cor : '';
        $this->department = $condutor->visitante?->setor_destino ?? 'DSM - Coordenação DSM';
        $this->estimatedStay = $condutor->visitante?->permanencia_estimada ?? '2 horas';
        $this->recurringVisitor = null;
        $this->isEditing = true;
        $this->showFormModal = true;
    }

    public function updatedSearchQuery(): void
    {
        $this->resetPage();
    }

    public function updatedDateFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['searchQuery', 'statusFilter', 'dateFilter']);
        $this->resetPage();
        unset($this->filteredVisitors);
    }

    public function resetForm(): void
    {
        $this->reset([
            'cpf',
            'plate',
            'visitReason',
            'visitorName',
            'vehicleModel',
            'vehicleColor',
            'selectedId',
            'selectedCondutorId',
            'recurringVisitor',
            'isEditing',
        ]);
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

    /**
     * Consulta paginada e otimizada de visitantes com seleção explícita de colunas e Eager Loading.
     */
    #[Computed]
    public function filteredVisitors(): LengthAwarePaginator
    {
        $query = RegistroAcesso::query()
            ->select([
                'id',
                'veiculo_id',
                'condutor_id',
                'placa_registro',
                'placa_corrigida',
                'data_hora_entrada',
                'data_hora_saida',
                'status',
                'justificativa',
            ])
            ->with([
                'condutor:id,nome,cpf,tipo',
                'condutor.visitante:condutor_id,motivo_visita,setor_destino,permanencia_estimada',
                'veiculo:id,condutor_id,placa',
            ])
            ->whereHas('condutor', fn (Builder $q) => $q->where('tipo', 'visitante'));

        if ($this->dateFilter !== 'todos') {
            $query->filtrarData($this->dateFilter);
        }

        if ($this->statusFilter === 'ativo') {
            $query->whereNull('data_hora_saida');
        } elseif ($this->statusFilter === 'finalizado') {
            $query->whereNotNull('data_hora_saida');
        }

        $termo = trim($this->searchQuery);
        if ($termo !== '') {
            $termoUpper = strtoupper($termo);
            $termoDigitos = preg_replace('/\D/', '', $termo) ?? '';

            $query->where(function (Builder $q) use ($termo, $termoUpper, $termoDigitos): void {
                $q->whereLike('placa_registro', "%{$termoUpper}%")
                    ->orWhereHas('condutor', function (Builder $cq) use ($termo, $termoDigitos): void {
                        $cq->whereLike('nome', "%{$termo}%")
                            ->orWhereLike('cpf', "%{$termo}%");

                        if ($termoDigitos !== '') {
                            $cq->orWhereLike('cpf', "%{$termoDigitos}%");
                        }
                    });
            });
        }

        return $query
            ->latest('data_hora_entrada')
            ->latest('id')
            ->paginate(10)
            ->through(function ($registro): object {
                /** @var RegistroAcesso $registro */
                return $this->mapVisitRecord($registro);
            });
    }

    /**
     * Converte um RegistroAcesso em objeto estruturado para a view.
     */
    private function mapVisitRecord(RegistroAcesso $registro): object
    {
        $condutor = $registro->condutor;
        $veiculo = $registro->veiculo;
        $visitante = $condutor?->visitante;

        $motivo = $visitante?->motivo_visita ?? 'Visita ao Campus';
        if ($registro->justificativa && str_contains($registro->justificativa, '): ')) {
            $partes = explode('): ', $registro->justificativa, 2);
            if (! empty($partes[1])) {
                $motivo = $partes[1];
            }
        }

        return (object) [
            'id' => $registro->id,
            'condutor_id' => $condutor?->id,
            'nome' => $condutor?->nome ?? 'Visitante Identificado',
            'cpf' => $condutor?->cpf ?? '',
            'placa_veiculo' => $registro->placa_corrigida ?: ($registro->placa_registro ?: ($veiculo?->placa ?? RegistroAcesso::PLACA_NAO_IDENTIFICADA)),
            'setor_destino' => $visitante?->setor_destino ?? 'Campus FATEC',
            'permanencia_estimada' => $visitante?->permanencia_estimada ?? '2 horas',
            'destino_motivo' => $motivo,
            'created_at' => $registro->data_hora_entrada ?? Carbon::now(),
            'data_hora_entrada' => $registro->data_hora_entrada,
            'data_hora_saida' => $registro->data_hora_saida,
            'status' => $registro->data_hora_saida === null ? 'ativo' : 'finalizado',
        ];
    }

    public function render()
    {
        return view('livewire.portaria.visitantes', [
            'filteredVisitors' => $this->filteredVisitors,
        ]);
    }
}
