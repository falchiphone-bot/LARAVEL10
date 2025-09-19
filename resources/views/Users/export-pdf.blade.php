<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Usuários (PDF)</title>
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
    </style>
    </head>
<body>
    <div class="header">
        <table style="width: 100%; border: none; border-collapse: collapse;">
            <tr>
                <td style="border: none; width: 140px;">
                    <?php
                        $resolvedLogoPath = null; $logoSrc = null; $useUrl = isset($logoUrl) && $logoUrl;
                        if ($useUrl) { $logoSrc = $logoUrl; } else {
                            $candidates = [
                                public_path('images/logo.png'),
                                public_path('storage/images/logo.png'),
                                storage_path('app/public/images/logo.png'),
                                base_path('app/public/images/logo.png'),
                            ];
                            foreach ($candidates as $p) { if (file_exists($p)) { $resolvedLogoPath = $p; break; } }
                            if ($resolvedLogoPath) {
                                $mime = @mime_content_type($resolvedLogoPath) ?: 'image/png';
                                $bin = @file_get_contents($resolvedLogoPath);
                                if ($bin !== false) { $logoSrc = 'data:'.$mime.';base64,'.base64_encode($bin); }
                            }
                        }
                    ?>
                    <?php if($logoSrc): ?>
                        <img src="<?= $logoSrc ?>" class="logo" alt="Logo">
                    <?php else: ?>
                        <div class="small">[sem logo]</div>
                    <?php endif; ?>
                </td>
                <td style="border: none; text-align: right;">
                    <h1>{{ $headerTitle ?? 'Usuários' }}</h1>
                    <div class="meta">{{ $headerSubtitle ?? ('Gerado em: '.date('d/m/Y H:i')) }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <table style="width: 100%; border: none; border-collapse: collapse;">
            <tr>
                <td style="border: none; text-align: left;">
                    <?php $userName = auth()->user()->name ?? null; $defaultLeft = 'Relatório gerado por ' . ($userName ?: config('app.name', 'Laravel')); ?>
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
                    <th>Email</th>
                    <th>Data de cadastro</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registros as $r)
                    <tr>
                        <td>{{ $r->name }}</td>
                        <td>{{ $r->email }}</td>
                        <td>{{ optional($r->created_at)->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="small">Nenhum registro encontrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </main>
</body>
</html>
