<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class CpfValido implements ValidationRule
{
    /**
     * Valida o CPF pelo cálculo oficial dos dígitos verificadores (Módulo 11 da Receita Federal).
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cpf = preg_replace('/\D/', '', (string) $value) ?? '';

        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf) === 1) {
            $fail('Informe um CPF válido com 11 dígitos.');

            return;
        }

        for ($t = 9; $t < 11; $t++) {
            $soma = 0;
            for ($c = 0; $c < $t; $c++) {
                $soma += ((int) $cpf[$c]) * (($t + 1) - $c);
            }

            $digito = ((10 * $soma) % 11) % 10;

            if (((int) $cpf[$t]) !== $digito) {
                $fail('Informe um CPF válido (os dígitos verificadores não conferem).');

                return;
            }
        }
    }
}
