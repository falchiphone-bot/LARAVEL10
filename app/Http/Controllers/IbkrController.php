<?php

namespace App\Http\Controllers;

use App\Services\IbkrWebApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class IbkrController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','verified']);
    }

    public function connect(Request $request, IbkrWebApiService $svc)
    {
        // Valida configuração antes de redirecionar (para exibir erro no modal em vez de 403 direto na IBKR)
        $clientId = (string) config('ibkr.client_id');
        $redirect = (string) config('ibkr.redirect_uri');
        $authUrl  = (string) config('ibkr.oauth_authorize_url');
        if (empty($clientId)) {
            return redirect()->route('ibkr.status')->with('error', 'IBKR: client_id ausente. Configure IBKR_CLIENT_ID no .env.');
        }
        if (stripos($clientId, 'SEU_CLIENT_ID') !== false) {
            return redirect()->route('ibkr.status')->with('error', 'IBKR: client_id inválido (placeholder). Atualize IBKR_CLIENT_ID no .env.');
        }
        if (empty($redirect)) {
            return redirect()->route('ibkr.status')->with('error', 'IBKR: redirect_uri ausente. Defina IBKR_REDIRECT_URI no .env ou APP_URL correto.');
        }
        if (stripos($redirect, 'SEU_DOMINIO') !== false) {
            return redirect()->route('ibkr.status')->with('error', 'IBKR: redirect_uri inválido (placeholder). Atualize IBKR_REDIRECT_URI no .env.');
        }
        if (empty($authUrl)) {
            return redirect()->route('ibkr.status')->with('error', 'IBKR: URL de autorização não configurada.');
        }
        // Gera state aleatório e guarda em sessão para mitigar CSRF
        $state = bin2hex(random_bytes(16));
        $request->session()->put('ibkr_oauth_state', $state);
        $url = $svc->getAuthorizeUrl($state);
        return redirect()->away($url);
    }

    public function callback(Request $request, IbkrWebApiService $svc)
    {
        $state = (string) $request->query('state');
        $code  = (string) $request->query('code');
        $expected = (string) $request->session()->pull('ibkr_oauth_state');
        if ($expected === '' || $state !== $expected) {
            return redirect()->route('ibkr.status')->with('error', 'State inválido. Tente novamente.');
        }
        if ($code === '') {
            return redirect()->route('ibkr.status')->with('error', 'Código de autorização ausente.');
        }
        $res = $svc->exchangeCodeForToken($code);
        if (!$res['ok']) {
            return redirect()->route('ibkr.status')->with('error', 'Falha ao trocar código por token.');
        }
        $data = $res['data'] ?? [];
        // Estrutura comum: { access_token, refresh_token, expires_in }
        $request->session()->put('ibkr.tokens', [
            'access_token' => $data['access_token'] ?? null,
            'refresh_token' => $data['refresh_token'] ?? null,
            'expires_in' => $data['expires_in'] ?? null,
            'saved_at' => now(),
        ]);
        return redirect()->route('ibkr.status')->with('success', 'Conectado com sucesso.');
    }

    public function status(Request $request, IbkrWebApiService $svc)
    {
        $tokens = (array) $request->session()->get('ibkr.tokens', []);
        $status = null;
        if (!empty($tokens['access_token'])) {
            $status = $svc->getAuthStatus($tokens['access_token']);
        }
        // Base para links do gateway local (https://{host}:{port})
        $scheme = (string) config('ibkr.gateway_scheme', 'https');
        $host   = $request->getHost();
        $port   = (int) config('ibkr.gateway_port', 5001);
        $base   = sprintf('%s://%s:%d', $scheme, $host, $port);
        return view('ibkr.status', [
            'tokens' => $tokens,
            'status' => $status,
            'base'   => $base,
        ]);
    }

    public function accounts(Request $request, IbkrWebApiService $svc)
    {
        // 1) Tenta via gateway local (https://{host}:{port}), reenviando cookies do navegador
        $scheme = (string) config('ibkr.gateway_scheme', 'https');
        $host   = $request->getHost();
        $port   = (int) config('ibkr.gateway_port', 5001);
        $base   = sprintf('%s://%s:%d', $scheme, $host, $port);

        try {
            $client = new Client([
                'base_uri' => $base . '/',
                'timeout' => (float) config('ibkr.http_timeout', 10.0),
                'connect_timeout' => (float) config('ibkr.http_connect_timeout', 5.0),
                'verify' => (bool) config('ibkr.gateway_verify', false),
            ]);
            $cookieHeader = (string) $request->headers->get('cookie', '');
            $resp = $client->get('v1/api/portfolio/accounts', [
                'headers' => array_filter([
                    'Accept' => 'application/json',
                    'Cookie' => $cookieHeader ?: null,
                ]),
            ]);
            $json = json_decode((string) $resp->getBody(), true) ?: [];
            if ($request->boolean('raw')) {
                return response()->json($json);
            }
            return view('ibkr.accounts', [ 'accounts' => $json, 'source' => 'gateway', 'base' => $base ]);
        } catch (GuzzleException $e) {
            Log::warning('IBKR gateway accounts error', ['code'=>$e->getCode(),'msg'=>substr($e->getMessage(),0,180)]);
            // Continua para fallback via serviço (OAuth org API), se disponível
        } catch (\Throwable $e) {
            Log::warning('IBKR gateway accounts error (throwable)', ['msg'=>substr($e->getMessage(),0,180)]);
        }

        // 2) Fallback: usa token OAuth salvo e consulta via serviço externo
        $tokens = (array) $request->session()->get('ibkr.tokens', []);
        if (!empty($tokens['access_token'])) {
            $res = $svc->getAccounts($tokens['access_token']);
            if ($res['ok']) {
                if ($request->boolean('raw')) {
                    return response()->json($res['data']);
                }
                return view('ibkr.accounts', [ 'accounts' => $res['data'], 'source' => 'oauth' ]);
            }
        }

        return redirect()->route('ibkr.status')->with('error', 'Falha ao consultar contas. Inicie o SSO (Dispatcher) e tente novamente.');
    }

    public function apiWeb(Request $request)
    {
        // Renderiza a view construindo base (scheme + host + :porta) conforme solicitado (porta 5001 por padrão)
    $scheme = (string) config('ibkr.gateway_scheme', 'https');
        $host   = $request->getHost();
        $port   = (int) config('ibkr.gateway_port', 5001);
        $base   = sprintf('%s://%s:%d', $scheme, $host, $port);
        return view('ibkr.api_web', [ 'base' => $base ]);
    }

    public function gatewayAccounts(Request $request)
    {
        // Renderiza view que buscará diretamente no gateway local via fetch do navegador (mantém cookies/sessão do gateway)
        $scheme = (string) config('ibkr.gateway_scheme', 'https');
        $host   = $request->getHost();
        $port   = (int) config('ibkr.gateway_port', 5001);
        $base   = sprintf('%s://%s:%d', $scheme, $host, $port);
        return view('ibkr.accounts_gateway', [ 'base' => $base ]);
    }

    // Formulário para importar/colar JSON de contas e renderizar como view
    public function importAccountsForm(Request $request)
    {
        return view('ibkr.import_accounts');
    }

    public function importAccountsProcess(Request $request)
    {
        $data = $request->validate([
            'json' => ['nullable','string'],
            'json_file' => ['nullable','file'],
            'save' => ['nullable','boolean'],
        ]);

        $jsonStr = trim((string)($data['json'] ?? ''));
        if ($jsonStr === '' && $request->hasFile('json_file')) {
            $jsonStr = (string) file_get_contents($request->file('json_file')->getRealPath());
        }
        if ($jsonStr === '') {
            return back()->with('error', 'Informe o JSON (colar) ou envie um arquivo .json.')->withInput();
        }

        $decoded = json_decode($jsonStr, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            return back()->with('error', 'JSON inválido: '.json_last_error_msg())->withInput();
        }

        // Opcionalmente salva uma cópia em storage local
        $savedPath = null;
        if ($request->boolean('save')) {
            $dir = 'tmp/ibkr';
            $name = 'accounts-'.date('Ymd-His').'.json';
            Storage::disk('local')->put($dir.'/'.$name, $jsonStr);
            $savedPath = storage_path('app/'.$dir.'/'.$name);
        }

        return view('ibkr.accounts', [
            'accounts' => $decoded,
            'source' => 'manual',
            'saved_path' => $savedPath,
        ]);
    }

    public function saveGatewayAccounts(Request $request)
    {
        // Tenta obter diretamente do gateway local no servidor e salvar em storage
        $scheme = (string) config('ibkr.gateway_scheme', 'https');
        $host   = $request->getHost();
        $port   = (int) config('ibkr.gateway_port', 5001);
        $base   = sprintf('%s://%s:%d', $scheme, $host, $port);

        try {
            $client = new Client([
                'base_uri' => $base . '/',
                'timeout' => (float) config('ibkr.http_timeout', 10.0),
                'connect_timeout' => (float) config('ibkr.http_connect_timeout', 5.0),
                'verify' => (bool) config('ibkr.gateway_verify', false),
            ]);
            $cookieHeader = (string) $request->headers->get('cookie', '');
            $resp = $client->get('v1/api/portfolio/accounts', [
                'headers' => array_filter([
                    'Accept' => 'application/json',
                    // Tenta repassar cookies do navegador (se existirem)
                    'Cookie' => $cookieHeader ?: null,
                ]),
            ]);
            $body = (string) $resp->getBody();
            $json = json_decode($body, true);
            if ($json === null && json_last_error() !== JSON_ERROR_NONE) {
                return back()->with('error', 'Resposta do gateway não é JSON válido.');
            }
            $dir = 'tmp/ibkr';
            $name = 'accounts-'.date('Ymd-His').'.json';
            Storage::disk('local')->put($dir.'/'.$name, $body);
            // Também salva um symlink lógico "latest"
            Storage::disk('local')->put($dir.'/accounts-latest.json', $body);

            return view('ibkr.accounts', [
                'accounts' => $json,
                'source' => 'gateway-saved',
                'saved_path' => storage_path('app/'.$dir.'/'.$name),
                'base' => $base,
            ])->with('success', 'JSON salvo em storage e exibido');
        } catch (GuzzleException $e) {
            Log::warning('IBKR saveGatewayAccounts error', ['code'=>$e->getCode(),'msg'=>substr($e->getMessage(),0,180)]);
            return back()->with('error', 'Não foi possível baixar do gateway (sessão/CORS/SSL). Abra o SSO Dispatcher e tente novamente.');
        } catch (\Throwable $e) {
            Log::warning('IBKR saveGatewayAccounts error (throwable)', ['msg'=>substr($e->getMessage(),0,180)]);
            return back()->with('error', 'Erro ao salvar JSON do gateway.');
        }
    }
}
