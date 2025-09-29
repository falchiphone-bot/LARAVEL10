<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Faixas Salariais - Valores Mínimos</title>
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
    <h2>Faixas Salariais - Valores Mínimos</h2>
    <table>
        <thead>
            <tr>
                <th>Faixa</th>
                <th>Senioridade</th>
                <th>Tipo Contrato</th>
                <th>Periodicidade</th>
                <th class="text-end">Valor Mínimo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($faixas as $f)
            <tr>
                <td>{{ $f->nome }}</td>
                <td>{{ $f->senioridade }}</td>
                <td>{{ $f->tipo_contrato }}</td>
                <td>{{ $f->periodicidade }}</td>
                <td class="text-end">{{ number_format($f->valor_minimo,2,',','.') }}</td>
            </tr>
            @endforeach
            <tr class="total">
                <td colspan="4">Total dos valores mínimos</td>
                <td class="text-end">R$ {{ number_format($totalMinimos,2,',','.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
