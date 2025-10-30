<?php

namespace App\Http\Controllers;

use App\Services\IbkrWebApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        return view('ibkr.status', [
            'tokens' => $tokens,
            'status' => $status,
        ]);
    }

    public function accounts(Request $request, IbkrWebApiService $svc)
    {
        $tokens = (array) $request->session()->get('ibkr.tokens', []);
        if (empty($tokens['access_token'])) {
            return redirect()->route('ibkr.connect')->with('error', 'Conecte sua conta IBKR primeiro.');
        }
        $res = $svc->getAccounts($tokens['access_token']);
        if (!$res['ok']) {
            return back()->with('error', 'Falha ao consultar contas IBKR.');
        }
        return response()->json($res['data']);
    }
}
