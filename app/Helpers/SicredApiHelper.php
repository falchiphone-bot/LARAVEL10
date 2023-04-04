<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

 class SicredApiHelper
{

    public static function auth()
    {
        $auth = Http::asForm()
        ->withBasicAuth(config('services.sicred.client_id'),config('services.sicred.secret_id'))
        ->withHeaders([
            'x-api-key' => config('services.sicred.token'),
            'context' => 'COBRANCA',
        ])
        ->post('https://api-parceiro.sicredi.com.br/sb/auth/openapi/token',
        [
            'username' => '123456789',
            'password' => 'teste123',
            'grant_type' => 'password',
            'scope' => 'cobranca',

        ])->json();

        return $auth;
    }

    public static function boletoLiquidadoDia($codigoBeneficiario,$dia)
    {
        $auth = SicredApiHelper::auth();

        return Http::asForm()
        // ->withBasicAuth('6c393213-778d-4b72-b49f-6347c1e8c5aa','2591f57e-97b1-48d8-a639-7f200b09c03d')
        ->withHeaders([
            'x-api-key' => '2dd4c03d-c692-4852-9e2e-f98d207c62e4',
            'Authorization' => 'bearer '.$auth['id_token'],
            'cooperativa' => '6789',
            'posto' => '03',
        ])
        ->get('https://api-parceiro.sicredi.com.br/sb/cobranca/boleto/v1/boletos/liquidados/dia',
        [
            'codigoBeneficiario' => $codigoBeneficiario,
            'dia' => $dia,
            'cpfCnpjBeneficiarioFinal' => '1234567891225',
            'pagina' => 1,

        ])->json();
    }

}


#
