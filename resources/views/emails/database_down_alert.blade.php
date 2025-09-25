@php($app = config('app.name'))
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>[{{ $app }}] Falha de Conexão Banco De Dados SQL ({{ $connection ?? $dbConnection ?? 'n/d' }})</title>
    <style>
        body {font-family: system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;color:#111;margin:0;padding:24px;background:#f8fafc;}
        .card {background:#fff;border-radius:8px;padding:24px;max-width:640px;margin:0 auto;border:1px solid #e2e8f0;}
        h1 {font-size:18px;margin:0 0 12px;color:#b91c1c;}
        table {width:100%;border-collapse:collapse;font-size:13px;margin:16px 0;}
        th,td {text-align:left;padding:6px 8px;border-bottom:1px solid #f1f5f9;vertical-align:top;}
        th {width:150px;font-weight:600;color:#334155;background:#f8fafc;}
        pre {white-space:pre-wrap;font-size:12px;background:#1e293b;color:#f1f5f9;padding:12px;border-radius:6px;line-height:1.4;margin:0;}
        .small {font-size:12px;color:#475569;margin-top:24px;}
    </style>
</head>
<body>
    <div class="card">
        <h1>Alerta: Falha de Conexão com o Banco de Dados SQL</h1>
        <p>O aplicativo <strong>{{ " de PEDRO ROBERTO FALCHI " }}</strong> detectou uma falha ao tentar conectar ao banco de dados SQL.</p>
        <table>
            <tr><th>Conexão</th><td>{{ $connection ?? $dbConnection ?? 'n/d' }}</td></tr>
            <tr><th>Driver</th><td>{{ $driver ?? 'n/d' }}</td></tr>
            <tr><th>Host</th><td>{{ $host ?? 'n/d' }}</td></tr>
            <tr><th>Database</th><td>{{ $database ?? 'n/d' }}</td></tr>
            <tr><th>Data/Hora</th><td>{{ \Carbon\Carbon::parse($ts)->timezone(config('app.timezone'))->format('d/m/Y H:i:s') }}</td></tr>
        </table>
        @if($errorMessage)
            <p><strong>Mensagem:</strong></p>
            <pre>{{ $errorMessage }}</pre>
        @endif
        <p style="margin-top:18px;font-size:13px;line-height:1.5;">Este e-mail é disparado somente na primeira detecção dentro da janela de supressão.</p>
        <p class="small">&copy; {{ date('Y') }} {{ 'PEDRO ROBERTO FALCHI' }}</p>
    </div>
</body>
</html>
