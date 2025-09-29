<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Faixas Salariais por Representante</title>
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
    <h2>PDF de faixas salariais por representante</h2>
    <p><strong>Representante:</strong> {{ $rep->nome }}</p>
    <p><strong>Período:</strong> {{ \Carbon\Carbon::parse($request->data_ini)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($request->data_fim)->format('d/m/Y') }}</p>
    <hr>
    @foreach($envios as $envio)
        <h3>{{ $envio->nome ?? ('Envio #' . $envio->id) }} <span style="font-weight:normal;">({{ $envio->created_at ? $envio->created_at->format('d/m/Y') : '-' }})</span></h3>
        <table>
            <thead>
                <tr>
                    <th>Faixa</th>
                    <th>Senioridade</th>
                    <th>Tipo Contrato</th>
                    <th>Periodicidade</th>
                    <th class="text-end">Valor Mínimo (R$)</th>
                    <th class="text-end">Valor Máximo (R$)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($envio->safFaixasSalariais as $f)
                <tr>
                    <td>{{ $f->nome }}</td>
                    <td>{{ $f->senioridade }}</td>
                    <td>{{ $f->tipo_contrato }}</td>
                    <td>{{ $f->periodicidade }}</td>
                    <td class="text-end">{{ number_format($f->valor_minimo,2,',','.') }}</td>
                    <td class="text-end">{{ number_format($f->valor_maximo,2,',','.') }}</td>
                </tr>
                @endforeach
                <tr class="total">
                    <td colspan="4">Totais deste envio</td>
                    <td class="text-end">R$ {{ number_format($envio->safFaixasSalariais->sum('valor_minimo'),2,',','.') }}</td>
                    <td class="text-end">R$ {{ number_format($envio->safFaixasSalariais->sum('valor_maximo'),2,',','.') }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach
    <hr>
    <h3>Total geral valores mínimos: R$ {{ number_format($totalGeralMin,2,',','.') }}</h3>
    <h3>Total geral valores máximos: R$ {{ number_format($totalGeralMax,2,',','.') }}</h3>
    <p>Quantidade total de faixas listadas: {{ $totalLinhas }}</p>
</body>
</html>
