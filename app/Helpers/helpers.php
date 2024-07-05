<?php

use Carbon\Carbon;

/**
 * Calcula a idade a partir de uma data de nascimento.
 *
 * @param string $dataNascimento Data de nascimento no formato AAAA-MM-DD.
 * @return int Idade em anos.
 */
function calcularIdade($dataNascimento)
{
    // Converte a string da data de nascimento para um objeto Carbon
    $dataNascimento = Carbon::createFromFormat('Y-m-d', $dataNascimento);

    // Obtém a data atual
    $dataAtual = Carbon::now();

    // Calcula a diferença em anos entre a data atual e a data de nascimento
    $idade = $dataAtual->diffInYears($dataNascimento);

    return $idade;
}

