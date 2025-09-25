<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ ($breaker ?? false) ? 'Banco Temporariamente Indisponível' : 'Falha na Conexão com o Banco de Dados' }}</title>
    <style>
        :root { color-scheme: light dark; }
        body {margin:0;font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;background:#f3f4f6;color:#1f2937;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:32px;}
        .card {background:#fff;max-width:740px;width:100%;border-radius:14px;padding:32px;box-shadow:0 4px 16px -4px rgba(0,0,0,.12),0 2px 6px -2px rgba(0,0,0,.06);position:relative;}
        h1 {margin:0 0 12px;font-size:clamp(1.4rem,2.4vw,2rem);display:flex;align-items:center;gap:12px;font-weight:600;}
        h1 span.badge {background:#dc2626;color:#fff;font-size:12px;padding:4px 8px;border-radius:6px;letter-spacing:.5px;}
        ul.meta {list-style:none;margin:0 0 20px;padding:0;display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:6px 18px;font-size:13px;}
        ul.meta li {line-height:1.3;background:#f9fafb;border:1px solid #e5e7eb;padding:6px 8px;border-radius:6px;}
        code.msg {display:block;white-space:pre-wrap;font-size:12px;line-height:1.4;background:#111827;color:#f3f4f6;padding:12px 14px;border-radius:8px;max-height:220px;overflow:auto;margin:0 0 18px;}
        p.tip {font-size:13px;line-height:1.5;margin:0 0 16px;color:#374151;}
        .actions {display:flex;flex-wrap:wrap;gap:10px;margin-top:4px;}
        .btn {text-decoration:none;display:inline-flex;align-items:center;gap:6px;font-size:14px;font-weight:500;padding:10px 18px;border-radius:8px;border:1px solid transparent;cursor:pointer;transition:.18s background,.18s color,.18s border-color;}
        .btn-primary {background:#2563eb;color:#fff;}
        .btn-primary:hover {background:#1d4ed8;}
        .btn-secondary {background:#4b5563;color:#fff;}
        .btn-secondary:hover {background:#374151;}
        .btn-outline {background:#fff;border-color:#d1d5db;color:#374151;}
        .btn-outline:hover {border-color:#9ca3af;background:#f3f4f6;}
        footer {margin-top:28px;font-size:11px;text-align:center;color:#6b7280;}
        @media (max-width:560px){.card{padding:22px;} ul.meta{grid-template-columns:1fr 1fr;} h1{flex-direction:column;align-items:flex-start;} }
        @media (prefers-color-scheme: dark){body{background:#111827;color:#e5e7eb;} .card{background:#1f2937;box-shadow:0 4px 18px -4px rgba(0,0,0,.5);} ul.meta li{background:#374151;border-color:#4b5563;color:#e5e7eb;} p.tip{color:#d1d5db;} .btn-outline{background:#1f2937;color:#e5e7eb;border-color:#4b5563;} .btn-outline:hover{background:#374151;} footer{color:#9ca3af;} }
    </style>
</head>
<body>
    <main class="card" role="main" aria-labelledby="titulo-erro-db">
        <h1 id="titulo-erro-db">
            <span class="badge">DB</span>
            {{ ($breaker ?? false) ? 'Banco de dados temporariamente Indisponível. Avise o administrador do sistema.' : 'Falha na Conexão com o Banco de Dados' }}
        </h1>

        @if(!empty($exceptionMessage))
            <code class="msg" aria-label="Mensagem técnica">
{{ trim($exceptionMessage) }}
            </code>
        @endif

        <ul class="meta" aria-label="Metadados da conexão">
            <li><strong>Driver:</strong><br>{{ $driver ?? 'n/d' }}</li>
            <li><strong>Conexão:</strong><br>{{ $connectionName ?? 'n/d' }}</li>
            <li><strong>Host:</strong><br>{{ $host ?? 'n/d' }}</li>
            <li><strong>Database:</strong><br>{{ $database ?? 'n/d' }}</li>
            <li><strong>Data/Hora:</strong><br>{{ now()->format('d/m/Y H:i:s') }}</li>
            @if(!empty($requestId))
                <li><strong>Request ID:</strong><br>{{ $requestId }}</li>
            @endif
        </ul>

        @if($breaker ?? false)
            <p class="tip">
                O banco de dados está indisponível e estamos evitando novas tentativas por alguns segundos
                para não sobrecarregar o sistema (circuit breaker ativo).
            </p>
            @if(!empty($retryAfter))
                <p class="tip">Tente novamente em aproximadamente <strong>{{ $retryAfter }}s</strong>. A página não atualiza sozinha.</p>
            @endif
        @else
            <p class="tip">
                O sistema não conseguiu estabelecer comunicação com o servidor de banco de dados.
                Possíveis causas: serviço parado, firewall/porta bloqueada, credenciais inválidas, timeout de rede
                ou saturação de recursos. Revise as configurações do <code>.env</code> e o status do servidor SQL.
            </p>
            <p class="tip">Após corrigir, clique em <strong>Tentar novamente</strong>. Se o problema persistir, acione o suporte.</p>
        @endif

        <div class="actions">
            <a class="btn btn-primary" href="{{ url()->current() }}">Tentar novamente</a>
            <a class="btn btn-secondary" href="/">Ir para Início</a>
            @if(app()->environment('local') && config('app.debug'))
                <button class="btn btn-outline" onclick="location.reload()">Reload</button>
            @endif
        </div>
        <footer>
            HTTP {{ ($breaker ?? false) ? 503 : 500 }} &middot; {{ config('app.name') }}
        </footer>
    </main>
</body>
</html>
