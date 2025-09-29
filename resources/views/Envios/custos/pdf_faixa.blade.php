<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Custos por Faixa Salarial</title>
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
    <h2>PDF de custos por faixa salarial</h2>
    <p><strong>Período:</strong> {{ $periodo ?? '-' }}</p>
    <hr>
    @foreach($faixas as $faixa)
        <h3>{{ $faixa->nome }}</h3>
        @php $custos = $faixa->custos ?? collect(); @endphp
        <table>
            <thead>
                <tr>
                    <th>Envio</th>
                    <th>Data do Custo</th>
                    <th>Nome do Custo</th>
                    <th class="text-end">Valor (R$)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($custos as $c)
                <tr>
                    <td>{{ $c->envio->nome ?? '-' }}</td>
                    <td>{{ optional($c->data)->format('d/m/Y') }}</td>
                    <td>{{ $c->nome }}</td>
                    <td class="text-end">{{ number_format($c->valor,2,',','.') }}</td>
                </tr>
                @endforeach
                <tr class="total">
                    <td colspan="3">Total desta faixa</td>
                    <td class="text-end">R$ {{ number_format($custos->sum('valor'),2,',','.') }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach
    <hr>
    <h3>Total geral: R$ {{ number_format($totalGeral,2,',','.') }}</h3>
</body>
</html>
