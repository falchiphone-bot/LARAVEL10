<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

 class SicredApiHelper
{

    public static function auth($codigoBeneficiario,$codigocooperativa)
    {
        $auth = Http::asForm()
        ->withBasicAuth(config('services.sicredi.client_id'),config('services.sicredi.secret_id'))
        ->withHeaders([
            'x-api-key' => config('services.sicredi.token'),
            'context' => 'COBRANCA',
        ])
        ->post('https://api-parceiro.sicredi.com.br/auth/openapi/token',
        [
            'username' => $codigoBeneficiario.$codigocooperativa,
            'password' => 'DB48A4120D058F35EBD28158500FC9349E51A923C48FF464F3A93FAE1D2E35AF',
            'grant_type' => 'password',
            // 'refresh_token' => 'password',
            'scope' => 'cobranca',

        ])->json();

        return $auth;
    }

    public static function boletoLiquidadoDia($codigoBeneficiario,$codigocooperativa,$posto,$dia)
    {
        $auth = SicredApiHelper::auth($codigoBeneficiario,$codigocooperativa);
        dd($auth);

        return Http::asForm()
        ->withHeaders([
            'x-api-key' => config('services.sicredi.token'),
            'Authorization' => 'bearer '.$auth['access_token'],
            'cooperativa' => $codigocooperativa,
            'posto' => $posto,
        ])
        ->get('https://api-parceiro.sicredi.com.br/cobranca/boleto/v1/boletos/liquidados/dia',
        [
            'codigoBeneficiario' => $codigoBeneficiario,
            'dia' => $dia,
            // 'cpfCnpjBeneficiarioFinal' => '36585615000174',
            // 'pagina' => 1,

        ])->json();
    }

}


#
