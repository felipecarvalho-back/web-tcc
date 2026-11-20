<?php

namespace App\Actions\Portaria;

use App\Enums\StatusAcesso;
use App\Enums\TipoCondutor;
use App\Enums\TipoOcupante;
use App\Models\Condutor;
use App\Models\RegistroAcesso;
use App\Models\Veiculo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ConfirmarAcessoPortariaAction
{
    /**
     * Caso 1: Libera veículo identificado no banco após confirmar o código de acesso do condutor.
     *
     * @param  array<int, string>  $passengers
     */
    public function confirmarCondutor(
        RegistroAcesso $registro,
        Condutor $condutor,
        ?Veiculo $veiculo,
        string $codigoFormatado,
        ?int $operadorId,
        array $passengers = []
    ): void {
        DB::transaction(function () use ($registro, $condutor, $veiculo, $codigoFormatado, $operadorId, $passengers): void {
            $registro->condutor_id = $condutor->id;
            $registro->veiculo_id = $veiculo?->id ?? $registro->veiculo_id;
            $registro->operador_id = $operadorId ?: $registro->operador_id;
            $registro->status = StatusAcesso::Autorizado;
            $registro->justificativa = "Condutor confirmado por código ({$codigoFormatado})";
            $registro->save();

            if (in_array($condutor->tipo?->value, TipoCondutor::caronaValues(), true)) {
                $this->registrarCaronas($registro, $passengers, $operadorId);
            }
        });
    }

    /**
     * Caso 2: Libera passagem após corrigir placa e validar código do proprietário.
     *
     * @param  array<int, string>  $passengers
     */
    public function liberarComPlacaCorrigida(
        RegistroAcesso $registro,
        Condutor $condutor,
        Veiculo $veiculo,
        string $placaFormatada,
        string $codigoFormatado,
        ?int $operadorId,
        array $passengers = []
    ): void {
        DB::transaction(function () use ($registro, $condutor, $veiculo, $placaFormatada, $codigoFormatado, $operadorId, $passengers): void {
            if ($placaFormatada !== $registro->placa_registro) {
                $registro->placa_corrigida = substr($placaFormatada, 0, 10);
            }

            $registro->veiculo_id = $veiculo->id;
            $registro->condutor_id = $condutor->id;
            $registro->operador_id = $operadorId ?: $registro->operador_id;
            $registro->status = StatusAcesso::LiberadoManual;
            $registro->justificativa = "Placa corrigida ({$placaFormatada}) e código validado ({$codigoFormatado})";
            $registro->save();

            if (in_array($condutor->tipo?->value, TipoCondutor::caronaValues(), true)) {
                $this->registrarCaronas($registro, $passengers, $operadorId);
            }
        });
    }

    /**
     * Caso 3: Libera veículo de terceiro mediante código de Professor ou Funcionário.
     *
     * @param  array<int, string>  $passengers
     */
    public function liberarCarroDeTerceiro(
        RegistroAcesso $registro,
        Condutor $condutor,
        string $placaFormatada,
        string $codigoFormatado,
        ?int $operadorId,
        array $passengers = []
    ): void {
        DB::transaction(function () use ($registro, $condutor, $placaFormatada, $codigoFormatado, $operadorId, $passengers): void {
            if ($placaFormatada !== '' && $placaFormatada !== $registro->placa_registro) {
                $registro->placa_corrigida = substr($placaFormatada, 0, 10);
            }

            $registro->condutor_id = $condutor->id;
            $registro->veiculo_id = null;
            $registro->operador_id = $operadorId ?: $registro->operador_id;
            $registro->status = StatusAcesso::LiberadoManual;
            $registro->justificativa = "Carro de terceiro liberado por código ({$codigoFormatado})";
            $registro->save();

            $this->registrarCaronas($registro, $passengers, $operadorId);
        });
    }

    /**
     * Registra a saída do veículo e das eventuais caronas vinculadas.
     */
    public function registrarSaida(RegistroAcesso $registro): void
    {
        $agora = Carbon::now();

        DB::transaction(function () use ($registro, $agora): void {
            $registro->data_hora_saida = $agora;
            $registro->status = StatusAcesso::Finalizado;
            $registro->save();

            $registro->caronas()
                ->whereNull('data_hora_saida')
                ->update([
                    'data_hora_saida' => $agora,
                    'status' => StatusAcesso::Finalizado,
                ]);
        });
    }

    /**
     * Registra os passageiros/caronas vinculados à passagem principal do veículo.
     *
     * @param  array<int, string>  $passengers
     */
    private function registrarCaronas(RegistroAcesso $passagemPrincipal, array $passengers, ?int $operadorId): void
    {
        $passagemPrincipal->loadMissing('condutor');

        foreach ($passengers as $codigoCarona) {
            $passageiro = Condutor::buscarAtivoPorCodigo($codigoCarona, TipoCondutor::caronaValues());
            if (! $passageiro) {
                continue;
            }

            RegistroAcesso::create([
                'veiculo_id' => $passagemPrincipal->veiculo_id,
                'condutor_id' => $passageiro->id,
                'tipo_ocupante' => TipoOcupante::Passageiro,
                'passagem_origem_id' => $passagemPrincipal->id,
                'placa_registro' => $passagemPrincipal->placa_registro,
                'placa_corrigida' => $passagemPrincipal->placa_corrigida,
                'cancela' => $passagemPrincipal->cancela,
                'data_hora_entrada' => $passagemPrincipal->data_hora_entrada ?? Carbon::now(),
                'operador_id' => $operadorId ?: $passagemPrincipal->operador_id,
                'foto_entrada_path' => $passagemPrincipal->foto_entrada_path,
                'status' => $passagemPrincipal->status,
                'justificativa' => "Carona com {$passagemPrincipal->nomeCondutorExibicao()} ({$codigoCarona})",
            ]);
        }
    }
}
