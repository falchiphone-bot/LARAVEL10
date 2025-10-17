<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Balancete</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; }
        th { background: #f1f1f1; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
    <h3>Balancete por período</h3>
    @php $temGrupos = !empty($grupos ?? []); @endphp
    @if($temGrupos)
        @foreach($grupos as $g)
            <h4 style="margin-top:12px;">{{ $g['label'] }}</h4>
            <table>
                <thead>
                    <tr>
                        <th>Conta</th>
                        <th>Classificação</th>
                        <th class="text-end">Débito</th>
                        <th class="text-end">Crédito</th>
                        <th class="text-end">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($g['linhas'] as $l)
                    <tr>
                        <td>
                            {{ $l['conta'] }}
                        </td>
                        <td>{{ $l['codigo'] ?? '' }}</td>
                        <td class="text-end">{{ number_format($l['debito'], 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($l['credito'], 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($l['saldo'], 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>Subtotal {{ $g['label'] }}</th>
                        <th></th>
                        <th class="text-end">{{ number_format($g['totDeb'] ?? 0, 2, ',', '.') }}</th>
                        <th class="text-end">{{ number_format($g['totCred'] ?? 0, 2, ',', '.') }}</th>
                        <th class="text-end">{{ number_format($g['totSaldo'] ?? 0, 2, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        @endforeach
        <h4 style="margin-top:12px;">Totais gerais</h4>
        <table>
            <tfoot>
                <tr>
                    <th>Total</th>
                    <th></th>
                    <th class="text-end">{{ number_format($totDeb, 2, ',', '.') }}</th>
                    <th class="text-end">{{ number_format($totCred, 2, ',', '.') }}</th>
                    <th class="text-end">{{ number_format($totSaldo, 2, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>

        <h4 style="margin-top:12px;">Demonstrativo de Resultado</h4>
        <table>
            <tbody>
                <tr>
                    <th>Receitas</th>
                    <td class="text-end">{{ number_format($dreReceitas ?? 0, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>Despesas</th>
                    <td class="text-end">{{ number_format($dreDespesas ?? 0, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>Resultado</th>
                    <td class="text-end">{{ number_format($dreResultado ?? 0, 2, ',', '.') }} {{ ($dreResultado ?? 0) < 0 ? '(PREJUÍZO)' : '(LUCRO)' }}</td>
                </tr>
            </tbody>
        </table>
    @else
        <table>
            <thead>
                <tr>
                    <th>Conta</th>
                    <th>Classificação</th>
                    <th class="text-end">Débito</th>
                    <th class="text-end">Crédito</th>
                    <th class="text-end">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($linhas as $l)
                <tr>
                    <td>
                        {{ $l['conta'] }}
                    </td>
                    <td>{{ $l['codigo'] ?? '' }}</td>
                    <td class="text-end">{{ number_format($l['debito'], 2, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($l['credito'], 2, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($l['saldo'], 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th>Total</th>
                    <th></th>
                    <th class="text-end">{{ number_format($totDeb, 2, ',', '.') }}</th>
                    <th class="text-end">{{ number_format($totCred, 2, ',', '.') }}</th>
                    <th class="text-end">{{ number_format($totSaldo, 2, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>

        <h4 style="margin-top:12px;">Demonstrativo de Resultado</h4>
        <table>
            <tbody>
                <tr>
                    <th>Receitas</th>
                    <td class="text-end">{{ number_format($dreReceitas ?? 0, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>Despesas</th>
                    <td class="text-end">{{ number_format($dreDespesas ?? 0, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>Resultado</th>
                    <td class="text-end">{{ number_format($dreResultado ?? 0, 2, ',', '.') }} {{ ($dreResultado ?? 0) < 0 ? '(PREJUÍZO)' : '(LUCRO)' }}</td>
                </tr>
            </tbody>
        </table>
    @endif
</body>
</html>
