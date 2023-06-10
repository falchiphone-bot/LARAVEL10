<?php

namespace App\Helpers;


class FinancaHelper
{

    public static function calcularTabelaPrice($valorTotal, $taxaJuros, $parcelas)
     {
        $taxa = $taxaJuros / 100;// Taxa de juros mensal
        $valorParcela = ($valorTotal * $taxa) / (1 - pow(1 + $taxa, -$parcelas));

        return $valorParcela;
    }

    function validarCPF($cpf) {
        // Remover caracteres não numéricos
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        // Verificar se o CPF possui 11 dígitos
        if (strlen($cpf) != 11) {
            return false;
        }

        // Verificar se todos os dígitos são iguais (ex: 111.111.111-11)
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Calcular o primeiro dígito verificador
        $soma = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma += ($cpf[$i] * (10 - $i));
        }
        $resto = $soma % 11;
        $digitoVerificador1 = ($resto < 2) ? 0 : (11 - $resto);

        // Calcular o segundo dígito verificador
        $soma = 0;
        for ($i = 0; $i < 10; $i++) {
            $soma += ($cpf[$i] * (11 - $i));
        }
        $resto = $soma % 11;
        $digitoVerificador2 = ($resto < 2) ? 0 : (11 - $resto);

        // Verificar se os dígitos verificadores são válidos
        if ($cpf[9] != $digitoVerificador1 || $cpf[10] != $digitoVerificador2) {
            return false;
        }

        return true;
    }
 


}
