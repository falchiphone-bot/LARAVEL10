<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Envios sem Faixa Salarial</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h2>Sem nenhuma faixa salarial</h2>
    @if($rep)
    <p><strong>Representante:</strong> {{ $rep->nome }}</p>
    @else
    <p><strong>Representante:</strong> (Todos)</p>
    @endif
    <p><strong>Período (criação de envio):</strong> {{ \Carbon\Carbon::parse($request->data_ini)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($request->data_fim)->format('d/m/Y') }}</p>
    <hr>
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Cadastrado em</th>
            </tr>
        </thead>
        <tbody>
            @forelse($envios as $envio)
            <tr>
                <td>{{ $envio->nome ?? ('Envio #'.$envio->id) }}</td>
                <td>{{ optional($envio->created_at)->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="2">Nenhum envio sem faixa salarial no período.</td>
            </tr>
            @endforelse
            <tr>
                <th>Total</th>
                <th>{{ $totalLinhas }}</th>
            </tr>
        </tbody>
    </table>
</body>
</html>
