
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dados da trilha - LIVEPRF</title>
    <style>
        html,body{margin:0;padding:0;background:#0b1220;color:#e5e7eb;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial,sans-serif}
        .wrap{padding:12px}
    h2{margin:0 0 6px;font-size:15px;color:#fff}
    .box{background:#111a2b;border:1px solid #1e2a44;border-radius:10px;padding:12px;min-height:380px}
    .section{margin-top:8px}
        .note{font-size:12px;opacity:.7;margin-top:8px}
        a{color:#8ab4ff}
    </style>
    <script>
        // Atualização leve: recarrega a cada 15s para manter informação fresca
        setTimeout(function(){ try{ location.reload(); }catch(e){} }, 15000);
    </script>
</head>
<body>
<div class="wrap">
    <div class="box">
        <h2>Agora tocando</h2>
        <div class="section">
            @php $s = isset($stream) && is_array($stream) ? $stream : []; @endphp
            @if(!empty($s['song']))
                <div style="font-size:18px;font-weight:700;line-height:1.3">{{ $s['song'] }}</div>
                @if(!empty($s['artist']))
                    <div style="opacity:.85">Artista: {{ $s['artist'] }}</div>
                @endif
                <div class="note">Atualizado: {{ isset($s['updated_at']) ? \Carbon\Carbon::parse($s['updated_at'])->format('d/m/Y H:i:s') : now()->format('d/m/Y H:i:s') }}</div>
            @else
                <div>Indisponível no momento.</div>
                @if(!empty($s['reason']))
                    <div class="note">Motivo: {{ $s['reason'] }}</div>
                @endif
            @endif
        </div>

        <h2 class="section">Músicas Recentes</h2>
        <div class="section">
            <!-- maximo de 5 -->
            @php $limit = 5; @endphp. 
            @php $recentSrv = (isset($recent) && is_array($recent)) ? $recent : ['items' => []]; @endphp
            @if(!empty($recentSrv['items']))
                @php $recentItems = array_slice($recentSrv['items'], 0, $limit); @endphp
                <div style="display:flex;flex-direction:column;gap:4px">
                    @foreach($recentItems as $it)
                        <div class="cctrack" style="display:flex;gap:10px;align-items:center;border-bottom:1px solid #1e2a44;padding:5px 0">
                            @php $img = $it['image_url'] ?? null; @endphp
                            @if(!empty($img))
                                <img src="{{ $img }}" alt="capa" width="36" height="36" style="border-radius:6px;object-fit:cover;background:#111a2b;border:1px solid #1e2a44">
                            @endif
                            @php $href = $it['link'] ?? null; @endphp
                            @php
                                $rel = null;
                                if (!empty($it['timestamp']) && is_numeric($it['timestamp'])) {
                                    try {
                                        $rel = \Carbon\Carbon::createFromTimestamp((int) $it['timestamp'])
                                            ->locale('pt_BR')
                                            ->diffForHumans(null, true) . ' atrás';
                                    } catch (\Throwable $e) {
                                        $rel = null;
                                    }
                                }
                            @endphp
                            <div style="flex:1;min-width:0">
                                <div class="cctitle" style="font-weight:600;color:#e5e7eb;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                    @if(!empty($href))
                                        <a href="{{ $href }}" target="_blank" rel="noopener" style="color:inherit;text-decoration:none">{{ $it['title'] ?? '' }}</a>
                                    @else
                                        {{ $it['title'] ?? '' }}
                                    @endif
                                </div>
                                @if(!empty($it['description']))
                                    <div class="ccartist" style="opacity:.85;font-size:12px">{{ $it['description'] }}</div>
                                @endif
                                @if(!empty($rel))
                                    <div class="cctime" style="opacity:.65;font-size:11px">{{ $rel }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                @php
                    $recentStatic = json_decode('{"title":"Live PRF M\u00fasicas Recentes","description":"","link":"http:\/\/localhost\/","date":1761475160,"generator":"Centova Cast","category":"M\u00fasica","items":[{"title":"Desconhecido - WITHOUT YOU","link":"http:\/\/localhost\/","description":"","date":1761485789,"enclosure":{"url":"http:\/\/paineldj6.com.br:2197\/static\/liveprf\/covers\/nocover.png","type":"image\/png"}},{"title":"Desconhecido - 1131","link":"http:\/\/localhost\/","description":"","date":1761485785,"enclosure":{"url":"http:\/\/paineldj6.com.br:2197\/static\/liveprf\/covers\/nocover.png","type":"image\/png"}},{"title":"DIANA ROSS E LIONEL RICHIE - ENDLESS LOVE","link":"https:\/\/itunes.apple.com\/us\/album\/%C3%A1lbum-desconhecido\/1088144901?uo=4","description":"Desconhecido","date":1761485520,"enclosure":{"url":"https:\/\/is3-ssl.mzstatic.com\/image\/thumb\/Music69\/v4\/16\/bb\/23\/16bb2309-a73c-6cc4-8604-ecaa03350b65\/source\/100x100bb.jpg","type":"image\/jpeg"}},{"title":"Maria Beth\u00c3\u00a2nia - Brincar de Viver  [\u00c3?udio Oficial]","link":"http:\/\/localhost\/","description":"","date":1761485223,"enclosure":{"url":"http:\/\/paineldj6.com.br:2197\/static\/liveprf\/covers\/nocover.png","type":"image\/png"}},{"title":"VERONICA SABINO - DEMAIS","link":"https:\/\/itunes.apple.com\/us\/album\/guitarra-makaka-dan%C3%A7as-a-um-deus-desconhecido\/988094010?uo=4","description":"Desconhecido","date":1761485001,"enclosure":{"url":"https:\/\/is4-ssl.mzstatic.com\/image\/thumb\/Music5\/v4\/f5\/9a\/99\/f59a99c6-6034-40d0-6dcb-15d17a86a28f\/source\/100x100bb.jpg","type":"image\/jpeg"}}]}', true);
                @endphp
                @if(is_array($recentStatic) && !empty($recentStatic['items']))
                    @php $recentStaticItems = array_slice($recentStatic['items'], 0, $limit); @endphp
                    <div style="display:flex;flex-direction:column;gap:4px">
                        @foreach($recentStaticItems as $it)
                            <div class="cctrack" style="display:flex;gap:10px;align-items:center;border-bottom:1px solid #1e2a44;padding:5px 0">
                                @php $img = $it['enclosure']['url'] ?? null; @endphp
                                @if(!empty($img))
                                    <img src="{{ $img }}" alt="capa" width="36" height="36" style="border-radius:6px;object-fit:cover;background:#111a2b;border:1px solid #1e2a44">
                                @endif
                                @php $href = $it['link'] ?? null; @endphp
                                @php
                                    $rel = null;
                                    if (!empty($it['date']) && is_numeric($it['date'])) {
                                        try {
                                            $rel = \Carbon\Carbon::createFromTimestamp((int) $it['date'])
                                                ->locale('pt_BR')
                                                ->diffForHumans(null, true) . ' atrás';
                                        } catch (\Throwable $e) {
                                            $rel = null;
                                        }
                                    }
                                @endphp
                                <div style="flex:1;min-width:0">
                                    <div class="cctitle" style="font-weight:600;color:#e5e7eb;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                        @if(!empty($href))
                                            <a href="{{ $href }}" target="_blank" rel="noopener" style="color:inherit;text-decoration:none">{{ $it['title'] ?? '' }}</a>
                                        @else
                                            {{ $it['title'] ?? '' }}
                                        @endif
                                    </div>
                                    @if(!empty($it['description']))
                                        <div class="ccartist" style="opacity:.85;font-size:12px">{{ $it['description'] }}</div>
                                    @endif
                                    @if(!empty($rel))
                                        <div class="cctime" style="opacity:.65;font-size:11px">{{ $rel }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div>Indisponível.</div>
                @endif
            @endif
        </div>
    </div>

</div>
</body>
</html>
