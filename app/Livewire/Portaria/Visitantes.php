<?php

namespace App\Livewire\Portaria;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Cadastro Rápido de Visitantes - Sentinela FATEC')]
class Visitantes extends Component
{
    public ?int $selectedId = null;

    // Propriedades do Formulário
    public string $cpf = '';
    public string $plate = '';
    public string $visitReason = '';
    public string $visitorName = '';
    public string $vehicleModel = '';
    public string $department = 'DSM - Coordenação DSM';
    public string $estimatedStay = '2 horas';

    // Propriedades de Feedback e Modais
    public string $toastMessage = '';
    public string $toastType = 'success';
    public bool $isEditing = false;
    public bool $showDeleteModal = false;
    public ?int $visitorToDeleteId = null;

    // Propriedades de Filtro da Interface
    public string $searchPlate = '';
    public string $statusFilter = '';

    protected function rules(): array
    {
        return [
            'cpf' => 'required|min:11',
            'plate' => 'required|min:7',
            'visitReason' => 'required|min:3',
        ];
    }

    protected function messages(): array
    {
        return [
            'cpf.required' => 'O CPF do visitante é obrigatório.',
            'plate.required' => 'A placa do carro é obrigatória.',
            'visitReason.required' => 'O motivo da visita é obrigatório.',
        ];
    }

