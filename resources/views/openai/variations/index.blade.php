@extends('layouts.bootstrap5')
@section('content')
<div class="container-fluid">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h4 mb-0">Variações Mensais Salvas</h1>
    <button type="button" id="btn-toggle-openai-variations-index-layout" class="btn btn-outline-dark btn-sm" title="Alterna exibição compacta (oculta filtros e toolbars para ganhar espaço vertical)">Modo Compacto</button>
  </div>
  <style>
    .filters-bar{display:flex;flex-wrap:wrap;gap:.5rem 1rem;align-items:flex-end}
    .filters-bar > *{display:inline-flex;flex-direction:column}
    .filters-bar .form-label{margin-bottom:.25rem}
    .filters-bar .form-select,.filters-bar .form-control{width:auto}
  </style>
  <form method="get" class="filters-bar mb-3" style="display:flex;flex-wrap:wrap;gap:.5rem 1rem;align-items:flex-end">
    @if(request()->filled('import_file'))
      <input type="hidden" name="import_file" value="{{ request('import_file') }}" />
    @endif
    <div class="col-auto">
      <label class="form-label mb-0 small">Ano</label>
  <select name="year" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
        <option value="">Todos</option>
        @foreach($years as $y)
          <option value="{{ $y }}" @selected((string)$y === (string)$year)>{{ $y }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label mb-0 small">Agrupar</label>
      <div class="form-check form-switch mt-1">
        <input class="form-check-input" type="checkbox" value="1" name="grouped" id="groupedToggle" onchange="this.form.submit()" @checked($grouped ?? false) />
        <label class="form-check-label small" for="groupedToggle">por código</label>
      </div>
    </div>
    @if($grouped ?? false)
      <div class="col-auto">
        <label class="form-label mb-0 small">Meses Spark</label>
        <select name="spark_window" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
          @foreach([3,6,9,12,18,24] as $w)
            <option value="{{ $w }}" @selected(($sparkWindow ?? 6)===$w)>{{ $w }}</option>
          @endforeach
        </select>
      </div>
    @endif
  <div class="col-auto align-self-end">
      <button class="btn btn-sm btn-primary">Filtrar</button>
    </div>
  <div class="col-auto align-self-end">
      @php
        $quickBase = array_filter([
          'year' => request('year') ?: null,
          'month' => request('month') ?: null,
          'code' => $code ?: null,
          'sort' => ($sort ?? 'year_desc') !== 'year_desc' ? $sort : null,
          'change' => ($change ?? '') ?: null,
          'grouped' => ($grouped ?? false) ? 1 : null,
          'spark_window' => ($grouped ?? false) ? ($sparkWindow ?? null) : null,
          'trend' => ($trendFilter ?? '') ?: null,
          'currency' => request('currency') ?: null,
          'import_file' => request('import_file') ?: null,
        ]);
      @endphp
      <div class="btn-group btn-group-sm" role="group" aria-label="Atalhos de sinal">
        <a href="{{ route('openai.variations.index', $quickBase) }}"
           class="btn btn-outline-secondary {{ (($polarity ?? '')==='') ? 'active' : '' }}"
           title="Mostrar todos (positivos e negativos)">Todos</a>
        <a href="{{ route('openai.variations.index', array_merge($quickBase, ['polarity'=>'positive'])) }}"
           class="btn btn-outline-success {{ (($polarity ?? '')==='positive') ? 'active' : '' }}"
           title="Mostrar apenas variações positivas">Somente positivos</a>
        <a href="{{ route('openai.variations.index', array_merge($quickBase, ['polarity'=>'negative'])) }}"
           class="btn btn-outline-danger {{ (($polarity ?? '')==='negative') ? 'active' : '' }}"
           title="Mostrar apenas variações negativas">Somente negativos</a>
      </div>
    </div>

  <div class="col-auto align-self-end">
      @php
        $monthQuickBase = array_filter([
          'year' => request('year') ?: null,
          'code' => $code ?: null,
          'polarity'=> ($polarity ?? null) ?: null,
          'sort' => ($sort ?? 'year_desc') !== 'year_desc' ? $sort : null,
          'change' => ($change ?? '') ?: null,
          'grouped' => ($grouped ?? false) ? 1 : null,
          'spark_window' => ($grouped ?? false) ? ($sparkWindow ?? null) : null,
          'trend' => ($trendFilter ?? '') ?: null,
          'currency' => request('currency') ?: null,
          'import_file' => request('import_file') ?: null,
          // Por padrão, ao clicar no mês, listar tudo (sem paginação)
          'no_page' => 1,
        ]);
        $curMonth = (int) (request('month') ?: 0);
      @endphp
      <div id="month-shortcuts-group" class="btn-group btn-group-sm flex-wrap" role="group" aria-label="Atalhos de mês">
        <a href="{{ route('openai.variations.index', $monthQuickBase) }}" class="btn btn-outline-secondary {{ $curMonth===0 ? 'active' : '' }}" title="Limpar filtro de mês">Limpar mês</a>
        @for($m=1;$m<=12;$m++)
          <a href="{{ route('openai.variations.index', array_merge($monthQuickBase, ['month'=>$m])) }}"
             class="btn btn-outline-primary {{ $curMonth===$m ? 'active' : '' }}"
             title="Filtrar por mês {{ str_pad($m,2,'0',STR_PAD_LEFT) }}">{{ str_pad($m,2,'0',STR_PAD_LEFT) }}</a>
        @endfor
      </div>
      <div class="btn-group btn-group-sm ms-2" role="group" aria-label="Paginação">
        <a href="{{ route('openai.variations.index', array_merge($monthQuickBase, ['no_page'=>1])) }}" class="btn btn-outline-primary {{ request('no_page') ? 'active' : '' }}" title="Sem paginação (mantém mês selecionado se houver)">Sem paginação</a>
        <a href="{{ route('openai.variations.index', array_merge($monthQuickBase, ['no_page'=>null])) }}" class="btn btn-outline-secondary {{ request('no_page') ? '' : 'active' }}" title="Exibir paginado">Paginado</a>
      </div>
    </div>

    <div class="col-auto">
      <label class="form-label mb-0 small">Capital</label>
      @php
        $selCurrency = strtoupper((string)request('currency','USD'));
        if(!in_array($selCurrency, ['USD','BRL'], true)) { $selCurrency = 'USD'; }
        // Prefill: se capital não informado, usar total da carteira na moeda selecionada
        $capitalPrefill = request('capital');
        if($capitalPrefill === null || $capitalPrefill === ''){
          if($selCurrency==='BRL' && isset($portfolioBrlTotal) && $portfolioBrlTotal){
            $capitalPrefill = number_format($portfolioBrlTotal, 2, ',', '.');
          } elseif(isset($portfolioUsdTotal) && $portfolioUsdTotal) {
            $capitalPrefill = number_format($portfolioUsdTotal, 2, ',', '.');
          }
        }
      @endphp
      <div class="d-flex align-items-end gap-2">
        <select name="currency" class="form-select form-select-sm w-auto" title="Moeda de exibição e entrada do capital" data-rate="{{ isset($usdToBrlRate) && is_numeric($usdToBrlRate) ? (float)$usdToBrlRate : '' }}">
          <option value="USD" @selected($selCurrency==='USD')>USD</option>
          <option value="BRL" @selected($selCurrency==='BRL')>BRL</option>
        </select>
        <input type="text" name="capital" value="{{ $capitalPrefill }}" class="form-control form-control-sm w-auto" placeholder="ex: 150.000,00" />
      </div>
      <small class="text-muted">
        Peso ∝ Diferença (%) positiva, baseado nos itens exibidos.
        @php $showCur = ($selCurrency==='BRL') ? 'R$' : 'US$'; @endphp
        @if($selCurrency==='BRL' && isset($portfolioBrlTotal) && $portfolioBrlTotal)
          Total carteira: R$ {{ number_format($portfolioBrlTotal,2,',','.') }}
        @elseif(isset($portfolioUsdTotal) && $portfolioUsdTotal)
          Total carteira: US$ {{ number_format($portfolioUsdTotal,2,',','.') }}
        @endif
        @if($selCurrency==='BRL' && !(isset($usdToBrlRate) && is_numeric($usdToBrlRate) && $usdToBrlRate>0))
          <br><span class="text-warning">Taxa USD→BRL indisponível. Valores serão tratados como USD.</span>
        @endif
      </small>
    </div>
    <div class="col-auto">
      <label class="form-label mb-0 small">Paginação</label>
      <div class="form-check form-switch mt-1">
        <input class="form-check-input" type="checkbox" name="no_page" value="1" id="noPageToggle" onchange="this.form.submit()" @checked(request('no_page')) />
        <label class="form-check-label small" for="noPageToggle">Listar tudo</label>
      </div>
    </div>
    <div class="col-auto">
      <label class="form-label mb-0 small">Máx por ativo (%)</label>
      <input type="text" name="cap_pct" value="{{ request('cap_pct','35') }}" class="form-control form-control-sm w-auto" placeholder="ex: 35" />
      <small class="text-muted">Limite de concentração por ativo (ex.: 35)</small>
    </div>
    <div class="col-auto">
      <label class="form-label mb-0 small">Meta global (%)</label>
      <input type="text" name="target_pct" value="{{ request('target_pct','20') }}" class="form-control form-control-sm w-auto" placeholder="ex: 20" />
      <small class="text-muted">Retorno alvo para a carteira (ex.: 20)</small>
    </div>
    <div class="col-auto align-self-end">
      <button id="filter-calc-alloc-btn" data-base-label="Calcular alocação" class="btn btn-sm btn-outline-primary position-relative" title="Usar capital e parâmetros para gerar alocação considerando (se houver) os ativos selecionados abaixo">Calcular alocação</button>
    </div>
  </form>

  @php
    $exportParams = array_filter([
      'year'=>request('year')?:null,
      'month'=>request('month')?:null,
      'code'=>$code?:null,
      'polarity'=> ($polarity ?? null) ?: null,
      'sort' => ($sort ?? 'year_desc') !== 'year_desc' ? $sort : null,
      'change' => ($change ?? '') ?: null,
      'grouped' => ($grouped ?? false) ? 1 : null,
      'spark_window' => ($grouped ?? false) ? ($sparkWindow ?? null) : null,
      'trend' => ($trendFilter ?? '') ?: null,
      'currency' => request('currency') ?: null,
    ]);
  @endphp
  <div class="mb-2 d-flex gap-2 align-items-center position-sticky top-0 z-3 bg-light py-2" style="top: 0; border-bottom: 1px solid rgba(0,0,0,.1);">
  <a href="{{ route('openai.variations.exportCsv', $exportParams) }}" class="btn btn-sm btn-outline-secondary" title="Exportar visão atual em CSV">Exportar CSV</a>
  <a href="{{ route('openai.variations.exportXlsx', $exportParams) }}" class="btn btn-sm btn-outline-success" title="Exportar visão atual em XLSX">Exportar XLSX</a>
    <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="var-clear-selection-allocation-top" title="Limpar seleção &amp; remover selected_codes da URL">Limpar seleção &amp; alocação</button>
    <div class="vr mx-2 d-none d-md-block"></div>
    <button type="button" id="btn-var-batch-flags" class="btn btn-sm btn-outline-warning" title="Aplicar COMPRAR/NÃO COMPRAR por código conforme sinal da variação (usa a linha mais recente por código)">Aplicar flags (variação)</button>
    <a href="{{ route('asset-stats.index') }}#gsc.tab=0" class="btn btn-sm btn-outline-dark" title="Ir para Asset Stats">Asset Stats</a>
    <div class="vr mx-2 d-none d-md-block"></div>
    <form id="form-clear-openai-variations-cache" action="{{ route('openai.variations.clearCache') }}" method="post" class="d-inline">
      @csrf
      <button type="submit" class="btn btn-sm btn-outline-danger" title="Limpar cache da aplicação (pode afetar contadores e listas temporariamente)">Limpar cache</button>
    </form>
  </div>

  @if(session('cache_cleared'))
    <div class="alert alert-success alert-dismissible fade show small my-2" role="alert">
      Cache limpo com sucesso.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- Filtros atuais expostos para JS (evita Blade dentro de <script>) -->
  <span id="var-filter-ym" data-year="{{ (int)($year ?? 0) }}" data-month="{{ (int)($month ?? 0) }}" hidden></span>

  <div class="card mb-3" style="background-color:#e9f8ee;border-color:#b7e3c7;">
    <div class="card-body py-2 d-flex flex-wrap gap-2 align-items-center">
      <strong class="me-2 text-success">Selecionados</strong>
      <button type="button" id="export-selected-csv" class="btn btn-sm btn-outline-success" title="Exportar somente códigos selecionados em CSV" disabled>Exportar Registros Selecionados CSV</button>
      <button type="button" id="export-selected-xlsx" class="btn btn-sm btn-outline-success" title="Exportar somente códigos selecionados em XLSX" disabled>Exportar Registros Selecionados XLSX</button>
      <form id="import-selected-form" action="{{ route('openai.variations.importSelected') }}" method="post" enctype="multipart/form-data" class="d-inline-block">
        @csrf
        <input type="hidden" name="year" value="{{ request('year') }}">
        <input type="hidden" name="month" value="{{ request('month') }}">
        <input type="hidden" name="no_page" value="1">
        <input type="hidden" name="code" value="{{ request('code') }}">
        <input type="hidden" name="polarity" value="{{ request('polarity') }}">
        <input type="hidden" name="currency" value="{{ request('currency') }}">
        <input type="hidden" name="capital" value="{{ request('capital') }}">
        <input type="hidden" name="cap_pct" value="{{ request('cap_pct') }}">
        <input type="hidden" name="target_pct" value="{{ request('target_pct') }}">
        @php $importFileQuery = request('import_file'); @endphp
        @if(session('import_file_name') || $importFileQuery)
          <span class="fw-bold text-dark me-2">Último arquivo importado: {{ session('import_file_name') ?: $importFileQuery }}</span>
        @endif
        <label class="btn btn-sm btn-outline-success mb-0">
          Importar selecionados
          <input id="import-selected-file" type="file" name="file" accept=".csv,.xlsx" class="d-none" />
        </label>
      </form>

    </div>
  </div>

  <!-- Modal de confirmação de importação -->
  <div class="modal fade" id="importSelectedConfirmModal" tabindex="-1" aria-labelledby="importSelectedConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="importSelectedConfirmModalLabel">Importar Selecionados</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Antes de prosseguir, confirme que o arquivo possui a primeira linha com o texto:<br>
          <strong>“Exportado dos registros selecionados CSV/XLSX”</strong>.<br>
          Essa verificação será feita ao importar. Deseja continuar?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary btn-cancel-import" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success btn-confirm-import">OK</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Toast de sucesso da importação -->
  @if(session('success') && session('import_count'))
  <div class="position-fixed top-0 end-0 p-3" style="z-index: 1080;">
    <div id="importSuccessToast" class="toast align-items-center text-bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">
          <strong>Importação concluída:</strong> {{ session('import_count') }} código(s) aplicado(s).
          @if(session('import_preview'))
            <br><small>Códigos: {{ session('import_preview') }}</small>
          @endif
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  </div>
  @endif

  <!-- Modal: confirmar troca de mês -->
  <div class="modal fade" id="confirmMonthChangeModal" tabindex="-1" aria-labelledby="confirmMonthChangeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmMonthChangeModalLabel">Trocar mês</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Você deseja realmente alterar o mês selecionado? Quaisquer alterações não salvas poderão ser perdidas.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Não</button>
          <button type="button" class="btn btn-primary" id="btnConfirmMonthChange">Sim, continuar</button>
        </div>
      </div>
    </div>
  </div>

  @php
    // Parse inputs for allocation
    $capital = null;
    $capitalInput = request('capital');
    if ($capitalInput !== null && $capitalInput !== '') {
      $x = preg_replace('/[^\d,.-]/', '', (string)$capitalInput);
      $x = str_replace(['.', ' '], '', $x);
      $x = str_replace(',', '.', $x);
      if (is_numeric($x)) { $capital = (float)$x; }
    }
  $capPctInput = request('cap_pct','35');
    $targetPctInput = request('target_pct','20');
    $capPct = null; $targetPct = null;
    foreach ([[ 'src'=>$capPctInput,'ref'=>'capPct' ], [ 'src'=>$targetPctInput,'ref'=>'targetPct' ]] as $it) {
      $v = str_replace([' ', '%'], '', (string)$it['src']);
      $v = str_replace(',', '.', $v);
      if (is_numeric($v)) { ${$it['ref']} = (float)$v; }
    }
    $cap = ($capPct !== null) ? max(0.0, min(1.0, $capPct/100.0)) : 0.35; // default 35%
    $target = ($targetPct !== null) ? max(-1.0, min(10.0, $targetPct/100.0)) : 0.20; // default 20%

  // Currency handling and conversion (normalize calculations to USD)
  $selCurrency = strtoupper((string)request('currency','USD'));
  if(!in_array($selCurrency,['USD','BRL'],true)) { $selCurrency='USD'; }
  $rate = (isset($usdToBrlRate) && is_numeric($usdToBrlRate) && $usdToBrlRate>0) ? (float)$usdToBrlRate : null;
  $calcCapital = $capital;
  if($capital !== null && $selCurrency==='BRL' && $rate){ $calcCapital = $capital / $rate; }
  $dispMul = ($selCurrency==='BRL' && $rate) ? $rate : 1.0;
  $curSymbol = ($selCurrency==='BRL' && $rate) ? 'R$' : '$US';

  $alloc = [];
  $allocOrder = request('alloc_order','');
  $selectedCodesIn = array_map(fn($c)=> strtoupper(trim((string)$c)), ($selectedCodes ?? []));
  $selectedCodesIn = array_values(array_filter(array_unique($selectedCodesIn), fn($c)=> $c!==''));
    $sum = 0.0;
    if ($capital && $capital > 0) {
      // Build rows (grouped or not)
      $rows = [];
      if (($grouped ?? false) && isset($groupedData)) {
        foreach ($groupedData as $g) {
          $diff = $g['diff'] ?? null;
          $cur = optional($g['latest'])->variation;
          $prev = $g['prev_variation'] ?? null;
          $rows[] = [
            'code' => $g['asset_code'] ?? '-',
            'title'=> $g['chat_title'] ?? '',
            'cur'  => is_null($cur) ? null : (float)$cur,
            'prev' => is_null($prev) ? null : (float)$prev,
            'diff' => is_null($diff) ? null : (float)$diff,
            'chat_id' => optional($g['latest'])->chat_id ?? null,
            'year' => optional($g['latest'])->year ?? null,
            'month'=> optional($g['latest'])->month ?? null,
          ];
        }
      } else {
        foreach ($variations as $v) {
          $cur = $v->variation;
          $pv  = $prevVariationMap[$v->id] ?? null;
          $diff = (!is_null($pv)) ? ($cur - $pv) : null;
          $tinfo = $trendData[$v->id] ?? null;
          $rows[] = [
            'code' => $v->asset_code ?? '-',
            'title'=> $v->chat?->title ?? '',
            'cur'  => is_null($cur) ? null : (float)$cur,
            'prev' => is_null($pv)  ? null : (float)$pv,
            'diff' => is_null($diff)? null : (float)$diff,
            'chat_id' => $v->chat_id ?? null,
            'trend_code' => $tinfo['code'] ?? null,
            'trend_label'=> $tinfo['label'] ?? null,
            'trend_badge'=> $tinfo['badge'] ?? 'secondary',
            'year' => $v->year ?? null,
            'month'=> $v->month ?? null,
          ];
        }
      }
      // Filter by selection (if any selected codes passed by GET)
      $selectedMode = false;
      if(!empty($selectedCodesIn)) {
        $rows = array_values(array_filter($rows, fn($r)=> in_array(strtoupper($r['code']), $selectedCodesIn)));
        $selectedMode = true;
      }
      if($selectedMode) {
        // Usar todos os selecionados. Critério de score:
        // 1) diff > 0 => score = diff
        // 2) diff <=0 ou null: se cur > 0 usa cur; senão score = 0
        $accel = $rows;
        $sum = 0.0;
        foreach($accel as &$r){
          $d = $r['diff'] ?? null; $c = $r['cur'] ?? null;
            $score = 0.0;
            if(!is_null($d) && $d > 0) { $score = (float)$d; }
            elseif(!is_null($c) && $c > 0) { $score = (float)$c; }
            $r['_score'] = $score;
            $sum += $score;
        }
        unset($r);
        // Se todos scores forem zero, distribuir igual
        if($sum <= 0 && count($accel)>0){
          foreach($accel as &$r){ $r['_score'] = 1.0; }
          unset($r); $sum = count($accel);
        }
      } else {
        // Modo original: apenas positivos (acelerando) e fallback top 10
        $accel = array_values(array_filter($rows, function($r){ return isset($r['diff']) && $r['diff'] > 0; }));
        if (count($accel) === 0) {
          $accel = $rows;
          usort($accel, function($a,$b){ return ($b['cur'] ?? -INF) <=> ($a['cur'] ?? -INF); });
          $accel = array_slice($accel, 0, 10);
          foreach ($accel as &$r) { if (!isset($r['diff']) || $r['diff'] === null) { $r['diff'] = max(0.0, (float)($r['cur'] ?? 0)); } }
          unset($r);
        }
        $sum = 0.0;
        foreach ($accel as $r) { $sum += max(0.0, (float)($r['diff'] ?? 0)); }
      }
      if ($sum > 0) {
        // Base weights
        $baseW = [];
        foreach ($accel as $r) {
          if($selectedMode) {
            $baseW[] = ($sum>0) ? (($r['_score'] ?? 0)/$sum) : (1.0/max(count($accel),1));
          } else {
            $baseW[] = max(0.0, (float)($r['diff'] ?? 0)) / $sum;
          }
        }
        // Apply cap via iterative water-filling
        $n = count($accel);
        $finalW = array_fill(0, $n, 0.0);
        $unc = range(0, $n-1);
        $remaining = 1.0;
        for ($iter=0; $iter<10 && $remaining>1e-9 && count($unc)>0; $iter++) {
          $sumBase = 0.0; foreach ($unc as $j) { $sumBase += $baseW[$j]; }
          if ($sumBase <= 0) {
            $eq = $remaining / max(count($unc),1);
            foreach ($unc as $j) { $finalW[$j] += $eq; }
            $remaining = 0.0; break;
          }
          $toCap = [];
          foreach ($unc as $j) {
            $w = $remaining * ($baseW[$j]/max($sumBase,1e-12));
            if ($w > $cap + 1e-9) { $toCap[] = $j; }
          }
          if (count($toCap) === 0) {
            foreach ($unc as $j) { $finalW[$j] += $remaining * ($baseW[$j]/$sumBase); }
            $remaining = 0.0; break;
          }
          foreach ($toCap as $j) {
            $finalW[$j] += $cap;
            $remaining -= $cap;
            $unc = array_values(array_filter($unc, fn($x)=> $x !== $j));
          }
        }
        // small leftover to best below cap
        if ($remaining > 1e-9) {
          $best = -1; $bestVal = -1.0;
          for ($i=0;$i<$n;$i++) { if ($finalW[$i] < $cap - 1e-9 && $finalW[$i] > $bestVal) { $bestVal = $finalW[$i]; $best = $i; } }
          if ($best >= 0) { $finalW[$best] += $remaining; $remaining = 0.0; }
        }
        // Build allocation with targets
        $seenCodes = [];
        foreach ($accel as $idx=>$r) {
          $codeKey = strtoupper($r['code'] ?? '');
          if($codeKey !== '' && isset($seenCodes[$codeKey])) { continue; }
          $w = max(0.0, min(1.0, $finalW[$idx]));
          $val = $w * ($calcCapital ?? 0);
          // Busca último preço (amount) do registro mais recente deste chat
          $lastPrice = null;
          $cid = $r['chat_id'] ?? null;
          if ($cid) {
            try {
              $lp = \App\Models\OpenAIChatRecord::where('chat_id', $cid)
                ->orderByDesc('occurred_at')
                ->value('amount');
              if (is_numeric($lp)) { $lastPrice = (float)$lp; }
            } catch (\Throwable $e) { /* noop */ }
          }
          $qty = ($lastPrice && $lastPrice > 0) ? ($val / $lastPrice) : null;
          $alloc[] = $r + [
            'weight' => $w,
            'amount' => $val,
            'gain_target' => $val * $target,
            'last_price' => $lastPrice,
            'qty' => $qty,
          ];
          if($codeKey !== '') { $seenCodes[$codeKey] = true; }
        }
        if($allocOrder === 'trend') {
          $orderMap = [
            'alta_acelerando'=>1,
            'reversao_alta'=>2,
            'alta_estavel'=>3,
            'alta_perdendo'=>4,
            'queda_aliviando'=>5,
            'neutro'=>6,
            'sem_historico'=>7,
            'queda_estavel'=>8,
            'queda_acelerando'=>9,
            'reversao_baixa'=>10,
          ];
          usort($alloc, function($a,$b) use ($orderMap){
            $oa = $orderMap[$a['trend_code'] ?? ''] ?? 100;
            $ob = $orderMap[$b['trend_code'] ?? ''] ?? 100;
            if ($oa === $ob) { return strcmp($a['code'] ?? '', $b['code'] ?? ''); }
            return $oa <=> $ob;
          });
        }
      }
    }
  @endphp
@push('styles')
<style>
  body.openai-variations-index-compact header { display:none !important; }
  body.openai-variations-index-compact form.filters-bar { display:none !important; }
  body.openai-variations-index-compact .mb-2.d-flex.gap-2.align-items-center.position-sticky { display:none !important; }
  body.openai-variations-index-compact #btn-toggle-openai-variations-index-layout { background:#212529; color:#fff; }
</style>
@endpush

@push('scripts')
<script>
  (function(){
    // Confirmação ao trocar mês via atalhos
    const group = document.getElementById('month-shortcuts-group');
    const modalEl = document.getElementById('confirmMonthChangeModal');
    if(group && modalEl){
      let pendingHref = null;
      let modalInstance = null;
      group.addEventListener('click', function(ev){
        const a = ev.target.closest('a[href]');
        if(!a) return;
        // Se o link já está ativo, não intercepta
        if (a.classList.contains('active')) { return; }
        ev.preventDefault();
        pendingHref = a.getAttribute('href');
        if(typeof bootstrap !== 'undefined' && bootstrap.Modal){
          if(!modalInstance) modalInstance = new bootstrap.Modal(modalEl);
          modalInstance.show();
        } else {
          if(confirm('Você deseja realmente alterar o mês selecionado? Quaisquer alterações não salvas poderão ser perdidas.')){
            window.location.href = pendingHref;
          }
        }
      });
      const btn = document.getElementById('btnConfirmMonthChange');
      if(btn){
        btn.addEventListener('click', function(){
          if(pendingHref){
            const inst = (typeof bootstrap!=='undefined'&&bootstrap.Modal)? bootstrap.Modal.getInstance(modalEl) : null;
            if(inst) inst.hide();
            window.location.href = pendingHref;
          }
        });
      }
    }
  })();
</script>
@endpush

@push('scripts')
<script>
(function(){
  const LS_KEY='openai_variations_index_layout_compact';
  const BTN_ID='btn-toggle-openai-variations-index-layout';
  function apply(){
    const on = localStorage.getItem(LS_KEY)==='1';
    document.body.classList.toggle('openai-variations-index-compact', on);
    const btn = document.getElementById(BTN_ID);
    if(btn){ btn.textContent = on ? 'Modo Completo' : 'Modo Compacto'; }
  }
  document.addEventListener('DOMContentLoaded', function(){
    apply();
    const btn = document.getElementById(BTN_ID);
    if(btn){
      btn.addEventListener('click', function(){
        const next = !(localStorage.getItem(LS_KEY)==='1');
        localStorage.setItem(LS_KEY, next ? '1' : '0');
        apply();
      });
    }
  });
})();
</script>
@endpush

  @if(request('trigger_alloc') && $capital && $sum > 0 && count($alloc) > 0)
    <div class="card mb-3 shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Alocação sugerida</strong>
        <div class="d-flex flex-wrap align-items-center gap-2">
          @if(!empty($selectedMode))
            <span class="badge bg-info" title="Alocação gerada em modo seleção (somente códigos escolhidos)">Modo seleção</span>
            @php
              $__selCodes = $selectedCodesIn ?? [];
              $__selTotal = count($__selCodes);
              $__selPreview = implode(', ', array_slice($__selCodes,0,12));
              if($__selTotal>12) { $__selPreview .= '…'; }
            @endphp
            <small class="text-muted" id="alloc-selected-summary">
              {{ $__selTotal }} selecionado(s): {{ $__selPreview }}
              <button type="button" class="btn btn-xs btn-link p-0 ms-1" id="btn-toggle-selected-codes" style="font-size: .7rem">ver todos</button>
            </small>
          @endif
          <small class="text-muted" id="alloc-count">Ativos: {{ count($alloc) }}</small>
          <small class="text-muted">Base: itens exibidos • Peso ∝ Diferença (%) positiva • Cap: {{ number_format($cap*100,0,',','.') }}% • Meta: {{ number_format($target*100,0,',','.') }}%</small>
          <button type="button" id="alloc-select-all" class="btn btn-xs btn-outline-secondary btn-sm py-0">Marcar todos</button>
          <button type="button" id="alloc-select-none" class="btn btn-xs btn-outline-secondary btn-sm py-0">Desmarcar</button>
          <button type="button" id="alloc-clear-table" class="btn btn-xs btn-outline-danger btn-sm py-0" title="Remover todas as linhas da alocação exibida (não recalcula)">Esvaziar</button>
          <button type="button" id="alloc-undo-clear" class="btn btn-xs btn-outline-secondary btn-sm py-0 d-none" title="Restaurar a última alocação esvaziada">Desfazer</button>
          <button type="button" id="alloc-recalc" class="btn btn-xs btn-primary btn-sm py-0" title="Recalcular somente com os ativos selecionados (usa os mesmos parâmetros de capital, cap e meta)">Calcular Alocação (Selecionados)</button>
          @php $allocOrder = request('alloc_order',''); @endphp
          <div class="btn-group btn-group-sm" role="group" aria-label="Ordenar alocação">
            <a href="{{ request()->fullUrlWithQuery(['alloc_order'=>null]) }}" class="btn btn-outline-secondary {{ $allocOrder==='' ? 'active' : '' }}" title="Ordenar pelo fluxo padrão (diferença e cap)">Padrão</a>
            <a href="{{ request()->fullUrlWithQuery(['alloc_order'=>'trend']) }}" class="btn btn-outline-secondary {{ $allocOrder==='trend' ? 'active' : '' }}" title="Ordenar por Tendência (Alta→Queda)">Tendência</a>
          </div>

        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-striped align-middle mb-0">
              @if(!empty($selectedMode) && !empty($selectedCodesIn))
                <caption class="small ms-2">Códigos selecionados ({{ count($selectedCodesIn) }}):
                  <span id="alloc-selected-full" class="d-none">{{ implode(', ', $selectedCodesIn) }}</span>
                </caption>
              @endif
            <thead class="table-light">
              <tr>
                <th style="width:2%"><input type="checkbox" id="alloc-master" /></th>
                <th style="width:14%">Código</th>
                <th>Conversa / Ativo</th>
                <th class="text-end" style="width:12%">Variação Atual (%)</th>
                <th class="text-end" style="width:12%">Anterior (%)</th>
                <th class="text-end" style="width:12%">Diferença (pp)</th>
                <th class="text-center" style="width:10%">Tendência</th>
                <th class="text-end" style="width:14%">Aloc.Fato</th>
                <th class="text-end" style="width:12%">Peso (cap)</th>
                <th class="text-end" style="width:16%">Valor ({{ $curSymbol }})</th>
                <th class="text-end" style="width:16%">Ganho alvo ({{ $curSymbol }})</th>
                <th class="text-end" style="width:12%">Preço atual ({{ $curSymbol }})</th>
                <th class="text-end" style="width:12%">Qtd</th>
              </tr>
            </thead>
            <tbody>
              @php
                $sumQty = 0;
                $sumAllocFato = 0.0;
              @endphp
              @foreach($alloc as $r)
                <tr>
                  <td><input type="checkbox" class="alloc-row" value="{{ $r['code'] }}" checked /></td>
                  <td><strong>{{ $r['code'] ?: '—' }}</strong></td>
                  <td class="text-truncate" style="max-width: 420px">{{ $r['title'] ?: '—' }}</td>
                  <td class="text-end">@if(!is_null($r['cur'])) {{ number_format($r['cur'], 4, ',', '.') }} @else — @endif</td>
                  <td class="text-end">@if(!is_null($r['prev'])) {{ number_format($r['prev'], 4, ',', '.') }} @else — @endif</td>
                  <td class="text-end">@if(!is_null($r['diff'])) {{ number_format($r['diff'], 4, ',', '.') }} @else — @endif</td>
                  <td class="text-center">
                    @if(!empty($r['trend_label']))
                      <span class="badge bg-{{ $r['trend_badge'] ?? 'secondary' }}" title="{{ $r['trend_label'] }}">{{ $r['trend_label'] }}</span>
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                  @php
                    $rowYear = (int)($r['year'] ?? $year ?? 0);
                    $rowMonth = (int)($r['month'] ?? $month ?? 0);
                    $afKey = 'openai:variations:alloc_fato:'.$rowYear.':'.$rowMonth.':'.strtoupper($r['code'] ?? '');
                    $afVal = \Illuminate\Support\Facades\Cache::get($afKey);
                    $afRaw = is_numeric($afVal) ? (float)$afVal : null;
                    if(is_numeric($afRaw)) { $sumAllocFato += (float)$afRaw; }
                  @endphp
                  <td class="text-end" data-code="{{ $r['code'] }}">
                    <div class="input-group input-group-sm justify-content-end">
                      <span class="input-group-text">{{ $curSymbol }}</span>
                      <input type="text"
                             class="form-control form-control-sm text-end alloc-fato-input"
                             value="{{ $afRaw !== null ? number_format($afRaw, 2, '.', '') : '' }}"
                             placeholder="0.00"
                             data-code="{{ $r['code'] }}"
                             data-year="{{ (int)($r['year'] ?? $year ?? 0) }}"
                             data-month="{{ (int)($r['month'] ?? $month ?? 0) }}"
                             inputmode="decimal"
                             style="max-width: 120px" />
                    </div>
                  </td>
                  <td class="text-end">{{ number_format($r['weight']*100, 2, ',', '.') }}%</td>
                  <td class="text-end">{{ $curSymbol }} {{ number_format($r['amount'] * $dispMul, 2, ',', '.') }}</td>
                  <td class="text-end">{{ $curSymbol }} {{ number_format($r['gain_target'] * $dispMul, 2, ',', '.') }}</td>
                  <td class="text-end">
                    @if(isset($r['last_price']) && $r['last_price'] !== null)
                      {{ $curSymbol }} {{ number_format($r['last_price'] * $dispMul, 2, ',', '.') }}
                    @else
                      —
                    @endif
                  </td>
                  <td class="text-end">
                    @if(isset($r['qty']) && $r['qty'] !== null)
                      @php $sumQty += is_numeric($r['qty']) ? $r['qty'] : 0; @endphp
                      {{ number_format($r['qty'], 4, ',', '.') }}
                    @else
                      —
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <th colspan="7" class="text-end">Totais</th>
                <th id="alloc-fato-total-cell" class="text-end" data-cursymbol="{{ $curSymbol }}">{{ $curSymbol }} <span id="alloc-fato-total-val">{{ number_format($sumAllocFato, 2, ',', '.') }}</span>
                  <span id="alloc-fato-save-badge" class="badge bg-success ms-2 align-middle" style="vertical-align: middle;">salvo</span>
                  <span id="alloc-fato-excess-badge" class="badge bg-danger ms-2 align-middle d-none" style="vertical-align: middle;"></span>
                </th>
                <th></th>
                <th class="text-end">{{ $curSymbol }} {{ number_format(($calcCapital ?? 0) * $dispMul, 2, ',', '.') }}</th>
                <th class="text-end">{{ $curSymbol }} {{ number_format((($calcCapital ?? 0) * $target) * $dispMul, 2, ',', '.') }}</th>
                <th></th>
                <th class="text-end">@if($sumQty>0) {{ number_format($sumQty, 4, ',', '.') }} @endif</th>
              </tr>
            </tfoot>
          </table>
          <span id="alloc-fato-endpoint" data-url="{{ route('openai.variations.saveAllocFato') }}" hidden></span>
          @php $capDisplay = (($calcCapital ?? 0) * $dispMul); @endphp
          <span id="alloc-capital-display" data-cap="{{ number_format($capDisplay, 2, '.', '') }}" hidden></span>
        </div>
      </div>
    </div>
  @elseif(request()->has('capital') && request('trigger_alloc'))
    <div class="alert alert-warning">Não foi possível calcular a alocação. Verifique o capital informado e se há Diferença (%) positiva nos itens (ou itens selecionados no modo seleção).</div>
  @endif
  <!-- /////// -->
  <form method="get" class="filters-bar mb-3">
    @php
      // Parâmetros que precisamos preservar ao trocar Mês/Código/Sinal/Mudança/Tendência
      $persistKeys = ['year','capital','cap_pct','target_pct','grouped','spark_window','currency'];
    @endphp
    @if(request()->filled('import_file'))
      <input type="hidden" name="import_file" value="{{ request('import_file') }}" />
    @endif
    @foreach($persistKeys as $pk)
      @if(request()->filled($pk))
        <input type="hidden" name="{{ $pk }}" value="{{ request($pk) }}" />
      @endif
    @endforeach
    @if(request()->has('selected_codes'))
      @foreach((array)request('selected_codes') as $sc)
        @if($sc!=='')<input type="hidden" name="selected_codes[]" value="{{ $sc }}" />@endif
      @endforeach
    @endif
    <div class="col-auto">
      <label class="form-label mb-0 small">Mês</label>
  <select name="month" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
        <option value="">Todos</option>
        @for($m=1;$m<=12;$m++)
          <option value="{{ $m }}" @selected((int)($month ?? 0) === $m)>{{ str_pad($m,2,'0',STR_PAD_LEFT) }}</option>
        @endfor
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label mb-0 small">Código</label>
  <input type="text" name="code" value="{{ $code }}" class="form-control form-control-sm w-auto" placeholder="TSLA" />
    </div>
    <div class="col-auto">
      <label class="form-label mb-0 small">Sinal</label>
  <select name="polarity" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
        <option value="" @selected(($polarity ?? '')==='')>Todos</option>
        <option value="positive" @selected(($polarity ?? '')==='positive')>Positivos</option>
        <option value="negative" @selected(($polarity ?? '')==='negative')>Negativos</option>
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label mb-0 small">Mudança</label>
  <select name="change" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
        <option value="" @selected(($change ?? '')==='')>Todas</option>
        <option value="melhoria" @selected(($change ?? '')==='melhoria')>Melhoria</option>
        <option value="piora" @selected(($change ?? '')==='piora')>Piora</option>
        <option value="igual" @selected(($change ?? '')==='igual')>Igual</option>
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label mb-0 small">Tendência</label>
  <select name="trend" class="form-select form-select-sm w-auto" onchange="this.form.submit()" title="Filtrar por tendência calculada">
        @php
          $trendFilter = $trendFilter ?? '';
          $trendOptions = [
            '' => 'Todas',
            'alta_acelerando' => 'Alta Acelerando',
            'alta_estavel' => 'Alta Estável',
            'alta_perdendo' => 'Alta Perdendo',
            'queda_acelerando' => 'Queda Acelerando',
            'queda_estavel' => 'Queda Estável',
            'queda_aliviando' => 'Queda Aliviando',
            'reversao_alta' => 'Reversão Alta',
            'reversao_baixa' => 'Reversão Baixa',
            'neutro' => 'Neutro',
            'sem_historico' => 'Sem Histórico',
          ];
        @endphp
        @foreach($trendOptions as $tv=>$tl)
          <option value="{{ $tv }}" @selected($trendFilter===$tv)>{{ $tl }}</option>
        @endforeach
      </select>
    </div>
  </form>
<!-- /////// -->
  @if($grouped ?? false)
    <div class="alert alert-info py-2 small">Modo agrupado por código — mostrando últimas {{ $sparkWindow }} variações disponíveis por conversa/código. Ordenação por Diferença (%) disponível.</div>
    <div class="mb-2 small">
      <strong>Legenda Tendências:</strong>
      <span class="badge bg-success">Alta Acelerando</span>
      <span class="badge bg-success opacity-75">Alta Estável</span>
      <span class="badge bg-warning text-dark">Alta Perdendo</span>
      <span class="badge bg-danger">Queda Acelerando</span>
      <span class="badge bg-danger opacity-75">Queda Estável</span>
      <span class="badge bg-primary">Queda Aliviando</span>
      <span class="badge bg-success border border-light">Reversão Alta</span>
      <span class="badge bg-danger border border-light">Reversão Baixa</span>
      <span class="badge bg-secondary">Neutro</span>
      <span class="badge bg-secondary opacity-50">Sem Histórico</span>
    </div>
    <div class="table-responsive">
      <div class="small text-muted mb-1">Ativos: {{ isset($groupedData) ? count($groupedData) : 0 }}</div>
      <table class="table table-sm table-striped align-middle">
        <thead>
          <tr>
            <th>Código</th>
            <th>Conversa</th>
            <th>Último (Ano/Mês)</th>
            <th>Variação Atual (%)</th>
            @php
              if(!isset($baseParamsGrouped)){
                $baseParamsGrouped = array_filter([
                  'year'=>request('year')?:null,
                  'month'=>request('month')?:null,
                  'code'=>$code?:null,
                  'polarity'=> ($polarity ?? null) ?: null,
                  'change' => ($change ?? '') ?: null,
                  'grouped'=>1,
                  'spark_window'=>$sparkWindow,
                  'currency' => request('currency') ?: null,
                  'import_file' => request('import_file') ?: null,
                ]);
              }
              $isPrevAsc = ($sort ?? '') === 'prev_asc';
              $isPrevDesc = ($sort ?? '') === 'prev_desc';
              $prevNext = $isPrevAsc ? 'prev_desc' : ($isPrevDesc ? 'year_desc' : 'prev_asc');
              $prevIcon = $isPrevAsc ? '↑' : ($isPrevDesc ? '↓' : '↕');
            @endphp
            <th>
              <a class="text-decoration-none" href="{{ route('openai.variations.index', array_merge($baseParamsGrouped, ['sort'=>$prevNext])) }}" title="Ordenar / alternar por Variação anterior">Anterior (%) {{ $prevIcon }}</a>
            </th>
            @php
              $baseParamsGrouped = array_filter([
                'year'=>request('year')?:null,
                'month'=>request('month')?:null,
                'code'=>$code?:null,
                'polarity'=> ($polarity ?? null) ?: null,
                'change' => ($change ?? '') ?: null,
                'grouped'=>1,
                'spark_window'=>$sparkWindow,
                'currency' => request('currency') ?: null,
                'import_file' => request('import_file') ?: null,
              ]);
              $isDiffAsc = ($sort ?? '') === 'diff_asc';
              $isDiffDesc = ($sort ?? '') === 'diff_desc';
              $diffNext = $isDiffAsc ? 'diff_desc' : ($isDiffDesc ? 'year_desc' : 'diff_asc');
              $diffIcon = $isDiffAsc ? '↑' : ($isDiffDesc ? '↓' : '↕');
            @endphp
            <th>
              <a class="text-decoration-none" href="{{ route('openai.variations.index', array_merge($baseParamsGrouped, ['sort'=>$diffNext])) }}" title="Ordenar / alternar por Diferença (atual - anterior)">Diferença (%) {{ $diffIcon }}</a>
            </th>
            <th>Tendência</th>
            <th>Sparkline</th>
          </tr>
        </thead>
        <tbody>
          @forelse($groupedData as $g)
            @php
              $latest = $g['latest'];
              $pv = $g['prev_variation'];
              $diff = $g['diff'];
              $clsLatest = $latest->variation > 0 ? 'text-success' : ($latest->variation < 0 ? 'text-danger' : 'text-muted');
              $clsPrev = (!is_null($pv)) ? ($pv > 0 ? 'text-success' : ($pv < 0 ? 'text-danger' : 'text-muted')) : 'text-muted';
              $clsDiff = (!is_null($diff)) ? ($diff > 0 ? 'text-success' : ($diff < 0 ? 'text-danger' : 'text-muted')) : 'text-muted';
              $badge = is_null($diff) ? '' : ($diff > 0 ? '<span class="badge bg-success ms-1" title="Melhoria">↑</span>' : ($diff < 0 ? '<span class="badge bg-danger ms-1" title="Piora">↓</span>' : '<span class="badge bg-secondary ms-1" title="Sem mudança">=</span>'));
              // Sparkline data
              $values = array_map(fn($r)=> (float)$r->variation, $g['rows']);
              $minV = min($values); $maxV = max($values); $range = ($maxV - $minV) ?: 1;
              $w = max( (count($values)-1)*12, 24 ); $h = 40; // largura proporcional
              $points = [];
              foreach($values as $i=>$val){
                $x = ($i/(max(count($values)-1,1)))*($w-4)+2; // margem 2
                $y = $h - 2 - (($val - $minV)/$range)*($h-4); // invertido
                $points[] = $x.','.$y;
              }
              $sparkTitle = 'Valores: '.implode(', ', array_map(fn($v)=>number_format($v,2,',','.'), $values));
            @endphp
            <tr>
              <td>{{ $g['asset_code'] }}</td>
              <td>{{ $g['chat_title'] }}</td>
              <td>{{ $latest->year }}/{{ str_pad($latest->month,2,'0',STR_PAD_LEFT) }}</td>
              <td class="{{ $clsLatest }}">{{ number_format($latest->variation,4,',','.') }}%</td>
              <td class="{{ $clsPrev }}">@if(!is_null($pv)) {{ number_format($pv,4,',','.') }}% @else — @endif</td>
              <td class="{{ $clsDiff }}">@if(!is_null($diff)) {{ number_format($diff,4,',','.') }}% {!! $badge !!} @else — @endif</td>
              @php
                $tLabel = $g['trend_label'] ?? null;
                $tBadge = $g['trend_badge'] ?? 'secondary';
                $tNorm = $g['normalized_variation'] ?? $latest->variation;
                $tConf = $g['trend_confidence'] ?? 0;
                $tDE = $g['days_elapsed'] ?? null; $tDM = $g['days_month'] ?? null;
              @endphp
              <td>
                @if($tLabel)
                  <span class="badge bg-{{ $tBadge }}" title="Tendência: {{ $tLabel }} | Normalizado: {{ number_format($tNorm,4,',','.') }}% | Confiança: {{ number_format($tConf*100,1,',','.') }}% @if($tDE && $tDM) | Dias: {{ $tDE }}/{{ $tDM }} @endif">{{ $tLabel }}</span>
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
              <td>
                <svg width="{{ $w }}" height="{{ $h }}" viewBox="0 0 {{ $w }} {{ $h }}" preserveAspectRatio="none" class="spark" aria-label="Sparkline" role="img" title="{{ $sparkTitle }}">
                  <polyline fill="none" stroke="#0d6efd" stroke-width="2" points="{{ implode(' ', $points) }}" />
                  @if(count($points))
                    @php $lastCoords = explode(',', end($points)); @endphp
                    <circle cx="{{ $lastCoords[0] }}" cy="{{ $lastCoords[1] }}" r="3" fill="#0d6efd" />
                  @endif
                </svg>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted">Nenhum dado agrupado encontrado.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  @else
  @php
    // Contagem de ativos únicos na página atual (modo não agrupado)
    $uniqueAssetCount = collect($variations instanceof \Illuminate\Contracts\Pagination\Paginator ? $variations->items() : $variations)
      ->pluck('asset_code')
      ->filter()
      ->unique()
      ->count();
  @endphp
  <div class="small text-muted mb-1">Ativos nesta página: {{ $uniqueAssetCount }} • Registros totais: @if(method_exists($variations,'total')) {{ $variations->total() }} @else {{ $uniqueAssetCount }} @endif</div>
  <div class="table-responsive">
    <table class="table table-sm table-striped align-middle">
      <thead>
        <tr>
          <th style="width:2%"><input type="checkbox" id="var-master" title="Marcar / desmarcar todos" /></th>
          <th>ID</th>
          @php
            // Parâmetros base preservados para os links de ordenação
            $baseParams = array_filter([
              'year'=>request('year')?:null,
              'month'=>request('month')?:null,
              'code'=>$code?:null,
              'polarity'=> ($polarity ?? null) ?: null,
              'change' => ($change ?? '') ?: null,
              'currency' => request('currency') ?: null,
              'import_file' => request('import_file') ?: null,
            ]);
          @endphp
          @php
            $isCodeAsc = ($sort ?? '') === 'code_asc';
            $isCodeDesc = ($sort ?? '') === 'code_desc';
            $codeNext = $isCodeAsc ? 'code_desc' : ($isCodeDesc ? 'year_desc' : 'code_asc');
            $codeIcon = $isCodeAsc ? '↑' : ($isCodeDesc ? '↓' : '↕');
          @endphp
          <th>
            <a class="text-decoration-none" href="{{ route('openai.variations.index', array_merge($baseParams, ['sort'=>$codeNext])) }}" title="Ordenar / alternar ordenação por código">
              Código {{ $codeIcon }}
            </a>
          </th>
          <th>Conversa / Ativo</th>
          @php
            $isYearAsc = ($sort ?? '') === 'year_asc';
            $isYearDesc = ($sort ?? '') === 'year_desc';
            $yearNext = $isYearAsc ? 'year_desc' : ($isYearDesc ? 'month_desc' : 'year_asc');
            // Nota: ciclo diferente pode confundir; manter padrão 3 estados como demais: asc->desc->padrão(year_desc). Mas year_desc é também o padrão, então: asc->desc->asc? Melhor replicar padrão: (none)->asc->desc->none. Como default já é year_desc, faremos: if default e user clica: year_asc.
            // Ajuste: se default (year_desc) e não explicitamente setado, mostrar ícone ↕ sem link extra.
            $yearIcon = $isYearAsc ? '↑' : ($isYearDesc ? '↓' : '↕');
            if($isYearDesc && request('sort') !== 'year_desc'){ /* year_desc vindo explicitamente */ }
            $yearNext = $isYearAsc ? 'year_desc' : ($isYearDesc ? 'year_asc' : 'year_asc');

            $isMonthAsc = ($sort ?? '') === 'month_asc';
            $isMonthDesc = ($sort ?? '') === 'month_desc';
            $monthNext = $isMonthAsc ? 'month_desc' : ($isMonthDesc ? 'year_desc' : 'month_asc');
            $monthIcon = $isMonthAsc ? '↑' : ($isMonthDesc ? '↓' : '↕');
          @endphp
          <th>
            <a class="text-decoration-none" href="{{ route('openai.variations.index', array_merge($baseParams, ['sort'=>$yearNext])) }}" title="Ordenar / alternar por ano">
              Ano {{ $yearIcon }}
            </a>
          </th>
          <th>
            <a class="text-decoration-none" href="{{ route('openai.variations.index', array_merge($baseParams, ['sort'=>$monthNext])) }}" title="Ordenar / alternar por mês">
              Mês {{ $monthIcon }}
            </a>
          </th>
          @php
            $isVarAsc = ($sort ?? '') === 'variation_asc';
            $isVarDesc = ($sort ?? '') === 'variation_desc';
            $nextSort = $isVarAsc ? 'variation_desc' : ($isVarDesc ? 'year_desc' : 'variation_asc');
            $icon = $isVarAsc ? '↑' : ($isVarDesc ? '↓' : '↕');
          @endphp
          <th>
            <a class="text-decoration-none" href="{{ route('openai.variations.index', array_merge($baseParams, ['sort'=>$nextSort])) }}" title="Ordenar / alternar ordenação pela variação">
              Variação Atual (%) {{ $icon }}
            </a>
          </th>
          @php
            $isPrevAsc = ($sort ?? '') === 'prev_asc';
            $isPrevDesc = ($sort ?? '') === 'prev_desc';
            $prevNext = $isPrevAsc ? 'prev_desc' : ($isPrevDesc ? 'year_desc' : 'prev_asc');
            $prevIcon = $isPrevAsc ? '↑' : ($isPrevDesc ? '↓' : '↕');
          @endphp
          <th title="Variação do mês anterior (mesma conversa)">
            <a class="text-decoration-none" href="{{ route('openai.variations.index', array_merge($baseParams, ['sort'=>$prevNext])) }}" title="Ordenar / alternar por variação anterior">Anterior (%) {{ $prevIcon }}</a>
          </th>
          @php
            $isDiffAsc = ($sort ?? '') === 'diff_asc';
            $isDiffDesc = ($sort ?? '') === 'diff_desc';
            $diffNext = $isDiffAsc ? 'diff_desc' : ($isDiffDesc ? 'year_desc' : 'diff_asc');
            $diffIcon = $isDiffAsc ? '↑' : ($isDiffDesc ? '↓' : '↕');
          @endphp
          <th>
            <a class="text-decoration-none" href="{{ route('openai.variations.index', array_merge($baseParams, ['sort'=>$diffNext])) }}" title="Ordenar / alternar ordenação por Diferença (atual - anterior)">Diferença (%) {{ $diffIcon }}</a>
          </th>
          <th>Tendência</th>
          @php
            $isCreatedAsc = ($sort ?? '') === 'created_asc';
            $isCreatedDesc = ($sort ?? '') === 'created_desc';
            $createdNext = $isCreatedAsc ? 'created_desc' : ($isCreatedDesc ? 'year_desc' : 'created_asc');
            $createdIcon = $isCreatedAsc ? '↑' : ($isCreatedDesc ? '↓' : '↕');
            $isUpdatedAsc = ($sort ?? '') === 'updated_asc';
            $isUpdatedDesc = ($sort ?? '') === 'updated_desc';
            $updatedNext = $isUpdatedAsc ? 'updated_desc' : ($isUpdatedDesc ? 'year_desc' : 'updated_asc');
            $updatedIcon = $isUpdatedAsc ? '↑' : ($isUpdatedDesc ? '↓' : '↕');
          @endphp
          <th>
            <a class="text-decoration-none" href="{{ route('openai.variations.index', array_merge($baseParams, ['sort'=>$createdNext])) }}" title="Ordenar / alternar por data de criação">
              Criado {{ $createdIcon }}
            </a>
          </th>
          <th>
            <a class="text-decoration-none" href="{{ route('openai.variations.index', array_merge($baseParams, ['sort'=>$updatedNext])) }}" title="Ordenar / alternar por data de atualização">
              Atualizado {{ $updatedIcon }}
            </a>
          </th>
          <th title="Flag por usuário: COMPRAR ou NÃO COMPRAR">Flag</th>
        </tr>
      </thead>
      <tbody>
        @forelse($variations as $v)
          @php
            $pv = $prevVariationMap[$v->id] ?? null;
            $diff = (!is_null($pv)) ? ($v->variation - $pv) : null;
          @endphp
          <tr data-row-code="{{ strtoupper($v->asset_code) }}" data-year="{{ (int)$v->year }}" data-month="{{ (int)$v->month }}" data-variation="{{ $v->variation }}" @if(!is_null($diff)) data-diff="{{ $diff }}" @endif>
            <td><input type="checkbox" class="var-select" value="{{ strtoupper($v->asset_code) }}" /></td>
            <td>{{ $v->id }}</td>
            <td>{{ $v->asset_code }}</td>
            <td>
              @php
           // 'from' agora é o último dia do mês anterior
        $firstOfMonth = \Carbon\Carbon::create($v->year, $v->month, 1);
        $fromDate = $firstOfMonth->copy()->subDay()->format('Y-m-d');
        // Último registro (mais recente) deste chat; se existir, usar data dele como 'to'
        $lastRecordDate = null;
        if($v->chat && $v->chat->relationLoaded('records') && $v->chat->records->count()){
          $lastRecordDate = optional($v->chat->records->sortByDesc('occurred_at')->first()->occurred_at)->format('Y-m-d');
        } elseif($v->chat) {
          try {
            $lastRecord = \App\Models\OpenAIChatRecord::where('chat_id',$v->chat_id)
              ->orderByDesc('occurred_at')
              ->select('occurred_at')
              ->first();
            if($lastRecord){ $lastRecordDate = optional($lastRecord->occurred_at)->format('Y-m-d'); }
          } catch(\Throwable $e) { /* silencioso */ }
        }
        $toDate = $lastRecordDate ?: $firstOfMonth->copy()->endOfMonth()->format('Y-m-d');
              @endphp
              @if($v->chat)
                <a href="{{ route('openai.records.index', ['chat_id'=>$v->chat_id,'from'=>$fromDate,'to'=>$toDate,'filter_exact'=>1]) }}" class="text-decoration-none" title="Ver registros da conversa no mês ({{ $fromDate }} a {{ $toDate }})">
                  {{ $v->chat->title }} @if($v->chat->code) <span class="badge bg-dark ms-1">{{ $v->chat->code }}</span> @endif
                </a>
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
            <td>{{ $v->year }}</td>
            <td>{{ str_pad($v->month,2,'0',STR_PAD_LEFT) }}</td>
            <td>{{ number_format($v->variation, 4, ',', '.') }}</td>
            <td>
              @php /* $pv já calculado antes da <tr> */ @endphp
              @if(!is_null($pv))
                @php
                  $clsPrev = $pv > 0 ? 'text-success' : ($pv < 0 ? 'text-danger' : 'text-muted');
                  $arrowPrev = $pv > 0 ? '▲' : ($pv < 0 ? '▼' : '▶');
                @endphp
                <span class="small {{ $clsPrev }}" title="Variação mês anterior">{{ $arrowPrev }} {{ number_format($pv, 4, ',', '.') }}%</span>
              @else
                <span class="text-muted small">—</span>
              @endif
            </td>
            @php
              // $diff já calculado antes da <tr>
              $clsDiff = is_null($diff) ? 'text-muted' : ($diff > 0 ? 'text-success' : ($diff < 0 ? 'text-danger' : 'text-secondary'));
              $badge = '';
              if(!is_null($diff)){
                if($diff > 0) $badge = '<span class="badge bg-success ms-1" title="Melhoria">↑</span>';
                elseif($diff < 0) $badge = '<span class="badge bg-danger ms-1" title="Piora">↓</span>';
                else $badge = '<span class="badge bg-secondary ms-1" title="Sem mudança">=</span>';
              }
            @endphp
            <td class="small {{ $clsDiff }}">@if(!is_null($diff)) {{ number_format($diff,4,',','.') }}% {!! $badge !!} @else — @endif</td>
            @php $trend = $trendData[$v->id] ?? null; @endphp
            <td class="small">
              @if($trend)
                @php
                  $tBadge = $trend['badge'] === 'info' ? 'primary' : $trend['badge'];
                @endphp
                <span class="badge bg-{{ $tBadge }}" data-trend-code="{{ $trend['code'] }}" title="Tendência: {{ $trend['label'] }} | Normalizado: {{ number_format($trend['normalized'],4,',','.') }}% | Confiança: {{ number_format($trend['confidence']*100,1,',','.') }}% @if($trend['days_elapsed'] && $trend['days_month']) | Dias: {{ $trend['days_elapsed'] }}/{{ $trend['days_month'] }} @endif">{{ $trend['label'] }}</span>
              @else
                <span class="text-muted" data-trend-code="">—</span>
              @endif
            </td>
            <td>{{ $v->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td>
            <td>{{ $v->updated_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td>
            <td class="text-center">
              @php $flagCode = strtoupper(trim($v->asset_code ?? '')); @endphp
              @if($flagCode !== '')
                <div class="d-inline-flex align-items-center gap-2">
                  <span class="badge bg-secondary" data-flag-code="{{ $flagCode }}">—</span>
                  <button type="button" class="btn btn-xs btn-outline-secondary" data-flag-toggle data-flag-code="{{ $flagCode }}" title="Alternar COMPRAR/NÃO COMPRAR">Alternar</button>
                </div>
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="text-center text-muted">Nenhuma variação encontrada.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @endif
  @if(!($grouped ?? false))
    <div class="mt-2 d-flex flex-wrap gap-2 align-items-center small">
      <strong>Seleção para Alocação:</strong>
      <button type="button" class="btn btn-sm btn-outline-secondary" id="var-select-clear">Limpar</button>
      <button type="button" class="btn btn-sm btn-outline-secondary" id="var-select-positive" title="Selecionar códigos com variação atual > 0">Positivos</button>
      <button type="button" class="btn btn-sm btn-outline-secondary" id="var-select-buy" title="Selecionar códigos com flag COMPRAR">COMPRAR</button>
      <button type="button" class="btn btn-sm btn-outline-secondary" id="var-select-trend-up" title="Selecionar tendências de alta / reversão / alívio">Tendências Alta</button>
  <button type="button" class="btn btn-sm btn-outline-success" id="var-select-trend-accelerating" title="Selecionar apenas tendência Alta Acelerando (alta_acelerando) e gerar alocação">Alta Acelerando</button>
      <div class="input-group input-group-sm" style="width:170px;">
        <span class="input-group-text" title="Diferença mínima (pp) para seleção de Diff +">Diff &gt;</span>
        <input type="text" class="form-control" id="diff-threshold" value="{{ request('diff_threshold','0.20') }}" />
      </div>
      <button type="button" class="btn btn-sm btn-outline-primary" id="var-select-diff-positive" title="Selecionar onde Diferença (pp) excede o limiar informado">Diff + &gt; Limiar</button>
      <span class="text-muted" id="var-selection-count"></span>
    </div>
  @endif
  @if(!($grouped ?? false))
    @if(!(request('no_page')))
      <div>
        {{ $variations->links() }}
      </div>
    @endif
  @endif
</div>
@endsection
@push('scripts')
@if(isset($portfolioCodes))
  <script type="application/json" id="pf-codes-json">{!! json_encode(array_values(array_unique(array_filter(($portfolioCodes ?? [])))), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
@endif
<script>
 (function(){
  // Confirmação para limpar cache
  const clearForm = document.getElementById('form-clear-openai-variations-cache');
  if(clearForm){
    clearForm.addEventListener('submit', function(ev){
      const msg = 'Confirmar limpar o cache da aplicação?\n\nIsso pode afetar contadores, listas e caches temporários por alguns minutos.';
      if(!confirm(msg)) { ev.preventDefault(); }
    });
  }
   const master = document.getElementById('alloc-master');
   const rows = () => Array.from(document.querySelectorAll('.alloc-row'));
   const btnAll = document.getElementById('alloc-select-all');
   const btnNone = document.getElementById('alloc-select-none');
  const btnClearTable = document.getElementById('alloc-clear-table');
   const btnRecalc = document.getElementById('alloc-recalc');
  const allocCountEl = document.getElementById('alloc-count');
   function syncMaster(){
     const r = rows();
     if(r.length===0) return;
     master.checked = r.every(ch=>ch.checked);
     master.indeterminate = !master.checked && r.some(ch=>ch.checked);
   }
   // Disponibiliza para outros scripts
   window._allocSyncMaster = syncMaster;

  // Marca na lista de variações apenas os códigos já marcados na alocação (não desmarca nada automaticamente)
  function propagateAllocationMarks(){
    try {
      const allocCodes = rows().filter(c=>c.checked).map(c=> (c.value||'').toUpperCase()).filter(Boolean);
      if(!allocCodes.length) return;
      const set = new Set(allocCodes);
      const varBoxes = Array.from(document.querySelectorAll('.var-select'));
      let changed = false;
      varBoxes.forEach(vb=>{
        const code = (vb.value||'').toUpperCase();
        if(set.has(code) && !vb.checked){ vb.checked = true; changed = true; }
      });
      if(changed){
        try {
          const sel = Array.from(document.querySelectorAll('.var-select:checked')).map(x=>x.value.toUpperCase());
          localStorage.setItem('openai_variations_selected_codes', JSON.stringify(sel));
        } catch(_e) {}
        document.dispatchEvent(new CustomEvent('var-selection-updated'));
      }
    } catch(_e) { /* noop */ }
  }

  // Flag global para evitar loops de eventos entre tabelas
  window._allocVarSyncing = false;

  function updateVarSelectionStorage(){
    try {
      const sel = Array.from(document.querySelectorAll('.var-select:checked')).map(x=>x.value.toUpperCase());
      localStorage.setItem('openai_variations_selected_codes', JSON.stringify(sel));
    } catch(_e) {}
  }
  function updateVarMasterState(){
    const varMaster = document.getElementById('var-master');
    if(!varMaster) return;
    const boxes = Array.from(document.querySelectorAll('.var-select'));
    if(!boxes.length) return;
    const allChecked = boxes.every(b=>b.checked);
    const someChecked = boxes.some(b=>b.checked);
    varMaster.checked = allChecked;
    varMaster.indeterminate = !allChecked && someChecked;
  }
  // Expor para outros blocos (sincronização em scripts posteriores)
  window.updateVarSelectionStorage = updateVarSelectionStorage;
  window.updateVarMasterState = updateVarMasterState;

  // Sincroniza mudança de um checkbox de alocação para o correspondente na lista de variações
  function mirrorAllocChange(ch){
    const code = (ch.value||'').toUpperCase();
    if(!code) return;
    if(window._allocVarSyncing) return; // evita recursão
    window._allocVarSyncing = true;
    try {
      document.querySelectorAll('.var-select').forEach(vb=>{
        if((vb.value||'').toUpperCase() === code && vb.checked !== ch.checked){
          vb.checked = ch.checked;
        }
      });
  updateVarSelectionStorage();
  updateVarMasterState();
  if(typeof updateSelectionCount === 'function') updateSelectionCount();
      // Dispara evento para outros ouvintes (contador, etc.)
      try {
        const synthetic = new Event('change', {bubbles:true});
        document.dispatchEvent(synthetic);
      } catch(_e) { /* noop */ }
    } finally {
      window._allocVarSyncing = false;
    }
  }
   if(master){
     master.addEventListener('change', ()=>{
       rows().forEach(ch=>{ ch.checked = master.checked; });
       syncMaster();
       if(typeof updateSelectionCount === 'function') updateSelectionCount();
     });
   }
   // Modal para Marcar/Desmarcar todos na alocação
   function ensureAllocModal(){
     if(document.getElementById('allocBulkSelectModal')) return document.getElementById('allocBulkSelectModal');
     const html = `\n<div class="modal fade" id="allocBulkSelectModal" tabindex="-1" aria-hidden="true">\n  <div class="modal-dialog modal-sm modal-dialog-centered">\n    <div class="modal-content">\n      <div class="modal-header py-2">\n        <h6 class="modal-title mb-0">Alocação - Seleção</h6>\n        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>\n      </div>\n      <div class="modal-body small py-3">\n        <p class="mb-2">Aplicar ação em todos os ativos alocados?</p>\n        <div class="d-grid gap-2">\n          <button type="button" class="btn btn-primary btn-sm" id="btn-alloc-select-all">Marcar todos</button>\n          <button type="button" class="btn btn-outline-danger btn-sm" id="btn-alloc-unselect-all">Desmarcar todos</button>\n        </div>\n      </div>\n      <div class="modal-footer py-2">\n        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>\n      </div>\n    </div>\n  </div>\n</div>`;
     const wrap = document.createElement('div'); wrap.innerHTML = html; document.body.appendChild(wrap.firstElementChild); return document.getElementById('allocBulkSelectModal');
   }
   function showAllocModal(){
     const el = ensureAllocModal();
     if(typeof bootstrap !== 'undefined' && bootstrap.Modal){
       (new bootstrap.Modal(el)).show();
     } else {
       // Fallback sem Bootstrap: confirm dual
       const applyAll = confirm('Marcar todos? (Cancelar = Desmarcar todos)');
       bulkSyncAllocation(applyAll);
     }
   }
   function bulkSyncAllocation(checked){
     const allocs = rows();
     allocs.forEach(c=> c.checked = checked);
     // Sincroniza para tabela de variações (somente códigos que existem lá)
     try {
       window._allocVarSyncing = true;
       const codes = new Set(allocs.map(c=> (c.value||'').toUpperCase()));
       document.querySelectorAll('.var-select').forEach(v=>{
         const code = (v.value||'').toUpperCase();
         if(codes.has(code)) v.checked = checked;
       });
       if(typeof updateVarSelectionStorage === 'function') updateVarSelectionStorage();
       if(typeof updateVarMasterState === 'function') updateVarMasterState();
     } catch(_e){} finally { window._allocVarSyncing = false; }
     syncMaster();
     try { if(typeof window._updateCalcButtonState === 'function') window._updateCalcButtonState(); } catch(_e){}
   }
   if(btnAll){ btnAll.addEventListener('click', (e)=>{ e.preventDefault(); showAllocModal(); }); }
   if(btnNone){ btnNone.addEventListener('click', (e)=>{ e.preventDefault(); showAllocModal(); }); }
   // Ações dentro do modal
   document.addEventListener('click', function(ev){
     const id = ev.target && ev.target.id;
     if(id === 'btn-alloc-select-all'){ bulkSyncAllocation(true); const m = document.getElementById('allocBulkSelectModal'); if(m && typeof bootstrap!=='undefined'&&bootstrap.Modal){ bootstrap.Modal.getInstance(m)?.hide(); } }
     else if(id === 'btn-alloc-unselect-all'){ bulkSyncAllocation(false); const m = document.getElementById('allocBulkSelectModal'); if(m && typeof bootstrap!=='undefined'&&bootstrap.Modal){ bootstrap.Modal.getInstance(m)?.hide(); } }
   }, true);
    if(btnClearTable){
      const btnUndo = document.getElementById('alloc-undo-clear');
      let lastAllocSnapshot = null; // [{code, html}]
      btnClearTable.addEventListener('click', ()=>{
        if(!confirm('Confirmar esvaziar a alocação atual? Esta ação pode ser desfeita enquanto permanecer na página.')) return;
        const table = master ? master.closest('table') : null;
        const tbody = table ? table.querySelector('tbody') : null;
        if(!tbody) return;
        // Snapshot
        lastAllocSnapshot = Array.from(tbody.querySelectorAll('tr')).map(tr=>({code: (tr.querySelector('.alloc-row')?.value||'').toUpperCase(), html: tr.outerHTML}));
        tbody.querySelectorAll('tr').forEach(tr=>tr.remove());
        if(allocCountEl){ allocCountEl.textContent = 'Ativos: 0'; }
        // Remover parâmetros de seleção e trigger da URL para não recriar após refresh
        try {
          const url = new URL(window.location.href);
          // Apagar todos selected_codes[] e trigger_alloc
          const toDelete = [];
          url.searchParams.forEach((v,k)=>{ if(k==='selected_codes[]' || k==='trigger_alloc') toDelete.push(k); });
          toDelete.forEach(k=> url.searchParams.delete(k));
          // Atualizar histórico sem recarregar
          window.history.replaceState({}, document.title, url.pathname + (url.searchParams.toString()?('?'+url.searchParams.toString()):''));
        } catch(_e) { /* noop */ }
        // Remover hidden inputs existentes para selected_codes
        document.querySelectorAll('input[name="selected_codes[]"]').forEach(el=>el.remove());
        document.querySelectorAll('input[name="trigger_alloc"]').forEach(el=>el.remove());
        // Limpar seleção persistida (localStorage)
        try { localStorage.removeItem('openai_variations_selected_codes'); } catch(_e) {}
        // Opcional: feedback visual rápido
        try {
          const msg = document.createElement('div');
          msg.className = 'alert alert-info py-1 px-2 position-fixed top-0 end-0 m-3 shadow';
          msg.style.zIndex = 1080;
          msg.textContent = 'Alocação esvaziada. Recalcule para gerar novamente.';
          document.body.appendChild(msg);
          setTimeout(()=>{ msg.remove(); }, 3500);
        } catch(_e) {}
        if(btnUndo){ btnUndo.classList.remove('d-none'); }
     });
      if(btnUndo){
        btnUndo.addEventListener('click', ()=>{
          if(!lastAllocSnapshot || !lastAllocSnapshot.length){ alert('Nada para restaurar.'); return; }
          const table = master ? master.closest('table') : null;
          const tbody = table ? table.querySelector('tbody') : null;
          if(!tbody) return;
          tbody.innerHTML = lastAllocSnapshot.map(r=>r.html).join('');
          // Reativar eventos dos checkboxes restaurados
          tbody.querySelectorAll('.alloc-row').forEach(ch=>{
            ch.addEventListener('change', (e)=>{ syncMaster(); mirrorAllocChange(e.target); if(typeof updateSelectionCount==='function') updateSelectionCount(); });
          });
          syncMaster();
          if(allocCountEl){ allocCountEl.textContent = 'Ativos: '+tbody.querySelectorAll('.alloc-row').length; }
          // Não restaura selected_codes automaticamente na URL para manter intenção do usuário de não persistir; fica apenas na sessão atual.
          // Oculta botão de desfazer após uso único
          btnUndo.classList.add('d-none');
        });
      }
   }
   rows().forEach(c=> c.addEventListener('change', (e)=>{
     syncMaster();
     mirrorAllocChange(e.target);
     try { if(typeof window._updateCalcButtonState === 'function') window._updateCalcButtonState(); } catch(_e){}
   }));

   // Recalcular usando somente selecionados: envia form GET preservando parâmetros e adicionando selected_codes
   if(btnRecalc){
     btnRecalc.addEventListener('click', ()=>{
       const sel = rows().filter(c=>c.checked).map(c=>c.value).filter((v,i,a)=>v && a.indexOf(v)==i);
      if(sel.length===0){
        // Fallback: tentar usar seleção da tabela principal de variações
        const varSel = Array.from(document.querySelectorAll('.var-select:checked')).map(c=>c.value.toUpperCase());
        if(varSel.length){
          // Disparar geração principal (usa lógica consolidada) adicionando no_page=1 para evitar paginação
          const topBtn = document.getElementById('filter-calc-alloc-btn');
          if(topBtn){ topBtn.click(); return; }
        }
        alert('Selecione ao menos um ativo.');
        return;
      }
       const form = document.createElement('form');
       form.method='GET';
       form.action = window.location.pathname;
       const params = new URLSearchParams(window.location.search);
       // remove paginação se houver
       params.delete('page');
      // Garantir no_page=1 para trazer todos os registros da seleção
      params.set('no_page','1');
       params.forEach((val,key)=>{
         const inp = document.createElement('input');
         inp.type='hidden'; inp.name=key; inp.value=val; form.appendChild(inp);
       });
       sel.forEach(code=>{
         const inp = document.createElement('input');
         inp.type='hidden'; inp.name='selected_codes[]'; inp.value=code; form.appendChild(inp);
       });
      // Sinaliza que deve montar a alocação
      const trg = document.createElement('input'); trg.type='hidden'; trg.name='trigger_alloc'; trg.value='1'; form.appendChild(trg);
       document.body.appendChild(form);
       form.submit();
     });
   }
   syncMaster();
  // Propaga no load inicial (caso recarregue a página com alocação existente)
  propagateAllocationMarks();
  // Marcar automaticamente os códigos que estão na carteira do usuário (sem desmarcar os já selecionados)
  try{
    const el = document.getElementById('pf-codes-json');
    if(el){
      // Nunca auto-marcar carteira sem seleção explícita (selected_codes) ou trigger de alocação
      try {
        const qs = new URLSearchParams(window.location.search);
        const hasSel = qs.getAll('selected_codes[]').length > 0;
        const hasAlloc = qs.has('trigger_alloc');
        if(!hasSel && !hasAlloc){ return; }
      } catch(_e) {}
      const arr = JSON.parse(el.textContent || '[]');
      if(Array.isArray(arr) && arr.length){
        const set = new Set(arr.map(c=>String(c||'').toUpperCase()));
        let changed=false;
        document.querySelectorAll('.var-select').forEach(ch=>{
          const code = (ch.value||'').toUpperCase();
          if(set.has(code) && !ch.checked){ ch.checked = true; changed=true; }
        });
        if(changed){
          if(typeof updateVarSelectionStorage === 'function') updateVarSelectionStorage();
          if(typeof updateVarMasterState === 'function') updateVarMasterState();
          document.dispatchEvent(new CustomEvent('var-selection-updated'));
        }
      }
    }
  }catch(_e){}
 })();
</script>
@endpush

@push('scripts')
<script>
// Integração do botão superior "Calcular alocação" com seleção de checkboxes (var-select)
(function(){
  const topBtn = document.getElementById('filter-calc-alloc-btn');
  if(!topBtn) return;
  const form = topBtn.closest('form');
  if(!form) return;
  function getVarSelected(){ return Array.from(document.querySelectorAll('.var-select:checked')).map(c=>c.value.toUpperCase()); }
  function getAllocSelected(){ return Array.from(document.querySelectorAll('.alloc-row:checked')).map(c=> (c.value||'').toUpperCase()); }
  function hasAllocTable(){ return document.querySelectorAll('.alloc-row').length > 0; }
  topBtn.addEventListener('click', function(ev){
    ev.preventDefault();
    // Limpar qualquer selected_codes[] anterior (para respeitar desmarcações)
    Array.from(form.querySelectorAll('input[name="selected_codes[]"]')).forEach(el=>el.remove());
    Array.from(form.querySelectorAll('input[name="trigger_alloc"]')).forEach(el=>el.remove());
    // Coletar seleção: PRIORIZE a tabela de Alocação quando existir; senão, use as variações
    const useAlloc = hasAllocTable();
    const finalList = useAlloc ? getAllocSelected() : getVarSelected();
    const finalCodes = new Set(finalList.filter(Boolean));
    // Criar novos hidden somente para os códigos efetivamente marcados agora
    finalCodes.forEach(code=>{
      const inp = document.createElement('input');
      inp.type='hidden'; inp.name='selected_codes[]'; inp.value=code; form.appendChild(inp);
    });
    // Se houver pelo menos um selecionado, garantir no_page=1
    if(finalCodes.size && !form.querySelector('input[name="no_page"]')){
      const np = document.createElement('input'); np.type='hidden'; np.name='no_page'; np.value='1'; form.appendChild(np);
    }
    // Gatilho explícito para montar a alocação
    const trg = document.createElement('input'); trg.type='hidden'; trg.name='trigger_alloc'; trg.value='1'; form.appendChild(trg);
    // Garantir que há capital informado para aparecer alocação
    const capitalField = form.querySelector('input[name="capital"]');
    if(!capitalField || capitalField.value.trim()===''){
      if(!confirm('Capital vazio. Prosseguir mesmo assim?')) return;
    }
    form.submit();
  });
})();
</script>
@endpush

@if(session('post_import_clear_alloc'))
@push('scripts')
<script>
// Após importar, acionar automaticamente a limpeza da alocação uma única vez
(function(){
  document.addEventListener('DOMContentLoaded', function(){
    try{
      const btn = document.getElementById('alloc-clear-table');
      if(btn){ btn.click(); }
      // Desabilitar o botão/label de Importar Selecionados após a importação
      const fileInp = document.getElementById('import-selected-file');
      const labelBtn = fileInp ? fileInp.closest('label') : null;
      if(fileInp && labelBtn){
        fileInp.setAttribute('disabled','disabled');
        fileInp.dataset.forceDisabled = '1';
        labelBtn.classList.add('disabled');
        labelBtn.setAttribute('aria-disabled','true');
        labelBtn.dataset.forceDisabled = '1';
        labelBtn.style.pointerEvents = 'none';
        labelBtn.title = 'Importação concluída. Selecione outro mês para habilitar novamente.';
      }
    }catch(_e){}
  });
})();
</script>
@endpush
@endif

@push('scripts')
<script>
// Extensões: diff threshold, persistência, auto-recalc Alta Acelerando
(function(){
  if(document.querySelector('[data-row-code]') === null) return; // somente modo não agrupado
  const diffBtn = document.getElementById('var-select-diff-positive');
  const diffInput = document.getElementById('diff-threshold');
  const acceleratingBtn = document.getElementById('var-select-trend-accelerating');
  const btnClear = document.getElementById('var-select-clear');
  const btnPositive = document.getElementById('var-select-positive');
  const btnBuy = document.getElementById('var-select-buy');
  const varMaster = document.getElementById('var-master');
  const selectionCount = document.getElementById('var-selection-count');
  const btnClearAll = document.getElementById('var-clear-selection-allocation-top');
  const topCalcBtn = document.getElementById('filter-calc-alloc-btn');
  const exportSelCsv = document.getElementById('export-selected-csv');
  const exportSelXlsx = document.getElementById('export-selected-xlsx');
  const importForm = document.getElementById('import-selected-form');
  const importFile = document.getElementById('import-selected-file');
  const importModalEl = document.getElementById('importSelectedConfirmModal');
  let _pendingImport = false;

  // Persistência de Aloc.Fato (AJAX)
  (function wireAllocFatoPersistence(){
    try{
      const endpointEl = document.getElementById('alloc-fato-endpoint');
      if(!endpointEl) return;
      const url = endpointEl.getAttribute('data-url');
      if(!url) return;
  const inputs = Array.from(document.querySelectorAll('input.alloc-fato-input[data-code]'));
      if(inputs.length === 0) return;
  const ymEl = document.getElementById('var-filter-ym');
  const yearGlobal = ymEl ? parseInt(ymEl.getAttribute('data-year')||'0',10) : 0;
  const monthGlobal = ymEl ? parseInt(ymEl.getAttribute('data-month')||'0',10) : 0;
      function parseNumberLike(v){
        if(v==null) return null;
        let s = (''+v).trim();
        if(!s) return null;
        // aceitar 1.234,56 e 1,234.56 e 1234.56
        s = s.replace(/[^\d,.-]/g,'');
        if(s.includes(',') && s.includes('.')){
          s = s.replace(/\./g,'');
          s = s.replace(',', '.');
        } else if(s.includes(',') && !s.includes('.')){
          s = s.replace(',', '.');
        }
        const n = Number(s);
        return isFinite(n) ? n : null;
      }
      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      // Não desabilitamos mais globalmente; usaremos year/month por input
      // Hidratar visual com o formato local (2 casas) quando já houver valor numérico no value
      inputs.forEach(inp=>{
        const v = parseNumberLike(inp.value);
        if(v!==null && v>=0){ inp.value = v.toFixed(2); }
      });
      async function saveOne(code, val, y, m){
        try{
          await fetch(url, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': token || ''
            },
            body: JSON.stringify({ code, value: val, year: y, month: m })
          });
        }catch(_e){ /* noop */ }
      }
      function saveOneBeacon(code, val, y, m){
        try{
          if(navigator.sendBeacon){
            const payload = JSON.stringify({ code, value: val, year: y, month: m });
            const blob = new Blob([payload], { type: 'application/json' });
            navigator.sendBeacon(url, blob);
            return true;
          }
        }catch(_e){}
        return false;
      }
      const timers = new Map();
      function scheduleSave(inp){
        // Indicar que estamos salvando
        try{ const b=document.getElementById('alloc-fato-save-badge'); if(b){ b.textContent='salvando…'; b.classList.remove('bg-success'); b.classList.add('bg-warning','text-dark'); } }catch(_e){}
        const code = inp.getAttribute('data-code')||'';
        const y = parseInt(inp.getAttribute('data-year')||String(yearGlobal)||'0',10);
        const m = parseInt(inp.getAttribute('data-month')||String(monthGlobal)||'0',10);
        const raw = inp.value;
        const val = parseNumberLike(raw);
        // Em digitação: não formatar nem limpar; apenas não agenda save se inválido
        if(val === null || !isFinite(val)){
          const prev = timers.get(inp); if(prev) { clearTimeout(prev); timers.delete(inp); }
          return;
        }
        if(val < 0){
          const prev = timers.get(inp); if(prev) { clearTimeout(prev); timers.delete(inp); }
          return;
        }
        // debounce 600ms (salva sem formatar o campo durante digitação)
        const prev = timers.get(inp);
        if(prev) clearTimeout(prev);
        const t = setTimeout(async ()=>{
          await saveOne(code, val, y, m);
          timers.delete(inp);
          // Se não houver mais timers pendentes, marcar como salvo
          if(timers.size === 0){
            try{ const b=document.getElementById('alloc-fato-save-badge'); if(b){ b.textContent='salvo'; b.classList.remove('bg-warning','text-dark'); b.classList.add('bg-success'); } }catch(_e){}
          }
        }, 600);
        timers.set(inp, t);
      }
      inputs.forEach(inp=>{
        inp.addEventListener('input', ()=> { scheduleSave(inp); try{ window.__recalcAllocFatoTotal && window.__recalcAllocFatoTotal(); }catch(_e){} });
        inp.addEventListener('blur', ()=>{ // flush imediato no blur
          const code = inp.getAttribute('data-code')||'';
          const y = parseInt(inp.getAttribute('data-year')||String(yearGlobal)||'0',10);
          const m = parseInt(inp.getAttribute('data-month')||String(monthGlobal)||'0',10);
          const val = parseNumberLike(inp.value);
          const prev = timers.get(inp); if(prev) { clearTimeout(prev); timers.delete(inp); }
          if(val === null){
            alert('Valor inválido. Informe um número (ex: 1234,56).');
            inp.focus();
            return;
          }
          if(val < 0){
            alert('Aloc.Fato não pode ser negativo.');
            inp.focus();
            return;
          }
          inp.value = val.toFixed(2);
          (async ()=>{
            try{
              const b=document.getElementById('alloc-fato-save-badge'); if(b){ b.textContent='salvando…'; b.classList.remove('bg-success'); b.classList.add('bg-warning','text-dark'); }
            }catch(_e){}
            await saveOne(code, val, y, m);
            try{
              const b=document.getElementById('alloc-fato-save-badge'); if(b){ b.textContent='salvo'; b.classList.remove('bg-warning','text-dark'); b.classList.add('bg-success'); }
            }catch(_e){}
          })();
          try{ window.__recalcAllocFatoTotal && window.__recalcAllocFatoTotal(); }catch(_e){}
        });
      });

      // Expor função para exportadores aguardarem os saves pendentes
      window.__flushAllocFatoSaves = async function(){
        const pending = Array.from(timers.values());
        timers.clear();
        if(pending.length){ await new Promise(r=> setTimeout(r, 650)); }
        // Além dos timers, garantir envio do estado atual de cada input com valor válido
        const promises = [];
        inputs.forEach(inp=>{
          const code = inp.getAttribute('data-code')||'';
          const y = parseInt(inp.getAttribute('data-year')||String(yearGlobal)||'0',10);
          const m = parseInt(inp.getAttribute('data-month')||String(monthGlobal)||'0',10);
          const val = parseNumberLike(inp.value);
          if(val!==null && val>=0){ promises.push(saveOne(code, val, y, m)); }
        });
        try{ await Promise.race([Promise.all(promises), new Promise(r=> setTimeout(r, 1000))]); }catch(_e){}
      }

      // Persistir alterações ao sair da página (melhor esforço)
      window.addEventListener('beforeunload', function(){
        inputs.forEach(inp=>{
          const code = inp.getAttribute('data-code')||'';
          const y = parseInt(inp.getAttribute('data-year')||String(yearGlobal)||'0',10);
          const m = parseInt(inp.getAttribute('data-month')||String(monthGlobal)||'0',10);
          const val = parseNumberLike(inp.value);
          if(val!==null && val>=0){ if(!saveOneBeacon(code, val, y, m)){ /* best-effort */ } }
        });
      });

      // Se a alocação está visível, persistir trigger_alloc=1 na URL (para sobreviver refresh)
      try{
        const allocVisible = !!document.getElementById('alloc-count');
        if(allocVisible){
          const url = new URL(window.location.href);
          if(url.searchParams.get('trigger_alloc') !== '1'){
            url.searchParams.set('trigger_alloc','1');
            window.history.replaceState({}, '', url.toString());
          }
          // Injetar nos formulários de filtro ativos
          document.querySelectorAll('form.filters-bar').forEach(f=>{
            if(!f.querySelector('input[name="trigger_alloc"]')){
              const inp = document.createElement('input');
              inp.type='hidden'; inp.name='trigger_alloc'; inp.value='1';
              f.appendChild(inp);
            }
          });
        }
      }catch(_e){}
    }catch(_e){}
  })();

  // Recalcular total de Aloc.Fato no frontend
  (function wireAllocFatoTotalCalc(){
    try{
  const totalEl = document.getElementById('alloc-fato-total-val');
  const totalCell = document.getElementById('alloc-fato-total-cell');
      const capEl = document.getElementById('alloc-capital-display');
      const saveBadge = document.getElementById('alloc-fato-save-badge');
  const excessBadge = document.getElementById('alloc-fato-excess-badge');
      if(!totalEl) return;
      const inputs = () => Array.from(document.querySelectorAll('input.alloc-fato-input[data-code]'));
      function parseNumberLike(v){
        if(v==null) return null; let s=(''+v).trim(); if(!s) return null; s=s.replace(/[^\d,.-]/g,'');
        if(s.includes(',') && s.includes('.')){ s=s.replace(/\./g,''); s=s.replace(',', '.'); }
        else if(s.includes(',') && !s.includes('.')){ s=s.replace(',', '.'); }
        const n=Number(s); return isFinite(n)?n:null;
      }
      function formatLocal(n){
        try{ return (n).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }catch(_e){ return (Math.round(n*100)/100).toFixed(2).replace('.', ','); }
      }
      window.__recalcAllocFatoTotal = function(){
        const sum = inputs().reduce((acc, inp)=>{ const v=parseNumberLike(inp.value); return acc + (v && v>0 ? v : 0); }, 0);
        totalEl.textContent = formatLocal(sum);
        // Destacar em vermelho quando exceder capital
        const cap = capEl ? parseNumberLike(capEl.getAttribute('data-cap')) : null;
        if(totalCell && cap !== null){
          if(sum > cap){
            totalCell.classList.add('text-danger');
            if(excessBadge){
              const sym = totalCell.getAttribute('data-cursymbol') || '';
              const diff = sum - cap;
              excessBadge.textContent = '+ ' + sym + ' ' + formatLocal(diff);
              excessBadge.classList.remove('d-none');
            }
          } else {
            totalCell.classList.remove('text-danger');
            if(excessBadge){ excessBadge.classList.add('d-none'); excessBadge.textContent=''; }
          }
        }
      };
      // Recalcular uma vez ao carregar
      window.__recalcAllocFatoTotal();
    }catch(_e){}
  })();

  // Habilita a importação somente quando um mês estiver selecionado
  (function handleImportEnableByMonth(){
    try{
      const ym = document.getElementById('var-filter-ym');
      const month = ym ? parseInt(ym.getAttribute('data-month')||'0',10) : 0;
      const labelBtn = importFile ? importFile.closest('label') : null;
      if(importFile && labelBtn){
        // Se estiver em modo "forçar desativado" (após import), não reabilitar
        const forceDisabled = importFile.dataset.forceDisabled === '1' || labelBtn.dataset.forceDisabled === '1';
        if(forceDisabled){
          importFile.setAttribute('disabled','disabled');
          labelBtn.classList.add('disabled');
          labelBtn.setAttribute('aria-disabled','true');
          labelBtn.style.pointerEvents = 'none';
          labelBtn.title = 'Importação concluída. Selecione outro mês para habilitar novamente.';
          return;
        }
        if(!month || month<=0){
          importFile.setAttribute('disabled','disabled');
          labelBtn.classList.add('disabled');
          labelBtn.setAttribute('aria-disabled','true');
          // Garantir que não clique: alguns navegadores ignoram .disabled em label
          labelBtn.style.pointerEvents = 'none';
          labelBtn.title = 'Selecione um mês para habilitar a importação';
        } else {
          importFile.removeAttribute('disabled');
          labelBtn.classList.remove('disabled');
          labelBtn.removeAttribute('aria-disabled');
          labelBtn.style.pointerEvents = '';
          labelBtn.title = 'Importar arquivo dos selecionados';
        }
      }
    }catch(_e){}
  })();

  // Intercepta escolha de arquivo para exibir modal de confirmação
  if(importFile && importForm && importModalEl){
    importFile.addEventListener('change', function(){
      if(!this.files || this.files.length === 0) return;
      const modal = new bootstrap.Modal(importModalEl);
      _pendingImport = true;
      modal.show();
    });
    importModalEl.addEventListener('click', function(ev){
      const isConfirm = ev.target && ev.target.classList && ev.target.classList.contains('btn-confirm-import');
      if(isConfirm && _pendingImport){
        _pendingImport = false;
        // Submete o formulário após confirmação
        importForm.submit();
      }
    });
    // Se cancelar, limpa o arquivo selecionado
    importModalEl.addEventListener('hidden.bs.modal', function(){
      if(_pendingImport){
        _pendingImport = false;
        if(importFile){ importFile.value = ''; }
      }
    });
  }

  // Exibir toast de sucesso se existir no DOM
  const toastEl = document.getElementById('importSuccessToast');
  if(toastEl && bootstrap?.Toast){
    const t = new bootstrap.Toast(toastEl, { delay: 5000 });
    t.show();
  }
  const LS_KEY = 'openai_variations_selected_codes';
  function varCheckboxes(){ return Array.from(document.querySelectorAll('.var-select')); }
  function getSelected(){ return varCheckboxes().filter(c=>c.checked).map(c=>c.value.toUpperCase()); }
  function showTransientInfo(msg){
    try{
      const msgEl = document.createElement('div');
      msgEl.className = 'alert alert-info py-1 px-2 position-fixed top-0 end-0 m-3 shadow';
      msgEl.style.zIndex = 1080;
      msgEl.textContent = msg;
      document.body.appendChild(msgEl);
      setTimeout(()=>{ try{ msgEl.remove(); }catch(_e){} }, 3000);
    }catch(_e){}
  }
  // Função global para que outros scripts (primeiro bloco) possam reavaliar habilitação do botão
  window._updateCalcButtonState = function(){
    const n = (document.querySelectorAll('.alloc-row').length > 0)
      ? Array.from(document.querySelectorAll('.alloc-row:checked')).length
      : getSelected().length;
    if(!topCalcBtn) return;
    const anyAllocChecked = Array.from(document.querySelectorAll('.alloc-row:checked')).length > 0;
    if(n === 0 && !anyAllocChecked){
      topCalcBtn.setAttribute('disabled','disabled');
    } else {
      topCalcBtn.removeAttribute('disabled');
    }
  };
  function updateSelectionCount(){
    const hasAlloc = document.querySelectorAll('.alloc-row').length > 0;
    const allocSelectedCount = hasAlloc ? Array.from(document.querySelectorAll('.alloc-row:checked')).length : 0;
    const n = hasAlloc ? allocSelectedCount : getSelected().length;
    if(selectionCount){ selectionCount.textContent = n ? (n+' selecionado(s)') : ''; }
    if(topCalcBtn){
      const base = topCalcBtn.getAttribute('data-base-label') || 'Calcular alocação';
      topCalcBtn.textContent = n ? base + ' ('+n+')' : base;
      // Tooltip dinâmica (title) com até 40 códigos (ou truncado)
      if(n){
        let codes = hasAlloc
          ? Array.from(document.querySelectorAll('.alloc-row:checked')).map(c=> (c.value||'').toUpperCase())
          : getSelected();
        const all = codes.join(', ');
        if(all.length > 300){
          let truncated = '';
          for(const c of codes){
            if((truncated + c).length > 300){ truncated += '…'; break; }
            truncated += (truncated ? ', ' : '') + c;
          }
          topCalcBtn.title = base + ': ' + truncated;
        } else {
          topCalcBtn.title = base + ': ' + all;
        }
      } else {
        topCalcBtn.title = 'Usar capital e parâmetros para gerar alocação considerando (se houver) os ativos selecionados abaixo';
      }
      window._updateCalcButtonState();
    }
    // Exportar Selecionados agora considera apenas os itens alocados
    if(exportSelCsv){ exportSelCsv.disabled = allocSelectedCount===0; }
    if(exportSelXlsx){ exportSelXlsx.disabled = allocSelectedCount===0; }
  }
  const __ym = document.getElementById('var-filter-ym');
  const FILTER_YEAR = __ym ? parseInt(__ym.getAttribute('data-year')||'0',10) : 0;
  const FILTER_MONTH = __ym ? parseInt(__ym.getAttribute('data-month')||'0',10) : 0;
  function setSelected(codes){
    const set = new Set(codes.map(c=>c.toUpperCase()));
    varCheckboxes().forEach(ch=>{
      const match = set.has((ch.value||'').toUpperCase());
      if(!match){ ch.checked = false; return; }
      // Restringir por ano/mês, quando filtros estão ativos
      const tr = ch.closest('tr');
      const ry = tr ? parseInt(tr.getAttribute('data-year')||'0',10) : 0;
      const rm = tr ? parseInt(tr.getAttribute('data-month')||'0',10) : 0;
      const okYear = (FILTER_YEAR && FILTER_YEAR>0) ? (ry === FILTER_YEAR) : true;
      const okMonth = (FILTER_MONTH && FILTER_MONTH>0) ? (rm === FILTER_MONTH) : true;
      ch.checked = match && okYear && okMonth;
    });
    persist();
    updateSelectionCount();
  }
  function persist(){ try{ localStorage.setItem(LS_KEY, JSON.stringify(getSelected())); }catch(_e){} }
  function restore(){
    const qs = new URLSearchParams(location.search);
    const hasMonth = qs.has('month') && (qs.get('month')||'') !== '';
    const isPaginated = !(qs.has('no_page') && (qs.get('no_page')||'') !== '' && qs.get('no_page') !== '0');
    const isNoPage = !isPaginated;
    // Se vieram códigos na URL, aplicamos diretamente (não apenas contamos)
    const urlCodes = qs.getAll('selected_codes[]').map(c=> (c||'').toUpperCase()).filter(c=>c);
    if(urlCodes.length>0){
      setSelected(urlCodes);
      // Persistir no localStorage também para manter ao trocar filtros sem selected_codes
      try{ localStorage.setItem(LS_KEY, JSON.stringify(urlCodes)); }catch(_e){}
      updateSelectionCount();
      return;
    }
    // Se filtro de mês ativo e sem selected_codes/trigger_alloc, não restaurar seleção e limpar armazenamento
    if(hasMonth && !qs.has('trigger_alloc')){
      setSelected([]);
      try{ localStorage.removeItem(LS_KEY); }catch(_e){}
      updateSelectionCount();
      showTransientInfo('Seleção limpa automaticamente (filtro de mês).');
      return;
    }
    // Se estiver paginado e sem selected_codes/trigger_alloc, limpar qualquer seleção
    if(isPaginated && !qs.has('trigger_alloc')){
      setSelected([]);
      try{ localStorage.removeItem(LS_KEY); }catch(_e){}
      updateSelectionCount();
      showTransientInfo('Seleção limpa automaticamente (paginado).');
      return;
    }
    // Se estiver em "Sem paginação" e sem selected_codes/trigger_alloc, também não restaurar seleção
    if(isNoPage && !qs.has('trigger_alloc')){
      setSelected([]);
      try{ localStorage.removeItem(LS_KEY); }catch(_e){}
      updateSelectionCount();
      showTransientInfo('Seleção limpa automaticamente (sem paginação).');
      return;
    }
    // Caso não haja na URL, tentar restaurar do localStorage
    try{
      const raw = localStorage.getItem(LS_KEY);
      if(!raw) { updateSelectionCount(); return; }
      const arr = JSON.parse(raw);
      if(Array.isArray(arr)) setSelected(arr);
    }catch(_e){}
    updateSelectionCount();
  }
  restore();
  updateSelectionCount();
  // Reagir a mudanças nas checkboxes de alocação para habilitar/desabilitar export selecionados
  try {
    document.querySelectorAll('.alloc-row').forEach(ch=>{
      ch.addEventListener('change', ()=>{ if(typeof updateSelectionCount==='function') updateSelectionCount(); });
    });
  } catch(_e) {}
  if(varMaster){
    // Modal de confirmação para marcar / desmarcar todos
    // HTML do modal (injetado uma única vez)
    if(!document.getElementById('varMasterConfirmModal')){
      const modalHtml = `\n<div class="modal fade" id="varMasterConfirmModal" tabindex="-1" aria-hidden="true">\n  <div class="modal-dialog modal-sm modal-dialog-centered">\n    <div class="modal-content">\n      <div class="modal-header py-2">\n        <h6 class="modal-title mb-0">Seleção de Ativos</h6>\n        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>\n      </div>\n      <div class="modal-body small py-3">\n        <p class="mb-2">Aplicar ação em todos os ativos exibidos?</p>\n        <div class="d-grid gap-2">\n          <button type="button" class="btn btn-primary btn-sm" id="btn-var-select-all">Marcar todos</button>\n          <button type="button" class="btn btn-outline-danger btn-sm" id="btn-var-unselect-all">Desmarcar todos</button>\n        </div>\n      </div>\n      <div class="modal-footer py-2">\n        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>\n      </div>\n    </div>\n  </div>\n</div>`;
      const wrap = document.createElement('div');
      wrap.innerHTML = modalHtml;
      document.body.appendChild(wrap.firstElementChild);
    }
    const modalEl = document.getElementById('varMasterConfirmModal');
    let modalInstance = null;
    function ensureModal(){
      if(!modalEl) return null;
      if(typeof bootstrap !== 'undefined' && bootstrap.Modal){
        if(!modalInstance) modalInstance = new bootstrap.Modal(modalEl);
        return modalInstance;
      }
      return null; // fallback usará confirm()
    }
    varMaster.addEventListener('click', (ev)=>{
      ev.preventDefault(); ev.stopPropagation();
      const inst = ensureModal();
      if(inst){ inst.show(); } else {
        // Fallback simples sem Bootstrap
        const action = confirm('Marcar todos? (Cancelar = Desmarcar todos)');
        const all = varCheckboxes();
        all.forEach(ch=> ch.checked = action);
        varMaster.checked = action;
        varMaster.indeterminate = false;
        persist(); updateSelectionCount();
      }
    });
    // Ações dos botões do modal
    document.addEventListener('click', (e)=>{
      if(e.target && e.target.id === 'btn-var-select-all'){
        const all = varCheckboxes(); all.forEach(ch=> ch.checked = true);
        varMaster.checked = true; varMaster.indeterminate = false;
        persist(); updateSelectionCount();
        const inst = ensureModal(); if(inst) inst.hide();
      } else if(e.target && e.target.id === 'btn-var-unselect-all'){
        const all = varCheckboxes(); all.forEach(ch=> ch.checked = false);
        varMaster.checked = false; varMaster.indeterminate = false;
        persist(); updateSelectionCount();
        const inst = ensureModal(); if(inst) inst.hide();
      }
    }, true);
    // Estado inicial (se todos já selecionados ao restaurar)
    setTimeout(()=>{
      const all = varCheckboxes();
      if(all.length){
        const allChecked = all.every(c=>c.checked);
        const someChecked = all.some(c=>c.checked);
        varMaster.checked = allChecked;
        varMaster.indeterminate = !allChecked && someChecked;
      }
    }, 0);
    document.addEventListener('change', e=>{
      const t = e && e.target;
      if(!(t && t.classList && t.classList.contains('var-select'))) return;
      const all = varCheckboxes();
      const allChecked = all.every(c=>c.checked);
      const someChecked = all.some(c=>c.checked);
      varMaster.checked = allChecked;
      varMaster.indeterminate = !allChecked && someChecked;
    });
  }
  // Botão Limpar
  if(btnClear){ btnClear.addEventListener('click', ()=>{ setSelected([]); try{localStorage.removeItem(LS_KEY);}catch(_e){} updateSelectionCount(); }); }
  if(btnClearAll){
    btnClearAll.addEventListener('click', ()=>{
      setSelected([]);
      try{localStorage.removeItem(LS_KEY);}catch(_e){}
      // Remove selected_codes da URL (sem recarregar) e também paginação
      const url = new URL(window.location.href);
      url.searchParams.delete('page');
      // Iterar por todas as chaves selected_codes[] (caso múltiplas instâncias)
      const toDelete = [];
      url.searchParams.forEach((v,k)=>{ if(k==='selected_codes[]') toDelete.push(k); });
      toDelete.forEach(k=>url.searchParams.delete(k));
      window.history.replaceState({}, document.title, url.pathname + (url.searchParams.toString() ? ('?'+url.searchParams.toString()) : ''));
      updateSelectionCount();
    });
  }
  function buildSelectedExportUrl(baseRoute){
    // Coletar SOMENTE os alocados (.alloc-row:checked)
    const sel = Array.from(document.querySelectorAll('.alloc-row:checked'))
      .map(c=> (c.value||'').toUpperCase())
      .filter(Boolean);
    if(!sel.length) return null;
  const params = new URLSearchParams(window.location.search);
    params.delete('page');
    // Remover selected_codes existentes para evitar duplicação
    const existing = [];
    params.forEach((v,k)=>{ if(k==='selected_codes[]') existing.push(k); });
    existing.forEach(k=>params.delete(k));
    // Preservar o nome do arquivo importado, se existir
    if(!params.has('import_file')){
      const qf = new URL(window.location.href).searchParams.get('import_file');
      if(qf){ params.set('import_file', qf); }
    }
    // Garantir que year/month acompanhem a exportação mesmo se não estiverem na URL
    try{
      const ym = document.getElementById('var-filter-ym');
      const y = ym ? parseInt(ym.getAttribute('data-year')||'0',10) : 0;
      const m = ym ? parseInt(ym.getAttribute('data-month')||'0',10) : 0;
      if(y && !params.has('year')) params.set('year', String(y));
      if(m && !params.has('month')) params.set('month', String(m));
    }catch(_e){}
    sel.forEach(code=> params.append('selected_codes[]', code));
    return baseRoute + (params.toString() ? ('?'+params.toString()) : '');
  }
  function openExport(url){ window.location.href = url; }
  if(exportSelCsv){
    exportSelCsv.addEventListener('click', ()=>{
      const url = buildSelectedExportUrl("{{ route('openai.variations.exportCsv') }}");
      if(!url){ alert('Nenhum selecionado.'); return; }
      const flush = window.__flushAllocFatoSaves ? window.__flushAllocFatoSaves() : Promise.resolve();
      flush.finally(()=> openExport(url));
    });
  }
  if(exportSelXlsx){
    exportSelXlsx.addEventListener('click', ()=>{
      const url = buildSelectedExportUrl("{{ route('openai.variations.exportXlsx') }}");
      if(!url){ alert('Nenhum selecionado.'); return; }
      const flush = window.__flushAllocFatoSaves ? window.__flushAllocFatoSaves() : Promise.resolve();
      flush.finally(()=> openExport(url));
    });
  }
  // Botão Positivos (variação atual > 0)
  if(btnPositive){
    btnPositive.addEventListener('click', ()=>{
      const codes=[];
      document.querySelectorAll('tr[data-row-code][data-variation]').forEach(tr=>{
        const v=parseFloat(tr.getAttribute('data-variation'));
        if(!isNaN(v) && v>0) codes.push(tr.getAttribute('data-row-code'));
      });
      if(!codes.length){ alert('Nenhum ativo positivo encontrado.'); return; }
      setSelected(codes);
      // Sincroniza seleção também na tabela de alocação (se já existir)
      (function(){
        // Incluir também quaisquer novos var-select marcados que não estão na alocação ainda
        const extraVar = Array.from(document.querySelectorAll('.var-select:checked'))
          .map(c=>c.value.toUpperCase())
          .filter(code=> sel.map(s=>s.toUpperCase()).indexOf(code) === -1);
        const union = new Set(sel.map(s=>s.toUpperCase()));
        extraVar.forEach(c=> union.add(c));
        Array.from(union).forEach(code=>{
          const inp = document.createElement('input');
          inp.type='hidden'; inp.name='selected_codes[]'; inp.value=code; form.appendChild(inp);
        });
        const trg = document.createElement('input'); trg.type='hidden'; trg.name='trigger_alloc'; trg.value='1'; form.appendChild(trg);
        // Se já existir botão superior e houver capital preenchido, podemos opcionalmente gerar automaticamente
        // (desativado para evitar cálculos inesperados no clique de filtro Positivos)
      })();
    });
  }
  // Botão COMPRAR (badge com dataset.noBuy != 1)
  if(btnBuy){
    btnBuy.addEventListener('click', ()=>{
      const codes=[];
      document.querySelectorAll('.badge[data-flag-code]').forEach(badge=>{
        const noBuy = badge.dataset.noBuy === '1';
        if(!noBuy){ const code = badge.getAttribute('data-flag-code'); if(code) codes.push(code.toUpperCase()); }
      });
      const uniq = Array.from(new Set(codes));
      if(!uniq.length){ alert('Nenhum ativo marcado como COMPRAR.'); return; }
      setSelected(uniq);
    });
  }
  if(diffBtn){
    diffBtn.addEventListener('click', ()=>{
      let thrStr = (diffInput?.value||'').trim().replace('%','').replace(',','.');
      let thr = parseFloat(thrStr); if(isNaN(thr)) thr = 0.0;
      const codes=[];
      document.querySelectorAll('tr[data-row-code][data-diff]').forEach(tr=>{
        const d = parseFloat(tr.getAttribute('data-diff'));
        if(!isNaN(d) && d > thr) codes.push(tr.getAttribute('data-row-code'));
      });
      if(!codes.length){ alert('Nenhum ativo com Diferença > '+thr+' encontrado.'); return; }
      setSelected(codes);
    });
  }
  // Botão Gerar Alocação (Selecionados) - recalcula usando var-select
  // generateBtn removido (unificado no botão superior)
  if(acceleratingBtn){
    acceleratingBtn.addEventListener('click', ()=>{
      // já existe lógica anterior marcando; aqui acionamos auto geração após pequena espera
      setTimeout(()=>{
        const capitalFilled = !!(document.querySelector('input[name="capital"]')?.value.trim());
  if(capitalFilled && getSelected().length){ topCalcBtn?.click(); }
      }, 250);
    });
  }
  // Listener único para var-select: persistência + sincronização com alocação
  document.addEventListener('change', function(e){
    const el = e.target;
    if(!(el && el.classList && el.classList.contains('var-select'))) return;
    const code = (el.value||'').toUpperCase();
    if(!code) return;
    try { persist(); } catch(_e){}
    if(typeof updateSelectionCount === 'function') try{ updateSelectionCount(); }catch(_e){}
    if(window._allocVarSyncing) return;
    const allocBoxes = document.querySelectorAll('.alloc-row');
    if(allocBoxes.length){
      window._allocVarSyncing = true;
      try {
        allocBoxes.forEach(cb=>{ if((cb.value||'').toUpperCase() === code){ cb.checked = el.checked; } });
        if(typeof window._allocSyncMaster === 'function') window._allocSyncMaster();
      } finally { window._allocVarSyncing = false; }
    }
    updateVarSelectionStorage();
    updateVarMasterState();
  });
})();
</script>
@endpush

@push('scripts')
<script>
  (function(){
    const btn = document.getElementById('btn-var-batch-flags');
    if (!btn) return;
    btn.addEventListener('click', function(){
      if (!confirm('Aplicar COMPRAR/NÃO COMPRAR para os códigos exibidos, conforme sinal da variação mais recente?')) return;
      const form = document.createElement('form');
      form.method = 'POST';
  form.action = "{{ route('openai.variations.batchFlags') }}";
      const tok = document.querySelector('meta[name="csrf-token"]');
      if (tok) {
        const inp = document.createElement('input'); inp.type = 'hidden'; inp.name = '_token'; inp.value = tok.getAttribute('content'); form.appendChild(inp);
      }
      // Copia filtros atuais
      try{
        const url = new URL(window.location.href);
        url.searchParams.forEach((v,k)=>{
          if (v !== null && v !== ''){
            const inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = k; inp.value = v;
            form.appendChild(inp);
          }
        });
      }catch(_e){}
      document.body.appendChild(form);
      form.submit();
    });

      // Selecionar tendências de alta ampla
      const btnTrendUp = document.getElementById('var-select-trend-up');
      const btnTrendAccelerating = document.getElementById('var-select-trend-accelerating');
      function getVarRows(){ return Array.from(document.querySelectorAll('table tbody tr[data-row-code]')); }
      function markCodes(codes){
        const set = new Set(codes.map(c=>c.toUpperCase()));
        document.querySelectorAll('.var-select').forEach(ch=>{
          if(set.has(ch.value.toUpperCase())) ch.checked = true; else ch.checked = false;
        });
        updateSelectionCount();
      }
      function updateSelectionCount(){
        const lbl = document.getElementById('var-selection-count');
        if(!lbl) return; const totalChecked = Array.from(document.querySelectorAll('.var-select:checked')).length;
        lbl.textContent = totalChecked > 0 ? totalChecked + ' selecionado(s)' : '';
      }
  // Listener duplicado removido (já há um handler central para var-select; evitar TypeError em targets sem classList)
      updateSelectionCount();
      if(btnTrendUp){
        btnTrendUp.addEventListener('click', ()=>{
          const rows = getVarRows();
          const desired = ['alta_acelerando','reversao_alta','alta_estavel','queda_aliviando'];
          const codes = [];
          rows.forEach(r=>{
            const badge = r.querySelector('[data-trend-code]');
              const code = r.getAttribute('data-row-code');
              const t = badge ? badge.getAttribute('data-trend-code') : null;
              if(code && t && desired.includes(t)) codes.push(code);
          });
          if(codes.length===0){ alert('Nenhum ativo com tendências de alta encontrada.'); return; }
          markCodes(codes);
        });
      }
      if(btnTrendAccelerating){
        btnTrendAccelerating.addEventListener('click', ()=>{
          const rows = getVarRows();
          const codes = [];
          rows.forEach(r=>{
            const badge = r.querySelector('[data-trend-code]');
            const code = r.getAttribute('data-row-code');
            const t = badge ? badge.getAttribute('data-trend-code') : null;
            if(code && t === 'alta_acelerando') codes.push(code);
          });
          if(codes.length===0){ alert('Nenhum ativo com tendência Alta Acelerando encontrado.'); return; }
          markCodes(codes);
          // Opcional: rolar até botão de geração para facilitar UX
          const topBtn = document.getElementById('filter-calc-alloc-btn');
          if(topBtn) topBtn.scrollIntoView({behavior:'smooth', block:'center'});
        });
      }
  })();
</script>
@endpush

@push('scripts')
<script>
  (function(){
    // Hidrata badges de flag por código
  const NO_BUY_GET = "{{ route('openai.assets.noBuy.get') }}";
    (async function(){
      try{
        const els = Array.from(document.querySelectorAll('[data-flag-code]'));
        const codes = Array.from(new Set(els.map(e => e.getAttribute('data-flag-code')).filter(Boolean)));
        for (const code of codes){
          try{
            const resp = await fetch(`${NO_BUY_GET}?code=${encodeURIComponent(code)}`, { headers: { 'Accept':'application/json' } });
            const data = await resp.json().catch(()=>null);
            const noBuy = !!(data && data.no_buy);
            els.filter(e => e.getAttribute('data-flag-code')===code).forEach(e => {
              if (e.classList.contains('badge')){
                e.className = 'badge ' + (noBuy ? 'bg-danger' : 'bg-success');
                e.textContent = noBuy ? 'NÃO COMPRAR' : 'COMPRAR';
                e.dataset.noBuy = noBuy ? '1' : '0';
              }
            });
          }catch(_e){/* noop */}
        }
      }catch(_e){/* noop */}
    })();

    // Alternar flag via POST
    document.addEventListener('click', async function(ev){
      const btn = ev.target.closest('[data-flag-toggle]');
      if (!btn) return;
      const code = btn.getAttribute('data-flag-code') || '';
      if (!code) return;
      const badge = btn.parentElement?.querySelector('.badge[data-flag-code="' + code + '"]');
      const current = badge ? (badge.dataset.noBuy === '1') : false;
      const next = !current; // true => NÃO COMPRAR; false => COMPRAR
      const prevHtml = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
      try{
  const url = "{{ route('openai.assets.noBuy.toggle') }}";
        const tok = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const resp = await fetch(url, {
          method: 'POST',
          headers: { 'Accept':'application/json', 'Content-Type':'application/json', 'X-CSRF-TOKEN': tok },
          body: JSON.stringify({ code, no_buy: next })
        });
        const data = await resp.json().catch(()=>null);
        if (!resp.ok || !data || data.ok !== true) {
          throw new Error((data && (data.message||data.error)) || 'Falha ao salvar flag');
        }
        if (badge){
          badge.className = 'badge ' + (next ? 'bg-danger' : 'bg-success');
          badge.textContent = next ? 'NÃO COMPRAR' : 'COMPRAR';
          badge.dataset.noBuy = next ? '1' : '0';
        }
      }catch(err){
        alert('Erro ao salvar flag: ' + String(err && err.message ? err.message : err));
      }finally{
        btn.disabled = false;
        btn.innerHTML = prevHtml;
      }
    });
  })();
</script>
@endpush
