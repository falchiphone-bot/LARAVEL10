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
      <form method="GET" action="{{ route('openai.records.assets') }}" class="row g-2 align-items-end">
        <div class="col-sm-3 col-md-2">
          <label class="form-label small mb-1">De</label>
          <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
        </div>
        <div class="col-sm-3 col-md-2">
          <label class="form-label small mb-1">Até</label>
          <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
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
        </div>
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
          <th style="width:10%" class="text-center">Cotação</th>
        </tr>
      </thead>
      <tbody>
        @forelse($records as $r)
          @php $code = trim($r->chat?->code ?? '') ?: trim($r->chat?->title ?? ''); @endphp
          <tr>
            <td>
              <strong>{{ $r->chat?->code ?? '—' }}</strong>
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
          <tr><td colspan="7" class="text-center text-muted">Nenhum ativo encontrado.</td></tr>
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
        // Feedback mínimo: esconder botão "Novo registro"
        btn.classList.add('d-none');
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
  })();
</script>
@endpush