    public function registerAndReleaseGate(): void
    {
        $this->validate();

        if ($this->isEditing && $this->selectedId) {
            DB::transaction(function () {
                DB::table('condutores')->where('id', $this->selectedId)->update([
                    'nome' => $this->visitorName ?: 'Visitante Identificado',
                    'cpf' => $this->cpf,
                    'updated_at' => now(),
                ]);

                DB::table('condutores_visitantes')->where('condutor_id', $this->selectedId)->update([
                    'motivo_visita' => $this->visitReason,
                    'setor_destino' => $this->department,
                    'permanencia_estimada' => $this->estimatedStay,
                    'updated_at' => now(),
                ]);

                DB::table('veiculos')->where('condutor_id', $this->selectedId)->update([
                    'placa' => strtoupper(trim($this->plate)),
                    'modelo' => $this->vehicleModel ?: 'Veículo de Passeio',
                    'updated_at' => now(),
                ]);

                DB::table('registros_acesso')->where('condutor_id', $this->selectedId)->update([
                    'placa_registro' => strtoupper(trim($this->plate)),
                    'updated_at' => now(),
                ]);
            });

            $this->triggerToast('Cadastro de visitante atualizado com sucesso!', 'success');
            $this->resetForm();
        } else {
            DB::transaction(function () {
                $condutorId = DB::table('condutores')->insertGetId([
                    'nome' => $this->visitorName ?: 'Visitante Identificado',
                    'cpf' => $this->cpf,
                    'tipo' => 'visitante',
                    'ativo' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('condutores_visitantes')->insert([
                    'condutor_id' => $condutorId,
                    'motivo_visita' => $this->visitReason,
                    'setor_destino' => $this->department,
                    'permanencia_estimada' => $this->estimatedStay,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $veiculoId = DB::table('veiculos')->insertGetId([
                    'condutor_id' => $condutorId,
                    'placa' => strtoupper(trim($this->plate)),
                    'modelo' => $this->vehicleModel ?: 'Veículo de Passeio',
                    'cor' => 'Não informada',
                    'ativo' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('registros_acesso')->insert([
                    'veiculo_id' => $veiculoId,
                    'condutor_id' => $condutorId,
                    'tipo_ocupante' => 'condutor',
                    'placa_registro' => strtoupper(trim($this->plate)),
                    'cancela' => 'Cancela 01',
                    'data_hora_entrada' => now(),
                    'operador_id' => Auth::id() ?? 1,
                    'status' => 'pendente',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

            $this->resetForm();
            $this->triggerToast('Visitante cadastrado com sucesso! Cancela 01 liberada para entrada.', 'success');
        }
    }

    public function editVisitor(int $id): void
    {
        $visitor = DB::table('condutores')
            ->select(
                'condutores.id',
                'condutores.nome',
                'condutores.cpf',
                'veiculos.placa as placa_veiculo',
                'veiculos.modelo as modelo_veiculo',
                'condutores_visitantes.motivo_visita as destino_motivo',
                'condutores_visitantes.setor_destino',
                'condutores_visitantes.permanencia_estimada'
            )
            ->leftJoin('condutores_visitantes', 'condutores.id', '=', 'condutores_visitantes.condutor_id')
            ->leftJoin('veiculos', 'condutores.id', '=', 'veiculos.condutor_id')
            ->where('condutores.id', $id)
            ->first();

        if ($visitor) {
            $this->selectedId = $visitor->id;
            $this->cpf = $visitor->cpf ?? '';
            $this->plate = $visitor->placa_veiculo ?? '';
            $this->visitReason = $visitor->destino_motivo ?? '';
            $this->visitorName = $visitor->nome ?? '';
            $this->vehicleModel = $visitor->modelo_veiculo ?? '';
            $this->department = $visitor->setor_destino ?? 'DSM - Coordenação DSM';
            $this->estimatedStay = $visitor->permanencia_estimada ?? '2 horas';
            $this->isEditing = true;
        }
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->visitorToDeleteId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteVisitor(): void
    {
        if ($this->visitorToDeleteId) {
            DB::transaction(function () {
                DB::table('registros_acesso')->where('condutor_id', $this->visitorToDeleteId)->delete();
                DB::table('veiculos')->where('condutor_id', $this->visitorToDeleteId)->delete();
                DB::table('condutores_visitantes')->where('condutor_id', $this->visitorToDeleteId)->delete();
                DB::table('condutores')->where('id', $this->visitorToDeleteId)->delete();
            });

            $this->triggerToast('Registro de visitante excluído com sucesso.', 'info');
        }

        $this->showDeleteModal = false;
        $this->visitorToDeleteId = null;
    }

    public function markVisitorExit(int $id): void
    {
        $registro = DB::table('registros_acesso')
            ->where('condutor_id', $id)
            ->whereNull('data_hora_saida')
            ->orderBy('id', 'desc')
            ->first();

        if ($registro) {
            DB::table('registros_acesso')
                ->where('id', $registro->id)
                ->update([
                    'status' => 'finalizado',
                    'data_hora_saida' => now(),
                    'updated_at' => now(),
                ]);

            $this->triggerToast("Saída registrada para o visitante!", 'info');
        }
    }

    public function resetForm(): void
    {
        $this->reset(['cpf', 'plate', 'visitReason', 'visitorName', 'vehicleModel', 'selectedId', 'isEditing']);
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

    public function render()
    {
        $query = DB::table('condutores')
            ->select(
                'condutores.id',
                'condutores.nome',
                'condutores.cpf',
                'condutores.created_at',
                'veiculos.placa as placa_veiculo',
                'veiculos.modelo as modelo_veiculo',
                'condutores_visitantes.motivo_visita as destino_motivo',
                'registros_acesso.status as raw_status',
                'registros_acesso.data_hora_entrada',
                'registros_acesso.data_hora_saida'
            )
            ->join('condutores_visitantes', 'condutores.id', '=', 'condutores_visitantes.condutor_id')
            ->leftJoin('veiculos', 'condutores.id', '=', 'veiculos.condutor_id')
            ->leftJoin('registros_acesso', 'condutores.id', '=', 'registros_acesso.condutor_id')
            ->where('condutores.tipo', 'visitante');

        // Filtro de placa
        if (!empty($this->searchPlate)) {
            $query->where('veiculos.placa', 'like', '%' . strtoupper(trim($this->searchPlate)) . '%');
        }

        // Filtro de status direto no SQL (se for pendente, busca os que não têm data de saída)
        if (!empty($this->statusFilter) && $this->statusFilter !== 'todos') {
            if ($this->statusFilter === 'pendente') {
                $query->whereNull('registros_acesso.data_hora_saida');
            } else {
                $query->where('registros_acesso.status', $this->statusFilter);
            }
        }

        $recentVisitors = $query->orderBy('condutores.created_at', 'desc')
            ->get()
            ->map(function ($item) {
                $item->created_at = $item->created_at ? Carbon::parse($item->created_at) : null;
                $item->data_hora_entrada = $item->data_hora_entrada ? Carbon::parse($item->data_hora_entrada) : null;
                $item->data_hora_saida = $item->data_hora_saida ? Carbon::parse($item->data_hora_saida) : null;
                
                // Atribui 'ativo' se não tiver data de saída para renderizar os botões na Blade
                $item->status = is_null($item->data_hora_saida) ? 'ativo' : 'finalizado';

                return $item;
            });

        return view('livewire.portaria.visitantes', [
            'recentVisitors' => $recentVisitors,
        ]);
    }
}