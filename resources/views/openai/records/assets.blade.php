@extends('layouts.bootstrap5')
@section('content')
{{-- <div class="container py-4"> --}}
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h5 mb-0 d-flex align-items-center gap-2">
      Ativos (sem repetição)
      <span id="market-status-badge" class="badge bg-secondary" title="Status do mercado (NYSE)">Mercado: carregando…</span>
    </h1>
    <div class="d-flex gap-2">
          <a href="{{ route('openai.records.index') }}" class="btn btn-outline-secondary">← Registros</a>
          <button type="button" id="toggle-stats-total" class="btn btn-outline-secondary" title="Mostrar/ocultar estatísticas gerais (sem limite baseline)">Stats Totais: <span data-state>OFF</span></button>
  <button type="button" id="toggle-local-badge" class="btn btn-sm btn-outline-secondary" title="Mostrar/ocultar badge local do mercado">
        Badge Mercado: <span data-state>ON</span>
      </button>
  <button type="button" id="toggle-stats-base" class="btn btn-outline-secondary" title="Mostrar/ocultar estatísticas base (≤Base)">Stats Base: <span data-state>OFF</span></button>
          <button type="button" id="btn-toggle-openai-assets-layout" class="btn btn-outline-dark btn-sm" title="Alterna exibição compacta (oculta filtros e cabeçalho)">Modo Compacto</button>
    </div>
  </div>
  <div class="card shadow-sm mb-3">
    <div class="card-body">
  <form id="assets-filter-form" method="GET" action="{{ route('openai.records.assets') }}" class="row g-2 align-items-end">
        <div class="col-sm-5 col-md-4">
          <label class="form-label small mb-1" title="Filtra pelos códigos das conversas do tipo 'Bolsa de Valores Americana'">Ativo (código)</label>
          <select name="asset" class="form-select form-select-sm">
            <option value="">Todos</option>
            @foreach(($assetOptions ?? collect()) as $opt)
              <option value="{{ $opt['label'] }}" {{ request('asset')===$opt['label'] ? 'selected' : '' }}>{{ $opt['text'] }}</option>
            @endforeach
          </select>
          <small class="text-muted d-block mt-1">Códigos extraídos das conversas do tipo “Bolsa de Valores Americana”.</small>
        </div>
        <div class="col-sm-3 col-md-2">
          <label class="form-label small mb-1">De</label>
          <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
        </div>
        <div class="col-sm-3 col-md-2">
          <label class="form-label small mb-1">Até</label>
          <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
        </div>
        <div class="col-sm-3 col-md-2">
          <label class="form-label small mb-1">Status de compra</label>
          <select name="buy" class="form-select form-select-sm">
            <option value="" {{ (string)request('buy')==='' ? 'selected' : '' }}>Todos</option>
            <option value="compra" {{ request('buy')==='compra' ? 'selected' : '' }}>COMPRAR</option>
            <option value="nao" {{ request('buy')==='nao' ? 'selected' : '' }}>NÃO COMPRAR</option>
          </select>
        </div>
        <div class="col-sm-3 col-md-2">
          <label class="form-label small mb-1" title="Inclui apenas ativos cujo último registro não é posterior a esta data">Sem registros após</label>
          <input type="date" name="no_after" value="{{ request('no_after') }}" class="form-control form-control-sm">
          <small class="text-muted d-block mt-1">Mostra somente grupos cujo último registro <= data.</small>
        </div>
        <div class="col-sm-3 col-md-2">
          <label class="form-label small mb-1">Data base (comparação)</label>
          <input type="date" id="assets-baseline" name="baseline" value="{{ request('baseline') }}" class="form-control form-control-sm">
        </div>
        <div class="col-sm-3 col-md-2">
          <label class="form-label small mb-1">Excluir data (linha)</label>
          <input type="date" id="assets-exclude-date" name="exclude_date" value="{{ request('exclude_date') }}" class="form-control form-control-sm" placeholder="YYYY-MM-DD" {{ request('baseline') ? '' : 'disabled' }}>
          <small class="text-muted d-block mt-1">Usa a data mostrada nas colunas Var/Dif (registro base). Requer informar a “Data base”.</small>
        </div>
        <div class="col-sm-4 col-md-4">
          <label class="form-label small mb-1">Conta de investimento</label>
          <select name="investment_account_id" class="form-select form-select-sm">
            <option value="">Todas</option>
            <option value="0" {{ (string)request('investment_account_id')==='0' ? 'selected' : '' }}>Sem conta</option>
            @foreach(($investmentAccounts ?? []) as $acc)
              <option value="{{ $acc->id }}" {{ (string)request('investment_account_id')===(string)$acc->id ? 'selected' : '' }}>
                {{ $acc->account_name }} @if($acc->broker) ({{ $acc->broker }}) @endif
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-sm-3 col-md-2">
          <label class="form-label small mb-1">Intervalo CHECK (ms)</label>
          <input type="number" min="0" step="50" name="auto_prev_interval" value="{{ request('auto_prev_interval') }}" class="form-control form-control-sm" placeholder="ex: 400">
          <small class="text-muted d-block mt-1">Intervalo do polling do CHECK. Padrão 400 ms.</small>
        </div>
        <div class="col-sm-3 col-md-2">
          <label class="form-label small mb-1">Atraso recarregar (ms)</label>
          <input type="number" min="0" step="50" name="auto_prev_reload_delay" value="{{ request('auto_prev_reload_delay') }}" class="form-control form-control-sm" placeholder="ex: 250">
          <small class="text-muted d-block mt-1">Espera antes de aplicar o filtro após inserir. Padrão 250 ms.</small>
        </div>
        <div class="col-sm-3 col-md-2">
          <label class="form-label small mb-1" title="Dias após a baseline para comparar tendência (0 desativa)">Trend (dias)</label>
          <input type="number" min="0" step="1" name="trend_days" value="{{ request('trend_days', $trendDays ?? 0) }}" class="form-control form-control-sm" placeholder="ex: 5">
        </div>
        <div class="col-sm-3 col-md-2">
          <label class="form-label small mb-1" title="Variação mínima (em %) para considerar sobe/desce (padrão 0.1%)">Epsilon (%)</label>
          <input type="number" min="0" step="0.01" name="trend_epsilon" value="{{ request('trend_epsilon', $trendEpsPct ?? 0.1) }}" class="form-control form-control-sm" placeholder="ex: 0.2">
        </div>
        <div class="col-sm-3 col-md-2 d-grid">
          <button class="btn btn-sm btn-outline-primary">Filtrar</button>
        </div>
      </form>
    </div>
  </div>

  <div class="table-responsive">
    @if(isset($totalSelected))
      <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
        <span class="badge bg-info text-dark">Total de registros selecionados: {{ number_format((int)$totalSelected, 0, ',', '.') }}</span>
        <div class="d-flex align-items-center gap-2">
          @php $exportParams = request()->all(); @endphp
          <a href="{{ route('openai.records.assets.exportCsv', $exportParams) }}" class="btn btn-sm btn-outline-success" title="Exportar visão atual em CSV">Exportar CSV</a>
          <a href="{{ route('openai.records.assets.exportSummaryCsv', $exportParams) }}" class="btn btn-sm btn-outline-success" title="Exportar resumo de tendências em CSV">CSV Resumo</a>
          <a href="{{ route('openai.records.assets.exportXlsx', $exportParams) }}" class="btn btn-sm btn-outline-success" title="Exportar XLSX com abas (Ativos e Resumo)">XLSX</a>
          <a href="{{ route('openai.records.assets.exportCsv', array_merge($exportParams, ['locale'=>'br'])) }}" class="btn btn-sm btn-outline-success" title="Exportar CSV com formatação brasileira (vírgula decimal)">CSV (pt-BR)</a>
          <div class="vr mx-2 d-none d-md-block"></div>
          <button type="button" id="btn-batch-flags" class="btn btn-sm btn-outline-warning" title="Define COMPRAR/NÃO COMPRAR conforme Dif (requer Data base)">Aplicar flags (Dif)</button>
          <button type="button" id="btn-batch-quotes" class="btn btn-sm btn-outline-primary">
            Consultar todos
          </button>
          <button type="button" id="btn-batch-stop" class="btn btn-sm btn-outline-danger d-none">
            Parar
          </button>
          <small id="batch-status" class="text-muted"></small>
          <div class="vr mx-2 d-none d-md-block"></div>
          <button type="button" id="btn-auto-prev-start" class="btn btn-sm btn-outline-success">
            CHECK
          </button>
          <button type="button" id="btn-auto-prev-stop" class="btn btn-sm btn-outline-danger d-none">
            Parar (CHECK)
          </button>
          <small id="auto-prev-status" class="text-muted"></small>
          <div class="vr mx-2 d-none d-md-block"></div>
          <button type="button" id="btn-usage" class="btn btn-sm btn-outline-secondary">Ver limites</button>
          <small id="usage-status" class="text-muted"></small>
        </div>
      </div>
      @if(request('baseline'))
        <div class="mb-2 small text-muted">
          • Linhas em destaque indicam que não há registro para a Data base (marcadas como “Sem base”).<br>
          • Selo “Base ok” indica que a data base foi encontrada para o ativo.
          <br>• Estatísticas com “≤Base” consideram apenas registros até o final do dia da baseline.
          <br>• Use o botão “Stats Totais” para alternar a exibição das estatísticas gerais (todo intervalo filtrado).
          <br>• Para exportar com vírgula decimal e datas dd/mm/aaaa use o botão “CSV (pt-BR)”.
        </div>
      @endif
      @if(($trendsSummary['total'] ?? 0) > 0)
        <div class="mb-2 small">
          <span class="badge bg-success">Sobe: {{ $trendsSummary['up'] }}</span>
          <span class="badge bg-danger ms-1">Desce: {{ $trendsSummary['down'] }}</span>
          <span class="badge bg-secondary ms-1">Mantém: {{ $trendsSummary['flat'] }}</span>
          <span class="text-muted ms-2">em {{ $trendsSummary['total'] }} ativo(s) (Δ em {{ $trendDays }} dia(s))</span>
        </div>
      @endif
      <div class="mb-2 small text-muted">
        • Na coluna “Cotação”, os itens aparecem empilhados para não alargar a tabela. O botão “Aplicar” é mostrado quando a data da cotação coincide com a data do registro; o destaque em vermelho aparece apenas quando o valor difere do atual.
      </div>
    @endif

    <table class="table table-sm table-bordered align-middle">
      <thead class="table-dark">
        <tr>
          @php
            $q = request()->except(['sort','dir']);
            $toggle = fn($col) => ($sort ?? 'code')===$col && ($dir ?? 'asc')==='asc' ? 'desc' : 'asc';
            $icon = fn($col) => ($sort ?? 'code')===$col ? (($dir ?? 'asc')==='asc' ? '▲' : '▼') : '';
          @endphp
          <th style="width:18%">
            <a class="text-white text-decoration-none" href="{{ route('openai.records.assets', array_merge($q, ['sort'=>'code','dir'=>$toggle('code')])) }}">Código {{ $icon('code') }}</a>
          </th>
          <th style="width:30%">
            <a class="text-white text-decoration-none" href="{{ route('openai.records.assets', array_merge($q, ['sort'=>'title','dir'=>$toggle('title')])) }}">Conversa {{ $icon('title') }}</a>
          </th>
          <th style="width:16%">
            <a class="text-white text-decoration-none" href="{{ route('openai.records.assets', array_merge($q, ['sort'=>'date','dir'=>$toggle('date')])) }}">Data/Hora {{ $icon('date') }}</a>
          </th>
          <th style="width:14%" class="text-end">
            <a class="text-white text-decoration-none" href="{{ route('openai.records.assets', array_merge($q, ['sort'=>'amount','dir'=>$toggle('amount')])) }}">Valor {{ $icon('amount') }}</a>
          </th>
          <th style="width:14%">
            <a class="text-white text-decoration-none" href="{{ route('openai.records.assets', array_merge($q, ['sort'=>'account','dir'=>$toggle('account')])) }}">Conta {{ $icon('account') }}</a>
          </th>
          <th style="width:8%" class="text-center">
            <a class="text-white text-decoration-none" href="{{ route('openai.records.assets', array_merge($q, ['sort'=>'qty','dir'=>$toggle('qty')])) }}">Qtd {{ $icon('qty') }}</a>
          </th>
          <th style="width:12%" class="text-end">
            <a class="text-white text-decoration-none" href="{{ route('openai.records.assets', array_merge($q, ['sort'=>'var','dir'=>$toggle('var')])) }}">Var (%) {{ $icon('var') }}</a>
          </th>
          <th style="width:12%" class="text-end">
            <a class="text-white text-decoration-none" href="{{ route('openai.records.assets', array_merge($q, ['sort'=>'diff','dir'=>$toggle('diff')])) }}">Dif {{ $icon('diff') }}</a>
          </th>
          <th style="width:9%" class="text-end stats-base d-none" title="Média até a baseline (inclui registros <= baseline; se sem baseline, usa média geral)">
            <a class="text-white text-decoration-none" href="{{ route('openai.records.assets', array_merge($q, ['sort'=>'avg','dir'=>$toggle('avg')])) }}">Média ≤Base {{ $icon('avg') }}</a>
          </th>
          <th style="width:9%" class="text-end stats-base d-none" title="Mediana dos valores até a baseline (ou geral)">
            <a class="text-white text-decoration-none" href="{{ route('openai.records.assets', array_merge($q, ['sort'=>'median','dir'=>$toggle('median')])) }}">Mediana {{ $icon('median') }}</a>
          </th>
          <th style="width:7%" class="text-end stats-base d-none" title="Máximo dos valores até a baseline (ou geral)">
            <a class="text-white text-decoration-none" href="{{ route('openai.records.assets', array_merge($q, ['sort'=>'max','dir'=>$toggle('max')])) }}">Máx {{ $icon('max') }}</a>
          </th>
          <th style="width:7%" class="text-end stats-base d-none" title="Mínimo dos valores até a baseline (ou geral)">
            <a class="text-white text-decoration-none" href="{{ route('openai.records.assets', array_merge($q, ['sort'=>'min','dir'=>$toggle('min')])) }}">Mín {{ $icon('min') }}</a>
          </th>
          <th style="width:6%" class="text-end stats-base d-none" title="Quantidade de registros até a baseline">
            <a class="text-white text-decoration-none" href="{{ route('openai.records.assets', array_merge($q, ['sort'=>'count_base','dir'=>$toggle('count_base')])) }}">N≤Base {{ $icon('count_base') }}</a>
          </th>
          <th style="width:9%" class="text-end stats-total d-none" title="Média geral no intervalo filtrado">
            <a class="text-white text-decoration-none" href="{{ route('openai.records.assets', array_merge($q, ['sort'=>'avg_total','dir'=>$toggle('avg_total')])) }}">Média Tot {{ $icon('avg_total') }}</a>
          </th>
          <th style="width:9%" class="text-end stats-total d-none" title="Mediana geral no intervalo filtrado">
            <a class="text-white text-decoration-none" href="{{ route('openai.records.assets', array_merge($q, ['sort'=>'median_total','dir'=>$toggle('median_total')])) }}">Mediana Tot {{ $icon('median_total') }}</a>
          </th>
          <th style="width:7%" class="text-end stats-total d-none" title="Máximo geral no intervalo filtrado">
            <a class="text-white text-decoration-none" href="{{ route('openai.records.assets', array_merge($q, ['sort'=>'max_total','dir'=>$toggle('max_total')])) }}">Máx Tot {{ $icon('max_total') }}</a>
          </th>
          <th style="width:7%" class="text-end stats-total d-none" title="Mínimo geral no intervalo filtrado">
            <a class="text-white text-decoration-none" href="{{ route('openai.records.assets', array_merge($q, ['sort'=>'min_total','dir'=>$toggle('min_total')])) }}">Mín Tot {{ $icon('min_total') }}</a>
          </th>
          <th style="width:6%" class="text-end stats-total d-none" title="Quantidade total de registros no intervalo">
            <a class="text-white text-decoration-none" href="{{ route('openai.records.assets', array_merge($q, ['sort'=>'count_total','dir'=>$toggle('count_total')])) }}">N Tot {{ $icon('count_total') }}</a>
          </th>
          <th style="width:10%" class="text-center" title="Itens empilhados: consultar, valor, horário e ações. O botão ‘Aplicar’ aparece quando a data da cotação coincide com a do registro.">Cotação</th>
          <th style="width:8%" class="text-center" title="Marca por usuário se o ativo está como NÃO COMPRAR">Flag</th>
          <th style="width:10%" class="text-end" title="Tendência do valor após a baseline + N dias">Tendência</th>
        </tr>
      </thead>
      <tbody>
        @forelse($records as $r)
          @php
            $code = trim($r->chat?->code ?? '') ?: trim($r->chat?->title ?? '');
            $b = isset($baselines) ? ($baselines[$code] ?? null) : null;
            $rowClass = request('baseline') && !$b ? 'table-warning' : '';
            // baselineRef: data do primeiro registro >= baseline, senão a própria baseline solicitada
            $baselineRef = null;
            if (request('baseline')) {
              if ($b && isset($b['occurred_at'])) { $baselineRef = $b['occurred_at']; }
              else {
                try { $baselineRef = \Carbon\Carbon::parse(request('baseline')); } catch (\Throwable $e) { $baselineRef = null; }
              }
            }
            // calcular dia útil anterior (NYSE) baseado na baselineRef
            $prevBizDateRow = null; $prevReasonRow = '';
            if ($baselineRef) {
              try {
                $svc = app(\App\Services\HolidayService::class);
                $info = $svc->previousBusinessDayInfo($baselineRef);
                $prevBizDateRow = $info['date'] ?? null;
                $prevReasonRow = (string)($info['reason'] ?? '');
              } catch (\Throwable $e) { $prevBizDateRow = null; $prevReasonRow=''; }
            }
          @endphp
          <tr class="{{ $rowClass }}">
            <td>
              <strong>{{ $r->chat?->code ?? '—' }}</strong>
              @if(request('baseline'))
                @if(!$b)
                  <span class="badge bg-warning text-dark ms-2" title="Não há registro na data base ou posterior">Sem base</span>
                @else
                  @php $baseTip = $baselineRef ? ('Registro base: ' . $baselineRef->format('d/m/Y H:i:s')) : 'Há registro na data base (ou posterior)'; @endphp
                  <span class="badge bg-success ms-2" title="{{ $baseTip }}">Base ok</span>
                @endif
                @if($prevBizDateRow)
                  <span class="badge bg-secondary ms-2 baseline-prev-badge" title="Data útil anterior ao registro base">Base anterior: {{ $prevBizDateRow->format('d/m/Y') }}</span>
                  <span class="badge bg-dark ms-1" title="Calendário: NYSE {{ $prevReasonRow ? '(' . $prevReasonRow . ')' : '' }}">NYSE</span>
                @else
                  <span class="badge bg-secondary ms-2 d-none baseline-prev-badge" title="Data útil anterior à base"></span>
                @endif
              @endif
            </td>
            <td>
              <a href="{{ route('openai.records.index', ['chat_id' => $r->chat_id]) }}" class="text-decoration-none">
                {{ $r->chat?->title ?? '—' }}
              </a>
            </td>
            <td>
              @php
                $dt = $r->occurred_at ? $r->occurred_at->clone()->locale('pt_BR') : null;
              @endphp
              @if($dt)
                {{ $dt->format('d/m/Y H:i:s') }} — {{ $dt->translatedFormat('l') }}
              @else
                —
              @endif
            </td>
            <td class="text-end">{{ number_format((float)$r->amount, 2, ',', '.') }}</td>
            <td>{{ $r->investmentAccount?->account_name ?? '—' }} @if($r->investmentAccount?->broker) <small class="text-muted">({{ $r->investmentAccount?->broker }})</small> @endif</td>
            <td class="text-center">{{ $counts[ $code ] ?? 1 }}</td>
            @php
              $b = isset($baselines) ? ($baselines[$code] ?? null) : null;
              $pct = null; $dif = null; $cls = '';
              if ($b && isset($b['amount'])) {
                $base = (float) $b['amount'];
                $cur = (float) ($r->amount ?? 0);
                $dif = $cur - $base;
                if (abs($base) > 0.0000001) {
                  $pct = ($dif / $base) * 100.0;
                }
                if ($dif > 0) { $cls = 'text-success'; }
                elseif ($dif < 0) { $cls = 'text-danger'; }
                else { $cls = 'text-muted'; }
              }
            @endphp
            <td class="text-end {{ $cls }}">
              @if($pct === null)
                —
              @else
                {{ number_format((float)$pct, 2, ',', '.') }} %
                @if($b && isset($b['occurred_at']))
                  <small class="text-muted">({{ optional($b['occurred_at'])->format('d/m/Y') }})</small>
                @endif
              @endif
            </td>
            <td class="text-end {{ $cls }}">
              @if($dif === null)
                —
              @else
                {{ number_format((float)$dif, 2, ',', '.') }}
                @if($b && isset($b['occurred_at']))
                  <small class="text-muted">({{ optional($b['occurred_at'])->format('d/m/Y') }})</small>
                @endif
              @endif
              @if(request('baseline'))
                @php $symbol = strtoupper(trim($r->chat?->code ?? '')); @endphp
                @if($symbol)
                  <div class="mt-1 small">
                    @php
                      $baseAnteriorLabel = null; $btnDate = null;
                      if ($prevBizDateRow) {
                        try { $baseAnteriorLabel = $prevBizDateRow->format('d/m/Y'); $btnDate = $prevBizDateRow->format('Y-m-d'); } catch (\Throwable $e) { $baseAnteriorLabel = null; $btnDate = null; }
                      }
                    @endphp
                    <button type="button" class="btn btn-xs btn-outline-secondary btn-baseline-quote" data-role="baseline-quote" data-symbol="{{ $symbol }}" @if($btnDate) data-date="{{ $btnDate }}" @endif title="{{ ($baselineRef ? ('Registro base: '.$baselineRef->format('d/m/Y H:i:s')) : 'Registro base: —') . ($prevBizDateRow ? (' • Dia útil anterior (NYSE): '.$prevBizDateRow->format('d/m/Y')) : '') }}">
                      Buscar cotação{{ $baseAnteriorLabel ? ' (base anterior: ' . $baseAnteriorLabel . ')' : '' }}
                    </button>
                    <span class="baseline-quote-result ms-2"></span>
                  </div>
                @endif
              @endif
            </td>
            @php
              $codeKey = trim($r->chat?->code ?? '') ?: trim($r->chat?->title ?? '');
              $stats = ($baselineStats ?? collect())->get($codeKey) ?? [];
              $avgVal = $stats['avg'] ?? (($averages ?? collect())->get($codeKey) ?? null);
              $medianVal = $stats['median'] ?? null;
              $maxVal = $stats['max'] ?? null;
              $minVal = $stats['min'] ?? null;
            @endphp
            <td class="text-end stats-base d-none">@if($avgVal!==null) {{ number_format($avgVal, 2, ',', '.') }} @else — @endif</td>
            <td class="text-end stats-base d-none">@if($medianVal!==null) {{ number_format($medianVal, 2, ',', '.') }} @else — @endif</td>
            <td class="text-end stats-base d-none">@if($maxVal!==null) {{ number_format($maxVal, 2, ',', '.') }} @else — @endif</td>
            <td class="text-end stats-base d-none">@if($minVal!==null) {{ number_format($minVal, 2, ',', '.') }} @else — @endif</td>
            @php $countBase = $stats['count'] ?? null; @endphp
            <td class="text-end stats-base d-none">@if($countBase!==null) {{ $countBase }} @else — @endif</td>
            @php
              $statsAll = ($overallStats ?? collect())->get($codeKey) ?? [];
            @endphp
            <td class="text-end stats-total d-none">@if(isset($statsAll['avg'])) {{ number_format($statsAll['avg'], 2, ',', '.') }} @else — @endif</td>
            <td class="text-end stats-total d-none">@if(isset($statsAll['median'])) {{ number_format($statsAll['median'], 2, ',', '.') }} @else — @endif</td>
            <td class="text-end stats-total d-none">@if(isset($statsAll['max'])) {{ number_format($statsAll['max'], 2, ',', '.') }} @else — @endif</td>
            <td class="text-end stats-total d-none">@if(isset($statsAll['min'])) {{ number_format($statsAll['min'], 2, ',', '.') }} @else — @endif</td>
            <td class="text-end stats-total d-none">@if(isset($statsAll['count'])) {{ $statsAll['count'] }} @else — @endif</td>
            @php
              $grpKey = trim($r->chat?->code ?? '') ?: trim($r->chat?->title ?? '');
              $trend = ($trends ?? collect())->get($grpKey) ?? null;
              $tLabel = $trend['label'] ?? null; $tPct = $trend['pct'] ?? null; $tAt = $trend['at'] ?? null;
              $tCls = $tLabel==='up' ? 'text-success' : ($tLabel==='down' ? 'text-danger' : 'text-muted');
            @endphp
            <td class="text-end">
              @if($trend)
                <span class="{{ $tCls }}">
                  @if($tLabel==='up') ▲ @elseif($tLabel==='down') ▼ @else → @endif
                  @if($tPct !== null) {{ number_format((float)$tPct, 2, ',', '.') }} % @endif
                </span>
                @if($tAt)
                  <small class="text-muted">({{ \Carbon\Carbon::parse($tAt)->format('d/m/Y') }})</small>
                @endif
              @else
                —
              @endif
            </td>
            <td class="text-center" data-ref="{{ number_format((float)$r->amount, 6, '.', '') }}" data-occurred="{{ optional($r->occurred_at)->format('Y-m-d') }}" data-apply-url="{{ route('openai.records.applyQuote', $r) }}">
              @php $symbol = strtoupper(trim($r->chat?->code ?? '')); @endphp
              @if($symbol !== '')
                <div class="d-inline-flex flex-column align-items-stretch gap-1 cotacao-col">
                  <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary btn-quote" data-symbol="{{ $symbol }}" title="Consulta a cotação atual do ativo">Consultar</button>
                  </div>
                  <div class="d-flex align-items-baseline gap-2">
                    <span class="quote-value" aria-live="polite" title="Valor retornado pela consulta"></span>
                    <small class="quote-time text-muted" title="Horário da cotação obtida"></small>
                  </div>
                  <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-success d-none btn-apply-quote" title="Mostrado quando a data da cotação coincide com a data do registro; aplica o valor no registro">Aplicar</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary d-none btn-create-from-quote" title="Usado quando a data difere; cria um novo registro com essa cotação">Novo registro</button>
                  </div>
                </div>
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
            @php $flagCode = strtoupper(trim($r->chat?->code ?? '')); @endphp
            <td class="text-center">
              @if($flagCode)
                <span class="badge bg-secondary" data-flag-code="{{ $flagCode }}">—</span>
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="9" class="text-center text-muted">Nenhum ativo encontrado.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="alert alert-info mt-3">
    Esta é uma visualização agregada. Nenhum dado é salvo nesta tela. Em breve será possível habilitar a gravação em lote via uma tag.
  </div>
{{-- </div> --}}
@endsection

