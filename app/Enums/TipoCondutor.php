<?php

namespace App\Enums;

enum TipoCondutor: string
{
    case Professor = 'professor';
    case Funcionario = 'funcionario';
    case Prestador = 'prestador';
    case Visitante = 'visitante';

    /**
     * Retorna todos os valores válidos do enum.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Retorna os tipos institucionais cadastrados no painel administrativo.
     *
     * @return array<int, string>
     */
    public static function institutionalValues(): array
    {
        return [
            self::Professor->value,
            self::Funcionario->value,
            self::Prestador->value,
        ];
    }

    /**
     * Retorna os tipos autorizados a usar carro de terceiro e registrar caronas.
     *
     * @return array<int, string>
     */
    public static function caronaValues(): array
    {
        return [
            self::Professor->value,
            self::Funcionario->value,
        ];
    }

    /**
     * Indica se o condutor pertence a um vínculo institucional (não visitante).
     */
    public function isInstitutional(): bool
    {
        return $this !== self::Visitante;
    }
}
