<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Custos por Representante</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
        th { background: #f0f0f0; }
        .text-end { text-align: right; }
        .total { font-weight: bold; background: #f9f9f9; }
        h3 { margin-bottom: 0; }
    </style>
</head>
<body>
    <h2>Custos por Representante</h2>
    <p><strong>Representante:</strong> {{ $rep->nome }}</p>
    <p><strong>Período:</strong> {{ \Carbon\Carbon::parse($request->data_ini)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($request->data_fim)->format('d/m/Y') }}</p>
    <hr>
    @php
        $custosPorEnvio = $custos->groupBy(function($c) {
            return $c->envio_id;
        });
    @endphp
    @foreach($custosPorEnvio as $envioId => $custosDoEnvio)
        @php $envio = $custosDoEnvio->first()->envio; @endphp
        <h3>{{ $envio->nome ?? ('Envio #' . $envio->id) }} <span style="font-weight:normal;">({{ $envio->created_at ? $envio->created_at->format('d/m/Y') : '-' }})</span></h3>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Nome do Custo</th>
                    <th class="text-end">Valor (R$)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($custosDoEnvio as $c)
                <tr>
                    <td>{{ optional($c->data)->format('d/m/Y') }}</td>
                    <td>{{ $c->nome }}</td>
                    <td class="text-end">{{ number_format($c->valor,2,',','.') }}</td>
                </tr>
                @endforeach
                <tr class="total">
                    <td colspan="2">Total deste envio</td>
                    <td class="text-end">R$ {{ number_format($custosDoEnvio->sum('valor'),2,',','.') }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach
    <hr>
    <h3>Total geral: R$ {{ number_format($totalGeral,2,',','.') }}</h3>
</body>
</html>