@push('styles')
<style>
  body.openai-assets-compact header { display:none !important; }
  body.openai-assets-compact .card.shadow-sm.mb-3 { display:none !important; }
  body.openai-assets-compact #btn-toggle-openai-assets-layout { background:#212529; color:#fff; }
</style>
@endpush

@push('scripts')
<script>
(function(){
  const LS_KEY='openai_assets_layout_compact';
  const btn = document.getElementById('btn-toggle-openai-assets-layout');
  function apply(){
    const on = localStorage.getItem(LS_KEY)==='1';
    document.body.classList.toggle('openai-assets-compact', on);
    if(btn){ btn.textContent = on ? 'Modo Completo' : 'Modo Compacto'; }
  }
  document.addEventListener('DOMContentLoaded', function(){
    apply();
    btn?.addEventListener('click', ()=>{ const next = !(localStorage.getItem(LS_KEY)==='1'); localStorage.setItem(LS_KEY,next?'1':'0'); apply(); });
  });
})();
</script>
@endpush
<div id="assets-config"
     data-api-quote="{{ route('api.market.quote') }}"
     data-api-historical="{{ route('api.market.historical') }}"
     data-api-usage="{{ route('api.market.usage') }}"
     data-api-status="{{ route('api.market.status') }}"
     data-route-batch-flags="{{ route('openai.records.assets.batchFlags') }}"
     data-route-no-buy-get="{{ route('openai.assets.noBuy.get') }}"
     class="d-none"></div>

