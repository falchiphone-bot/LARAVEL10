@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h5 mb-0">Ativos (sem repetição)</h1>
    <div class="d-flex gap-2">
      <a href="{{ route('openai.records.index') }}" class="btn btn-outline-secondary">← Registros</a>
    </div>
  </div>
  <div class="card shadow-sm mb-3">
    <div class="card-body">
  <form id="assets-filter-form" method="GET" action="{{ route('openai.records.assets') }}" class="row g-2 align-items-end">
        <div class="col-sm-3 col-md-2">
          <label class="form-label small mb-1">De</label>
          <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
        </div>
        <div class="col-sm-3 col-md-2">
          <label class="form-label small mb-1">Até</label>
          <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
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
        </div>
      </div>
      @if(request('baseline'))
        <div class="mb-2 small text-muted">
          • Linhas em destaque indicam que não há registro para a Data base (marcadas como “Sem base”).<br>
          • Selo “Base ok” indica que a data base foi encontrada para o ativo.
        </div>
      @endif
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
          <th style="width:10%" class="text-center">Cotação</th>
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
            <td>{{ $r->occurred_at?->format('d/m/Y H:i:s') ?? '—' }}</td>
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
            <td class="text-center" data-ref="{{ number_format((float)$r->amount, 6, '.', '') }}" data-occurred="{{ optional($r->occurred_at)->format('Y-m-d') }}" data-apply-url="{{ route('openai.records.applyQuote', $r) }}">
              @php $symbol = strtoupper(trim($r->chat?->code ?? '')); @endphp
              @if($symbol !== '')
                <div class="d-inline-flex align-items-center gap-2">
                  <button type="button" class="btn btn-sm btn-outline-primary btn-quote" data-symbol="{{ $symbol }}">
                    Consultar
                  </button>
                  <span class="quote-value text-nowrap" aria-live="polite"></span>
                  <small class="quote-time text-muted"></small>
                  <button type="button" class="btn btn-sm btn-outline-success d-none btn-apply-quote" title="Aplicar cotação ao valor">Aplicar</button>
                  <button type="button" class="btn btn-sm btn-outline-secondary d-none btn-create-from-quote" title="Criar novo registro com esta cotação">Novo registro</button>
                </div>
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
</div>
@endsection

@push('scripts')
<script>
  (function(){
    const endpoint = "{{ route('api.market.quote') }}";
  const endpointHist = "{{ route('api.market.historical') }}";
    let batchAbort = false;
    function formatPrice(value) {
      const n = Number(value);
      if (!isFinite(n)) return '';
      try {
        return n.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      } catch (e) {
        return n.toFixed(2);
      }
    }
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
        const url = endpoint + '?symbol=' + encodeURIComponent(symbol);
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
          // Comparar preço com valor de referência e pintar se diferente
          const refStr = container ? container.getAttribute('data-ref') : null;
          const refVal = refStr ? parseFloat(refStr) : NaN;
          const price = Number(data.price);
          if (isFinite(price) && isFinite(refVal)){
            const p2 = Math.round(price * 100) / 100;
            const r2 = Math.round(refVal * 100) / 100;
            if (p2 !== r2 && container){
              container.classList.add('table-danger');
              if (btnApply) { btnApply.classList.remove('d-none'); btnApply.disabled = false; btnApply.setAttribute('data-amount', String(price)); }
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
  // Feedback: esconder botão e reenviar filtro para atualizar a lista
  btn.classList.add('d-none');
  const f = document.getElementById('assets-filter-form');
  if (f) { (typeof f.requestSubmit === 'function') ? f.requestSubmit() : f.submit(); }
  else { window.location.reload(); }
      }catch(err){
        alert('Erro ao criar novo registro: ' + String(err.message || err));
      }finally{
        btn.disabled = false;
        btn.innerHTML = prev;
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
      for (let i=0;i<all.length;i++){
        if (batchAbort) { if(status){ status.textContent = `Interrompido (${i}/${all.length})`; } break; }
        const b = all[i];
        if(status){ status.textContent = `(${i+1}/${all.length})`; }
        try{
          await handleQuoteButton(b);
          ok++;
        }catch(e){ fail++; }
      }
      if(!batchAbort){ if(status){ status.textContent = `Concluído: ${ok} ok, ${fail} erro(s)`; } }
      btn.disabled = false;
      btn.innerHTML = prev;
      if (btnStop){ btnStop.classList.add('d-none'); btnStop.disabled = true; }
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
      btn.click();
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
      autoPrevTimer = setInterval(runAutoPrev, 400);
      setTimeout(runAutoPrev, 100);
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
    // Restaura estado ao carregar a página
    if (localStorage.getItem(AUTO_KEY) === '1') {
      // aguarda carregamento e então inicia automaticamente
      window.addEventListener('load', () => startAutoPrev());
    }

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
          throw new Error((data && (data.error || data.message)) || 'Sem dados para a data base');
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
              // CHECK ativo: recarrega para reavaliar a nova primeira linha
              if (window.__autoPrevActive && localStorage.getItem('assets.autoPrev.enabled') === '1') {
                const f = document.getElementById('assets-filter-form');
                if (f) { (typeof f.requestSubmit === 'function') ? f.requestSubmit() : f.submit(); }
                else { window.location.reload(); }
                // fallback: se por algum motivo não recarregar, libera e tenta de novo
                setTimeout(()=>{ if(window.__autoPrevActive){ window.__autoPrevBusy = false; runAutoPrev(); } }, 1500);
              } else {
                // Comportamento padrão: recarrega a lista aplicando filtros
                const f = document.getElementById('assets-filter-form');
                if (f) { (typeof f.requestSubmit === 'function') ? f.requestSubmit() : f.submit(); }
                else { window.location.reload(); }
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
                setTimeout(() => { if(!createBtn.disabled) createBtn.click(); }, 50);
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
        if (out) { out.textContent = 'Erro'; out.classList.add('text-danger'); }
        // em erro, libera para tentar novamente no CHECK
        try{
          if (window.__autoPrevActive) {
            window.__autoPrevBusy = false;
            setTimeout(runAutoPrev, 300);
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
@endpush
