<?php

namespace App\Actions\Admin;

use App\Enums\TipoCondutor;
use App\Models\Condutor;
use App\Models\CondutorFuncionario;
use App\Models\CondutorPrestador;
use App\Models\CondutorProfessor;
use App\Models\RegistroAcesso;
use App\Models\Veiculo;
use Illuminate\Support\Facades\DB;

class SalvarCondutorAction
{
    /**
     * Persiste (cria ou atualiza) um condutor institucional, sua especialização e seus veículos.
     *
     * @param  array{
     *     editing_id: int|null,
     *     name: string,
     *     cpf: string,
     *     category: string,
     *     code: string,
     *     department: string,
     *     validade_acesso: string|null,
     *     ativo: bool,
     *     vehicles: array<int, array{id: int|null, plate: string, model: string, color: string}>
     * }  $dados
     */
    public function execute(array $dados): Condutor
    {
        $tipo = TipoCondutor::from($dados['category']);

        return DB::transaction(function () use ($dados, $tipo): Condutor {
            $categoriaAnterior = null;

            if ($dados['editing_id']) {
                $condutor = Condutor::with('veiculos')->findOrFail($dados['editing_id']);
                $categoriaAnterior = $condutor->tipo;

                $condutor->update([
                    'nome' => trim($dados['name']),
                    'cpf' => trim($dados['cpf']),
                    'tipo' => $tipo,
                    'validade_acesso' => $dados['validade_acesso'],
                    'ativo' => $dados['ativo'],
                ]);
            } else {
                $condutor = Condutor::withTrashed()->updateOrCreate(
                    ['cpf' => trim($dados['cpf'])],
                    [
                        'nome' => trim($dados['name']),
                        'tipo' => $tipo,
                        'validade_acesso' => $dados['validade_acesso'],
                        'ativo' => $dados['ativo'],
                        'deleted_at' => null,
                    ]
                );
            }

            if ($categoriaAnterior !== null && $categoriaAnterior !== $tipo) {
                CondutorProfessor::where('condutor_id', $condutor->id)->delete();
                CondutorFuncionario::where('condutor_id', $condutor->id)->delete();
                CondutorPrestador::where('condutor_id', $condutor->id)->delete();
            }

            match ($tipo) {
                TipoCondutor::Professor => CondutorProfessor::updateOrCreate(
                    ['condutor_id' => $condutor->id],
                    [
                        'codigo_acesso' => $dados['code'],
                        'departamento' => $dados['department'] !== '' ? $dados['department'] : null,
                    ]
                ),
                TipoCondutor::Funcionario => CondutorFuncionario::updateOrCreate(
                    ['condutor_id' => $condutor->id],
                    [
                        'codigo_acesso' => $dados['code'],
                        'setor' => $dados['department'] !== '' ? $dados['department'] : null,
                    ]
                ),
                TipoCondutor::Prestador => CondutorPrestador::updateOrCreate(
                    ['condutor_id' => $condutor->id],
                    [
                        'codigo_acesso' => $dados['code'],
                        'empresa' => $dados['department'],
                    ]
                ),
                TipoCondutor::Visitante => null,
            };

            $idsEnviados = [];

            foreach ($dados['vehicles'] as $vehicle) {
                $placaBruta = trim($vehicle['plate']);
                if ($placaBruta === '') {
                    continue;
                }

                $placaFormatada = RegistroAcesso::formatarPlaca($placaBruta);
                $corFormatada = trim($vehicle['color']) !== '' ? trim($vehicle['color']) : 'Não informada';

                $dadosVeiculo = [
                    'condutor_id' => $condutor->id,
                    'placa' => $placaFormatada,
                    'modelo' => trim($vehicle['model']) !== '' ? trim($vehicle['model']) : 'Veículo de Passeio',
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

            return $condutor;
        });
    }
}
