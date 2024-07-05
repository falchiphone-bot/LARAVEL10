<?php

namespace App\Helpers;
use Carbon\Carbon;

class FinancaHelper
{

    public static function calcularTabelaPrice($valorTotal, $taxaJuros, $parcelas)
     {
        $taxa = $taxaJuros / 100;// Taxa de juros mensal
        $valorParcela = ($valorTotal * $taxa) / (1 - pow(1 + $taxa, -$parcelas));

        return $valorParcela;
    }


        /**
         * Calcula a idade a partir de uma data de nascimento.
         *
         * @param string $dataNascimento Data de nascimento no formato AAAA-MM-DD.
         * @return int|null Idade em anos ou null se o formato for inválido.
         */
        public static function calcularIdade($dataNascimento)
        {
            // Verifica se a data está no formato AAAA-MM-DD
            // if (!self::isValidDate($dataNascimento)) {
            //     dd($dataNascimento);
            //     return null;
            // }

            // Converte a string da data de nascimento para um objeto Carbon
            try {
                $dataNascimento = Carbon::createFromFormat('Y-m-d', $dataNascimento);
            } catch (\Exception $e) {
                // Retorna null se a data não puder ser analisada

                dd($dataNascimento, $e);
                return null;
            }


            // Obtém a data atual
            $dataAtual = Carbon::now();

            // Calcula a diferença em anos entre a data atual e a data de nascimento
            $idade = $dataAtual->diffInYears($dataNascimento);

            return $idade;
        }

        /**
         * Verifica se a data está no formato AAAA-MM-DD.
         *
         * @param string $date
         * @return bool
         */
        private static function isValidDate($date)
        {
            $d = \DateTime::createFromFormat('Y-m-d', $date);
            // dd($d);
            return $d && $d->format('Y-m-d') === $date;
        }



}
