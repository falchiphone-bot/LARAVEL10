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
    @php
        $empresaSel = collect($empresas ?? [])->firstWhere('ID', $empresaId);
        $empresaNome = $empresaSel->Descricao ?? '';
    @endphp
    <p style="margin:4px 0 12px 0; font-size: 12px;">
        Empresa: <strong>{{ $empresaNome ?: '—' }}</strong>
        &nbsp;•&nbsp; Período: <strong>{{ $deBr ?: '—' }}</strong> a <strong>{{ $ateBr ?: '—' }}</strong>
    </p>
    @php $temGrupos = !empty($grupos ?? []); @endphp
    @if(($showHier ?? false))
        @php
            $nodeTotals = [];
            $leafNames = [];
            $nodePrevTotals = [];
            foreach(($linhas ?? []) as $l){
                $code = trim((string)($l['codigo'] ?? ''));
                if($code==='') continue;
                $leafNames[$code] = $l['conta'] ?? '';
                $parts = array_values(array_filter(explode('.', $code), fn($p)=>$p!=='' && $p!==null));
                $n = count($parts);
                for($k=1;$k<=min($n,5);$k++){
                    $prefix = implode('.', array_slice($parts,0,$k));
                    if(!isset($nodeTotals[$prefix])) $nodeTotals[$prefix] = ['deb'=>0.0,'cred'=>0.0,'saldo'=>0.0];
                    $nodeTotals[$prefix]['deb'] += (float)($l['debito'] ?? 0);
                    $nodeTotals[$prefix]['cred'] += (float)($l['credito'] ?? 0);
                    $nodeTotals[$prefix]['saldo'] += (float)($l['saldo'] ?? 0);
                }
                if(($showPrev ?? true)){
                    $prev = (float)($l['saldo_anterior'] ?? 0);
                    if($prev != 0){
                        for($k=1;$k<=min($n,5);$k++){
                            $prefix = implode('.', array_slice($parts,0,$k));
                            if(!isset($nodePrevTotals[$prefix])) $nodePrevTotals[$prefix] = 0.0;
                            $nodePrevTotals[$prefix] += $prev;
                        }
                    }
                }
            }
            $codes = array_keys($nodeTotals);
            natcasesort($codes);
        @endphp
        <table>
            <thead>
                <tr>
                    <th>Conta/Nó</th>
                    <th>Classificação</th>
                    <th class="text-end">Débito</th>
                    <th class="text-end">Crédito</th>
                    <th class="text-end">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($codes as $code)
                    @php
                        $parts = array_values(array_filter(explode('.', $code)));
                        $grau = count($parts);
                        $label = $code;
                        if($grau===1){ $label = ($grau1Labels[$code] ?? $code); }
                        elseif($grau===2){ $label = ($grau2Labels[$code] ?? $code); }
                        elseif($grau===3){ $label = ($grau3Labels[$code] ?? $code); }
                        elseif($grau===4){ $label = ($grau4Labels[$code] ?? $code); }
                        else { $label = ($leafNames[$code] ?? $code); }
                        $nt = $nodeTotals[$code];
                        $indent = max(0,$grau-1) * 10;
                        $prevNode = ($nodePrevTotals[$code] ?? 0);
                    @endphp
                    @if(($showPrev ?? true) && $grau <= 4 && abs($prevNode) > 0)
                    <tr>
                        <td><span style="padding-left: {{ $indent }}px; display:inline-block;"><small style="color:#666; font-style: italic;">Saldo Anterior</small></span></td>
                        <td><small style="color:#666;">{{ $code }}</small></td>
                        <td class="text-end"></td>
                        <td class="text-end"></td>
                        <td class="text-end"><small style="font-style: italic;">{{ number_format($prevNode, 2, ',', '.') }}</small></td>
                    </tr>
                    @endif
                    @if(($showPrev ?? true) && $grau === 5 && abs($prevNode) > 0)
                    <tr style="background-color:#e6f2ff;">
                        <td><span style="padding-left: {{ $indent }}px; display:inline-block;"><span style="font-weight:bold; font-style: italic; color:#000;">Saldo Anterior</span></span></td>
                        <td><span style="font-weight:bold; font-style: italic; color:#000;">{{ $code }}</span></td>
                        <td class="text-end"></td>
                        <td class="text-end"></td>
                        <td class="text-end"><span style="font-weight:bold; font-style: italic; color:#000;">{{ number_format($prevNode, 2, ',', '.') }}</span></td>
                    </tr>
                    @endif
                    <tr>
                        <td><span style="padding-left: {{ $indent }}px; display:inline-block;">{{ $label }}</span></td>
                        <td>{{ $code }}</td>
                        <td class="text-end"><strong>{{ number_format($nt['deb'], 2, ',', '.') }}</strong></td>
                        <td class="text-end"><strong>{{ number_format($nt['cred'], 2, ',', '.') }}</strong></td>
                        <td class="text-end"><strong>{{ number_format($nt['saldo'], 2, ',', '.') }}</strong></td>
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
                @php $__res = $dreResultado ?? 0; $__bg = ($__res < 0) ? '#ffe6e6' : '#e6f2ff'; @endphp
                <tr style="background: {{ $__bg }};">
                    <th>Resultado</th>
                    <td class="text-end" style="color:#000; font-weight: bold;">{{ number_format($__res, 2, ',', '.') }} {{ $__res < 0 ? '(PREJUÍZO)' : '(LUCRO)' }}</td>
                </tr>
            </tbody>
        </table>
    @elseif($temGrupos)
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
                @php
                    $nivel4Totals = [];
                    $nivel4PrevTotals = [];
                    foreach(($g['linhas'] ?? []) as $lCalc){
                        $codigoCalc = trim((string)($lCalc['codigo'] ?? ''));
                        if($codigoCalc === '') continue;
                        $parts = array_filter(explode('.', $codigoCalc));
                        if(count($parts) >= 5){
                            $prefix4 = implode('.', array_slice($parts, 0, 4));
                            if(!isset($nivel4Totals[$prefix4])){ $nivel4Totals[$prefix4] = ['deb'=>0.0,'cred'=>0.0,'saldo'=>0.0]; }
                            $nivel4Totals[$prefix4]['deb'] += (float)($lCalc['debito'] ?? 0);
                            $nivel4Totals[$prefix4]['cred'] += (float)($lCalc['credito'] ?? 0);
                            $nivel4Totals[$prefix4]['saldo'] += (float)($lCalc['saldo'] ?? 0);
                            if(($showPrev ?? true)){
                                $nivel4PrevTotals[$prefix4] = ($nivel4PrevTotals[$prefix4] ?? 0) + (float)($lCalc['saldo_anterior'] ?? 0);
                            }
                        }
                    }
                @endphp
                <tbody>
                    @php $lastPrefix4 = null; @endphp
                    @foreach($g['linhas'] as $idx => $l)
                        @php
                            $codigoFull = trim((string)($l['codigo'] ?? ''));
                            $parts = $codigoFull !== '' ? array_values(array_filter(explode('.', $codigoFull))) : [];
                            $prefix4 = count($parts) >= 5 ? implode('.', array_slice($parts, 0, 4)) : null;
                        @endphp
                        @if($prefix4 && $prefix4 !== $lastPrefix4)
                            @php
                                $lastPrefix4 = $prefix4; $tot4 = $nivel4Totals[$prefix4] ?? ['deb'=>0,'cred'=>0,'saldo'=>0];
                                $prev4 = $nivel4PrevTotals[$prefix4] ?? 0;
                                $partsPrefix = explode('.', $prefix4);
                                $p1 = $partsPrefix[0] ?? null;
                                $p2 = count($partsPrefix)>=2 ? implode('.', array_slice($partsPrefix,0,2)) : null;
                                $p3 = count($partsPrefix)>=3 ? implode('.', array_slice($partsPrefix,0,3)) : null;
                                $trail = [];
                                if(!empty($p1) && !empty(($grau1Labels[$p1] ?? null))) $trail[] = ($grau1Labels[$p1] ?? '') . " ($p1)";
                                if(!empty($p2) && !empty(($grau2Labels[$p2] ?? null))) $trail[] = ($grau2Labels[$p2] ?? '') . " ($p2)";
                                if(!empty($p3) && !empty(($grau3Labels[$p3] ?? null))) $trail[] = ($grau3Labels[$p3] ?? '') . " ($p3)";
                            @endphp
                            @if(($showTrail ?? true) && !empty($trail))
                                <tr>
                                    <td colspan="5"><small style="color:#666;">{{ implode(' • ', $trail) }}</small></td>
                                </tr>
                            @endif
                            @if(($showPrev ?? true) && (abs($prev4) > 0))
                                <tr>
                                    <td><small style="color:#666; font-style: italic;">Saldo Anterior</small></td>
                                    <td><small style="color:#666;">{{ $prefix4 }}</small></td>
                                    <td class="text-end">—</td>
                                    <td class="text-end">—</td>
                                    <td class="text-end"><small style="font-style: italic;">{{ number_format($prev4, 2, ',', '.') }}</small></td>
                                </tr>
                            @endif
                            <tr>
                                <td>
                                    @php $g4desc = ($grau4Labels[$prefix4] ?? null) ?? null; @endphp
                                    <strong>{{ $g4desc ?: $prefix4 }}</strong>
                                    @if($g4desc)
                                        <small style="color:#666;">({{ $prefix4 }})</small>
                                    @endif
                                </td>
                                <td>Grau 4 • Subtotal — {{ $prefix4 }}</td>
                                <td class="text-end"><strong>{{ number_format($tot4['deb'], 2, ',', '.') }}</strong></td>
                                <td class="text-end"><strong>{{ number_format($tot4['cred'], 2, ',', '.') }}</strong></td>
                                <td class="text-end"><strong>{{ number_format($tot4['saldo'], 2, ',', '.') }}</strong></td>
                            </tr>
                        @endif
                        <tr>
                            <td>{{ $l['conta'] }}</td>
                            <td>{{ $l['codigo'] ?? '' }}</td>
                            <td class="text-end">{{ number_format($l['debito'], 2, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($l['credito'], 2, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($l['saldo'], 2, ',', '.') }}</td>
                        </tr>
                        @php
                            $nextPrefix4 = null;
                            if(isset($g['linhas'][$idx+1])){
                                $n = $g['linhas'][$idx+1];
                                $nCode = trim((string)($n['codigo'] ?? ''));
                                $nParts = $nCode !== '' ? array_values(array_filter(explode('.', $nCode))) : [];
                                $nextPrefix4 = count($nParts) >= 5 ? implode('.', array_slice($nParts, 0, 4)) : null;
                            }
                        @endphp
                        @if($prefix4 && $prefix4 !== $nextPrefix4)
                            @php $tot4 = $nivel4Totals[$prefix4] ?? ['deb'=>0,'cred'=>0,'saldo'=>0]; @endphp
                            <tr>
                                <td>
                                    @php $g4desc = ($grau4Labels[$prefix4] ?? null) ?? null; @endphp
                                    <strong>Total {{ $g4desc ?: $prefix4 }}</strong>
                                    @if($g4desc)
                                        <small style="color:#666;">({{ $prefix4 }})</small>
                                    @endif
                                </td>
                                <td>{{ $prefix4 }}</td>
                                <td class="text-end"><strong>{{ number_format($tot4['deb'], 2, ',', '.') }}</strong></td>
                                <td class="text-end"><strong>{{ number_format($tot4['cred'], 2, ',', '.') }}</strong></td>
                                <td class="text-end"><strong>{{ number_format($tot4['saldo'], 2, ',', '.') }}</strong></td>
                            </tr>
                        @endif
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
                @php $__res = $dreResultado ?? 0; $__bg = ($__res < 0) ? '#ffe6e6' : '#e6f2ff'; @endphp
                <tr style="background: {{ $__bg }};">
                    <th>Resultado</th>
                    <td class="text-end" style="color:#000; font-weight: bold;">{{ number_format($__res, 2, ',', '.') }} {{ $__res < 0 ? '(PREJUÍZO)' : '(LUCRO)' }}</td>
                </tr>
            </tbody>
        </table>
    @else
        @php
            $nivel4Totals = [];
            $nivel4PrevTotals = [];
            foreach(($linhas ?? []) as $lCalc){
                $codigoCalc = trim((string)($lCalc['codigo'] ?? ''));
                if($codigoCalc === '') continue;
                $parts = array_filter(explode('.', $codigoCalc));
                if(count($parts) >= 5){
                    $prefix4 = implode('.', array_slice($parts, 0, 4));
                    if(!isset($nivel4Totals[$prefix4])){ $nivel4Totals[$prefix4] = ['deb'=>0.0,'cred'=>0.0,'saldo'=>0.0]; }
                    $nivel4Totals[$prefix4]['deb'] += (float)($lCalc['debito'] ?? 0);
                    $nivel4Totals[$prefix4]['cred'] += (float)($lCalc['credito'] ?? 0);
                    $nivel4Totals[$prefix4]['saldo'] += (float)($lCalc['saldo'] ?? 0);
                    if(($showPrev ?? true)){
                        $nivel4PrevTotals[$prefix4] = ($nivel4PrevTotals[$prefix4] ?? 0) + (float)($lCalc['saldo_anterior'] ?? 0);
                    }
                }
            }
        @endphp
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
                @php $lastPrefix4 = null; @endphp
                @foreach($linhas as $idx => $l)
                    @php
                        $codigoFull = trim((string)($l['codigo'] ?? ''));
                        $parts = $codigoFull !== '' ? array_values(array_filter(explode('.', $codigoFull))) : [];
                        $prefix4 = count($parts) >= 5 ? implode('.', array_slice($parts, 0, 4)) : null;
                    @endphp
                    @if($prefix4 && $prefix4 !== $lastPrefix4)
                        @php $lastPrefix4 = $prefix4; $tot4 = $nivel4Totals[$prefix4] ?? ['deb'=>0,'cred'=>0,'saldo'=>0]; $prev4 = $nivel4PrevTotals[$prefix4] ?? 0; @endphp
                        @if(($showPrev ?? true) && (abs($prev4) > 0))
                            <tr>
                                <td><small style="color:#666; font-style: italic;">Saldo Anterior</small></td>
                                <td><small style="color:#666;">{{ $prefix4 }}</small></td>
                                <td class="text-end">—</td>
                                <td class="text-end">—</td>
                                <td class="text-end"><small style="font-style: italic;">{{ number_format($prev4, 2, ',', '.') }}</small></td>
                            </tr>
                        @endif
                        @php
                            $partsPrefix = explode('.', $prefix4);
                            $p1 = $partsPrefix[0] ?? null;
                            $p2 = count($partsPrefix)>=2 ? implode('.', array_slice($partsPrefix,0,2)) : null;
                            $p3 = count($partsPrefix)>=3 ? implode('.', array_slice($partsPrefix,0,3)) : null;
                            $trail = [];
                            if(!empty($p1) && !empty(($grau1Labels[$p1] ?? null))) $trail[] = ($grau1Labels[$p1] ?? '') . " ($p1)";
                            if(!empty($p2) && !empty(($grau2Labels[$p2] ?? null))) $trail[] = ($grau2Labels[$p2] ?? '') . " ($p2)";
                            if(!empty($p3) && !empty(($grau3Labels[$p3] ?? null))) $trail[] = ($grau3Labels[$p3] ?? '') . " ($p3)";
                        @endphp
                        @if(($showTrail ?? true) && !empty($trail))
                            <tr>
                                <td colspan="5"><small style="color:#666;">{{ implode(' • ', $trail) }}</small></td>
                            </tr>
                        @endif
                        <tr>
                            <td>
                                @php $g4desc = ($grau4Labels[$prefix4] ?? null) ?? null; @endphp
                                <strong>{{ $g4desc ?: $prefix4 }}</strong>
                                @if($g4desc)
                                    <small style="color:#666;">({{ $prefix4 }})</small>
                                @endif
                            </td>
                            <td>Grau 4 • Subtotal — {{ $prefix4 }}</td>
                            <td class="text-end"><strong>{{ number_format($tot4['deb'], 2, ',', '.') }}</strong></td>
                            <td class="text-end"><strong>{{ number_format($tot4['cred'], 2, ',', '.') }}</strong></td>
                            <td class="text-end"><strong>{{ number_format($tot4['saldo'], 2, ',', '.') }}</strong></td>
                        </tr>
                    @endif
                    <tr>
                        <td>{{ $l['conta'] }}</td>
                        <td>{{ $l['codigo'] ?? '' }}</td>
                        <td class="text-end">{{ number_format($l['debito'], 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($l['credito'], 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($l['saldo'], 2, ',', '.') }}</td>
                    </tr>
                    @php
                        $nextPrefix4 = null;
                        if(isset($linhas[$idx+1])){
                            $n = $linhas[$idx+1];
                            $nCode = trim((string)($n['codigo'] ?? ''));
                            $nParts = $nCode !== '' ? array_values(array_filter(explode('.', $nCode))) : [];
                            $nextPrefix4 = count($nParts) >= 5 ? implode('.', array_slice($nParts, 0, 4)) : null;
                        }
                    @endphp
                    @if($prefix4 && $prefix4 !== $nextPrefix4)
                        @php $tot4 = $nivel4Totals[$prefix4] ?? ['deb'=>0,'cred'=>0,'saldo'=>0]; @endphp
                        <tr>
                            <td>
                                @php $g4desc = ($grau4Labels[$prefix4] ?? null) ?? null; @endphp
                                <strong>Total {{ $g4desc ?: $prefix4 }}</strong>
                                @if($g4desc)
                                    <small style="color:#666;">({{ $prefix4 }})</small>
                                @endif
                            </td>
                            <td>{{ $prefix4 }}</td>
                            <td class="text-end"><strong>{{ number_format($tot4['deb'], 2, ',', '.') }}</strong></td>
                            <td class="text-end"><strong>{{ number_format($tot4['cred'], 2, ',', '.') }}</strong></td>
                            <td class="text-end"><strong>{{ number_format($tot4['saldo'], 2, ',', '.') }}</strong></td>
                        </tr>
                    @endif
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
                @php $__res = $dreResultado ?? 0; $__bg = ($__res < 0) ? '#ffe6e6' : '#e6f2ff'; @endphp
                <tr style="background: {{ $__bg }};">
                    <th>Resultado</th>
                    <td class="text-end" style="color:#000; font-weight: bold;">{{ number_format($__res, 2, ',', '.') }} {{ $__res < 0 ? '(PREJUÍZO)' : '(LUCRO)' }}</td>
                </tr>
            </tbody>
        </table>
    @endif
</body>
</html>
