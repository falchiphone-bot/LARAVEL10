@php
    // Estilos e layout para PDF de Faixas Salariais
@endphp
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>SAF - Faixas Salariais (PDF)</title>
    <style>
        * { box-sizing: border-box; }
        @page { margin: 105px 30px 60px 30px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 18px; margin: 0 0 6px; }
        .meta { font-size: 11px; color: #444; margin: 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px 8px; border: 1px solid #ccc; }
        th { background: #f2f2f2; text-align: left; }
        .small { font-size: 10px; color: #666; }
        .header { position: fixed; top: -90px; left: 0; right: 0; height: 90px; }
        .footer { position: fixed; bottom: -40px; left: 0; right: 0; height: 40px; font-size: 10px; color: #666; }
        .logo { height: 70px; }
        .page-number:before { content: counter(page); }
        .total-pages:before { content: counter(pages); }
        .text-end { text-align: right; }
    </style>
    @php
        // Função helper local para formatar valores monetários e datas
        $fmtMoney = function($valor, $moeda) {
            $prefix = $moeda === 'BRL' ? 'R$ ' : '';
            return $prefix . number_format((float)$valor, 2, ',', '.');
        };
        $fmtDate = function($date) {
            if (!$date) return '';
            try { return \Illuminate\Support\Carbon::parse($date)->format('d/m/Y'); } catch (Exception $e) { return (string)$date; }
        };
    @endphp
</head>
<body>
    <div class="header">
        <table style="width: 100%; border: none; border-collapse: collapse;">
            <tr>
                <td style="border: none; width: 140px;">
                    @php
                        $resolvedLogoPath = null;
                        $logoSrc = null;
                        $useUrl = isset($logoUrl) && $logoUrl;
                        if ($useUrl) {
                            $logoSrc = $logoUrl;
                        } else {
                            $candidates = [
                                public_path('images/logo.png'),
                                public_path('storage/images/logo.png'),
                                storage_path('app/public/images/logo.png'),
                                base_path('app/public/images/logo.png'),
                            ];
                            foreach ($candidates as $p) {
                                if (file_exists($p)) { $resolvedLogoPath = $p; break; }
                            }
                            if ($resolvedLogoPath) {
                                $mime = @mime_content_type($resolvedLogoPath) ?: 'image/png';
                                $bin = @file_get_contents($resolvedLogoPath);
                                if ($bin !== false) {
                                    $logoSrc = 'data:'.$mime.';base64,'.base64_encode($bin);
                                }
                            }
                        }
                    @endphp
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}" class="logo" alt="Logo">
                    @else
                        <div class="small">[sem logo]</div>
                    @endif
                </td>
                <td style="border: none; text-align: right;">
                    <h1>{{ $headerTitle ?? 'SAF - Faixas Salariais' }}</h1>
                    <div class="meta">{{ $headerSubtitle ?? ('Gerado em: '.date('d/m/Y H:i')) }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <table style="width: 100%; border: none; border-collapse: collapse;">
            <tr>
                <td style="border: none; text-align: left;">
                    @php
                        $userName = auth()->user()->name ?? null;
                        $defaultLeft = 'Relatório gerado por ' . ($userName ?: config('app.name', 'Laravel'));
                    @endphp
                    {{ !empty($footerLeft) ? $footerLeft : $defaultLeft }}
                </td>
                <td style="border: none; text-align: right;">
                    @if(!empty($footerRight))
                        {{ $footerRight }}
                    @else
                        Total: {{ $registros->count() }} | Página <span class="page-number"></span> de <span class="total-pages"></span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <main>
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Função</th>
                    <th>Tipo Prestador</th>
                    <th>Senioridade</th>
                    <th>Contrato</th>
                    <th>Per.</th>
                    <th class="text-end">Mín.</th>
                    <th class="text-end">Máx.</th>
                    <th>Moeda</th>
                    <th>Vigência</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registros as $r)
                    <tr>
                        <td>{{ $r->nome }}</td>
                        <td>{{ optional($r->funcaoProfissional)->nome }}</td>
                        <td>{{ optional($r->tipoPrestador)->nome }}</td>
                        <td>{{ $r->senioridade }}</td>
                        <td>{{ $r->tipo_contrato }}</td>
                        <td>{{ $r->periodicidade }}</td>
                        <td class="text-end">{{ $fmtMoney($r->valor_minimo, $r->moeda) }}</td>
                        <td class="text-end">{{ $fmtMoney($r->valor_maximo, $r->moeda) }}</td>
                        <td>{{ $r->moeda }}</td>
                        <td>{{ $fmtDate($r->vigencia_inicio) }} - {{ $r->vigencia_fim ? $fmtDate($r->vigencia_fim) : 'aberta' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="small">Nenhum registro encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </main>
</body>
</html>
