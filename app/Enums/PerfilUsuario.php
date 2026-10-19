<?php

namespace App\Enums;

enum PerfilUsuario: string
{
    case Admin = 'admin';
    case Operador = 'operador';
    case Supervisor = 'supervisor';

    /**
     * Retorna o prefixo utilizado na geração do código de operador.
     */
    public function prefixoCodigo(): string
    {
        return match ($this) {
            self::Admin => 'ADM',
            self::Supervisor => 'SUP',
            self::Operador => 'GDA',
        };
    }

    /**
     * Retorna a faixa numérica inicial do código para cada perfil.
     */
    public function numeroInicial(): int
    {
        return match ($this) {
            self::Admin => 0,
            self::Supervisor => 200,
            self::Operador => 100,
        };
    }

    /**
     * Retorna todos os valores válidos do enum para regras de validação.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Perfis autorizados a operar o módulo de portaria/guarita.
     *
     * @return array<int, string>
     */
    public static function portariaValues(): array
    {
        return [
            self::Operador->value,
            self::Supervisor->value,
        ];
    }
}
