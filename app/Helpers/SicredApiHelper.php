<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

class SicredApiHelper
{
    public static function auth($codigoBeneficiario, $codigocooperativa, $refres_token = false)
    {
        if ($refres_token) {
            $auth = Http::asForm()
                ->withBasicAuth(config('services.sicredi.client_id'), config('services.sicredi.secret_id'))
                ->withHeaders([
                    'x-api-key' => config('services.sicredi.token'),
                    'context' => 'COBRANCA',
                ])
                ->post('https://api-parceiro.sicredi.com.br/auth/openapi/token', [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refres_token,
                ])
                ->json();
        } else {
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
            $auth['data_criacao'] = Carbon::now();
            Request::session()->put('auth_sicred', $auth);
        }

        return $auth;
    }

    public static function boletoLiquidadoDia($codigoBeneficiario, $codigocooperativa, $posto, $dia)
    {

        dd(Request::session()->get('auth_sicred'));
        if (session('auth_sicred')) {
            session('auth_sicred')['data_criacao'] = 1;
            $horaAtual = Carbon::now();
            dd(session('auth_sicred')['data_criacao']->format('d/m/Y H:i:s'), $horaAtual->format('d/m/Y H:i:s'));
            $fimToken = session('auth_sicred')['data_criacao']->addSecond(session('auth_sicred')['expires_in']);
            $fimTokenRefresh = session('auth_sicred')['data_criacao']->addSecond(session('auth_sicred')['refresh_expires_in']);
            $tempoSessaoToken = $horaAtual->diffInSeconds($fimToken);
            $tempoSessaoTokenRefresh = $horaAtual->diffInSeconds($fimTokenRefresh);
            if ($tempoSessaoToken < session('auth_sicred')['expires_in']) {
                $auth = session('auth_sicred');
            } elseif ($tempoSessaoTokenRefresh < session('auth_sicred')['refresh_expires_in']) {
                dd($tempoSessaoToken);
                $auth = SicredApiHelper::auth($codigoBeneficiario, $codigocooperativa, session('auth_sicred')['refresh_token']);
                dd($auth, 'Passou no de 1 hora');
            } else {
                $auth = SicredApiHelper::auth($codigoBeneficiario, $codigocooperativa);
            }
        } else {
            $auth = SicredApiHelper::auth($codigoBeneficiario, $codigocooperativa);
        }

        $consulta = Http::asForm()
            ->withHeaders([
                'x-api-key' => config('services.sicredi.token'),
                'Authorization' => 'bearer ' . $auth['access_token'],
                'cooperativa' => $codigocooperativa,
                'posto' => $posto,
            ])
            ->get('https://api-parceiro.sicredi.com.br/cobranca/boleto/v1/boletos/liquidados/dia', [
                'codigoBeneficiario' => $codigoBeneficiario,
                'dia' => $dia,
                // 'cpfCnpjBeneficiarioFinal' => '36585615000174',
                // 'pagina' => 1,
            ])->json();
        dd($consulta);
    }
}
