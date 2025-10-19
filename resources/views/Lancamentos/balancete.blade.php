@extends('layouts.bootstrap5')
@section('content')
<div class="py-4 bg-light">
  <div class="container">
    <div class="card mb-3 border-warning">
      <div class="card-header">
        <strong>Balancete por período</strong>
      </div>
      <div class="card-body" style="background-color:#fff8e1;">
        <form method="get" action="{{ route('lancamentos.balancete') }}" class="row g-3" id="formBalancete">
          <div class="col-md-6">
            <label class="form-label">Empresa</label>
            <select name="empresa_id" class="form-select">
              @foreach($empresas as $e)
                <option value="{{ $e->ID }}" {{ (string)$empresaId === (string)$e->ID ? 'selected' : '' }}>{{ $e->Descricao }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">De</label>
            <input type="date" name="de" class="form-control" value="{{ $de }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Até</label>
            <input type="date" name="ate" class="form-control" value="{{ $ate }}">
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-primary">Gerar</button>
            <a href="/PlanoContas/dashboard" class="btn btn-outline-secondary ms-2">Voltar</a>
          </div>
          <div class="col-12 mt-2">
            <div class="card border-success" style="background-color:#e9f7ef;">
              <div class="card-body py-2">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-check">
                      <input type="hidden" name="trail" value="0">
                      <input class="form-check-input" type="checkbox" name="trail" value="1" id="trailToggle" {{ ($showTrail ?? true) ? 'checked' : '' }}>
                      <label class="form-check-label fw-semibold" for="trailToggle">
                        Exibir trilha hierárquica (níveis 1 → 3 acima do Grau 4)
                      </label>
                    </div>
                  </div>
                  <div class="col-md-6 mt-2 mt-md-0">
                    <div class="form-check">
                      <input type="hidden" name="hier" value="0">
                      <input class="form-check-input" type="checkbox" name="hier" value="1" id="hierToggle" {{ ($showHier ?? false) ? 'checked' : '' }}>
                      <label class="form-check-label fw-semibold" for="hierToggle">
                        Exibir balancete hierárquico (graus 1 → 5)
                      </label>
                    </div>
                  </div>
                  <div class="col-md-6 mt-2">
                    <div class="form-check">
                      <input type="hidden" name="prev" value="0">
                      <input class="form-check-input" type="checkbox" name="prev" value="1" id="prevToggle" {{ ($showPrev ?? true) ? 'checked' : '' }}>
                      <label class="form-check-label fw-semibold" for="prevToggle">
                        Somar e exibir Saldo Anterior nas contas patrimoniais (1 e 2)
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
        <script>
          (function(){
            const trail = document.getElementById('trailToggle');
            const hier = document.getElementById('hierToggle');
            if(trail && hier){
              trail.addEventListener('change', function(){ if(this.checked){ hier.checked = false; } });
              hier.addEventListener('change', function(){ if(this.checked){ trail.checked = false; } });
            }
          })();
        </script>
      </div>
    </div>
    @if(!empty($linhas))
      <div>
        <strong>Resultados</strong>
        @php
          $empresaSel = collect($empresas ?? [])->firstWhere('ID', $empresaId);
          $empresaNome = $empresaSel->Descricao ?? '';
        @endphp
        <div class="card border-warning mt-2" style="background-color:#fff8e1;">
          <div class="card-body py-2">
            <div class="row g-2 align-items-center">
              <div class="col-md-6 col-12">
                <div class="fw-semibold small text-warning">Empresa</div>
                <div class="fs-6">{{ $empresaNome ?: '—' }}</div>
              </div>
              <div class="col-md-3 col-6">
                <div class="fw-semibold small text-warning">De</div>
                <div class="fs-6">{{ $deBr ?: '—' }}</div>
              </div>
              <div class="col-md-3 col-6">
                <div class="fw-semibold small text-warning">Até</div>
                <div class="fs-6">{{ $ateBr ?: '—' }}</div>
              </div>
            </div>
          </div>
        </div>
        @php
          $exportParams = [
            'empresa_id' => $empresaId,
            'de' => $de,
            'ate' => $ate,
            'trail' => ($showTrail ?? true) ? 1 : 0,
            'hier' => ($showHier ?? false) ? 1 : 0,
            'prev' => ($showPrev ?? true) ? 1 : 0,
          ];
        @endphp
        <div class="mt-2">
          <a class="btn btn-outline-success btn-sm" href="{{ route('lancamentos.balancete.exportXlsx', $exportParams) }}">Exportar XLSX</a>
          <a class="btn btn-outline-secondary btn-sm" href="{{ route('lancamentos.balancete.exportCsv', $exportParams) }}">Exportar CSV</a>
          <a class="btn btn-outline-danger btn-sm" href="{{ route('lancamentos.balancete.exportPdf', $exportParams) }}" target="_blank" rel="noopener">Exportar PDF</a>
        </div>

        @php $temGrupos = !empty($grupos ?? []); @endphp
        @if(($showHier ?? false))
          @php
            // Construir balancete hierárquico a partir das linhas
            $nodeTotals = [];
            $leafNames = [];
            // Soma de saldo anterior por prefixo (níveis 1..4)
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
              // Acumula saldo anterior até o nível 5 (inclui folha grau 5)
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
          <table class="table table-sm table-striped align-middle mt-3">
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
                  $indent = max(0, $grau - 1) * 12;
                  $label = $code;
                  if($grau===1){ $label = ($grau1Labels[$code] ?? $code); }
                  elseif($grau===2){ $label = ($grau2Labels[$code] ?? $code); }
                  elseif($grau===3){ $label = ($grau3Labels[$code] ?? $code); }
                  elseif($grau===4){ $label = ($grau4Labels[$code] ?? $code); }
                  else { $label = ($leafNames[$code] ?? $code); }
                  $nt = $nodeTotals[$code];
                  $prevNode = ($nodePrevTotals[$code] ?? 0);
                @endphp
                @if(($showPrev ?? true) && $grau <= 4 && abs($prevNode) > 0)
                  <tr>
                    <td>
                      <span style="display:inline-block; padding-left: {{ $indent }}px;">
                        <small class="text-muted fst-italic">Saldo Anterior</small>
                      </span>
                    </td>
                    <td><small class="text-muted">{{ $code }}</small></td>
                    <td class="text-end"></td>
                    <td class="text-end"></td>
                    <td class="text-end"><small class="fst-italic">{{ number_format($prevNode, 2, ',', '.') }}</small></td>
                  </tr>
                @endif
                @if(($showPrev ?? true) && $grau === 5 && abs($prevNode) > 0)
                  <tr style="background-color:#e6f2ff;">
                    <td>
                      <span style="display:inline-block; padding-left: {{ $indent }}px;">
                        <span style="font-weight:bold; font-style:italic; color:#000;">Saldo Anterior</span>
                      </span>
                    </td>
                    <td><span style="font-weight:bold; font-style:italic; color:#000;">{{ $code }}</span></td>
                    <td class="text-end"></td>
                    <td class="text-end"></td>
                    <td class="text-end"><span style="font-weight:bold; font-style:italic; color:#000;">{{ number_format($prevNode, 2, ',', '.') }}</span></td>
                  </tr>
                @endif
                <tr class="{{ $grau<=4 ? 'table-active' : '' }}">
                  <td>
                    <span style="display:inline-block; padding-left: {{ $indent }}px;">
                      {{ $label }}
                    </span>
                  </td>
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
          <div class="mt-4">
            <strong>Demonstrativo de Resultado</strong>
            <table class="table table-sm align-middle">
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
                <tr style="background-color: {{ $__bg }};">
                  <th>Resultado</th>
                  <td class="text-end" style="color:#000; font-weight:bold;">
                    {{ number_format($__res, 2, ',', '.') }}
                    <small class="ms-2">{{ $__res < 0 ? 'PREJUÍZO' : 'LUCRO' }}</small>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        @else
        @if(isset($hasBelowGrau5) && $hasBelowGrau5)
          <div class="alert alert-warning d-flex align-items-center mt-3" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            @if(($countGrau5Plus ?? 0) === 0)
              Todas as contas estão com classificação abaixo do grau 5 (profundidade mínima atual: grau {{ $minGrau ?? '—' }}, máxima: grau {{ $maxGrau ?? '—' }}).
            @else
              Existem {{ $belowGrau5Count }} conta(s) com classificação abaixo do grau 5 (profundidade mínima: grau {{ $minGrau ?? '—' }}, máxima: grau {{ $maxGrau ?? '—' }}).
            @endif
          </div>
  @endif

        @if($temGrupos)
          @foreach($grupos as $g)
            <h6 class="mt-3 mb-2"><strong>{{ $g['label'] }}</strong></h6>
            <table class="table table-sm table-striped align-middle">
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
                // Pré-calcula subtotais por prefixo de grau 4 somente para linhas com grau >= 5
                $nivel4Totals = [];
                $nivel4PrevTotals = [];
                foreach(($g['linhas'] ?? []) as $lCalc){
                  $codigoCalc = trim((string)($lCalc['codigo'] ?? ''));
                  if($codigoCalc === '') continue;
                  $parts = array_values(array_filter(explode('.', $codigoCalc), fn($p)=>$p!=='' && $p!==null));
                  if(count($parts) >= 5){
                    $prefix4 = implode('.', array_slice($parts, 0, 4));
                    if(!isset($nivel4Totals[$prefix4])){
                      $nivel4Totals[$prefix4] = ['deb'=>0.0,'cred'=>0.0,'saldo'=>0.0];
                    }
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
                    $parts = $codigoFull !== '' ? array_values(array_filter(explode('.', $codigoFull), fn($p)=>$p!=='' && $p!==null)) : [];
                    $prefix4 = count($parts) >= 5 ? implode('.', array_slice($parts, 0, 4)) : null;
                  @endphp
                  @if($prefix4 && $prefix4 !== $lastPrefix4)
                    @php
                      $lastPrefix4 = $prefix4;
                      $tot4 = $nivel4Totals[$prefix4] ?? ['deb'=>0,'cred'=>0,'saldo'=>0];
                      $prev4 = $nivel4PrevTotals[$prefix4] ?? 0;
                      $g4desc = ($grau4Labels[$prefix4] ?? null) ?? null;
                      $p1 = explode('.', $prefix4)[0] ?? null;
                      $partsPrefix = explode('.', $prefix4);
                      $p2 = count($partsPrefix)>=2 ? implode('.', array_slice($partsPrefix,0,2)) : null;
                      $p3 = count($partsPrefix)>=3 ? implode('.', array_slice($partsPrefix,0,3)) : null;
                      $trail = [];
                      if(!empty($p1) && !empty(($grau1Labels[$p1] ?? null))) $trail[] = ($grau1Labels[$p1] ?? '') . " ($p1)";
                      if(!empty($p2) && !empty(($grau2Labels[$p2] ?? null))) $trail[] = ($grau2Labels[$p2] ?? '') . " ($p2)";
                      if(!empty($p3) && !empty(($grau3Labels[$p3] ?? null))) $trail[] = ($grau3Labels[$p3] ?? '') . " ($p3)";
                    @endphp
                    @if(($showTrail ?? true) && !empty($trail))
                      <tr class="table-light">
                        <td colspan="5">
                          <small class="text-muted">{{ implode(' • ', $trail) }}</small>
                        </td>
                      </tr>
                    @endif
                    @if(($showPrev ?? true) && (abs($prev4) > 0))
                      <tr>
                        <td>
                          <small class="text-muted fst-italic">Saldo Anterior</small>
                        </td>
                        <td class="text-muted"><small>{{ $prefix4 }}</small></td>
                        <td class="text-end">—</td>
                        <td class="text-end">—</td>
                        <td class="text-end"><small class="fst-italic">{{ number_format($prev4, 2, ',', '.') }}</small></td>
                      </tr>
                    @endif
                    <tr class="table-active">
                      <td>
                        <strong>{{ $g4desc ?: $prefix4 }}</strong>
                        @if($g4desc)
                          <small class="text-muted ms-1">({{ $prefix4 }})</small>
                        @endif
                      </td>
                      <td>
                        <span class="me-2">{{ $prefix4 }}</span>
                        <span class="badge text-bg-secondary">Grau 4 • Subtotal</span>
                      </td>
                      <td class="text-end"><strong>{{ number_format($tot4['deb'], 2, ',', '.') }}</strong></td>
                      <td class="text-end"><strong>{{ number_format($tot4['cred'], 2, ',', '.') }}</strong></td>
                      <td class="text-end"><strong>{{ number_format($tot4['saldo'], 2, ',', '.') }}</strong></td>
                    </tr>
                  @endif
                  <tr>
                    <td>
                      <a href="/Contas/Extrato/{{ $l['conta_id'] }}?de={{ $de }}&ate={{ $ate }}" target="_blank" rel="noopener noreferrer" title="Abrir extrato da conta">{{ $l['conta'] }}</a>
                    </td>
                    <td>
                      @php
                        $codigo = $l['codigo'] ?? '';
                        $grau = (int)($l['codigo_grau'] ?? 0);
                      @endphp
                      @if($grau > 0 && $grau < 5)
                        <span class="text-warning" title="Classificação grau {{ $grau }} (abaixo de 5)">
                          <i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $codigo }}
                        </span>
                      @else
                        {{ $codigo }}
                      @endif
                    </td>
                    <td class="text-end">{{ number_format($l['debito'], 2, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($l['credito'], 2, ',', '.') }}</td>
                    <td class="text-end {{ $l['saldo'] < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($l['saldo'], 2, ',', '.') }}</td>
                  </tr>
                  @php
                    $nextPrefix4 = null;
                    if(isset($g['linhas'][$idx+1])){
                      $n = $g['linhas'][$idx+1];
                      $nCode = trim((string)($n['codigo'] ?? ''));
                      $nParts = $nCode !== '' ? array_values(array_filter(explode('.', $nCode), fn($p)=>$p!=='' && $p!==null)) : [];
                      $nextPrefix4 = count($nParts) >= 5 ? implode('.', array_slice($nParts, 0, 4)) : null;
                    }
                  @endphp
                  @if($prefix4 && $prefix4 !== $lastPrefix4)
                    @php $lastPrefix4 = $prefix4; $tot4 = $nivel4Totals[$prefix4] ?? ['deb'=>0,'cred'=>0,'saldo'=>0]; $g4desc = ($grau4Labels[$prefix4] ?? null) ?? null; @endphp
                    @php
                      $p1 = explode('.', $prefix4)[0] ?? null;
                      $partsPrefix = explode('.', $prefix4);
                      $p2 = count($partsPrefix)>=2 ? implode('.', array_slice($partsPrefix,0,2)) : null;
                      $p3 = count($partsPrefix)>=3 ? implode('.', array_slice($partsPrefix,0,3)) : null;
                      $trail = [];
                      if(!empty($p1) && !empty(($grau1Labels[$p1] ?? null))) $trail[] = ($grau1Labels[$p1] ?? '') . " ($p1)";
                      if(!empty($p2) && !empty(($grau2Labels[$p2] ?? null))) $trail[] = ($grau2Labels[$p2] ?? '') . " ($p2)";
                      if(!empty($p3) && !empty(($grau3Labels[$p3] ?? null))) $trail[] = ($grau3Labels[$p3] ?? '') . " ($p3)";
                    @endphp
                    @if(($showTrail ?? true) && !empty($trail))
                      <tr class="table-light">
                        <td colspan="5">
                          <small class="text-muted">{{ implode(' • ', $trail) }}</small>
                        </td>
                      </tr>
                    @endif
                    <tr class="table-secondary">
                      <td>
                        <strong>Total {{ $g4desc ?: $prefix4 }}</strong>
                        @if($g4desc)
                          <small class="text-muted ms-1">({{ $prefix4 }})</small>
                        @endif
                      </td>
                      <td class="text-muted">{{ $prefix4 }}</td>
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

          <div class="mt-3">
            <strong>Totais gerais</strong>
            <table class="table table-sm align-middle">
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
          </div>

          <div class="mt-4">
            <strong>Demonstrativo de Resultado</strong>
            <table class="table table-sm align-middle">
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
                <tr style="background-color: {{ $__bg }};">
                  <th>Resultado</th>
                  <td class="text-end" style="color:#000; font-weight:bold;">
                    {{ number_format($__res, 2, ',', '.') }}
                    <small class="ms-2">{{ $__res < 0 ? 'PREJUÍZO' : 'LUCRO' }}</small>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        @else
          <table class="table table-sm table-striped align-middle">
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
              // Subtotais de grau 4 para tabela sem grupos
              $nivel4Totals = [];
              $nivel4PrevTotals = [];
              foreach(($linhas ?? []) as $lCalc){
                $codigoCalc = trim((string)($lCalc['codigo'] ?? ''));
                if($codigoCalc === '') continue;
                $parts = array_values(array_filter(explode('.', $codigoCalc), fn($p)=>$p!=='' && $p!==null));
                if(count($parts) >= 5){
                  $prefix4 = implode('.', array_slice($parts, 0, 4));
                  if(!isset($nivel4Totals[$prefix4])){
                    $nivel4Totals[$prefix4] = ['deb'=>0.0,'cred'=>0.0,'saldo'=>0.0];
                  }
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
              @foreach($linhas as $idx => $l)
                @php
                  $codigoFull = trim((string)($l['codigo'] ?? ''));
                  $parts = $codigoFull !== '' ? array_values(array_filter(explode('.', $codigoFull), fn($p)=>$p!=='' && $p!==null)) : [];
                  $prefix4 = count($parts) >= 5 ? implode('.', array_slice($parts, 0, 4)) : null;
                @endphp
                @if($prefix4 && $prefix4 !== $lastPrefix4)
                    @php $lastPrefix4 = $prefix4; $tot4 = $nivel4Totals[$prefix4] ?? ['deb'=>0,'cred'=>0,'saldo'=>0]; $prev4 = $nivel4PrevTotals[$prefix4] ?? 0; $g4desc = ($grau4Labels[$prefix4] ?? null) ?? null; @endphp
                    @if(($showPrev ?? true) && (abs($prev4) > 0))
                      <tr>
                        <td>
                          <small class="text-muted fst-italic">Saldo Anterior</small>
                        </td>
                        <td class="text-muted"><small>{{ $prefix4 }}</small></td>
                        <td class="text-end">—</td>
                        <td class="text-end">—</td>
                        <td class="text-end"><small class="fst-italic">{{ number_format($prev4, 2, ',', '.') }}</small></td>
                      </tr>
                    @endif
                  <tr class="table-active">
                    <td>
                      <strong>{{ $g4desc ?: $prefix4 }}</strong>
                      @if($g4desc)
                        <small class="text-muted ms-1">({{ $prefix4 }})</small>
                      @endif
                    </td>
                    <td>
                      <span class="me-2">{{ $prefix4 }}</span>
                      <span class="badge text-bg-secondary">Grau 4 • Subtotal</span>
                    </td>
                    <td class="text-end"><strong>{{ number_format($tot4['deb'], 2, ',', '.') }}</strong></td>
                    <td class="text-end"><strong>{{ number_format($tot4['cred'], 2, ',', '.') }}</strong></td>
                    <td class="text-end"><strong>{{ number_format($tot4['saldo'], 2, ',', '.') }}</strong></td>
                  </tr>
                @endif
                <tr>
                  <td>
                    <a href="/Contas/Extrato/{{ $l['conta_id'] }}?de={{ $de }}&ate={{ $ate }}" target="_blank" rel="noopener noreferrer" title="Abrir extrato da conta">{{ $l['conta'] }}</a>
                  </td>
                  <td>
                    @php
                      $codigo = $l['codigo'] ?? '';
                      $grau = (int)($l['codigo_grau'] ?? 0);
                    @endphp
                    @if($grau > 0 && $grau < 5)
                      <span class="text-warning" title="Classificação grau {{ $grau }} (abaixo de 5)">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $codigo }}
                      </span>
                    @else
                      {{ $codigo }}
                    @endif
                  </td>
                  <td class="text-end">{{ number_format($l['debito'], 2, ',', '.') }}</td>
                  <td class="text-end">{{ number_format($l['credito'], 2, ',', '.') }}</td>
                  <td class="text-end {{ $l['saldo'] < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($l['saldo'], 2, ',', '.') }}</td>
                </tr>
                @php
                  $nextPrefix4 = null;
                  if(isset($linhas[$idx+1])){
                    $n = $linhas[$idx+1];
                    $nCode = trim((string)($n['codigo'] ?? ''));
                    $nParts = $nCode !== '' ? array_values(array_filter(explode('.', $nCode), fn($p)=>$p!=='' && $p!==null)) : [];
                    $nextPrefix4 = count($nParts) >= 5 ? implode('.', array_slice($nParts, 0, 4)) : null;
                  }
                @endphp
                @if($prefix4 && $prefix4 !== $nextPrefix4)
                  @php $tot4 = $nivel4Totals[$prefix4] ?? ['deb'=>0,'cred'=>0,'saldo'=>0]; $g4desc = ($grau4Labels[$prefix4] ?? null) ?? null; @endphp
                  <tr class="table-secondary">
                    <td>
                      <strong>Total {{ $g4desc ?: $prefix4 }}</strong>
                      @if($g4desc)
                        <small class="text-muted ms-1">({{ $prefix4 }})</small>
                      @endif
                    </td>
                    <td class="text-muted">{{ $prefix4 }}</td>
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

          <div class="mt-4">
            <strong>Demonstrativo de Resultado</strong>
            <table class="table table-sm align-middle">
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
                <tr style="background-color: {{ $__bg }};">
                  <th>Resultado</th>
                  <td class="text-end" style="color:#000; font-weight:bold;">
                    {{ number_format($__res, 2, ',', '.') }}
                    <small class="ms-2">{{ $__res < 0 ? 'PREJUÍZO' : 'LUCRO' }}</small>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        @endif
        @endif
      </div>
    @else
      <div class="alert alert-info">Selecione empresa e período e clique em Gerar.</div>
    @endif
  </div>
 </div>
@endsection

