@extends('layouts.bootstrap5')
@section('content')
<div class="container-fluid">
  <h1 class="h4 mb-3">Variações Mensais Salvas</h1>
  <form method="get" class="row g-2 mb-3">
    <div class="col-auto">
      <label class="form-label mb-0 small">Ano</label>
      <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">Todos</option>
        @foreach($years as $y)
          <option value="{{ $y }}" @selected((string)$y === (string)$year)>{{ $y }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label mb-0 small">Mês</label>
      <select name="month" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">Todos</option>
        @for($m=1;$m<=12;$m++)
          <option value="{{ $m }}" @selected((int)($month ?? 0) === $m)>{{ str_pad($m,2,'0',STR_PAD_LEFT) }}</option>
        @endfor
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label mb-0 small">Código</label>
      <input type="text" name="code" value="{{ $code }}" class="form-control form-control-sm" placeholder="TSLA" />
    </div>
    <div class="col-auto">
      <label class="form-label mb-0 small">Sinal</label>
      <select name="polarity" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="" @selected(($polarity ?? '')==='')>Todos</option>
        <option value="positive" @selected(($polarity ?? '')==='positive')>Positivos</option>
        <option value="negative" @selected(($polarity ?? '')==='negative')>Negativos</option>
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label mb-0 small">Mudança</label>
      <select name="change" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="" @selected(($change ?? '')==='')>Todas</option>
        <option value="melhoria" @selected(($change ?? '')==='melhoria')>Melhoria</option>
        <option value="piora" @selected(($change ?? '')==='piora')>Piora</option>
        <option value="igual" @selected(($change ?? '')==='igual')>Igual</option>
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label mb-0 small">Tendência</label>
      <select name="trend" class="form-select form-select-sm" onchange="this.form.submit()" title="Filtrar por tendência calculada">
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
        <select name="spark_window" class="form-select form-select-sm" onchange="this.form.submit()">
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
        ]);
      @endphp
      <div class="btn-group btn-group-sm" role="group" aria-label="Atalhos de sinal">
        <a href="{{ route('openai.variations.index', $quickBase) }}"
           class="btn btn-outline-secondary {{ (($polarity ?? '')==='' ) ? 'active' : '' }}"
           title="Mostrar todos (positivos e negativos)">Todos</a>
        <a href="{{ route('openai.variations.index', array_merge($quickBase, ['polarity'=>'positive'])) }}"
           class="btn btn-outline-success {{ (($polarity ?? '')==='positive') ? 'active' : '' }}"
           title="Mostrar apenas variações positivas">Somente positivos</a>
        <a href="{{ route('openai.variations.index', array_merge($quickBase, ['polarity'=>'negative'])) }}"
           class="btn btn-outline-danger {{ (($polarity ?? '')==='negative') ? 'active' : '' }}"
           title="Mostrar apenas variações negativas">Somente negativos</a>
      </div>
    </div>
    <div class="col-12"></div>
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
        ]);
        $curMonth = (int) (request('month') ?: 0);
      @endphp
      <div class="btn-group btn-group-sm flex-wrap" role="group" aria-label="Atalhos de mês">
        <a href="{{ route('openai.variations.index', $monthQuickBase) }}" class="btn btn-outline-secondary {{ $curMonth===0 ? 'active' : '' }}" title="Limpar filtro de mês">Limpar mês</a>
        @for($m=1;$m<=12;$m++)
          <a href="{{ route('openai.variations.index', array_merge($monthQuickBase, ['month'=>$m])) }}"
             class="btn btn-outline-primary {{ $curMonth===$m ? 'active' : '' }}"
             title="Filtrar por mês {{ str_pad($m,2,'0',STR_PAD_LEFT) }}">{{ str_pad($m,2,'0',STR_PAD_LEFT) }}</a>
        @endfor
      </div>
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
    ]);
  @endphp
  <div class="mb-2 d-flex gap-2">
    <a href="{{ route('openai.variations.exportCsv', $exportParams) }}" class="btn btn-sm btn-outline-secondary" title="Exportar visão atual em CSV">Exportar CSV</a>
    <a href="{{ route('openai.variations.exportXlsx', $exportParams) }}" class="btn btn-sm btn-outline-success" title="Exportar visão atual em XLSX">Exportar XLSX</a>
    <div class="vr mx-2 d-none d-md-block"></div>
    <button type="button" id="btn-var-batch-flags" class="btn btn-sm btn-outline-warning" title="Aplicar COMPRAR/NÃO COMPRAR por código conforme sinal da variação (usa a linha mais recente por código)">Aplicar flags (variação)</button>
    <a href="{{ route('asset-stats.index') }}#gsc.tab=0" class="btn btn-sm btn-outline-dark" title="Ir para Asset Stats">Asset Stats</a>
  </div>

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
  <div class="table-responsive">
    <table class="table table-sm table-striped align-middle">
      <thead>
        <tr>
          <th>ID</th>
          @php
            // Parâmetros base preservados para os links de ordenação
            $baseParams = array_filter([
              'year'=>request('year')?:null,
              'month'=>request('month')?:null,
              'code'=>$code?:null,
              'polarity'=> ($polarity ?? null) ?: null,
              'change' => ($change ?? '') ?: null,
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
          <th>Tendência</th>
          <th title="Flag por usuário: COMPRAR ou NÃO COMPRAR">Flag</th>
        </tr>
      </thead>
      <tbody>
        @forelse($variations as $v)
          <tr>
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
              @php $pv = $prevVariationMap[$v->id] ?? null; @endphp
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
              $diff = (!is_null($pv)) ? ($v->variation - $pv) : null;
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
                <span class="badge bg-{{ $tBadge }}" title="Tendência: {{ $trend['label'] }} | Normalizado: {{ number_format($trend['normalized'],4,',','.') }}% | Confiança: {{ number_format($trend['confidence']*100,1,',','.') }}% @if($trend['days_elapsed'] && $trend['days_month']) | Dias: {{ $trend['days_elapsed'] }}/{{ $trend['days_month'] }} @endif">{{ $trend['label'] }}</span>
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
            @php $trend = $trendData[$v->id] ?? null; @endphp
            <td class="small">
              @if($trend)
                <span class="badge bg-{{ $trend['badge'] }}" title="Tendência: {{ $trend['label'] }} | Normalizado: {{ number_format($trend['normalized'],4,',','.') }}% | Confiança: {{ number_format($trend['confidence']*100,1,',','.') }}% @if($trend['days_elapsed'] && $trend['days_month']) | Dias: {{ $trend['days_elapsed'] }}/{{ $trend['days_month'] }} @endif">{{ $trend['label'] }}</span>
              @else
                <span class="text-muted">—</span>
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
    <div>
      {{ $variations->links() }}
    </div>
  @endif
</div>
@endsection

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
