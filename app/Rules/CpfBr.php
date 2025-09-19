<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CpfBr implements Rule
{
    public function passes($attribute, $value): bool
    {
        if ($value === null || $value === '') return true; // nullable
        $cpf = preg_replace('/\D/', '', (string)$value);
        if (strlen($cpf) !== 11) return false;
        if (preg_match('/^(\d)\1{10}$/', $cpf)) return false; // repetidos

        // Calcula DV
        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$t] != $d) return false;
        }
        return true;
    }

    public function message(): string
    {
        return 'O :attribute informado é inválido.';
    }
}