@push('scripts')
<script>
  (function(){
    // Dispara clique sem alterar foco (evita rolagem automática)
    function triggerClick(el){
      if (!el) return;
      try{
        const evt = new MouseEvent('click', { bubbles: true, cancelable: true, view: window });
        el.dispatchEvent(evt);
      }catch(_e){
        // fallback mínimo
        try{ el.click(); }catch(_e2){}
      }
    }
    const cfgEl = document.getElementById('assets-config');
    const cfg = cfgEl ? cfgEl.dataset : {};
    const endpoint = cfg.apiQuote || '';
    const endpointHist = cfg.apiHistorical || '';
    const endpointUsage = cfg.apiUsage || '';
    const endpointStatus = cfg.apiStatus || '';
    let batchAbort = false;
    // Utilitário: obtém número de querystring ou localStorage com fallback
    function getConfigNumber(paramName, defaultValue){
      try{
        const url = new URL(window.location.href);
        const qv = url.searchParams.get(paramName);
        if (qv !== null && qv !== '') {
          const n = Number(qv);
          if (isFinite(n) && n >= 0) return n;
        }
      }catch(e){/* noop */}
      try{
        const keyMap = {
          'auto_prev_interval': 'assets.autoPrev.intervalMs',
          'auto_prev_reload_delay': 'assets.autoPrev.reloadDelayMs',
        };
        const lsKey = keyMap[paramName] || paramName;
        const ls = localStorage.getItem(lsKey);
        if (ls !== null && ls !== ''){
          const n = Number(ls);
          if (isFinite(n) && n >= 0) return n;
        }
      }catch(e){/* noop */}
      return defaultValue;
    }
    function formatPrice(value) {
      const n = Number(value);
      if (!isFinite(n)) return '';
      try {
        return n.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      } catch (e) {
        return n.toFixed(2);
      }
    }
    // Toggle para mostrar/ocultar o badge local (por página)
    (function(){
      const KEY = 'assets.localBadge.visible';
      const btn = document.getElementById('toggle-local-badge');
      const badge = document.getElementById('market-status-badge');
      function getVisible(){
        try{ return localStorage.getItem(KEY) !== '0'; }catch(_e){ return true; }
      }
      function setVisible(v){
        try{ localStorage.setItem(KEY, v ? '1' : '0'); }catch(_e){}
      }
      function apply(){
        const vis = getVisible();
        if (badge){ badge.classList.toggle('d-none', !vis); }
        if (btn){ const s = btn.querySelector('[data-state]'); if (s) s.textContent = vis ? 'ON' : 'OFF'; }
      }
      if (btn){
        btn.addEventListener('click', function(){ setVisible(!getVisible()); apply(); });
      }
      apply();
    })();
    // Mercado: buscar status atual (NYSE) e pintar badge
    (async function(){
      try{
        const badge = document.getElementById('market-status-badge');
        if(!badge) return;
        const resp = await fetch(endpointStatus, { headers: { 'Accept':'application/json' } });
        const data = await resp.json().catch(()=>null);
        if(!resp.ok || !data){ throw new Error('Falha ao obter status'); }
        const st = String(data.status||'').toLowerCase();
        const label = String(data.label||'Mercado');
        const next = data.next_change_at ? ` • Próx: ${String(data.next_change_at).replace('T',' ').slice(0,16)}` : '';
        // cor por status
        let cls = 'bg-secondary';
        if (st === 'open') cls = 'bg-success';
        else if (st === 'pre') cls = 'bg-warning text-dark';
        else if (st === 'after') cls = 'bg-info text-dark';
        else if (st === 'closed') cls = 'bg-secondary';
        badge.className = 'badge ' + cls;
        badge.textContent = `Mercado: ${label}` + next;
        if (data.reason){ badge.title = `${label} — ${data.reason}`; }
      }catch(_e){
        const badge = document.getElementById('market-status-badge');
        if (badge){ badge.className='badge bg-secondary'; badge.textContent='Mercado: indisponível'; }
      }
    })();
    // Botão: Ver limites (snapshot de uso e limites)
    document.getElementById('btn-usage')?.addEventListener('click', async function(){
      const btn = this;
      const out = document.getElementById('usage-status');
      const prev = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
      if (out) out.textContent = '';
      try{
        const url = endpointUsage + '?probe=1';
        const resp = await fetch(url, { headers: { 'Accept':'application/json' } });
        const data = await resp.json();
        if (!resp.ok || !data){ throw new Error('Falha ao obter limites'); }
        const parts = [];
        if (data.alpha_vantage) {
          const a = data.alpha_vantage;
          parts.push(`Alpha: usado ${a.used_today}/${a.daily_limit || '?'} hoje${a.last_reason ? ` [${a.last_reason}]` : ''}`);
        }
        if (data.stooq) {
          const s = data.stooq;
          parts.push(`Stooq: usado ${s.used_today}${s.last_reason ? ` [${s.last_reason}]` : ''}`);
        }
        if (data.yahoo_rapidapi) {
          const y = data.yahoo_rapidapi;
          const usedHdr = typeof y.header_requests_used === 'number' ? y.header_requests_used : null;
          const remHdr = typeof y.header_requests_remaining === 'number' ? y.header_requests_remaining : null;
          const limHdr = typeof y.header_requests_limit === 'number' ? y.header_requests_limit : null;
          let txt = `Yahoo(RapidAPI): ${y.configured ? 'configurado' : 'não configurado'}`;
          if (y.configured) {
            // Preferir números dos headers; se ausentes, usar fallback env (daily_limit) com used_today
            if (limHdr !== null && (usedHdr !== null || remHdr !== null)) {
              if (usedHdr !== null) {
                txt += `, usado ${usedHdr}/${limHdr}`;
              } else if (remHdr !== null) {
                txt += `, restante ${remHdr}/${limHdr}`;
              }
            } else if (remHdr !== null && typeof y.daily_limit === 'number') {
              // Temos Remaining via header, mas não o Limit nos headers; usar fallback de limite diário
              txt += `, restante ${remHdr}/${y.daily_limit}`;
            } else if (typeof y.daily_limit === 'number' && typeof y.used_today === 'number') {
              const used = Math.max(0, y.used_today);
              const limit = y.daily_limit;
              txt += `, usado ${used}/${limit}`;
            } else if (typeof y.used_today === 'number' && y.used_today > 0) {
              // fallback mínimo
              txt += ` • lógico hoje: ${y.used_today}`;
            }
          }
          parts.push(txt);
        }
        if (out) out.textContent = parts.join(' • ');
      }catch(e){ if (out) out.textContent = 'Erro ao consultar limites'; }
      finally { btn.disabled = false; btn.innerHTML = prev; }
    });
  function formatUpdatedAtBR(s){
      if(!s) return '';
      // Tenta transformar em ISO seguro para Safari (YYYY-MM-DDTHH:mm:ssZ)
      let iso = String(s).trim();
      const hasTZ = /[zZ]|[+-]\d{2}:?\d{2}$/.test(iso);
      if(iso.match(/^\d{4}-\d{2}-\d{2}(?:[ T]\d{2}:\d{2}(?::\d{2})?)?$/) && !hasTZ){
        iso = iso.replace(' ', 'T');
        // Assume UTC quando vier sem timezone (serviço usa gmdate em alguns casos)
        if(!/T\d{2}:\d{2}/.test(iso)){
          // só data
          try {
            const parts = iso.split('T')[0].split('-');
            return parts[2] + '/' + parts[1] + '/' + parts[0];
          } catch(e){ return s; }
        }
        iso += 'Z';
      }
      const d = new Date(iso);
      if(isNaN(d.getTime())){ return s; }
      const dd = String(d.getDate()).padStart(2,'0');
      const mm = String(d.getMonth()+1).padStart(2,'0');
      const yyyy = d.getFullYear();
      const HH = String(d.getHours()).padStart(2,'0');
      const MM = String(d.getMinutes()).padStart(2,'0');
      return `${dd}/${mm}/${yyyy} ${HH}:${MM}`;
    }
    async function handleQuoteButton(btn){
      const symbol = (btn.getAttribute('data-symbol') || '').trim();
      if(!symbol){ return; }
  const container = btn.closest('td');
      const out = container ? container.querySelector('.quote-value') : null;
      const outTime = container ? container.querySelector('.quote-time') : null;
  const btnApply = container ? container.querySelector('.btn-apply-quote') : null;
      const prevHtml = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
      if (out) { out.textContent = ''; out.classList.remove('text-danger'); out.removeAttribute('title'); }
      if (outTime) { outTime.textContent = ''; }
      // Limpa destaque anterior e esconde botão aplicar
      if (container) { container.classList.remove('table-danger'); }
  if (btnApply) { btnApply.classList.add('d-none'); btnApply.disabled = true; btnApply.removeAttribute('data-amount'); }
  const btnNew = container ? container.querySelector('.btn-create-from-quote') : null;
  if (btnNew) { btnNew.classList.add('d-none'); btnNew.disabled = true; btnNew.removeAttribute('data-amount'); btnNew.removeAttribute('data-updated-at'); }
      try{
  const url = endpoint + '?symbol=' + encodeURIComponent(symbol) + '&persist=1';
        const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const data = await resp.json();
        if (!resp.ok || !data || typeof data.price === 'undefined' || data.price === null){
          throw new Error((data && (data.error || data.message)) || 'Falha ao obter cotação');
        }
        const txt = formatPrice(data.price) + (data.currency ? ' ' + String(data.currency).toUpperCase() : '');
        if (out) { out.textContent = txt; }
        if (outTime) {
          const when = formatUpdatedAtBR(data.updated_at);
          outTime.textContent = when ? `(${when})` : '';
        }
        // Regra: só destacar e permitir aplicar se a data da cotação == data do registro
        // Extrai data (YYYY-MM-DD) de updated_at
        let quoteDateKey = '';
        if (data.updated_at) {
          const raw = String(data.updated_at).trim().replace(' ', 'T');
          const d = new Date(/Z$/.test(raw) ? raw : (raw + (/[T]\d{2}:\d{2}/.test(raw) ? 'Z' : '')));
          if (!isNaN(d.getTime())) {
            const y = d.getUTCFullYear();
            const m = String(d.getUTCMonth()+1).padStart(2,'0');
            const dd = String(d.getUTCDate()).padStart(2,'0');
            quoteDateKey = `${y}-${m}-${dd}`;
          }
        }
        const recordDateKey = container ? (container.getAttribute('data-occurred') || '') : '';
        if (quoteDateKey && recordDateKey && quoteDateKey === recordDateKey) {
          // Se a data da cotação coincide com a data do registro, habilita o botão Aplicar
          const refStr = container ? container.getAttribute('data-ref') : null;
          const refVal = refStr ? parseFloat(refStr) : NaN;
          const price = Number(data.price);
          if (isFinite(price)) {
            if (btnApply) {
              btnApply.classList.remove('d-none');
              btnApply.disabled = false;
              btnApply.setAttribute('data-amount', String(price));
            }
            // Comparar preço com valor de referência e destacar apenas quando diferente
            if (isFinite(refVal) && container) {
              const p2 = Math.round(price * 100) / 100;
              const r2 = Math.round(refVal * 100) / 100;
              if (p2 !== r2) {
                container.classList.add('table-danger');
              }
            }
          }
        } else {
          // Datas diferentes: dar opção de criar novo registro
          const price = Number(data.price);
          if (btnNew && isFinite(price)) {
            btnNew.classList.remove('d-none');
            btnNew.disabled = false;
            btnNew.setAttribute('data-amount', String(price));
            btnNew.setAttribute('data-updated-at', data.updated_at ? String(data.updated_at) : '');
          }
        }
      }catch(err){
        if (out) { out.textContent = 'Erro'; out.classList.add('text-danger'); out.title = String(err.message || err); }
        // Em erro na consulta atual: se CHECK estiver ativo, parar automaticamente
        try{
          if (window.__autoPrevActive) {
            const statusMsg = 'Erro na consulta; CHECK parado';
            const status = document.getElementById('auto-prev-status');
            if (status) status.textContent = statusMsg;
            if (typeof stopAutoPrev === 'function') stopAutoPrev();
          }
        }catch(e){/* noop */}
        if (outTime) { outTime.textContent = ''; }
      }finally{
        btn.disabled = false;
        btn.innerHTML = prevHtml;
      }
    }
    // Click individual: usa a função de tratamento
    document.addEventListener('click', async function(ev){
      const btn = ev.target.closest('.btn-quote');
      if(!btn) return;
      await handleQuoteButton(btn);
    });

    // Aplicar cotação ao valor do registro
    document.addEventListener('click', async function(ev){
      const btn = ev.target.closest('.btn-apply-quote');
      if(!btn) return;
      const cell = btn.closest('td');
      if(!cell) return;
      const url = cell.getAttribute('data-apply-url');
      const amountStr = btn.getAttribute('data-amount');
      const val = amountStr ? parseFloat(amountStr) : NaN;
      if(!url || !isFinite(val)) return;
      const prev = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
      try{
        const resp = await fetch(url, {
          method: 'PATCH',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({ amount: val })
        });
        const data = await resp.json();
        if(!resp.ok || !data || data.ok !== true){
          throw new Error((data && (data.message||data.error)) || 'Falha ao aplicar cotação');
        }
        // Atualiza "Valor" exibido na linha (coluna de Valor fica 2 colunas à esquerda da célula cotação)
        const row = cell.closest('tr');
        if (row){
          const valorCell = row.querySelector('td:nth-child(4)');
          if (valorCell){ valorCell.textContent = formatPrice(val); valorCell.classList.add('fw-bold'); }
        }
        cell.classList.remove('table-danger');
        btn.classList.add('d-none');
        // Em modo lote, sinaliza conclusão para o orquestrador
        try{ if (window.__batchCreating) { document.dispatchEvent(new CustomEvent('quote:apply:done', { detail: { ok: true } })); } }catch(_e){}
      }catch(err){
        alert('Erro ao aplicar cotação: ' + String(err.message || err));
      }finally{
        btn.disabled = false;
        btn.innerHTML = prev;
      }
    });

    // Criar novo registro a partir da cotação
    document.addEventListener('click', async function(ev){
      const btn = ev.target.closest('.btn-create-from-quote');
      if(!btn) return;
      const cell = btn.closest('td');
      if(!cell) return;
      const url = cell.getAttribute('data-apply-url'); // usaremos a mesma base de record como referência
      if(!url) return;
      const createUrl = url.replace('/apply-quote', '/from-quote');
      const amountStr = btn.getAttribute('data-amount');
      const whenStr = btn.getAttribute('data-updated-at') || '';
      const val = amountStr ? parseFloat(amountStr) : NaN;
      if(!isFinite(val)) return;
      const prev = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
      try{
        const resp = await fetch(createUrl, {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({ amount: val, updated_at: whenStr })
        });
        const data = await resp.json().catch(()=>({}));
        if(!resp.ok || !data || data.ok !== true){
          throw new Error((data && (data.message||data.error)) || 'Falha ao criar registro');
        }
        // Se estamos em modo lote, não recarregamos a página por item
        if (window.__batchCreating) {
          try{ document.dispatchEvent(new CustomEvent('quote:create:done', { detail: { ok: true } })); }catch(_e){}
          btn.classList.add('d-none');
          btn.disabled = true;
        } else {
          // Modo manual: esconder botão e rolar para a próxima linha
          btn.classList.add('d-none');
          const row = cell.closest('tr');
          const nextRow = row ? row.nextElementSibling : null;
          if (nextRow && typeof nextRow.scrollIntoView === 'function') {
            try {
              nextRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
              // Destaque temporário para indicar foco visual
              nextRow.classList.add('table-primary');
              setTimeout(()=>{ nextRow.classList.remove('table-primary'); }, 1000);
            } catch(_e) { /* noop */ }
          }
        }
      }catch(err){
        alert('Erro ao criar novo registro: ' + String(err.message || err));
      }finally{
  if (!window.__batchCreating) { btn.disabled = false; btn.innerHTML = prev; }
      }
    });

    // Consultar todos (lote sequencial, linha a linha)
    document.getElementById('btn-batch-quotes')?.addEventListener('click', async function(){
      const btn = this;
      const status = document.getElementById('batch-status');
      const btnStop = document.getElementById('btn-batch-stop');
      const all = Array.from(document.querySelectorAll('button.btn-quote'));
      if (all.length === 0) { if(status) status.textContent = 'Nada para consultar'; return; }
      const prev = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Consultando…';
      // Exibir botão Parar
      batchAbort = false;
      if (btnStop){ btnStop.classList.remove('d-none'); btnStop.disabled = false; }
      let ok = 0, fail = 0;
      window.__batchCreating = true;
      function waitCreateDone(){ return new Promise(res=>{ const h=(ev)=>{ document.removeEventListener('quote:create:done', h); res(ev?.detail?.ok!==false); }; document.addEventListener('quote:create:done', h, { once:true }); }); }
      function waitApplyDone(){ return new Promise(res=>{ const h=(ev)=>{ document.removeEventListener('quote:apply:done', h); res(ev?.detail?.ok!==false); }; document.addEventListener('quote:apply:done', h, { once:true }); }); }
      for (let i=0;i<all.length;i++){
        if (batchAbort) { if(status){ status.textContent = `Interrompido (${i}/${all.length})`; } break; }
        const b = all[i];
        if(status){ status.textContent = `(${i+1}/${all.length})`; }
        try{
          await handleQuoteButton(b);
          // Tenta criar novo registro automaticamente quando aplicável
          const cell = b.closest('td');
          const btnApply = cell ? cell.querySelector('.btn-apply-quote') : null;
          const btnNew = cell ? cell.querySelector('.btn-create-from-quote') : null;
          if (btnApply && !btnApply.classList.contains('d-none') && !btnApply.disabled) {
            // Quando a data coincide, aplica automaticamente
            triggerClick(btnApply);
            const applied = await waitApplyDone();
            if (applied) ok++; else fail++;
          } else if (btnNew && !btnNew.classList.contains('d-none') && !btnNew.disabled) {
            triggerClick(btnNew);
            const created = await waitCreateDone();
            if (created) ok++; else fail++;
          } else {
            ok++;
          }
        }catch(e){ fail++; }
      }
      if(!batchAbort){ if(status){ status.textContent = `Concluído: ${ok} ok, ${fail} erro(s)`; } }
      btn.disabled = false;
      btn.innerHTML = prev;
      if (btnStop){ btnStop.classList.add('d-none'); btnStop.disabled = true; }
      // Recarrega uma única vez ao final
      window.__batchCreating = false;
      try{
        if (!batchAbort) {
          const f = document.getElementById('assets-filter-form');
          setTimeout(()=>{ if (f) { (typeof f.requestSubmit === 'function') ? f.requestSubmit() : f.submit(); } else { window.location.reload(); } }, 200);
        }
      }catch(_e){}
    });

    // Botão Parar
    document.getElementById('btn-batch-stop')?.addEventListener('click', function(){
      batchAbort = true;
      this.disabled = true;
    });

    // CHECK: modo automático sempre na primeira linha da tabela
    const AUTO_KEY = 'assets.autoPrev.enabled';
    let autoAbort = false;
    let autoPrevTimer = null; // intervalo de polling
    window.__autoPrevActive = false; // estado ligado/desligado
    window.__autoPrevBusy = false;   // evita cliques duplicados na mesma página
    // Preservar estado do CHECK ao enviar o filtro: adiciona auto_prev=1
    (function(){
      const form = document.getElementById('assets-filter-form');
      if (!form) return;
      form.addEventListener('submit', function(){
        try{
          if (localStorage.getItem(AUTO_KEY) === '1'){
            let hp = form.querySelector('input[name="auto_prev"]');
            if (!hp) {
              hp = document.createElement('input');
              hp.type = 'hidden';
              hp.name = 'auto_prev';
              form.appendChild(hp);
            }
            hp.value = '1';
          }
        }catch(e){/* noop */}
      });
    })();
    function updateAutoStatus(msg){
      const status = document.getElementById('auto-prev-status');
      if (!status) return;
      status.textContent = typeof msg === 'string' ? msg : '';
    }
    function runAutoPrev(){
      if (autoAbort || !window.__autoPrevActive || window.__autoPrevBusy) return;
      const btn = document.querySelector('tbody button.btn-baseline-quote[data-role="baseline-quote"]');
      if (!btn) { updateAutoStatus('Aguardando a primeira linha…'); return; }
      window.__autoPrevBusy = true;
      updateAutoStatus('Consultando primeira linha…');
  triggerClick(btn);
    }
    function startAutoPrev(){
      autoAbort = false;
      window.__autoPrevActive = true;
      window.__autoPrevBusy = false;
      localStorage.setItem(AUTO_KEY, '1');
      document.getElementById('btn-auto-prev-start')?.classList.add('d-none');
      const stopBtn = document.getElementById('btn-auto-prev-stop');
      if (stopBtn){ stopBtn.classList.remove('d-none'); stopBtn.disabled = false; }
      updateAutoStatus('Ativo');
      // inicia polling para garantir continuidade após recarregar
      if (autoPrevTimer) { clearInterval(autoPrevTimer); autoPrevTimer = null; }
  const intervalMs = getConfigNumber('auto_prev_interval', 400);
  autoPrevTimer = setInterval(runAutoPrev, Math.max(100, intervalMs));
  setTimeout(runAutoPrev, Math.min(250, intervalMs));
    }
    function stopAutoPrev(){
      autoAbort = true;
      window.__autoPrevActive = false;
      window.__autoPrevBusy = false;
      localStorage.removeItem(AUTO_KEY);
      if (autoPrevTimer) { clearInterval(autoPrevTimer); autoPrevTimer = null; }
      document.getElementById('btn-auto-prev-start')?.classList.remove('d-none');
      const stopBtn = document.getElementById('btn-auto-prev-stop');
      if (stopBtn){ stopBtn.classList.add('d-none'); stopBtn.disabled = true; }
      updateAutoStatus('Parado');
    }
    document.getElementById('btn-auto-prev-start')?.addEventListener('click', startAutoPrev);
    document.getElementById('btn-auto-prev-stop')?.addEventListener('click', stopAutoPrev);
    // Restaura estado ao carregar a página (considera localStorage e query auto_prev=1)
    (function(){
      try{
        const url = new URL(window.location.href);
        const autoParam = url.searchParams.get('auto_prev');
        const shouldAuto = (localStorage.getItem(AUTO_KEY) === '1') || (autoParam === '1');
        if (!shouldAuto) return;
        const kickoff = () => startAutoPrev();
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
          setTimeout(kickoff, 0);
        } else {
          window.addEventListener('DOMContentLoaded', kickoff);
        }
      }catch(_e){/* noop */}
    })();

    // Buscar cotação histórica usando exatamente a data indicada no botão (texto Base anterior)
    document.addEventListener('click', async function(ev){
      const btn = ev.target.closest('.btn-baseline-quote');
      if(!btn) return;
      const symbol = btn.getAttribute('data-symbol');
      let date = btn.getAttribute('data-date');
      if(!date){
        // tentar extrair do rótulo "Base anterior: dd/mm/aaaa" na coluna Código
        try{
          const row = btn.closest('tr');
          const badge = row?.querySelector('td:nth-child(1) .baseline-prev-badge');
          if (badge){
            const m = String(badge.textContent||'').match(/(\d{2})\/(\d{2})\/(\d{4})/);
            if (m){ date = `${m[3]}-${m[2]}-${m[1]}`; }
          }
        }catch(e){ /* noop */ }
      }
      if(!date){
        alert('Sem data base anterior definida para esta linha.');
        // libera para nova tentativa automática
        if (window.__autoPrevActive) {
          window.__autoPrevBusy = false;
          setTimeout(runAutoPrev, 300);
        }
        return;
      }
      const row = btn.closest('tr');
      const cell = btn.closest('td');
      const out = cell ? cell.querySelector('.baseline-quote-result') : null;
      const prev = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
      if (out) { out.textContent=''; out.classList.remove('text-danger'); }
      try{
        const url = endpointHist + '?symbol=' + encodeURIComponent(symbol) + '&date=' + encodeURIComponent(date);
        const resp = await fetch(url, { headers: { 'Accept':'application/json' } });
        const data = await resp.json();
        if (!resp.ok || !data || data.price === null || !data.date){
          let msg = (data && (data.error || data.message)) || 'Sem dados para a data base';
          if (data && data.reason) { msg += ` [${data.reason}]`; }
          if (data && data.detail) { msg += ` - ${data.detail}`; }
          throw new Error(msg);
        }
        const txt = formatPrice(data.price) + (data.currency ? ' ' + String(data.currency).toUpperCase() : '') + ' (' + data.date.replace(/^(\d{4})-(\d{2})-(\d{2})$/, '$3/$2/$1') + ')';
        if (out) {
          out.textContent = txt;
          // Oferecer criar registro desta data
          const createBtn = document.createElement('button');
          createBtn.type = 'button';
          createBtn.className = 'btn btn-xs btn-success ms-2';
          createBtn.textContent = 'Inserir registro';
          createBtn.addEventListener('click', async ()=>{
            const cotCell = row.querySelector('td[data-apply-url]');
            if(!cotCell) return;
            const urlCreate = cotCell.getAttribute('data-apply-url').replace('/apply-quote','/from-quote');
            try{
              const resp2 = await fetch(urlCreate, {
                method: 'POST',
                headers: { 'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                body: JSON.stringify({ amount: Number(data.price), updated_at: data.date })
              });
              const r2 = await resp2.json().catch(()=>({}));
              if(!resp2.ok || !r2 || r2.ok !== true) throw new Error((r2 && (r2.message||r2.error)) || 'Falha ao inserir');
              createBtn.disabled = true; createBtn.textContent = 'Inserido';
              // CHECK ativo: recarrega para reavaliar a nova primeira linha (com atraso opcional)
              if (window.__autoPrevActive && localStorage.getItem('assets.autoPrev.enabled') === '1') {
                const f = document.getElementById('assets-filter-form');
                const delay = getConfigNumber('auto_prev_reload_delay', 250);
                setTimeout(()=>{
                  if (f) { (typeof f.requestSubmit === 'function') ? f.requestSubmit() : f.submit(); }
                  else { window.location.reload(); }
                }, Math.max(0, delay));
                // fallback: se por algum motivo não recarregar, libera e tenta de novo
                setTimeout(()=>{ if(window.__autoPrevActive){ window.__autoPrevBusy = false; runAutoPrev(); } }, 1500);
              } else {
                // Comportamento padrão: recarrega a lista aplicando filtros
                const f = document.getElementById('assets-filter-form');
                const delay = getConfigNumber('auto_prev_reload_delay', 250);
                setTimeout(()=>{
                  if (f) { (typeof f.requestSubmit === 'function') ? f.requestSubmit() : f.submit(); }
                  else { window.location.reload(); }
                }, Math.max(0, delay));
              }
            }catch(e){ alert('Erro: ' + String(e.message||e)); }
            finally {
              // Se houve erro e o CHECK continuar ativo, libera para nova tentativa
              try{
                if (window.__autoPrevActive && localStorage.getItem('assets.autoPrev.enabled') === '1') {
                  window.__autoPrevBusy = false;
                  setTimeout(runAutoPrev, 300);
                }
              }catch(_e){/* noop */}
            }
          });
          out.appendChild(createBtn);
            // Se CHECK estiver ativo, aciona automaticamente o botão Inserir registro
            try {
              if (localStorage.getItem('assets.autoPrev.enabled') === '1' && typeof autoAbort !== 'undefined' && !autoAbort && !createBtn.disabled) {
                setTimeout(() => { if(!createBtn.disabled) triggerClick(createBtn); }, 50);
              }
            } catch(e) { /* noop */ }
        }
        // Atualiza selo na coluna do código
        try{
          const codeCell = row?.querySelector('td:nth-child(1) .baseline-prev-badge');
          if (codeCell){
            const brDate = String(data.date).replace(/^(\d{4})-(\d{2})-(\d{2})$/, '$3/$2/$1');
            codeCell.textContent = 'Base anterior: ' + brDate;
            codeCell.classList.remove('d-none');
          }
        }catch(e){/* noop */}
      }catch(err){
        if (out) {
          const msg = String(err && err.message ? err.message : err || 'Erro desconhecido');
          out.textContent = 'Erro: ' + msg;
          out.classList.add('text-danger');
          out.title = msg;
        }
        // em erro na consulta histórica: parar automaticamente o CHECK
        try{
          if (window.__autoPrevActive) {
            updateAutoStatus('Erro na consulta; CHECK parado');
            stopAutoPrev();
          }
        }catch(e){/* noop */}
      }finally{
        btn.disabled = false;
        btn.innerHTML = prev;
      }
    });
  })();
