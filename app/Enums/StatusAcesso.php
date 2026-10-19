<?php

namespace App\Enums;

enum StatusAcesso: string
{
    case Pendente = 'pendente';
    case NaoCadastrado = 'nao_cadastrado';
    case Autorizado = 'autorizado';
    case EmPatio = 'em_patio';
    case Finalizado = 'finalizado';
    case Bloqueado = 'bloqueado';
    case LiberadoManual = 'liberado_manual';

    /**
     * Retorna o estado visual simplificado para os badges e ações da tabela.
     */
    public function viewState(bool $temSaida = false): string
    {
        if ($temSaida || $this === self::Finalizado) {
            return 'saida';
        }

        return match ($this) {
            self::Autorizado, self::EmPatio, self::LiberadoManual => 'autorizado',
            self::NaoCadastrado, self::Bloqueado => 'nao_cadastrado',
            default => 'pendente',
        };
    }

    /**
     * Retorna o rótulo legível do status para exibição na tela.
     */
    public function label(bool $temSaida = false, bool $temPlacaCorrigida = false, bool $semPlaca = false): string
    {
        if ($temSaida || $this === self::Finalizado) {
            return 'Saída Registrada';
        }

        return match ($this) {
            self::LiberadoManual => $temPlacaCorrigida ? 'Autorizado (Placa Corrigida)' : 'Liberado Manualmente',
            self::Autorizado, self::EmPatio => 'Autorizado',
            self::Bloqueado => 'Bloqueado',
            self::NaoCadastrado => $semPlaca ? 'Placa Não Identificada' : 'Não Cadastrado',
            self::Pendente => 'Aguardando Liberação',
        };
    }

    /**
     * Lista de valores considerados como autorizados/liberados em pátio.
     *
     * @return array<int, string>
     */
    public static function authorizedValues(): array
    {
        return [
            self::Autorizado->value,
            self::EmPatio->value,
            self::LiberadoManual->value,
        ];
    }

    /**
     * Lista de valores considerados como não cadastrados ou bloqueados.
     *
     * @return array<int, string>
     */
    public static function unregisteredValues(): array
    {
        return [
            self::NaoCadastrado->value,
            self::Bloqueado->value,
        ];
    }
}
