@extends('layouts.bootstrap5')
@section('content')
<div class="container-fluid">
  <h1 class="h5 mb-3">Posições por Ativo (Saldo e Preço Médio)</h1>
  <div class="card mb-3 shadow-sm">
    <div class="card-body">
      <form method="get" class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label small mb-1">Conta</label>
          <select name="account_id" class="form-select form-select-sm">
            <option value="">Todas</option>
            @foreach($accounts as $acc)
              <option value="{{ $acc->id }}" @selected($filter_account_id===$acc->id)>{{ $acc->account_name }} @if($acc->broker) ({{ $acc->broker }}) @endif</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">Cotações</label>
          <select name="quote_mode" class="form-select form-select-sm">
            <option value="api" @selected(($quote_mode ?? 'db')==='api')>API (Yahoo/Alpha/Stooq)</option>
            <option value="db" @selected(($quote_mode ?? 'db')==='db')>Banco de Dados (último)</option>
          </select>
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
          <label class="form-label small mb-1">Fonte</label>
          <select name="source" class="form-select form-select-sm">
            <option value="">Todas</option>
            @foreach($sources as $src)
              <option value="{{ $src }}" @selected($filter_source===$src)>{{ $src }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
          <button class="btn btn-sm btn-outline-primary flex-grow-1" type="submit">Filtrar</button>
          <a href="{{ route('cash.positions.summary') }}" class="btn btn-sm btn-outline-secondary">Limpar</a>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>Resumo</strong>
      <div class="d-flex align-items-center gap-2">
        @php
          $mode = $quote_mode ?? 'db';
          $qsDb = array_merge(request()->query(), ['quote_mode' => 'db']);
          $qsApi = array_merge(request()->query(), ['quote_mode' => 'api']);
          $hrefDb = route('cash.positions.summary', $qsDb) . '#gsc.tab=0';
          $hrefApi = route('cash.positions.summary', $qsApi) . '#gsc.tab=0';
        @endphp
        <div class="btn-group" role="group" aria-label="Alternar fonte de cotação">
          <a href="{{ $hrefDb }}" id="btnDbMode" data-mode="db" class="btn btn-sm {{ $mode==='db' ? 'btn-primary' : 'btn-outline-primary' }}">Banco de Dados</a>
          <a href="{{ $hrefApi }}" id="btnApiMode" data-mode="api" class="btn btn-sm {{ $mode==='api' ? 'btn-danger' : 'btn-outline-danger' }}">API</a>
          <button type="button" class="btn btn-sm btn-outline-secondary d-inline-block d-md-none" id="toggleQuoteModeBtn" data-mode="{{ $mode }}">Alternar</button>
        </div>
        <small class="text-muted">Ativos: {{ count($positions) }}</small>
        <a href="{{ route('cash.events.index', request()->query()) }}#gsc.tab=0" class="btn btn-sm btn-outline-secondary" title="Voltar para Eventos de Caixa">Voltar</a>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-sm table-striped align-middle mb-0" id="positionsTable">
        <thead class="table-light">
          <tr>
            <th>Ativo</th>
            <th class="text-end">Saldo (Qtd)</th>
            <th class="text-end">Saldo Médio (Preço)</th>
            <th class="text-end">Custo Total</th>
            <th class="text-end">Valor Atual</th>
            <th class="text-end">Novo Total</th>
            <th class="text-end">Variação</th>
          </tr>
        </thead>
        <tbody id="positionsTbody">
          @forelse($positions as $p)
            <tr data-symbol="{{ $p['symbol'] }}">
              <td class="col-symbol">
                @php
                  $assetStatsHref = route('asset-stats.index', ['symbol' => strtoupper($p['symbol'])]) . '#gsc.tab=0';
                @endphp
                <a href="{{ $assetStatsHref }}" target="_blank" rel="noopener noreferrer" title="Ver AssetDailyStat de {{ strtoupper($p['symbol']) }}">{{ strtoupper($p['symbol']) }}</a>
              </td>
              <td class="text-end col-qty">{{ number_format($p['qty'], 4, ',', '.') }}</td>
              <td class="text-end col-avg">{{ number_format($p['avg'], 4, ',', '.') }}</td>
              <td class="text-end col-cost">{{ number_format($p['cost'], 2, ',', '.') }}</td>
              <td class="text-end col-price">
                @if(isset($p['current_price']) && $p['current_price'] !== null)
                  {{ $p['currency'] ?? '' }} {{ number_format($p['current_price'], 4, ',', '.') }}
                  @if(!empty($p['quote_source']) || !empty($p['updated_at']))
                    @php $qs = strtoupper($p['quote_source'] ?? 'cot'); @endphp
                    <span class="badge bg-light text-muted border price-meta" title="{{ $p['quote_source'] ?? 'cotação' }} @if(!empty($p['updated_at'])) • {{ $p['updated_at'] }} @endif">
                      {{ $qs }}
                      @if(!empty($p['updated_at']))<span class="ms-1">{{ $p['updated_at'] }}</span>@endif
                    </span>
                  @endif
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
              <td class="text-end col-new-total">
                @if(isset($p['new_total']) && $p['new_total'] !== null)
                  {{ $p['currency'] ?? '' }} {{ number_format($p['new_total'], 2, ',', '.') }}
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
              <td class="text-end col-variation">
                @php
                  $var = $p['variation'] ?? null; $pct = $p['variation_pct'] ?? null;
                  $cls = $var === null ? '' : ($var > 0 ? 'text-success' : ($var < 0 ? 'text-danger' : ''));
                @endphp
                @if($var === null)
                  <span class="text-muted">—</span>
                @else
                  <span class="var-abs {{ $cls }}">{{ $p['currency'] ?? '' }} {{ number_format($var, 2, ',', '.') }}</span>
                  @if($pct !== null)
                    <small class="ms-1 var-pct {{ $cls }}">({{ number_format($pct*100, 2, ',', '.') }}%)</small>
                  @endif
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted">Sem dados no período.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if(!empty($variationTotals))
      <div class="card-body border-top" id="totalsBlock">
        <div class="mb-2 small text-muted">
          Modo de cotação: <strong id="modeText">{{ ($quote_mode ?? 'db') === 'db' ? 'Banco de Dados (último registro)' : 'API (Yahoo/Alpha/Stooq)' }}</strong>.
          @if(($quote_mode ?? 'db')==='db')
            Os valores podem estar defasados em relação ao mercado. Verifique a data indicada em cada ativo.
          @else
            As cotações são consultadas on-demand e salvas para hoje em AssetDailyStats.
          @endif
        </div>
        <div class="row g-3" id="totalsRows">
          @foreach($variationTotals as $cur => $vals)
            @php
              $pos = $vals['positive'] ?? 0.0;
              $neg = $vals['negative'] ?? 0.0; // já vem negativo
              $dif = $vals['difference'] ?? ($pos + $neg);
              $cls = $dif > 0 ? 'text-success' : ($dif < 0 ? 'text-danger' : 'text-muted');
            @endphp
            <div class="col-md-4">
              <div class="p-3 bg-light rounded border h-100">
                <div class="fw-bold mb-2">Totais por Moeda: {{ $cur }}</div>
                <div class="d-flex justify-content-between small">
                  <span>Saldo Positivo:</span>
                  <span>{{ $cur }} {{ number_format($pos, 2, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between small">
                  <span>Saldo Negativo:</span>
                  <span>{{ $cur }} {{ number_format($neg, 2, ',', '.') }}</span>
                </div>
                <hr class="my-2" />
                <div class="d-flex justify-content-between">
                  <span class="fw-semibold">Diferença:</span>
                  <span class="fw-semibold {{ $cls }}">{{ $cur }} {{ number_format($dif, 2, ',', '.') }}</span>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @endif
    <div class="card-footer small text-muted">
      Observação: o cálculo considera apenas eventos com textos reconhecíveis de compra/venda e usa média móvel simples.
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script type="application/json" id="positionsDbData">{!! json_encode($positionsDb ?? [], JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/json" id="positionsApiData">{!! json_encode($positionsApi ?? [], JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/json" id="totalsDbData">{!! json_encode($variationTotalsDb ?? [], JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/json" id="totalsApiData">{!! json_encode($variationTotalsApi ?? [], JSON_UNESCAPED_UNICODE) !!}</script>
<script>
(function(){
  // Pré-carrega datasets (db e api) vindos do backend via script JSON para evitar conflitos de lint
  function parseJsonEl(id, fallback){
    try{
      const el = document.getElementById(id); if(!el) return fallback;
      const txt = (el.textContent || '').trim();
      if(!txt) return fallback;
      return JSON.parse(txt);
    }catch(e){ return fallback; }
  }
  const positionsDb = parseJsonEl('positionsDbData', []);
  const positionsApi = parseJsonEl('positionsApiData', []);
  const totalsDb = parseJsonEl('totalsDbData', {});
  const totalsApi = parseJsonEl('totalsApiData', {});
  const initialMode = (document.getElementById('toggleQuoteModeBtn')?.getAttribute('data-mode')) || 'db';

  function formatNumber(n, decimals){
    if(n === null || n === undefined) return null;
    try { return Number(n).toLocaleString('pt-BR', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }); } catch(e){ return n; }
  }
  function renderPositions(mode){
    const data = (mode === 'db') ? positionsDb : positionsApi;
    const tbody = document.getElementById('positionsTbody');
    if(!tbody) return;
    // Mapa por símbolo para acesso rápido
    const map = {};
    data.forEach(p => { map[p.symbol] = p; });
    // Percorre linhas existentes e atualiza colunas dinâmicas
    tbody.querySelectorAll('tr[data-symbol]').forEach(tr => {
      const sym = tr.getAttribute('data-symbol');
      const p = map[sym];
      if(!p) return;
      const colPrice = tr.querySelector('.col-price');
      const colNew = tr.querySelector('.col-new-total');
      const colVar = tr.querySelector('.col-variation');
      if(colPrice){
        if(p.current_price !== null && p.current_price !== undefined){
          const priceTxt = `${p.currency || ''} ${formatNumber(p.current_price, 4)}`.trim();
          const meta = (p.quote_source || p.updated_at) ? `<span class="badge bg-light text-muted border price-meta" title="${p.quote_source || 'cotação'}${p.updated_at ? ' • '+p.updated_at : ''}">${(p.quote_source||'').toUpperCase()}${p.updated_at ? '<span class=\"ms-1\">'+p.updated_at+'</span>' : ''}</span>` : '';
          colPrice.innerHTML = priceTxt + ' ' + meta;
        } else {
          colPrice.innerHTML = '<span class="text-muted">—</span>';
        }
      }
      if(colNew){
        if(p.new_total !== null && p.new_total !== undefined){
          colNew.innerHTML = `${p.currency || ''} ${formatNumber(p.new_total, 2)}`.trim();
        } else {
          colNew.innerHTML = '<span class="text-muted">—</span>';
        }
      }
      if(colVar){
        if(p.variation === null || p.variation === undefined){
          colVar.innerHTML = '<span class="text-muted">—</span>';
        } else {
          const cls = p.variation > 0 ? 'text-success' : (p.variation < 0 ? 'text-danger' : '');
          const varAbs = `${p.currency || ''} ${formatNumber(p.variation, 2)}`.trim();
          const varPct = (p.variation_pct !== null && p.variation_pct !== undefined) ? ` <small class="ms-1 ${cls}">(${formatNumber(p.variation_pct*100, 2)}%)</small>` : '';
          colVar.innerHTML = `<span class="var-abs ${cls}">${varAbs}</span>${varPct}`;
        }
      }
    });
  }
  function renderTotals(mode){
    const rows = document.getElementById('totalsRows');
    if(!rows) return;
    const data = (mode === 'db') ? totalsDb : totalsApi;
    let html = '';
    Object.keys(data).forEach(cur => {
      const vals = data[cur] || {};
      const pos = Number(vals.positive || 0);
      const neg = Number(vals.negative || 0);
      const dif = (vals.difference !== undefined) ? Number(vals.difference) : (pos + neg);
      const cls = dif > 0 ? 'text-success' : (dif < 0 ? 'text-danger' : 'text-muted');
      html += `
      <div class="col-md-4">
        <div class="p-3 bg-light rounded border h-100">
          <div class="fw-bold mb-2">Totais por Moeda: ${cur}</div>
          <div class="d-flex justify-content-between small">
            <span>Saldo Positivo:</span>
            <span>${cur} ${formatNumber(pos, 2)}</span>
          </div>
          <div class="d-flex justify-content-between small">
            <span>Saldo Negativo:</span>
            <span>${cur} ${formatNumber(neg, 2)}</span>
          </div>
          <hr class="my-2" />
          <div class="d-flex justify-content-between">
            <span class="fw-semibold">Diferença:</span>
            <span class="fw-semibold ${cls}">${cur} ${formatNumber(dif, 2)}</span>
          </div>
        </div>
      </div>`;
    });
    rows.innerHTML = html || '<div class="text-muted">Sem totais.</div>';
  }
  // Sinaliza para outras telas (ex.: openai/records/assets) que ADStat foi atualizado via API
  function notifyAdStatUpdated(mode){
    try{
      if (mode !== 'api') return;
      const data = positionsApi || [];
      const valid = data.filter(p => p && p.symbol && (p.current_price !== null && p.current_price !== undefined));
      const symbols = Array.from(new Set(valid.map(p => String(p.symbol).toUpperCase())));
      if (symbols.length === 0) return;
      const payloadKey = 'adstat.updated.payload';
      let prev = null;
      try{ prev = JSON.parse(localStorage.getItem(payloadKey) || 'null'); }catch(_e){ prev = null; }
      const prevSyms = Array.isArray(prev?.symbols) ? prev.symbols : [];
      const merged = Array.from(new Set([...prevSyms, ...symbols]));
      // Monta itens por símbolo com preço/horário/moeda para evitar nova chamada em outras telas
      const items = Object.assign({}, (prev && typeof prev.items === 'object' && prev.items) ? prev.items : {});
      valid.forEach(p => {
        const k = String(p.symbol).toUpperCase();
        items[k] = { price: p.current_price, updated_at: p.updated_at || null, currency: p.currency || null };
      });
      const nowIso = new Date().toISOString();
      localStorage.setItem(payloadKey, JSON.stringify({ at: nowIso, symbols: merged, items }));
    }catch(_e){ /* noop */ }
  }
  function toggleMode(){
    const btn = document.getElementById('toggleQuoteModeBtn');
    if(!btn) return;
    const current = btn.getAttribute('data-mode') || 'db';
    const next = current === 'db' ? 'api' : 'db';
    btn.setAttribute('data-mode', next);
    btn.textContent = next === 'db' ? 'Alternar' : 'Alternar';
    renderPositions(next);
    renderTotals(next);
    notifyAdStatUpdated(next);
  const modeText = document.getElementById('modeText');
  if(modeText){ modeText.textContent = next === 'db' ? 'Banco de Dados (último registro)' : 'API (Yahoo/Alpha/Stooq)'; }
    // Alterna estilos dos botões principais
    const bDb = document.getElementById('btnDbMode');
    const bApi = document.getElementById('btnApiMode');
    if(bDb && bApi){
      if(next === 'db'){
        // DB em azul (primary)
        bDb.classList.remove('btn-outline-primary'); bDb.classList.add('btn-primary');
        // API em vermelho contornado
        bApi.classList.remove('btn-danger'); bApi.classList.add('btn-outline-danger');
      } else {
        // API em vermelho sólido
        bApi.classList.remove('btn-outline-danger'); bApi.classList.add('btn-danger');
        // DB em azul contornado
        bDb.classList.remove('btn-primary'); bDb.classList.add('btn-outline-primary');
      }
    }
  }
  // Inicializa e conecta evento
  document.addEventListener('DOMContentLoaded', function(){
    // Render garante consistência ao entrar
    renderPositions(initialMode);
    renderTotals(initialMode);
    notifyAdStatUpdated(initialMode);
    const btn = document.getElementById('toggleQuoteModeBtn');
    if(btn){ btn.addEventListener('click', toggleMode); }
    // Intercepta cliques nos botões DB/API para alternar client-side sem recarregar
    const bDb = document.getElementById('btnDbMode');
    const bApi = document.getElementById('btnApiMode');
    if(bDb){ bDb.addEventListener('click', function(ev){
      // Se já temos dataset DB carregado, alterna instantaneamente, senão segue link normal
      const hasDb = Array.isArray(positionsDb) && positionsDb.length > 0;
      if(hasDb){ ev.preventDefault(); document.getElementById('toggleQuoteModeBtn')?.setAttribute('data-mode','api'); toggleMode(); }
    }); }
    if(bApi){ bApi.addEventListener('click', function(ev){
      // Se já temos dataset API carregado, alterna instantaneamente, senão segue link normal
      const hasApi = Array.isArray(positionsApi) && positionsApi.length > 0;
      if(hasApi){ ev.preventDefault(); document.getElementById('toggleQuoteModeBtn')?.setAttribute('data-mode','db'); toggleMode(); }
    }); }
  });
})();
</script>
@endpush