</script>
<script>
  (function(){
    const baseline = document.getElementById('assets-baseline');
    const exclude = document.getElementById('assets-exclude-date');
    function toggle(){ if(!baseline || !exclude) return; exclude.disabled = !baseline.value; }
    baseline?.addEventListener('input', toggle);
    document.addEventListener('DOMContentLoaded', toggle);
    toggle();
  })();
</script>
<script>
  (function(){
    // Toggle de estatísticas base (Média, Mediana, Máx, Mín, N≤Base)
    const KEY='assets.showBaseStats';
    const btn=document.getElementById('toggle-stats-base');
  function get(){ try{return localStorage.getItem(KEY)==='1';}catch(e){return false;} }
    function set(v){ try{localStorage.setItem(KEY, v?'1':'0');}catch(e){} }
    function apply(){
      const show=get();
      document.querySelectorAll('.stats-base').forEach(el=>el.classList.toggle('d-none', !show));
      if(btn){ const s=btn.querySelector('[data-state]'); if(s) s.textContent = show?'ON':'OFF'; }
    }
    btn?.addEventListener('click', ()=>{ set(!get()); apply(); });
    apply();
  })();
</script>
<script>
  (function(){
    const KEY='assets.showTotalStats';
    const btn=document.getElementById('toggle-stats-total');
    function get(){ try{return localStorage.getItem(KEY)==='1';}catch(e){return false;} }
    function set(v){ try{localStorage.setItem(KEY, v?'1':'0');}catch(e){} }
    function apply(){
      const show=get();
      document.querySelectorAll('.stats-total').forEach(el=>el.classList.toggle('d-none', !show));
      if(btn){ const s=btn.querySelector('[data-state]'); if(s) s.textContent = show?'ON':'OFF'; }
    }
    btn?.addEventListener('click', ()=>{ set(!get()); apply(); });
    apply();
  })();
