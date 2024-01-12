<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

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
            ->withBasicAuth($client_id, $secret_id)
            ->withHeaders([
                'x-api-key' => $token_desenvolvedor,
                'context' => 'COBRANCA',
            ])
            ->post('https://api-parceiro.sicredi.com.br/auth/openapi/token', [
                'username' => $conta . $agencia,
                'password' => $token_conta_sicred,
                'grant_type' => 'password',
                'scope' => 'cobranca',
            ])
            ->json();
        if (empty($auth['access_token'])) {
            dd($conta, $agencia, $update_token = false, $token_conta_sicred, $client_id, $secret_id, $token_desenvolvedor, 'Sem autorização para acessar o recurso');
        }

        return $auth;
    }

    public static function boletoLiquidadoDia($cb, $dia)
    {
        if (Cache::get('access_token' . $cb->conta . $cb->agencia)) {
            $access_token = Cache::get('access_token' . $cb->conta . $cb->agencia);
        } elseif (Cache::get('refresh_token' . $cb->conta . $cb->agencia)) {
            $auth = SicredApiHelper::auth($cb->conta, $cb->agencia, Cache::get('refresh_token' . $cb->conta . $cb->agencia), $cb->token_conta, $cb->devSicredi->SICREDI_CLIENT_ID, $cb->devSicredi->SICREDI_CLIENT_SECRET, $cb->devSicredi->SICREDI_TOKEN);
            Cache::put('access_token' . $cb->conta . $cb->agencia, $auth['access_token'], $seconds = $auth['expires_in']);
            $access_token = Cache::get('access_token' . $cb->conta . $cb->agencia);
        } else {
            $auth = SicredApiHelper::auth($cb->conta, $cb->agencia, false, $cb->token_conta, $cb->devSicredi->SICREDI_CLIENT_ID, $cb->devSicredi->SICREDI_CLIENT_SECRET, $cb->devSicredi->SICREDI_TOKEN);
            $access_token = $auth['access_token'];
            Cache::put('access_token' . $cb->conta . $cb->agencia, $auth['access_token'], $seconds = $auth['expires_in']);
            Cache::put('refresh_token' . $cb->conta . $cb->agencia, $auth['refresh_token'], $seconds = $auth['refresh_expires_in']);
        }
        if ($access_token) {
            $dados = [];
            for ($pagina = 0; $pagina < 100; $pagina++) {
                $consulta = Http::asForm()
                    ->withHeaders([
                        'x-api-key' => config('services.sicredi.token'),
                        // 'Authorization' => 'bearer ' . ,
                        'cooperativa' => $cb->agencia,
                        'posto' => $cb->posto,
                    ])
                    ->withToken($access_token)
                    ->get('https://api-parceiro.sicredi.com.br/cobranca/boleto/v1/boletos/liquidados/dia', [
                        'codigoBeneficiario' => $cb->conta,
                        'dia' => $dia,
                        // 'cpfCnpjBeneficiarioFinal' => '36585615000174',
                        'pagina' => $pagina,
                    ]);

                if ($consulta->successful()) {
                    if ($consulta->json()['hasNext']) {
                        $dados = array_merge($dados, $consulta->json()['items']);
                    } else {
                        $dados = array_merge($dados, $consulta->json()['items']);
                        return ['status' => true, 'dados' => $dados];
                    }
                } else {
                    return ['status' => false, 'dados' => 'Erro ao consultar dados no banco'];
                }
            }
        } else {
            return ['status' => false, 'dados' => 'Falha ao obter token de autenticação'];
        }
    }

    public static function consultaBoleto($cb, $nosso_numero)
    {
        if (Cache::get('access_token' . $cb->conta . $cb->agencia)) {
            $access_token = Cache::get('access_token' . $cb->conta . $cb->agencia);
        } elseif (Cache::get('refresh_token' . $cb->conta . $cb->agencia)) {
            $auth = SicredApiHelper::auth($cb->conta, $cb->agencia, Cache::get('refresh_token' . $cb->conta . $cb->agencia), $cb->token_conta, $cb->devSicredi->SICREDI_CLIENT_ID, $cb->devSicredi->SICREDI_CLIENT_SECRET, $cb->devSicredi->SICREDI_TOKEN);
            Cache::put('access_token' . $cb->conta . $cb->agencia, $auth['access_token'], $seconds = $auth['expires_in']);
            $access_token = Cache::get('access_token' . $cb->conta . $cb->agencia);
        } else {
            $auth = SicredApiHelper::auth($cb->conta, $cb->agencia, false, $cb->token_conta, $cb->devSicredi->SICREDI_CLIENT_ID, $cb->devSicredi->SICREDI_CLIENT_SECRET, $cb->devSicredi->SICREDI_TOKEN);
            $access_token = $auth['access_token'];
            Cache::put('access_token' . $cb->conta . $cb->agencia, $auth['access_token'], $seconds = $auth['expires_in']);
            Cache::put('refresh_token' . $cb->conta . $cb->agencia, $auth['refresh_token'], $seconds = $auth['refresh_expires_in']);
        }

        if ($access_token) {
            $dados = [];
            $consulta = Http::asForm()
                ->withHeaders([
                    'x-api-key' => config('services.sicredi.token'),
                    // 'Authorization' => 'bearer ' . ,
                    'cooperativa' => $cb->agencia,
                    'posto' => $cb->posto,
                ])
                ->withToken($access_token)
                ->get('https://api-parceiro.sicredi.com.br/cobranca/boleto/v1/boletos', [
                    'codigoBeneficiario' => $cb->conta,
                    'nossoNumero' => $nosso_numero,
                ]);
            if ($consulta->successful()) {
                return ['status' => true, 'dados' => $consulta->json()];
            } else {
                return ['status' => false, 'dados' => 'Erro ao consultar dados no banco'];
            }
        } else {
            return ['status' => false, 'dados' => 'Falha ao obter token de autenticação'];
        }
    }
}
