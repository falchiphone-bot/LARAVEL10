<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $t0 = microtime(true);
        $request->authenticate();
        $t1 = microtime(true);

        $request->session()->regenerate();
        $t2 = microtime(true);

        if (config('app.log_login_timing')) {
            Log::info('login.timing', [
                'auth_ms' => (int) (($t1 - $t0) * 1000),
                'session_ms' => (int) (($t2 - $t1) * 1000),
                'total_ms' => (int) (($t2 - $t0) * 1000),
                'ip' => $request->ip(),
                'email' => $request->input('email'),
            ]);
        }

    // Use 303 See Other para garantir que o navegador faça GET na próxima página após POST /login
    return redirect()->intended(RouteServiceProvider::HOME, 303);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response|RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Após logout, direciona para a página de login (em qualquer domínio).
        // Isso evita cair em páginas públicas diferentes por subdomínio e
        // simplifica a UX (especialmente em contabilidade.falchi.com.br).
            // Para requisições AJAX/JSON, responde 204 sem redirecionar
            if ($request->expectsJson() || $request->ajax()) {
                return response()->noContent(204);
            }
            // Redireciona padrão (não-AJAX). Mantém como fallback.
            return redirect()->route('logout.goodbye', [], 303);
    }
}
