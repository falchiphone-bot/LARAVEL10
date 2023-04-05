<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;

class SicredApiHelper
{
    public static function auth($codigoBeneficiario, $codigocooperativa, $update_token = false)
    {
        if ($update_token) {
            $auth = Http::asForm()
                ->withBasicAuth(config('services.sicredi.client_id'), config('services.sicredi.secret_id'))
                ->withHeaders([
                    'x-api-key' => config('services.sicredi.token'),
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
                'username' => $codigoBeneficiario . $codigocooperativa,
                'password' => 'DB48A4120D058F35EBD28158500FC9349E51A923C48FF464F3A93FAE1D2E35AF',
                'grant_type' => 'password',
                'scope' => 'cobranca',
            ])
            ->json();

        return $auth;
    }

    public static function boletoLiquidadoDia($codigoBeneficiario, $codigocooperativa, $posto, $dia)
    {

        if (Cache::get('access_token')) {
            $access_token = Cache::get('access_token');
        } elseif (Cache::get('refresh_token')) {
            $auth = SicredApiHelper::auth($codigoBeneficiario, $codigocooperativa,Cache::get('refresh_token'));
            Cache::put('access_token', $auth['access_token'], $seconds = $auth['expires_in']);
            $access_token = Cache::get('access_token');
        } else {
            $auth = SicredApiHelper::auth($codigoBeneficiario, $codigocooperativa);
            $access_token = Cache::get('access_token');
            Cache::put('access_token', $auth['access_token'], $seconds = $auth['expires_in']);
            Cache::put('refresh_token', $auth['refresh_token'], $seconds = $auth['refresh_expires_in']);
        }

        // dd($access_token);
        $consulta = Http::asForm()
            ->withHeaders([
                'x-api-key' => config('services.sicredi.token'),
                'Authorization' => 'bearer ' . $access_token,
                'cooperativa' => $codigocooperativa,
                'posto' => $posto,
            ])
            ->get('https://api-parceiro.sicredi.com.br/cobranca/boleto/v1/boletos/liquidados/dia', [
                'codigoBeneficiario' => $codigoBeneficiario,
                'dia' => $dia,
                // 'cpfCnpjBeneficiarioFinal' => '36585615000174',
                // 'pagina' => 1,
            ])
            ->json();
        return $consulta;
    }
}
