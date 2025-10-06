<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>SAF - Colaboradores (PDF)</title>
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
        /* Bloco por registro */
        .record { width: 100%; border: 1px solid #bbb; margin: 8px 0 10px; page-break-inside: avoid; }
        .record td { border: 1px solid #ddd; vertical-align: top; }
        .record-header { background: #f7f7f7; font-weight: bold; }
        .text-right { text-align: right; }
        .muted { color: #666; }
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
                    <h1>{{ $headerTitle ?? 'SAF - Colaboradores' }}</h1>
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
        @if(isset($aggregates))
            <table style="width:100%; margin-bottom:10px; border:1px solid #bbb; border-collapse:collapse;">
                <thead>
                    <tr>
                        <th colspan="7" style="background:#f2f2f2; text-align:left;">Resumo de Salários (Registros Filtrados)</th>
                    </tr>
                    <tr>
                        <th>Total Registros</th>
                        <th>Ativos</th>
                        <th>Soma Salários</th>
                        <th>Soma (Ativos)</th>
                        <th>Média</th>
                        <th>Mínimo</th>
                        <th>Máximo</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ number_format($aggregates->total_registros ?? 0,0,',','.') }}</td>
                        <td>{{ number_format($aggregates->total_ativos ?? 0,0,',','.') }}</td>
                        <td>R$ {{ number_format($aggregates->soma_salarios ?? 0,2,',','.') }}</td>
                        <td>R$ {{ number_format($aggregates->soma_salarios_ativos ?? 0,2,',','.') }}</td>
                        <td>R$ {{ number_format($aggregates->media_salarios ?? 0,2,',','.') }}</td>
                        <td>R$ {{ number_format($aggregates->min_salario ?? 0,2,',','.') }}</td>
                        <td>R$ {{ number_format($aggregates->max_salario ?? 0,2,',','.') }}</td>
                    </tr>
                </tbody>
            </table>
        @endif

        @if(isset($totaisForma) && $totaisForma->count())
            <table style="width:100%; margin-bottom:15px; border:1px solid #bbb; border-collapse:collapse;">
                <thead>
                    <tr>
                        <th colspan="2" style="background:#f2f2f2; text-align:left;">Totais por Forma de Pagamento</th>
                    </tr>
                    <tr>
                        <th>Forma de Pagamento</th>
                        <th>Total Salários</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($totaisForma as $tf)
                        <tr>
                            <td>{{ $tf->forma_pagamento_nome ?? '—' }}</td>
                            <td>R$ {{ number_format($tf->total ?? 0,2,',','.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @forelse($registros as $r)
            <table class="record">
                <tbody>
                    <tr class="record-header">
                        <td><strong>Nome:</strong> {{ $r->nome }}</td>
                        <td class="text-right"><strong>Ativo:</strong> {{ $r->ativo ? 'SIM' : 'NÃO' }}</td>
                    </tr>
                    <tr>
                        <td colspan="2"><strong>Representante:</strong> {{ optional($r->representante)->nome }}</td>
                    </tr>
                    <tr>
                        <td><strong>Função:</strong> {{ optional($r->funcaoProfissional)->nome }}</td>
                        <td><strong>Tipo:</strong> {{ optional($r->tipoPrestador)->nome }}</td>
                    </tr>
                    <tr>
                        <td><strong>Faixa:</strong> {{ optional($r->faixaSalarial)->nome }}</td>
                        <td><strong>Documento:</strong> {{ $r->documento }}  <span class="muted">| CPF:</span> {{ $r->cpf }}</td>
                    </tr>
                    <tr>
                        <td><strong>Valor de salário:</strong> {{ $r->valor_salario !== null ? number_format($r->valor_salario, 2, ',', '.') : '' }}</td>
                        <td><strong>Chave PIX:</strong> {{ optional($r->pix)->nome }}</td>
                    </tr>
                    <tr>
                        <td><strong>Forma de Pagamento:</strong> {{ optional($r->formaPagamento)->nome }}</td>
                        <td><strong>Dia do pagamento:</strong> {{ $r->dia_pagamento !== null ? str_pad((string)$r->dia_pagamento, 2, '0', STR_PAD_LEFT) : '' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong> {{ $r->email }}</td>
                        <td><strong>Telefone:</strong> {{ $r->telefone }}</td>
                    </tr>
                    <tr>
                        <td colspan="2"><strong>Local:</strong> {{ $r->cidade }} - {{ $r->uf }} {{ $r->pais ? ' | '.$r->pais : '' }}</td>
                    </tr>
                </tbody>
            </table>
        @empty
            <table class="record"><tr><td class="small">Nenhum registro encontrado.</td></tr></table>
        @endforelse
    </main>
</body>
</html>
