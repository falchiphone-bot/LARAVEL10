<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Faixas Salariais sem Valor Mínimo</title>
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
    <h2>Faixas salariais sem valor mínimo definido</h2>
    <p><strong>Representante:</strong> {{ $rep->nome }}</p>
    <p><strong>Período (criação de envio):</strong> {{ \Carbon\Carbon::parse($request->data_ini)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($request->data_fim)->format('d/m/Y') }}</p>
    <hr>
    @forelse($envios as $envio)
        <h3>{{ $envio->nome ?? ('Envio #' . $envio->id) }} <span style="font-weight:normal;">({{ optional($envio->created_at)->format('d/m/Y') }})</span></h3>
        <table>
            <thead>
                <tr>
                    <th>Envio</th>
                    <th>Faixa</th>
                    <th>Vigência Início</th>
                    <th>Vigência Fim</th>
                    <th>Observações</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @if($envio->flag_sem_faixas)
                <tr>
                    <td>{{ $envio->nome ?? ('Envio #' . $envio->id) }}</td>
                    <td colspan="4">—</td>
                    <td>Sem nenhuma faixa vinculada</td>
                </tr>
                @endif
                @foreach($envio->faixas_sem_valor as $f)
                <tr>
                    <td>{{ $envio->nome ?? ('Envio #' . $envio->id) }}</td>
                    <td>{{ $f->nome }}</td>
                    <td>{{ $f->vigencia_inicio ? \Carbon\Carbon::parse($f->vigencia_inicio)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $f->vigencia_fim ? \Carbon\Carbon::parse($f->vigencia_fim)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $f->observacoes ?? '-' }}</td>
                    <td>{{ is_null($f->valor_minimo) ? 'Sem valor (null)' : 'Valor 0' }}</td>
                </tr>
                @endforeach
                <tr class="total">
                    <td colspan="6">
                        Total de registros deste envio: {{ ($envio->flag_sem_faixas ? 1 : 0) + $envio->faixas_sem_valor->count() }}
                        (faixas sem valor: {{ $envio->faixas_sem_valor->count() }} | sem faixas: {{ $envio->flag_sem_faixas ? 'sim' : 'não' }})
                    </td>
                </tr>
            </tbody>
        </table>
    @empty
        <p>Nenhum envio no período.</p>
    @endforelse
    <hr>
    <h3>Quantidade total de linhas (faixas sem valor + envios sem faixas): {{ $totalLinhas }}</h3>
</body>
</html>
