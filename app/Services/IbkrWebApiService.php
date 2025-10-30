<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class IbkrWebApiService
{
    protected Client $http;
    protected string $baseUrl;

    public function __construct(?Client $client = null)
    {
        $this->baseUrl = rtrim((string) config('ibkr.base_url'), '/');
        $this->http = $client ?: new Client([
            'base_uri' => $this->baseUrl.'/',
            'timeout' => (float) config('ibkr.http_timeout', 10.0),
            'connect_timeout' => (float) config('ibkr.http_connect_timeout', 5.0),
        ]);
    }

    public function getAuthorizeUrl(?string $state = null): string
    {
        $authUrl = (string) config('ibkr.oauth_authorize_url');
        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => (string) config('ibkr.client_id'),
            'redirect_uri' => (string) config('ibkr.redirect_uri'),
            'scope' => implode(' ', (array) config('ibkr.scopes', ['read'])),
            'state' => $state,
        ]);
        return $authUrl.(str_contains($authUrl, '?') ? '&' : '?').$query;
    }

    public function exchangeCodeForToken(string $code): array
    {
        $tokenUrl = (string) config('ibkr.oauth_token_url');
        try {
            $resp = $this->http->post($tokenUrl, [
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => (string) config('ibkr.redirect_uri'),
                    'client_id' => (string) config('ibkr.client_id'),
                    'client_secret' => (string) config('ibkr.client_secret'),
                ],
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);
            $data = json_decode((string) $resp->getBody(), true) ?: [];
            return ['ok' => true, 'data' => $data, 'source' => 'ibkr', 'updated_at' => now()];
        } catch (GuzzleException $e) {
            Log::warning('IBKR token exchange error', ['code' => $e->getCode(), 'msg' => substr($e->getMessage(), 0, 180)]);
            return ['ok' => false, 'reason' => 'api_error', 'detail' => 'token_exchange_failed', 'source' => 'ibkr'];
        }
    }

    public function refreshToken(string $refreshToken): array
    {
        $tokenUrl = (string) config('ibkr.oauth_token_url');
        try {
            $resp = $this->http->post($tokenUrl, [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken,
                    'client_id' => (string) config('ibkr.client_id'),
                    'client_secret' => (string) config('ibkr.client_secret'),
                ],
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);
            $data = json_decode((string) $resp->getBody(), true) ?: [];
            return ['ok' => true, 'data' => $data, 'source' => 'ibkr', 'updated_at' => now()];
        } catch (GuzzleException $e) {
            Log::warning('IBKR token refresh error', ['code' => $e->getCode(), 'msg' => substr($e->getMessage(), 0, 180)]);
            return ['ok' => false, 'reason' => 'api_error', 'detail' => 'token_refresh_failed', 'source' => 'ibkr'];
        }
    }

    /**
     * Checa status de autenticação (exemplo de endpoint comum em Client Portal Web API).
     */
    public function getAuthStatus(string $accessToken): array
    {
        return $this->request('GET', 'v1/api/iserver/auth/status', [], $accessToken);
    }

    /**
     * Lista contas (endpoint ilustrativo; ajuste conforme contrato da Web API de organizações)
     */
    public function getAccounts(string $accessToken): array
    {
        return $this->request('GET', 'v1/api/portfolio/accounts', [], $accessToken);
    }

    /**
     * Método utilitário para chamadas autenticadas com Bearer.
     */
    protected function request(string $method, string $path, array $options, string $accessToken): array
    {
        try {
            $opts = $options;
            $opts['headers'] = array_merge($opts['headers'] ?? [], [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$accessToken,
            ]);
            $resp = $this->http->request($method, ltrim($path, '/'), $opts);
            $json = json_decode((string) $resp->getBody(), true);
            if (!is_array($json)) { $json = ['raw' => (string)$resp->getBody()]; }
            return ['ok' => true, 'data' => $json, 'status' => $resp->getStatusCode(), 'source' => 'ibkr'];
        } catch (GuzzleException $e) {
            Log::warning('IBKR HTTP error', ['path' => $path, 'code' => $e->getCode(), 'msg' => substr($e->getMessage(), 0, 180)]);
            return ['ok' => false, 'reason' => 'api_error', 'detail' => 'http_error', 'status' => 0, 'source' => 'ibkr'];
        }
    }
}
