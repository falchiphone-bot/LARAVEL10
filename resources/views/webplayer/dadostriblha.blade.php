
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dados da trilha - LIVEPRF</title>
    <style>
        html,body{margin:0;padding:0;background:#0b1220;color:#e5e7eb;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial,sans-serif}
        .wrap{padding:12px}
        h2{margin:0 0 8px;font-size:16px;color:#fff}
        .box{background:#111a2b;border:1px solid #1e2a44;border-radius:10px;padding:12px}
        .section{margin-top:10px}
        .note{font-size:12px;opacity:.7;margin-top:8px}
        a{color:#8ab4ff}
    </style>
    <!-- AVISO: os scripts abaixo são fornecidos pelo provedor de streaming e podem não carregar em conexões HTTPS devido a mixed content -->
</head>
<body>
<div class="wrap">
    <div class="box">
        <h2>Agora tocando</h2>
        <div id="streaminfo" class="section">
            <script src="{{ route('radio.liveprf.js.streaminfo') }}"></script>
        </div>

        <h2 class="section">Faixas recentes</h2>
        <div id="recenttracks" class="section">
            <script src="{{ route('radio.liveprf.js.recenttracks') }}"></script>
        </div>

        <p class="note">Se nada aparecer, o provedor pode estar indisponível. Teste os proxies locais:
            <a href="{{ route('radio.liveprf.js.streaminfo') }}" target="_blank" rel="noopener">streaminfo.js</a>
            e <a href="{{ route('radio.liveprf.js.recenttracks') }}" target="_blank" rel="noopener">recenttracks.js</a>.
        </p>
    </div>
    
</div>
</body>
</html>
