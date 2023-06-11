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


