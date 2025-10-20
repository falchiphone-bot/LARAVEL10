<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rádio online - LIVEPRF</title>
    <meta name="robots" content="noindex,follow">
    <style>
        html,body{height:100%;margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial,sans-serif;background:#0b1220;color:#fff}
        .wrap{min-height:100%;display:flex;align-items:center;justify-content:center;padding:24px}
        .card{background:#111a2b;border:1px solid #1e2a44;border-radius:12px;max-width:520px;width:100%;padding:24px;box-shadow:0 10px 30px rgba(0,0,0,.35)}
        h1{font-size:20px;margin:0 0 12px}
        p{margin:0 0 16px;opacity:.85}
        .live{display:inline-flex;align-items:center;gap:8px;background:#e11d48;color:#fff;border-radius:999px;padding:6px 12px;font-weight:600;margin-bottom:16px}
        .live::before{content:"";width:8px;height:8px;border-radius:50%;background:#fff;box-shadow:0 0 0 6px rgba(255,255,255,.15)}
        .row{display:flex;gap:12px;align-items:center}
        audio{width:100%}
        .meta{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px;font-size:12px;opacity:.8}
        .badge{border:1px solid #2a3b63;border-radius:999px;padding:2px 8px}
        .note{font-size:12px;opacity:.7;margin-top:10px}
        a{color:#8ab4ff}
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="live">AO VIVO</div>
        <h1>LIVEPRF – Rádio online</h1>
        <p>Clique no play para ouvir a transmissão. Mantivemos um player simples e compatível.</p>
        <div class="row">
            <audio controls autoplay preload="none">
                <source src="http://paineldj6.com.br:8071/stream?type=.mp3" type="audio/mp3">
                Seu navegador não suporta áudio HTML5.
            </audio>
        </div>
        <div class="meta">
            <span class="badge">Formato: MP3</span>
            <span class="badge">Porta: 8071</span>
            <span class="badge">Bitrate dinâmico</span>
        </div>
        <p class="note">Se não tocar, verifique se seu navegador bloqueou autoplay com som ou se a origem http://paineldj6.com.br:8071 está acessível.
            Você também pode abrir o stream direto: <a href="http://paineldj6.com.br:8071/stream?type=.mp3" target="_blank" rel="noopener">link do stream</a>.
        </p>
        <div style="margin-top:16px">
            <iframe id="dados-trilha" src="{{ route('radio.liveprf.dados') }}" width="100%" height="360" frameborder="0" style="background:#0b1220;border:1px solid #1e2a44;border-radius:8px"></iframe>
        </div>
        <script>
            // Atualiza os dados da trilha periodicamente (5s)
            setInterval(function(){
                var f = document.getElementById('dados-trilha');
                if (f && f.contentWindow) {
                    try { f.contentWindow.location.reload(); } catch(e) {}
                }
            }, 5000);
        </script>
    </div>
</div>
</body>
</html>
