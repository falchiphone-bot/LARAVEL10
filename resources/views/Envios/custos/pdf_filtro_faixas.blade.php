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
                    <th>Vigência Início</th>
                    <th>Vigência Fim</th>
                    <th>Meses Vigência</th>
                    <th class="text-end">Valor Mínimo (R$)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($envio->safFaixasSalariais as $f)
                @php
                  $mesesVig = '-';
                  if($f->vigencia_inicio && $f->vigencia_fim) {
                      try {
                          $ini = \Carbon\Carbon::parse($f->vigencia_inicio)->startOfDay();
                          $fim = \Carbon\Carbon::parse($f->vigencia_fim)->startOfDay();
                          if($fim >= $ini) { $mesesVig = $ini->diffInMonths($fim) + 1; }
                      } catch (\Throwable $e) { $mesesVig='-'; }
                  }
                @endphp
                <tr>
                    <td>{{ $f->nome }}</td>
                    <td>{{ $f->vigencia_inicio ? \Carbon\Carbon::parse($f->vigencia_inicio)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $f->vigencia_fim ? \Carbon\Carbon::parse($f->vigencia_fim)->format('d/m/Y') : '-' }}</td>
                    <td class="text-end">{{ is_numeric($mesesVig) ? $mesesVig : '-' }}</td>
                    <td class="text-end">{{ number_format($f->valor_minimo,2,',','.') }}</td>
                </tr>
                @endforeach
                <tr class="total">
                    <td colspan="4">Totais deste envio</td>
                    <td class="text-end">R$ {{ number_format($envio->safFaixasSalariais->sum('valor_minimo'),2,',','.') }}</td>
                </tr>
                @php $custosMensaisEfetivos = $envio->safFaixasSalariais->sum('valor_minimo') * 0.2916; @endphp
                <tr class="total">
                    <td colspan="4">Custos mensais efetivos (29,16% do total mínimo)</td>
                    <td class="text-end">R$ {{ number_format($custosMensaisEfetivos,2,',','.') }}</td>
                </tr>
                @php $somaMinimoMaisEfetivos = $envio->safFaixasSalariais->sum('valor_minimo') + $custosMensaisEfetivos; @endphp
                <tr class="total">
                    <td colspan="4">Total mínimo + custos mensais efetivos</td>
                    <td class="text-end">R$ {{ number_format($somaMinimoMaisEfetivos,2,',','.') }}</td>
                </tr>
                @php
                  $mesesTotalEnvio = 0;
                  foreach($envio->safFaixasSalariais as $fxCalc){
                      if($fxCalc->vigencia_inicio && $fxCalc->vigencia_fim){
                          try {
                              $iniX = \Carbon\Carbon::parse($fxCalc->vigencia_inicio)->startOfDay();
                              $fimX = \Carbon\Carbon::parse($fxCalc->vigencia_fim)->startOfDay();
                              if($fimX >= $iniX){ $mesesTotalEnvio += $iniX->diffInMonths($fimX) + 1; }
                          } catch(\Throwable $e){}
                      }
                  }
                  $totalPeriodoEnvio = $somaMinimoMaisEfetivos * $mesesTotalEnvio;
                @endphp
                <tr class="total">
                    <td colspan="4">Total mínimo + custos mensais efetivos para o período ({{ $mesesTotalEnvio }} meses) = (R$ {{ number_format($somaMinimoMaisEfetivos,2,',','.') }} x {{ $mesesTotalEnvio }})</td>
                    <td class="text-end">R$ {{ number_format($totalPeriodoEnvio,2,',','.') }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach
    <hr>
    @php
      $totalCustosEfetivos = $totalGeralMin * 0.2916; 
      $totalMinimoMaisEfetivos = $totalGeralMin + $totalCustosEfetivos; 
      // Total geral meses (soma dos meses de cada faixa) e total período geral
      $totalMesesGeral = 0;
      foreach($envios as $envioSum){
          foreach($envioSum->safFaixasSalariais as $fxG){
              if($fxG->vigencia_inicio && $fxG->vigencia_fim){
                  try { $iniG=\Carbon\Carbon::parse($fxG->vigencia_inicio)->startOfDay(); $fimG=\Carbon\Carbon::parse($fxG->vigencia_fim)->startOfDay(); if($fimG >= $iniG){ $totalMesesGeral += $iniG->diffInMonths($fimG)+1; } } catch(\Throwable $e){}
              }
          }
      }
      $totalPeriodoGeral = $totalMinimoMaisEfetivos * $totalMesesGeral; // valor mensal combinado x meses totais
    @endphp
    <h3>Resumo Geral</h3>
    <table>
        <thead>
            <tr>
                <th>Descrição</th>
                <th class="text-end">Valor</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total geral valores mínimos</td>
                <td class="text-end">R$ {{ number_format($totalGeralMin,2,',','.') }}</td>
            </tr>
            <tr>
                <td>Custos mensais efetivos (29,16% do total mínimo)</td>
                <td class="text-end">R$ {{ number_format($totalCustosEfetivos,2,',','.') }}</td>
            </tr>
            <tr>
                <td>Total mínimo + custos mensais efetivos (valor mensal combinado)</td>
                <td class="text-end">R$ {{ number_format($totalMinimoMaisEfetivos,2,',','.') }}</td>
            </tr>
            <tr>
                <td>Total geral meses de vigência (soma de todas as faixas)</td>
                <td class="text-end">{{ $totalMesesGeral }}</td>
            </tr>
            <tr class="total">
                <td>Total mínimo + custos mensais efetivos para todos os meses<br><small>(R$ {{ number_format($totalMinimoMaisEfetivos,2,',','.') }} x {{ $totalMesesGeral }} meses)</small></td>
                <td class="text-end">R$ {{ number_format($totalPeriodoGeral,2,',','.') }}</td>
            </tr>
            <tr>
                <td>Quantidade total de faixas listadas</td>
                <td class="text-end">{{ $totalLinhas }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
