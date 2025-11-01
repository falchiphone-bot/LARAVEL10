<?php

function corrigirEnderecoEmail($enderecoEmail) {
    // Verifica se o endereço de e-mail contém o símbolo "@"
    if (strpos($enderecoEmail, '@') === false) {
        // Endereço de e-mail inválido, retorna o endereço original
        return $enderecoEmail;
    }

    // Remove caracteres inválidos do endereço de e-mail
    $enderecoCorrigido = preg_replace('/[^a-zA-Z0-9.@_-]/', '', $enderecoEmail);

    // Obtém o nome do usuário e o domínio do endereço corrigido
    $partesEndereco = explode('@', $enderecoCorrigido);
    $usuario = $partesEndereco[0];
    $dominio = isset($partesEndereco[1]) ? $partesEndereco[1] : '';

    // Remove caracteres inválidos do nome do usuário e do domínio
    $usuarioCorrigido = preg_replace('/[^a-zA-Z0-9._-]/', '', $usuario);
    $dominioCorrigido = preg_replace('/[^a-zA-Z0-9.-]/', '', $dominio);

    // Reconstroi o endereço corrigido com o símbolo "@" no local correto
    $enderecoCorrigido = $usuarioCorrigido . '@' . $dominioCorrigido;

    return $enderecoCorrigido;
}



function ValidarCPF($cpf) {
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

        // Position Sizer helper — cálculo de tamanho de posição e métricas associadas
        if (! function_exists('positionSizer')) {
            /**
             * Calcula tamanho de posição e métricas de risco/retorno.
             *
             * Parâmetros esperados em $in:
             * - equity: float (patrimônio total)
             * - riskPct: float (fração do risco por trade, ex.: 0.01 = 1%)
             * - entry: float (preço de entrada)
             * - stop: float (preço de stop)
             * - slippage: float (derrapagem por ação)
             * - feeShare: float (taxa por ação)
             * - fixed: float (custo fixo por trade)
             *
             * Retorna array com chaves: rPerShare, riskAllowed, riskAdj, size, notional, dir, t1, t2, maxLoss, breakeven
             */
            function positionSizer(array $in): array {
                $equity   = (float)($in['equity']   ?? 50000);
                $riskPct  = (float)($in['riskPct']  ?? 0.01);
                $entry    = (float)($in['entry']    ?? 371.00);
                $stop     = (float)($in['stop']     ?? 368.80);
                $slip     = (float)($in['slippage'] ?? 0.05);
                $feeShare = (float)($in['feeShare'] ?? 0.00);
                $fixed    = (float)($in['fixed']    ?? 0.00);

                $rPerShare = abs($entry - $stop) + $slip + $feeShare;
                $riskAllowed = $equity * $riskPct;
                $riskAdj = max($riskAllowed - $fixed, 0);
                $size = $rPerShare > 0 ? (int) floor($riskAdj / $rPerShare) : 0;
                $notional = $size * $entry;
                $dir = $entry > $stop ? 'Long' : 'Short';
                $t1 = $dir === 'Long' ? $entry + $rPerShare : $entry - $rPerShare;
                $t2 = $dir === 'Long' ? $entry + 2*$rPerShare : $entry - 2*$rPerShare;
                $maxLoss = $size * $rPerShare + $fixed;
                $breakeven = $size > 0
                    ? ($dir === 'Long' ? $entry + $fixed/$size : $entry - $fixed/$size)
                    : null;

                return compact('rPerShare','riskAllowed','riskAdj','size','notional','dir','t1','t2','maxLoss','breakeven');
            }
        }
    }

    function validarCNPJ($cnpj) {
        // Remover caracteres não numéricos
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        // Verificar se o CNPJ possui 14 dígitos
        if (strlen($cnpj) != 14) {
            return false;
        }

        // Verificar se todos os dígitos são iguais (ex: 00.000.000/0000-00)
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        // Calcular o primeiro dígito verificador
        $soma = 0;
        $multiplicador = 5;
        for ($i = 0; $i < 12; $i++) {
            $soma += ($cnpj[$i] * $multiplicador);
            $multiplicador = ($multiplicador == 2) ? 9 : ($multiplicador - 1);
        }
        $resto = $soma % 11;
        $digitoVerificador1 = ($resto < 2) ? 0 : (11 - $resto);

        // Calcular o segundo dígito verificador
        $soma = 0;
        $multiplicador = 6;
        for ($i = 0; $i < 13; $i++) {
            $soma += ($cnpj[$i] * $multiplicador);
            $multiplicador = ($multiplicador == 2) ? 9 : ($multiplicador - 1);
        }
        $resto = $soma % 11;
        $digitoVerificador2 = ($resto < 2) ? 0 : (11 - $resto);

        // Verificar se os dígitos verificadores são válidos
        if ($cnpj[12] != $digitoVerificador1 || $cnpj[13] != $digitoVerificador2) {
            return false;
        }

        return true;
    }


