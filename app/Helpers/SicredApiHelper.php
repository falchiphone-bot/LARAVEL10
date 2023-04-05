<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;

class SicredApiHelper
{
    public static function auth($conta, $agencia, $update_token = false, $token_conta_sicred, $client_id, $secret_id, $token_desenvolvedor)
    {
        if ($update_token) {
            $auth = Http::asForm()
                ->withBasicAuth($client_id, $secret_id)
                ->withHeaders([
                    'x-api-key' => $token_desenvolvedor,
                    'context' => 'COBRANCA',
                ])
                ->post('https://api-parceiro.sicredi.com.br/auth/openapi/token', [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $update_token,
                ])
                ->json();
            return $auth;
        }

        $auth = Http::asForm()
            ->withBasicAuth(config('services.sicredi.client_id'), config('services.sicredi.secret_id'))
            ->withHeaders([
                'x-api-key' => config('services.sicredi.token'),
                'context' => 'COBRANCA',
            ])
            ->post('https://api-parceiro.sicredi.com.br/auth/openapi/token', [
                'username' => $conta . $agencia,
                'password' => $token_conta_sicred,
                'grant_type' => 'password',
                'scope' => 'cobranca',
            ])
            ->json();

        return $auth;
    }

    public static function boletoLiquidadoDia($conta, $agencia, $posto, $token_conta, $client_id, $secret_id, $token_desenvolvedor, $dia)
    {
        // if (Cache::get('access_token')) {
        //     $access_token = Cache::get('access_token');
        // } elseif (Cache::get('refresh_token')) {
        //     $auth = SicredApiHelper::auth($conta, $agencia, Cache::get('refresh_token'), $token_conta, $client_id, $secret_id, $token_desenvolvedor);

        //     Cache::put('access_token', $auth['access_token'], $seconds = $auth['expires_in']);
        //     $access_token = Cache::get('access_token');
        // } else {
        //     $auth = SicredApiHelper::auth($conta, $agencia, false, $token_conta, $client_id, $secret_id, $token_desenvolvedor);

        //     $access_token = Cache::get('access_token');
        //     Cache::put('access_token', $auth['access_token'], $seconds = $auth['expires_in']);
        //     Cache::put('refresh_token', $auth['refresh_token'], $seconds = $auth['refresh_expires_in']);
        // }

        $auth = SicredApiHelper::auth($conta, $agencia, false, $token_conta, $client_id, $secret_id, $token_desenvolvedor);

            $access_token = Cache::get('access_token');
            Cache::put('access_token', $auth['access_token'], $seconds = $auth['expires_in']);
            Cache::put('refresh_token', $auth['refresh_token'], $seconds = $auth['refresh_expires_in']);

        // dd($access_token);
        $consulta = Http::asForm()
            ->withHeaders([
                'x-api-key' => config('services.sicredi.token'),
                'Authorization' => 'bearer ' . $access_token,
                'cooperativa' => $agencia,
                'posto' => $posto,
            ])
            ->get('https://api-parceiro.sicredi.com.br/cobranca/boleto/v1/boletos/liquidados/dia', [
                'codigoBeneficiario' => $conta,
                'dia' => $dia,
                // 'cpfCnpjBeneficiarioFinal' => '36585615000174',
                // 'pagina' => 1,
            ])
            ->json();
        return $consulta;
    }
}
