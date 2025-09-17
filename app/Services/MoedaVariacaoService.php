<?php

namespace App\Services;

use Illuminate\Support\Collection;

class MoedaVariacaoService
{
    /**
     * Calcula variação percentual entre registros consecutivos base anterior ou posterior.
     * \n Fórmulas:
     *  - posterior: (valor_posterior - valor_atual) / valor_atual * 100
     *  - anterior: (valor_atual - valor_anterior) / valor_anterior * 100
     * A coleção deve conter modelos com atributo 'valor' e 'data' (Carbon/date-cast).
     * Atribui atributos dinâmicos ao próprio modelo.
     *
     * @param Collection $colecao  Coleção de modelos Eloquent
     * @param string $base 'posterior'|'anterior'
     */
    public function atribuir(Collection $colecao, string $base = 'posterior'): void
    {
        if ($colecao->isEmpty()) {
            return;
        }

        $ordenada = $colecao->sortBy('data')->values();
        $count = $ordenada->count();

        for ($i = 0; $i < $count; $i++) {
            $atual = $ordenada[$i];
            $comparacao = null;
            $variacao = null;
            $dataComparacao = null;

            if ($base === 'posterior') {
                $comparacao = $ordenada[$i + 1] ?? null;
                if ($comparacao && (float)$atual->valor != 0.0) {
                    $variacao = (($comparacao->valor - $atual->valor) / $atual->valor) * 100.0;
                    $dataComparacao = $comparacao->data;
                }
            } else { // anterior
                $comparacao = $ordenada[$i - 1] ?? null;
                if ($comparacao && (float)$comparacao->valor != 0.0) {
                    $variacao = (($atual->valor - $comparacao->valor) / $comparacao->valor) * 100.0;
                    $dataComparacao = $comparacao->data;
                }
            }

            $atual->variacao_percentual = $variacao;
            $atual->variacao_tipo = $base;
            $atual->variacao_valor_atual = $atual->valor;
            $atual->variacao_valor_comparacao = $comparacao->valor ?? null;
            $atual->variacao_data_comparacao = $dataComparacao;
        }
    }
}
