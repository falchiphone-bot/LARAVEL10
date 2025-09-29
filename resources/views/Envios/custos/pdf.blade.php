<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Custos - {{ $envio->nome ?? ('Envio #' . $envio->id) }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
        th { background: #f0f0f0; }
        .text-end { text-align: right; }
        .total { font-weight: bold; background: #f9f9f9; }
    </style>
</head>
<body>
    <h2>Custos - {{ $envio->nome ?? ('Envio #' . $envio->id) }}</h2>
    <p><strong>Data de criação:</strong> {{ $envio->created_at ? $envio->created_at->format('d/m/Y H:i') : '-' }}</p>
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Nome</th>
                <th class="text-end">Valor (R$)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($custos as $c)
            <tr>
                <td>{{ optional($c->data)->format('d/m/Y') }}</td>
                <td>{{ $c->nome }}</td>
                <td class="text-end">{{ number_format($c->valor,2,',','.') }}</td>
            </tr>
            @endforeach
            <tr class="total">
                <td colspan="2">Total</td>
                <td class="text-end">R$ {{ number_format($total,2,',','.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
