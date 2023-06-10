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

}
