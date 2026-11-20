<?php

namespace App\Actions\Portaria;

use App\Enums\StatusAcesso;
use App\Enums\TipoCondutor;
use App\Enums\TipoOcupante;
use App\Models\Condutor;
use App\Models\CondutorVisitante;
use App\Models\RegistroAcesso;
use App\Models\Veiculo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrarVisitanteAction
{
    /**
     * Registra uma nova entrada de visitante ou atualiza um registro existente com proteção de integridade.
     *
     * @param  array{
     *     is_editing: bool,
     *     selected_id: int|null,
     *     selected_condutor_id: int|null,
     *     cpf: string,
     *     plate: string,
     *     visit_reason: string,
     *     visitor_name: string,
     *     vehicle_model: string,
     *     vehicle_color: string,
     *     department: string,
     *     estimated_stay: string,
     *     operador_id: int|null
     * }  $dados
     */
    public function execute(array $dados): RegistroAcesso
    {
        $nomeVisitante = trim($dados['visitor_name']) !== '' ? trim($dados['visitor_name']) : 'Visitante Identificado';
        $modeloVeiculo = trim($dados['vehicle_model']) !== '' ? trim($dados['vehicle_model']) : 'Veículo de Passeio';
        $corVeiculo = trim($dados['vehicle_color']) !== '' ? trim($dados['vehicle_color']) : 'Não informada';

        return DB::transaction(function () use ($dados, $nomeVisitante, $modeloVeiculo, $corVeiculo): RegistroAcesso {
            $condutorIdEdicao = $dados['selected_condutor_id'];
            $registroExistente = null;

            if ($dados['is_editing'] && $dados['selected_id']) {
                $registroExistente = RegistroAcesso::findOrFail($dados['selected_id']);
                $condutorIdEdicao = $condutorIdEdicao ?? $registroExistente->condutor_id;
            }

            $condutor = $this->resolverCondutor(
                $dados['is_editing'],
                $condutorIdEdicao,
                $dados['cpf'],
                $nomeVisitante
            );

            CondutorVisitante::updateOrCreate(
                ['condutor_id' => $condutor->id],
                [
                    'motivo_visita' => $dados['visit_reason'],
                    'setor_destino' => $dados['department'],
                    'permanencia_estimada' => $dados['estimated_stay'],
                ]
            );

            $veiculo = $this->resolverVeiculo(
                $condutor,
                $dados['plate'],
                $modeloVeiculo,
                $corVeiculo
            );

            if ($registroExistente) {
                $registroExistente->update([
                    'condutor_id' => $condutor->id,
                    'veiculo_id' => $veiculo->id,
                    'placa_registro' => $dados['plate'],
                    'justificativa' => "Visita ({$dados['department']}): {$dados['visit_reason']}",
                ]);

                return $registroExistente;
            }

            return RegistroAcesso::create([
                'veiculo_id' => $veiculo->id,
                'condutor_id' => $condutor->id,
                'tipo_ocupante' => TipoOcupante::Condutor,
                'placa_registro' => $dados['plate'],
                'cancela' => 'Cancela 01 (Principal)',
                'data_hora_entrada' => Carbon::now(),
                'operador_id' => $dados['operador_id'],
                'status' => StatusAcesso::EmPatio,
                'justificativa' => "Visita ({$dados['department']}): {$dados['visit_reason']}",
            ]);
        });
    }

    /**
     * Resolve ou cria o condutor visitante sem sobrescrever condutores institucionais existentes.
     */
    private function resolverCondutor(bool $isEditing, ?int $selectedCondutorId, string $cpf, string $nome): Condutor
    {
        if ($isEditing && $selectedCondutorId) {
            $condutor = Condutor::findOrFail($selectedCondutorId);
            $condutor->update([
                'nome' => $nome,
                'cpf' => $cpf,
            ]);

            return $condutor;
        }

        $existente = Condutor::withTrashed()->where('cpf', $cpf)->first();

        if ($existente && ! $existente->trashed() && $existente->tipo?->isInstitutional()) {
            throw ValidationException::withMessages([
                'cpf' => 'Este CPF já pertence a um condutor institucional cadastrado (professor, funcionário ou prestador). Utilize a liberação por código no Monitoramento.',
            ]);
        }

        if ($existente) {
            $existente->restore();
            $existente->update([
                'nome' => $nome,
                'tipo' => TipoCondutor::Visitante,
                'ativo' => true,
            ]);

            return $existente;
        }

        return Condutor::create([
            'cpf' => $cpf,
            'nome' => $nome,
            'tipo' => TipoCondutor::Visitante,
            'ativo' => true,
        ]);
    }

    /**
     * Resolve ou cria o veículo do visitante sem transferir veículos ativos de outros condutores.
     */
    private function resolverVeiculo(Condutor $condutor, string $placa, string $modelo, string $cor): Veiculo
    {
        $veiculoExistente = Veiculo::withTrashed()
            ->with('condutor:id,nome,tipo')
            ->where('placa', $placa)
            ->first();

        if (
            $veiculoExistente
            && ! $veiculoExistente->trashed()
            && $veiculoExistente->condutor_id !== $condutor->id
        ) {
            throw ValidationException::withMessages([
                'plate' => 'Esta placa já está vinculada a outro condutor ativo no sistema. Verifique a placa informada.',
            ]);
        }

        if ($veiculoExistente) {
            $veiculoExistente->restore();
            $veiculoExistente->update([
                'condutor_id' => $condutor->id,
                'modelo' => $modelo,
                'cor' => $cor,
                'ativo' => true,
            ]);

            return $veiculoExistente;
        }

        return Veiculo::create([
            'condutor_id' => $condutor->id,
            'placa' => $placa,
            'modelo' => $modelo,
            'cor' => $cor,
            'ativo' => true,
        ]);
    }
}
