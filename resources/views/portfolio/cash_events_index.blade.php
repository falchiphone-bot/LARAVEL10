@extends('layouts.bootstrap5')
@section('content')
<div class="container-fluid">
  <h1 class="h5 mb-3">Eventos de Caixa</h1>
  <div class="card mb-3 shadow-sm">
    <div class="card-body">
  <form method="get" class="row g-2 align-items-end">
        <div class="col-md-2">
          <label class="form-label small mb-1">Conta</label>
          <select name="account_id" class="form-select form-select-sm">
            <option value="">Todas</option>
            @foreach($accounts as $acc)
              <option value="{{ $acc->id }}" @selected($filter_account_id===$acc->id)>{{ $acc->account_name }} @if($acc->broker) ({{ $acc->broker }}) @endif</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">Categoria</label>
          <select name="category" class="form-select form-select-sm">
            <option value="">Todas</option>
            @foreach($categories as $cat)
              <option value="{{ $cat }}" @selected($filter_category===$cat)>{{ $cat }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">Status (contém)</label>
          <input type="text" name="status" value="{{ $filter_status }}" class="form-control form-control-sm" />
        </div>
        <div class="col-md-3">
          <label class="form-label small mb-1">Título (contém)</label>
          <input type="text" name="title" value="{{ $filter_title ?? '' }}" class="form-control form-control-sm" />
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">De</label>
          <input type="date" name="from" value="{{ $filter_from }}" class="form-control form-control-sm" />
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">Até</label>
          <input type="date" name="to" value="{{ $filter_to }}" class="form-control form-control-sm" />
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">Liq. De</label>
          <input type="date" name="settle_from" value="{{ $filter_settle_from }}" class="form-control form-control-sm" />
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">Liq. Até</label>
          <input type="date" name="settle_to" value="{{ $filter_settle_to }}" class="form-control form-control-sm" />
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">Direção</label>
          <select name="direction" class="form-select form-select-sm">
            <option value="">Todas</option>
            <option value="in" @selected($filter_direction==='in')>Entradas</option>
            <option value="out" @selected($filter_direction==='out')>Saídas</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">Valor >=</label>
          <input type="number" step="0.01" name="val_min" value="{{ $filter_val_min }}" class="form-control form-control-sm" />
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">Valor <=</label>
          <input type="number" step="0.01" name="val_max" value="{{ $filter_val_max }}" class="form-control form-control-sm" />
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">Fonte</label>
          <select name="source" class="form-select form-select-sm">
            <option value="">Todas</option>
            @foreach($sources as $src)
              <option value="{{ $src }}" @selected($filter_source===$src)>{{ $src }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">Ordenar por</label>
          <select name="sort" class="form-select form-select-sm">
            <option value="event_date" @selected($sort==='event_date')>Data</option>
            <option value="settlement_date" @selected($sort==='settlement_date')>Liquidação</option>
            <option value="category" @selected($sort==='category')>Categoria</option>
            <option value="title" @selected($sort==='title')>Título</option>
            <option value="amount" @selected($sort==='amount')>Valor</option>
            <option value="status" @selected($sort==='status')>Status</option>
          </select>
        </div>
        <div class="col-md-1">
          <label class="form-label small mb-1">Dir</label>
          <select name="dir" class="form-select form-select-sm">
            <option value="desc" @selected($dir==='desc')>DESC</option>
            <option value="asc" @selected($dir==='asc')>ASC</option>
          </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
          <button class="btn btn-sm btn-outline-primary flex-grow-1" type="submit">Filtrar</button>
          <a href="{{ route('cash.events.index') }}" class="btn btn-sm btn-outline-secondary" title="Limpar filtros">Limpar</a>
        </div>
        <div class="col-md-2">
          <div class="form-check mt-4" data-bs-toggle="tooltip" data-bs-title="Exibe a coluna 'Saldo Após' calculada a partir do snapshot mais recente da conta. Disponível apenas na página 1, com conta filtrada e ordenação por Data ou Liquidação.">
            <input class="form-check-input" type="checkbox" value="1" id="chkRunning" name="show_running" @checked($showRunning) />
            <label class="form-check-label small" for="chkRunning">Exibir Saldo Após</label>
          </div>
        </div>
        <div class="col-md-2 d-none">
          <div class="form-check mt-4" data-bs-toggle="tooltip" data-bs-title="Exibe um resumo agregado por ativo (quanto comprou, vendeu e pagou de taxa) considerando os filtros atuais.">
            <input class="form-check-input" type="checkbox" value="1" id="chkGroupAsset" name="group_asset" @checked($groupAsset ?? false) />
            <label class="form-check-label small" for="chkGroupAsset">Agrupar por Ativo</label>
          </div>
        </div>
      </form>
    </div>
    <div class="card-footer small text-muted">
      <div class="d-flex flex-wrap gap-3 align-items-center">
        <div>Entradas: <span class="text-success">{{ number_format($sumIn,2,',','.') }}</span> | Saídas: <span class="text-danger">{{ number_format($sumOut,2,',','.') }}</span> | Saldo (∑ filtrado): <strong>{{ number_format($sumTotal,2,',','.') }}</strong></div>
        <div class="ms-auto d-flex gap-2">
          <a href="{{ route('cash.positions.summary', request()->query()) }}#gsc.tab=0" class="btn btn-sm btn-outline-success" title="Ver resumo por ativo (saldo e preço médio)">Resumo por Ativo</a>
          <a href="{{ route('openai.portfolio.index') }}#gsc.tab=0" class="btn btn-sm btn-outline-secondary" title="Ver carteira (posições)">Carteira</a>
          <a href="{{ route('cash.events.export.csv', request()->query()) }}" class="btn btn-sm btn-outline-info" title="Exportar eventos filtrados em CSV">Exportar CSV</a>
          @can('CASH EVENTS - IMPORTAR')
          <a href="{{ route('cash.import.form') }}#gsc.tab=0" class="btn btn-sm btn-outline-primary" title="Importar novo bloco de caixa">Importar Caixa</a>
          <a href="{{ route('cash.import.csv.form') }}#gsc.tab=0" class="btn btn-sm btn-outline-primary" title="Importar CSV avenue-report-statement">Importar Caixa CSV</a>
          @endcan
          <button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#modalTruncateCash" title="Apagar todos os eventos e snapshots de caixa deste usuário">Limpar Tudo</button>
        </div>
      </div>
    </div>
  </div>
  <!-- Card de alternância de agrupamentos (fundo rosa) -->
  <div class="card mb-3 shadow-sm border-0" style="background-color:#ffe8f1;">
    <div class="card-body py-2">
      <div class="d-flex flex-wrap align-items-center gap-3 small">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" role="switch" id="toggleGroupAssetMirror">
          <label class="form-check-label" for="toggleGroupAssetMirror">Agrupar por Ativo</label>
        </div>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" role="switch" id="toggleGroupSelection">
          <label class="form-check-label" for="toggleGroupSelection">Agrupar por Seleção</label>
        </div>
        <div class="ms-auto d-flex align-items-center gap-2">
          <small class="text-muted">Marque eventos na lista e ative "Agrupar por Seleção" para ver o resumo.</small>
          <button class="btn btn-sm btn-outline-primary" id="btnSelectionRebuild" type="button">Atualizar resumo</button>
        </div>
      </div>
    </div>
  </div>
  <!-- Resumo por Seleção (dinâmico, client-side) -->
  <div class="card mb-3 shadow-sm d-none" id="selectionSummaryCard">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>Resumo por Seleção (filtrado nesta página)</strong>
      <div class="d-flex align-items-center gap-2 small">
        <label for="selDateYmd" class="mb-0">Data</label>
        <input type="date" id="selDateYmd" class="form-control form-control-sm" style="width: 160px;" />
        <button type="button" id="selMarkByDate" class="btn btn-sm btn-outline-primary" title="Marcar todos desta data">Marcar Data</button>
        <button type="button" id="selUnmarkByDate" class="btn btn-sm btn-outline-secondary" title="Desmarcar todos desta data">Desmarcar Data</button>
        <button type="button" id="selMarkAll" class="btn btn-sm btn-outline-primary" title="Marcar todos da página">Marcar todos</button>
        <button type="button" id="selUnmarkAll" class="btn btn-sm btn-outline-secondary" title="Desmarcar todos da página">Desmarcar</button>
        <button type="button" id="selExportCsv" class="btn btn-sm btn-outline-info" title="Exportar Resumo (CSV)">Exportar CSV</button>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-sm table-striped mb-0" id="selectionSummaryTable">
        <thead class="table-light">
          <tr>
            <th>Ativo</th>
            <th class="text-end">Compras</th>
            <th class="text-end">Vendas</th>
            <th class="text-end">Taxas (fee)</th>
            <th class="text-end">Saldo (Vnd - Cmp)</th>
            <th class="text-end">Variação (%)</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
          <tr id="selectionTotalsRow">
            <td class="text-end"><strong>Totais</strong></td>
            <td class="text-end" data-total="buy">—</td>
            <td class="text-end" data-total="sell">—</td>
            <td class="text-end" data-total="fee">—</td>
            <td class="text-end" data-total="net">—</td>
            <td class="text-end" data-total="varpct">—</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
  @if(!empty($periodSummary) || !empty($byAccountSummary))
  <div class="row g-3 mb-3">
    <div class="col-lg-7">
      <div class="card h-100 shadow-sm">
        <div class="card-header"><strong>Resumo por Período (mensal)</strong></div>
        <div class="table-responsive">
          <table class="table table-sm table-striped mb-0">
            <thead class="table-light">
              <tr>
                <th>Período</th>
                <th class="text-end">Compras</th>
                <th class="text-end">Vendas</th>
                <th class="text-end">Taxas (fee)</th>
                <th class="text-end">Saldo (Vnd - Cmp)</th>
              </tr>
            </thead>
            <tbody>
              @forelse($periodSummary ?? [] as $per => $s)
                <tr>
                  <td>{{ $per }}</td>
                  <td class="text-end text-danger">{{ number_format($s['buy'] ?? 0, 2, ',', '.') }}</td>
                  <td class="text-end text-success">{{ number_format($s['sell'] ?? 0, 2, ',', '.') }}</td>
                  <td class="text-end text-secondary">{{ number_format($s['fee'] ?? 0, 2, ',', '.') }}</td>
                  @php $net = ($s['sell'] ?? 0) - ($s['buy'] ?? 0); @endphp
                  <td class="text-end {{ $net>0 ? 'text-success' : ($net<0 ? 'text-danger' : 'text-secondary') }}">{{ number_format($net, 2, ',', '.') }}</td>
                </tr>
              @empty
                <tr><td colspan="5" class="text-center text-muted">Sem dados no período.</td></tr>
              @endforelse
            </tbody>
            @if(!empty($periodSummary))
            @php
              $tBuy = array_sum(array_map(fn($r)=>$r['buy'] ?? 0, $periodSummary));
              $tSell = array_sum(array_map(fn($r)=>$r['sell'] ?? 0, $periodSummary));
              $tFee = array_sum(array_map(fn($r)=>$r['fee'] ?? 0, $periodSummary));
              $tNet = $tSell - $tBuy;
            @endphp
            <tfoot>
              <tr>
                <td class="text-end"><strong>Totais</strong></td>
                <td class="text-end">{{ number_format($tBuy, 2, ',', '.') }}</td>
                <td class="text-end">{{ number_format($tSell, 2, ',', '.') }}</td>
                <td class="text-end">{{ number_format($tFee, 2, ',', '.') }}</td>
                <td class="text-end {{ $tNet>0 ? 'text-success' : ($tNet<0 ? 'text-danger' : 'text-secondary') }}">{{ number_format($tNet, 2, ',', '.') }}</td>
              </tr>
            </tfoot>
            @endif
          </table>
        </div>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="card h-100 shadow-sm">
        <div class="card-header"><strong>Resumo por Conta</strong></div>
        <div class="table-responsive">
          <table class="table table-sm table-striped mb-0">
            <thead class="table-light">
              <tr>
                <th>Conta</th>
                <th class="text-end">Compras</th>
                <th class="text-end">Vendas</th>
                <th class="text-end">Taxas (fee)</th>
              </tr>
            </thead>
            <tbody>
              @php
                // Map para nome da conta
                $accNames = collect($accounts ?? [])->keyBy('id');
              @endphp
              @forelse(($byAccountSummary ?? []) as $accId => $s)
                <tr>
                  <td>{{ $accNames[$accId]->account_name ?? ('Conta #'.$accId) }}</td>
                  <td class="text-end text-danger">{{ number_format($s['buy'] ?? 0, 2, ',', '.') }}</td>
                  <td class="text-end text-success">{{ number_format($s['sell'] ?? 0, 2, ',', '.') }}</td>
                  <td class="text-end text-secondary">{{ number_format($s['fee'] ?? 0, 2, ',', '.') }}</td>
                </tr>
              @empty
                <tr><td colspan="4" class="text-center text-muted">Sem dados por conta.</td></tr>
              @endforelse
            </tbody>
            @if(!empty($byAccountSummary))
            @php
              $ab = array_map(fn($r)=>$r['buy'] ?? 0, $byAccountSummary);
              $as = array_map(fn($r)=>$r['sell'] ?? 0, $byAccountSummary);
              $af = array_map(fn($r)=>$r['fee'] ?? 0, $byAccountSummary);
              $tBuyAcc = array_sum($ab);
              $tSellAcc = array_sum($as);
              $tFeeAcc = array_sum($af);
            @endphp
            <tfoot>
              <tr>
                <td class="text-end"><strong>Totais</strong></td>
                <td class="text-end">{{ number_format($tBuyAcc, 2, ',', '.') }}</td>
                <td class="text-end">{{ number_format($tSellAcc, 2, ',', '.') }}</td>
                <td class="text-end">{{ number_format($tFeeAcc, 2, ',', '.') }}</td>
              </tr>
            </tfoot>
            @endif
          </table>
        </div>
      </div>
    </div>
  </div>
  @endif
  <!-- Controles de paginação -->
  <form method="get" action="{{ route('cash.events.index') }}" class="mb-2">
    @foreach(request()->except(['paginate','per_page']) as $k=>$v)
      @if(is_array($v))
        @foreach($v as $vv)
          <input type="hidden" name="{{ $k }}[]" value="{{ $vv }}" />
        @endforeach
      @else
        <input type="hidden" name="{{ $k }}" value="{{ $v }}" />
      @endif
    @endforeach
    <div class="card shadow-sm border-0 mb-3" style="background:#fff7f7;">
      <div class="card-body py-2 d-flex flex-wrap align-items-center gap-3">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" role="switch" id="chkPaginate" name="paginate" value="1" @checked(($paginate ?? true)) />
          <label class="form-check-label" for="chkPaginate">Paginar resultados</label>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="inpPerPage" class="mb-0 small">Linhas por página</label>
          <input type="number" name="per_page" id="inpPerPage" class="form-control form-control-sm" style="width:100px" min="10" max="5000" value="{{ $perPage ?? 50 }}" />
          <input type="hidden" name="per_page" id="inpPerPageHidden" value="{{ $perPage ?? 50 }}" />
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="chkOnlyBuySell" name="only_buy_sell" value="1" @checked(($onlyBuySell ?? false)) />
          <label class="form-check-label" for="chkOnlyBuySell">Somente Compra/Venda</label>
        </div>
        <div>
          <button type="submit" class="btn btn-sm btn-outline-primary">Aplicar</button>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function(){
          const chk = document.getElementById('chkPaginate');
          const per = document.getElementById('inpPerPage');
          const perH = document.getElementById('inpPerPageHidden');
          const only = document.getElementById('chkOnlyBuySell');
          function sync(){
            if (chk && chk.checked){
              if (per){ per.value = 5000; per.setAttribute('readonly','readonly'); per.classList.add('bg-light'); }
              if (perH){ perH.value = 5000; }
              if (only){ only.disabled = false; }
            } else {
              if (per){ per.removeAttribute('readonly'); per.classList.remove('bg-light'); }
              if (only){ only.disabled = true; }
            }
          }
          if(chk){ chk.addEventListener('change', sync); }
          sync();
        });
        </script>
      </div>
    </div>
  </form>
  @if(($groupAsset ?? false) && !empty($byAssetSummary))
  <div class="card mb-3 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>Resumo por Ativo (filtrado)</strong>
      <div class="d-flex align-items-center gap-2 small">
        <label for="byAssetShow" class="mb-0">Mostrar:</label>
        <select id="byAssetShow" class="form-select form-select-sm" style="width:auto">
          <option value="all">Todos</option>
          <option value="marked">Marcados</option>
          <option value="unmarked">Não marcados</option>
        </select>
        <button type="button" id="byAssetMarkAll" class="btn btn-sm btn-outline-primary" title="Marcar todos os ativos visíveis">Marcar todos</button>
        <button type="button" id="byAssetUnmarkAll" class="btn btn-sm btn-outline-secondary" title="Desmarcar todos os ativos visíveis">Desmarcar todos</button>
        <a id="byAssetExportCsv" href="{{ route('cash.events.by-asset.export.csv', array_merge(request()->query(), ['group_asset'=>1])) }}" class="btn btn-sm btn-outline-info" title="Exportar Resumo por Ativo (CSV)">Exportar CSV</a>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-sm table-striped mb-0">
        <thead class="table-light">
          <tr>
            <th style="width:28px" title="Marcar ativo para filtro">✓</th>
            <th>Ativo</th>
            <th class="text-end">Compras</th>
            <th class="text-end">Vendas</th>
            <th class="text-end">Taxas (fee)</th>
            <th class="text-end">Saldo (Vnd - Cmp)</th>
            <th class="text-end">Variação (%)</th>
          </tr>
        </thead>
        <tbody>
          @foreach(($byAssetSummary ?? []) as $sym => $s)
            @php
              $buy = (float)($s['buy'] ?? 0);
              $sell = (float)($s['sell'] ?? 0);
              $net = $sell - $buy;
              $varPct = $buy > 0 ? ($net / $buy) * 100.0 : null;
            @endphp
            <tr data-sym="{{ $sym }}" data-buy="{{ $buy }}" data-sell="{{ $sell }}" data-fee="{{ (float)($s['fee'] ?? 0) }}" data-net="{{ $net }}" data-varpct="{{ $varPct !== null ? $varPct : '' }}">
              <td class="text-center align-middle">
                <input type="checkbox" class="form-check-input asset-mark" data-sym="{{ $sym }}" />
              </td>
              <td>
                <a href="{{ route('asset-stats.index', ['symbol'=>$sym]) }}#gsc.tab=0" target="_blank" rel="noopener" title="Abrir AssetDailyStat">{{ $sym }}</a>
              </td>
              <td class="text-end text-danger">{{ number_format($buy, 2, ',', '.') }}</td>
              <td class="text-end text-success">{{ number_format($sell, 2, ',', '.') }}</td>
              <td class="text-end text-secondary">{{ number_format($s['fee'] ?? 0, 2, ',', '.') }}</td>
              <td class="text-end {{ $net>0 ? 'text-success' : ($net<0 ? 'text-danger' : 'text-secondary') }}">{{ number_format($net, 2, ',', '.') }}</td>
              <td class="text-end {{ ($varPct!==null && $varPct>0) ? 'text-success' : (($varPct!==null && $varPct<0) ? 'text-danger' : 'text-secondary') }}">
                {{ $varPct!==null ? number_format($varPct, 2, ',', '.') . '%' : '—' }}
              </td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr id="byAssetTotalsRow">
            <td></td>
            <td class="text-end"><strong>Totais</strong></td>
            <td class="text-end" data-total="buy">—</td>
            <td class="text-end" data-total="sell">—</td>
            <td class="text-end" data-total="fee">—</td>
            <td class="text-end" data-total="net">—</td>
            <td class="text-end" data-total="varpct">—</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
  @endif
  <div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>Lista</strong>
      <small class="text-muted">Total: {{ $events->total() }} | Página {{ $events->currentPage() }}/{{ $events->lastPage() }}</small>
    </div>
    <div class="table-responsive">
      <table class="table table-sm table-striped align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width:28px" title="Selecionar evento" class="text-center">
              <input type="checkbox" class="form-check-input form-check-input-sm" id="chkAllPage">
            </th>
            <th>Data</th>
            <th>Liquidação</th>
            <th>Conta</th>
            <th>Categoria</th>
            <th>Título</th>
            <th>Detalhe</th>
            <th class="text-end">Valor (USD)</th>
            <th>Status</th>
            <th>Fonte</th>
            @if($canComputeRunning)
            <th class="text-end">Saldo Após</th>
            @endif
          </tr>
        </thead>
        <tbody>
        @forelse($events as $e)
          @php $cls = $e->amount>0?'text-success':($e->amount<0?'text-danger':'text-secondary'); @endphp
          <tr>
            <td class="text-center align-middle">
              <input type="checkbox" class="form-check-input form-check-input-sm evt-mark" data-title="{{ e($e->title) }}" data-detail="{{ e($e->detail) }}" data-category="{{ e($e->category) }}" data-amount="{{ number_format($e->amount,6,'.','') }}" data-ymd="{{ optional($e->event_date)->format('Y-m-d') }}">
            </td>
            <td>{{ $e->event_date? $e->event_date->format('d/m/Y'):'—' }}</td>
            <td>{{ $e->settlement_date? $e->settlement_date->format('d/m/Y'):'—' }}</td>
            <td>{{ $e->account? $e->account->account_name:'—' }}</td>
            <td>{{ $e->category }}</td>
            <td>{{ $e->title }}</td>
            <td>{{ $e->detail ?: '—' }}</td>
            <td class="text-end {{ $cls }}">{{ number_format($e->amount,2,',','.') }}</td>
            <td>{{ $e->status ?: '—' }}</td>
            <td><span class="badge bg-secondary-subtle text-secondary-emphasis border">{{ $e->source }}</span></td>
            @if($canComputeRunning)
            <td class="text-end">{{ $e->running_balance_after!==null ? number_format($e->running_balance_after,2,',','.') : '—' }}</td>
            @endif
          </tr>
        @empty
          <tr><td colspan="8" class="text-center text-muted">Nenhum evento encontrado.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">
      {{ $events->links() }}
    </div>
  </div>
</div>
@endsection

@push('scripts')
<div class="modal fade" id="modalTruncateCash" tabindex="-1" aria-labelledby="modalTruncateCashLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTruncateCashLabel">Confirmar limpeza total</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <form method="POST" action="{{ route('cash.events.truncate.user') }}" id="formTruncateCash">
        @csrf
        <input type="hidden" name="confirm" value="yes" />
        <div class="modal-body">
          <p class="small mb-2">Esta ação irá <strong>APAGAR TODOS</strong> os eventos e snapshots de caixa <strong>somente do seu usuário</strong>. Não pode ser desfeita.</p>
          <p class="small mb-2">Para confirmar, digite <code>APAGAR</code> abaixo:</p>
          <input type="text" name="confirm_token" class="form-control form-control-sm" placeholder="Digite APAGAR" required />
        </div>
        <div class="modal-footer d-flex justify-content-between">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-sm btn-danger" id="btnTruncateSubmit" disabled>Apagar Tudo</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
  const modal = document.getElementById('modalTruncateCash');
  if(!modal) return;
  const input = modal.querySelector('input[name="confirm_token"]');
  const btn = modal.querySelector('#btnTruncateSubmit');
  if(input && btn){
    input.addEventListener('input', function(){
      btn.disabled = (input.value.trim().toUpperCase() !== 'APAGAR');
    });
  }
});
</script>
<script>
// Marca/Desmarca ativos e filtra a tabela (client-side)
document.addEventListener('DOMContentLoaded', function(){
  // Espelha controle de "Agrupar por Ativo" para o card rosa e aplica exclusividade com "Agrupar por Seleção"
  const chkOriginalGroupAsset = document.getElementById('chkGroupAsset');
  const toggleGroupAssetMirror = document.getElementById('toggleGroupAssetMirror');
  const toggleGroupSelection = document.getElementById('toggleGroupSelection');
  const selectionCard = document.getElementById('selectionSummaryCard');
  const btnSelectionRebuild = document.getElementById('btnSelectionRebuild');
  // Estado inicial do espelho
  if (toggleGroupAssetMirror && chkOriginalGroupAsset) {
    toggleGroupAssetMirror.checked = chkOriginalGroupAsset.checked;
  }
  function applyExclusivity(){
    const selOn = !!(toggleGroupSelection && toggleGroupSelection.checked);
    const assetOn = !!(toggleGroupAssetMirror && toggleGroupAssetMirror.checked);
    if (toggleGroupAssetMirror) toggleGroupAssetMirror.disabled = selOn;
    if (toggleGroupSelection) toggleGroupSelection.disabled = assetOn;
    // Mostrar/ocultar card de seleção
    if (selectionCard) selectionCard.classList.toggle('d-none', !selOn);
    // Quando seleção ativa, esconder o resumo por ativo (se estiver renderizado)
    try{
      const byAssetCard = document.querySelector('.card.mb-3.shadow-sm:has(#byAssetTotalsRow)');
      if (byAssetCard) byAssetCard.style.display = selOn ? 'none' : '';
    }catch(_e){ /* :has pode não ser suportado - fallback abaixo */
      const byAssetTotals = document.getElementById('byAssetTotalsRow');
      if (byAssetTotals) {
        const card = byAssetTotals.closest('.card');
        if (card) card.style.display = selOn ? 'none' : '';
      }
    }
  }
  if (toggleGroupSelection){
    toggleGroupSelection.addEventListener('change', function(){
      if (toggleGroupSelection.checked && toggleGroupAssetMirror) {
        // desliga e desmarca Agrupar por Ativo (espelho e original)
        toggleGroupAssetMirror.checked = false;
        if (chkOriginalGroupAsset) chkOriginalGroupAsset.checked = false;
      }
      applyExclusivity();
      if (toggleGroupSelection.checked) rebuildSelectionSummary();
    });
  }
  if (toggleGroupAssetMirror){
    toggleGroupAssetMirror.addEventListener('change', function(){
      if (toggleGroupAssetMirror.checked && toggleGroupSelection) {
        toggleGroupSelection.checked = false;
      }
      // replica estado no input original e submete o form para atualizar via servidor
      if (chkOriginalGroupAsset) {
        chkOriginalGroupAsset.checked = toggleGroupAssetMirror.checked;
        const form = chkOriginalGroupAsset.closest('form');
        if (form) form.submit();
      }
      applyExclusivity();
    });
  }
  if (btnSelectionRebuild){ btnSelectionRebuild.addEventListener('click', rebuildSelectionSummary); }
  applyExclusivity();

  // Seleção na lista: marcar/desmarcar todos desta página
  const chkAllPage = document.getElementById('chkAllPage');
  function setAllPage(val){ document.querySelectorAll('input.evt-mark').forEach(cb=>{ cb.checked = !!val; }); }
  if (chkAllPage){ chkAllPage.addEventListener('change', function(){ setAllPage(chkAllPage.checked); if (toggleGroupSelection && toggleGroupSelection.checked) rebuildSelectionSummary(); }); }
  document.addEventListener('change', function(ev){
    const t = ev.target;
    if (t && t.classList && t.classList.contains('evt-mark')){
      if (toggleGroupSelection && toggleGroupSelection.checked) rebuildSelectionSummary();
    }
  });

  // Parser e agregador para Resumo por Seleção
  function parseBuySellSymbol(title, detail){
    const txt = String(title||'') + ' ' + String(detail||'');
    const t = txt.toUpperCase().normalize('NFKD');
    const norm = t.replace(/\s+/g,' ').trim();
    let m = norm.match(/\b(COMPRA|VENDA)\b\s+DE\s+(\d+[\.,]?\d*)\s+([A-Z0-9\.-:_]+)\s+A\s*\$\s*(\d+[\.,]?\d*)/u);
    if (m){ return { type: (m[1]==='COMPRA')?'buy':'sell', sym: (m[3]||'').trim() }; }
    m = norm.match(/\b(BUY|SELL)\b\s+(\d+[\.,]?\d*)\s+([A-Z0-9\.-:_]+)\s+(@|AT)\s*\$?\s*(\d+[\.,]?\d*)/u);
    if (m){ return { type: (m[1]==='BUY')?'buy':'sell', sym: (m[3]||'').trim() }; }
    return null;
  }
  function isFeeCategory(cat){
    const c = String(cat||'').toLowerCase();
    return c.includes('fee') || c.includes('taxa') || c.includes('commission') || c.includes('comissão');
  }
  function computeSelectionMap(){
    const rows = document.querySelectorAll('input.evt-mark:checked');
    const map = {};
    rows.forEach(cb => {
      const title = cb.getAttribute('data-title') || '';
      const detail = cb.getAttribute('data-detail') || '';
      const cat = cb.getAttribute('data-category') || '';
      const amount = parseFloat(cb.getAttribute('data-amount')||'0') || 0;
      const p = parseBuySellSymbol(title, detail);
      const fee = isFeeCategory(cat);
      if (!p || !p.sym) return;
      const sym = p.sym.toUpperCase();
      if (!map[sym]) map[sym] = { buy:0, sell:0, fee:0 };
      if (p.type==='buy') map[sym].buy += Math.abs(amount);
      else if (p.type==='sell') map[sym].sell += Math.abs(amount);
      if (fee) map[sym].fee += Math.abs(amount);
    });
    return map;
  }
  function fmt2(n){
    if (n===null || isNaN(n)) return '—';
    return new Intl.NumberFormat('pt-BR',{minimumFractionDigits:2, maximumFractionDigits:2}).format(n);
  }
  function rebuildSelectionSummary(){
    const map = computeSelectionMap();
    const tbody = document.querySelector('#selectionSummaryTable tbody');
    if (!tbody) return;
    tbody.innerHTML = '';
    const symbols = Object.keys(map).sort((a,b)=> a.localeCompare(b,'pt-BR',{sensitivity:'base', numeric:true}));
    let tBuy=0, tSell=0, tFee=0, tNet=0;
    symbols.forEach(sym => {
      const s = map[sym];
      const buy = +(s.buy||0); const sell= +(s.sell||0); const fee= +(s.fee||0);
      const net = sell - buy;
      tBuy += buy; tSell+=sell; tFee+=fee; tNet+=net;
      const varPct = buy>0 ? (net/buy)*100.0 : null;
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td><a href="${window.location.origin + '{{ route('asset-stats.index', ['symbol'=>'__SYM__']) }}'.replace('%2F','/').replace('__SYM__', encodeURIComponent(sym))}#gsc.tab=0" target="_blank" rel="noopener">${sym}</a></td>
        <td class="text-end text-danger">${fmt2(buy)}</td>
        <td class="text-end text-success">${fmt2(sell)}</td>
        <td class="text-end text-secondary">${fmt2(fee)}</td>
        <td class="text-end ${net>0?'text-success':(net<0?'text-danger':'text-secondary')}">${fmt2(net)}</td>
        <td class="text-end ${varPct!==null?(varPct>0?'text-success':(varPct<0?'text-danger':'text-secondary')):'text-secondary'}">${varPct!==null? fmt2(varPct)+'%':'—'}</td>`;
      tbody.appendChild(tr);
    });
    // Totais
    const foot = document.getElementById('selectionTotalsRow');
    if (foot){
      const set = (key, val, cls='')=>{
        const el = foot.querySelector(`[data-total="${key}"]`);
        if (el){ el.textContent = (key==='varpct' && val!==null) ? (fmt2(val)+'%') : fmt2(val); el.className = 'text-end '+cls; }
      };
      const tVarPct = tBuy>0 ? (tNet/tBuy)*100.0 : null;
      set('buy', tBuy);
      set('sell', tSell);
      set('fee', tFee);
      set('net', tNet, tNet>0?'text-success':(tNet<0?'text-danger':'text-secondary'));
      set('varpct', tVarPct, (tVarPct!==null && tVarPct>0)?'text-success':((tVarPct!==null && tVarPct<0)?'text-danger':'text-secondary'));
    }
  }
  // Ações no header do card de seleção
  const selMarkAll = document.getElementById('selMarkAll');
  const selUnmarkAll = document.getElementById('selUnmarkAll');
  if (selMarkAll){ selMarkAll.addEventListener('click', function(){ document.querySelectorAll('input.evt-mark').forEach(cb=> cb.checked = true); rebuildSelectionSummary(); }); }
  if (selUnmarkAll){ selUnmarkAll.addEventListener('click', function(){ document.querySelectorAll('input.evt-mark').forEach(cb=> cb.checked = false); rebuildSelectionSummary(); }); }
  const selExportCsv = document.getElementById('selExportCsv');
  if (selExportCsv){ selExportCsv.addEventListener('click', function(){
    const map = computeSelectionMap();
    const symbols = Object.keys(map).sort((a,b)=> a.localeCompare(b,'pt-BR',{sensitivity:'base', numeric:true}));
    let csv = '\ufeffsymbol;buy;sell;fee;net;variation_pct\n';
    let tBuy=0,tSell=0,tFee=0,tNet=0;
    symbols.forEach(sym=>{
      const s = map[sym];
      const buy = +(s.buy||0); const sell= +(s.sell||0); const fee= +(s.fee||0); const net = sell-buy; const varPct = buy>0? (net/buy)*100.0 : '';
      csv += [sym, buy.toFixed(6), sell.toFixed(6), fee.toFixed(6), net.toFixed(6), (varPct===''?'':Number(varPct).toFixed(6))].join(';')+'\n';
      tBuy+=buy; tSell+=sell; tFee+=fee; tNet+=net;
    });
    const tVarPct = tBuy>0? (tNet/tBuy)*100.0 : '';
    csv += ['TOTAL', tBuy.toFixed(6), tSell.toFixed(6), tFee.toFixed(6), tNet.toFixed(6), (tVarPct===''?'':Number(tVarPct).toFixed(6))].join(';');
    const blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    const now = new Date();
    const ts = now.getFullYear().toString().padStart(4,'0') + String(now.getMonth()+1).padStart(2,'0') + String(now.getDate()).padStart(2,'0') + '_' + String(now.getHours()).padStart(2,'0') + String(now.getMinutes()).padStart(2,'0') + String(now.getSeconds()).padStart(2,'0');
    a.href = url; a.download = 'cash_selection_'+ts+'.csv'; document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(url);
  }); }

  // Marcar/Desmarcar por Data (YYYY-MM-DD)
  const selDateYmd = document.getElementById('selDateYmd');
  const selMarkByDate = document.getElementById('selMarkByDate');
  const selUnmarkByDate = document.getElementById('selUnmarkByDate');
  function setByDate(ymd, checked){
    if (!ymd) return;
    document.querySelectorAll('input.evt-mark').forEach(cb => {
      const d = cb.getAttribute('data-ymd') || '';
      if (d === ymd){ cb.checked = !!checked; }
    });
    if (toggleGroupSelection && toggleGroupSelection.checked) rebuildSelectionSummary();
  }
  if (selMarkByDate){ selMarkByDate.addEventListener('click', function(){ setByDate(selDateYmd?.value || '', true); }); }
  if (selUnmarkByDate){ selUnmarkByDate.addEventListener('click', function(){ setByDate(selDateYmd?.value || '', false); }); }
  const LS_KEY = 'cash.byAsset.marks';
  const LS_SHOW = 'cash.byAsset.show';
  function loadMarks(){
    try { return JSON.parse(localStorage.getItem(LS_KEY) || '{}'); } catch(e){ return {}; }
  }
  function saveMarks(m){ localStorage.setItem(LS_KEY, JSON.stringify(m||{})); }
  function loadShow(){ return localStorage.getItem(LS_SHOW) || 'all'; }
  function saveShow(v){ localStorage.setItem(LS_SHOW, v); }

  const marks = loadMarks();
  const showSel = document.getElementById('byAssetShow');
  const nf = (window.Intl && Intl.NumberFormat) ? new Intl.NumberFormat('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2}) : null;
  function fmt(n){ if(n===null || isNaN(n)) return '—'; return nf? nf.format(n) : (Number(n).toFixed(2).replace('.',',')); }

  function computeTotals(){
    let tBuy=0, tSell=0, tFee=0, tNet=0, tVarPct=null;
    const rows = document.querySelectorAll('tr[data-sym]');
    rows.forEach(tr => {
      if (tr.style.display === 'none') return; // hidden by filter
      const buy = parseFloat(tr.getAttribute('data-buy')||'0') || 0;
      const sell = parseFloat(tr.getAttribute('data-sell')||'0') || 0;
      const fee = parseFloat(tr.getAttribute('data-fee')||'0') || 0;
      const net = parseFloat(tr.getAttribute('data-net')||'0') || 0;
      tBuy += buy; tSell += sell; tFee += fee; tNet += net;
    });
    const varPct = tBuy>0 ? (tNet/tBuy)*100.0 : null;
    const row = document.getElementById('byAssetTotalsRow');
    if (row){
      const set = (key, val, cls='')=>{
        const el = row.querySelector(`[data-total="${key}"]`);
        if (el){ el.textContent = (key==='varpct' && val!==null) ? (fmt(val)+'%') : fmt(val); el.className = 'text-end '+cls; }
      };
      set('buy', tBuy);
      set('sell', tSell);
      set('fee', tFee);
      set('net', tNet, tNet>0?'text-success':(tNet<0?'text-danger':'text-secondary'));
      set('varpct', varPct, (varPct!==null && varPct>0)?'text-success':((varPct!==null && varPct<0)?'text-danger':'text-secondary'));
    }
  }
  if (showSel) {
    showSel.value = loadShow();
    showSel.addEventListener('change', function(){ saveShow(showSel.value); applyFilter(); });
  }
  const btnMarkAll = document.getElementById('byAssetMarkAll');
  const btnUnmarkAll = document.getElementById('byAssetUnmarkAll');
  const exportLink = document.getElementById('byAssetExportCsv');
  function setVisibleMarks(value){
    document.querySelectorAll('tr[data-sym]').forEach(tr => {
      if (tr.style.display === 'none') return; // only visible
      const sym = tr.getAttribute('data-sym');
      const cb = tr.querySelector('input.asset-mark');
      if (cb){ cb.checked = !!value; }
      if (value) { marks[sym] = true; } else { delete marks[sym]; }
    });
    saveMarks(marks);
    applyFilter();
  }
  if (btnMarkAll){ btnMarkAll.addEventListener('click', ()=> setVisibleMarks(true)); }
  if (btnUnmarkAll){ btnUnmarkAll.addEventListener('click', ()=> setVisibleMarks(false)); }

  // Exportar CSV "como estiver": exporta exatamente as linhas VISÍVEIS (respeita 'Mostrar')
  function buildExportSymbols(){
    const syms = [];
    document.querySelectorAll('tr[data-sym]').forEach(tr => {
      if (tr.style.display === 'none') return;
      const sym = tr.getAttribute('data-sym');
      syms.push(sym);
    });
    return syms;
  }
  function appendQuery(url, key, value){
    const u = new URL(url, window.location.origin);
    if (value !== undefined && value !== null && value !== '') {
      u.searchParams.set(key, value);
    }
    return u.toString();
  }
  if (exportLink){
    exportLink.addEventListener('click', function(ev){
      try{
        ev.preventDefault();
        // Monta CSV diretamente do que está VISÍVEL na tabela
        const rows = [];
        document.querySelectorAll('tr[data-sym]').forEach(tr => {
          if (tr.style.display === 'none') return;
          const sym = tr.getAttribute('data-sym');
          const buy = parseFloat(tr.getAttribute('data-buy')||'0') || 0;
          const sell = parseFloat(tr.getAttribute('data-sell')||'0') || 0;
          const fee = parseFloat(tr.getAttribute('data-fee')||'0') || 0;
          const net = parseFloat(tr.getAttribute('data-net')||'0') || 0;
          const varpctAttr = tr.getAttribute('data-varpct');
          const varpct = (varpctAttr!==null && varpctAttr!=='') ? parseFloat(varpctAttr) : (buy>0 ? (net/buy)*100.0 : null);
          rows.push({sym, buy, sell, fee, net, varpct});
        });
        // Totais
        let tBuy=0, tSell=0, tFee=0, tNet=0;
        rows.forEach(r=>{ tBuy+=r.buy; tSell+=r.sell; tFee+=r.fee; tNet+=r.net; });
        const tVarPct = tBuy>0 ? (tNet/tBuy)*100.0 : null;
        const nf6 = (n)=> (n===null||isNaN(n))? '': Number(n).toFixed(6);
        let csv = '\ufeffsymbol;buy;sell;fee;net;variation_pct\n';
        rows.forEach(r=>{
          csv += [r.sym, nf6(r.buy), nf6(r.sell), nf6(r.fee), nf6(r.net), nf6(r.varpct)].join(';') + '\n';
        });
        csv += ['TOTAL', nf6(tBuy), nf6(tSell), nf6(tFee), nf6(tNet), nf6(tVarPct)].join(';');
        const blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        const now = new Date();
        const ts = now.getFullYear().toString().padStart(4,'0') +
                   String(now.getMonth()+1).padStart(2,'0') +
                   String(now.getDate()).padStart(2,'0') + '_' +
                   String(now.getHours()).padStart(2,'0') +
                   String(now.getMinutes()).padStart(2,'0') +
                   String(now.getSeconds()).padStart(2,'0');
        a.href = url;
        a.download = 'cash_by_asset_'+ts+'.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
      } catch(e){
        // fallback: exporta via servidor com os símbolos visíveis
        try{
          const syms = buildExportSymbols();
          const nextUrl = appendQuery(exportLink.href, 'symbols', syms.join(','));
          window.location.href = nextUrl;
        }catch(_e){ /* deixa link normal seguir */ }
      }
    });
  }

  // init checkboxes
  document.querySelectorAll('input.asset-mark[data-sym]').forEach(cb => {
    const sym = cb.getAttribute('data-sym');
    cb.checked = !!marks[sym];
    cb.addEventListener('change', function(){
      if (cb.checked) { marks[sym] = true; } else { delete marks[sym]; }
      saveMarks(marks);
      applyFilter();
    });
  });

  function applyFilter(){
    const mode = (showSel && showSel.value) ? showSel.value : 'all';
    document.querySelectorAll('tr[data-sym]').forEach(tr => {
      const sym = tr.getAttribute('data-sym');
      const marked = !!marks[sym];
      let visible = true;
      if (mode === 'marked') visible = marked;
      else if (mode === 'unmarked') visible = !marked;
      tr.style.display = visible ? '' : 'none';
    });
    computeTotals();
  }

  applyFilter();
  computeTotals();
});
</script>
@endpush