</script>
@endpush

@push('scripts')
<script>
  (function(){
    const btn = document.getElementById('btn-batch-flags');
    if (!btn) return;
    btn.addEventListener('click', function(){
      const baselineInput = document.querySelector('input[name="baseline"]');
      const baselineVal = baselineInput ? baselineInput.value.trim() : '';
      if (!baselineVal) {
        alert('Informe a Data base para calcular Dif e aplicar as flags.');
        return;
      }
      if (!confirm('Aplicar COMPRAR/NÃO COMPRAR com base no sinal da Dif para os itens exibidos?')) return;
      const form = document.createElement('form');
      form.method = 'POST';
      try{
        const ds = document.getElementById('assets-config')?.dataset;
        form.action = (ds && ds.routeBatchFlags) ? ds.routeBatchFlags : '';
      }catch(_e){ form.action=''; }
      const tok = document.querySelector('meta[name="csrf-token"]');
      if (tok) {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = '_token'; inp.value = tok.getAttribute('content');
        form.appendChild(inp);
      }
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
  // Hidrata as badges de Flag (COMPRAR/NÃO COMPRAR) por código
  (async function(){
    try{
    const els = Array.from(document.querySelectorAll('[data-flag-code]'));
      const codes = Array.from(new Set(els.map(e => e.getAttribute('data-flag-code')).filter(Boolean)));
      const NO_BUY_GET = (document.getElementById('assets-config')?.dataset?.routeNoBuyGet) || '';
      for (const code of codes){
        try{
      const resp = await fetch(`${NO_BUY_GET}?code=${encodeURIComponent(code)}`, { headers: { 'Accept':'application/json' } });
          const data = await resp.json().catch(()=>null);
          const noBuy = !!(data && data.no_buy);
          els.filter(e => e.getAttribute('data-flag-code')===code).forEach(e => {
            e.className = 'badge ' + (noBuy ? 'bg-danger' : 'bg-success');
            e.textContent = noBuy ? 'NÃO COMPRAR' : 'COMPRAR';
          });
        }catch(_e){/* noop */}
      }
    }catch(_e){/* noop */}
  })();
</script>
@endpush
