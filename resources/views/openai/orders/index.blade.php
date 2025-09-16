@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h4 mb-0 d-flex align-items-center gap-2">
      Ordens
      <x-market.badge storageKey="orders.localBadge.visible" idPrefix="orders" />
      <div class="form-check form-switch ms-2 small" title="Quando ligado, salva automaticamente a última cotação após consultar.">
        <input class="form-check-input" type="checkbox" id="autosave_quotes_toggle">
        <label class="form-check-label" for="autosave_quotes_toggle">AUTO SALVAR COTAÇAO CONSULTADA</label>
      </div>
    </h1>
    <div class="d-flex gap-2">
      <a href="{{ route('openai.records.index') }}" class="btn btn-outline-secondary">← Registros</a>
      <a href="{{ route('openai.chat') }}" class="btn btn-outline-dark">Chat</a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success py-2">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger py-2">{{ session('error') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger py-2">
      {{ $errors->first() }}
    </div>
  @endif

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form class="row g-2 align-items-end" method="GET" action="{{ route('openai.orders.index') }}">
        <div class="col-sm-4 col-md-3">
          <label class="form-label small mb-1">Conversa</label>
          <select name="chat_id" class="form-select form-select-sm">
            <option value="">Todas</option>
            @foreach($chats as $c)
              <option value="{{ $c->id }}" {{ ($chatId ?? 0) == $c->id ? 'selected' : '' }}>{{ $c->title }} @if($c->code) ({{ $c->code }}) @endif</option>
            @endforeach
          </select>
          @php $selChat = ($chatId ?? 0) ? $chats->firstWhere('id', $chatId) : null; @endphp
          @if($selChat && $selChat->code)
            <div class="mt-2 d-flex align-items-center gap-2">
              <span class="badge bg-dark">{{ $selChat->code }}</span>
              <button class="btn btn-sm btn-outline-info" type="button" id="mdq_btn" data-symbol="{{ $selChat->code }}">Cotação</button>
              <span id="mdq_result" class="small text-muted" aria-live="polite"></span>
            </div>
          @endif
        </div>
        <div class="col-sm-3 col-md-3">
          <label class="form-label small mb-1">Código</label>
          <input type="text" name="code" value="{{ $code ?? '' }}" class="form-control form-control-sm" placeholder="Ticker/código">
        </div>
        <div class="col-sm-3 col-md-2">
          <label class="form-label small mb-1">Tipo</label>
          <select name="type" class="form-select form-select-sm">
            <option value="">Todos</option>
            <option value="compra" {{ ($type ?? '')==='compra' ? 'selected' : '' }}>Compra</option>
            <option value="venda"  {{ ($type ?? '')==='venda'  ? 'selected' : '' }}>Venda</option>
          </select>
        </div>
        <div class="col-sm-2 col-md-2 d-grid">
          <button class="btn btn-sm btn-outline-primary">Filtrar</button>
        </div>
        @if(request()->hasAny(['chat_id','code','type']) && (request('chat_id')||request('code')||request('type')))
          <div class="col-sm-2 col-md-2">
            <a href="{{ route('openai.orders.index') }}" class="btn btn-sm btn-outline-dark w-100">Limpar</a>
          </div>
        @endif
      </form>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-sm table-bordered align-middle">
      <thead class="table-dark">
        @php
          $flip = fn($c) => (($sort ?? 'created_at') === $c && ($dir ?? 'desc')==='asc') ? 'desc' : 'asc';
          $icon = function($c) use ($sort,$dir){
            if(($sort ?? 'created_at') !== $c) return '↕';
            return (($dir ?? 'desc')==='asc') ? '↑' : '↓';
          };
          $base = array_filter([
            'chat_id' => $chatId ?: null,
            'code' => $code ?: null,
            'type' => ($type ?? null) && in_array($type,['compra','venda'],true) ? $type : null,
          ]);
        @endphp
        <tr>
          <th style="width:18%" class="{{ (($sort ?? 'created_at') === 'code') ? 'active-sort' : '' }}">
            <a class="text-decoration-none text-light" href="{{ route('openai.orders.index', array_merge($base,['sort'=>'code','dir'=>$flip('code')])) }}">Código {{ $icon('code') }}</a>
          </th>
          <th style="width:12%" class="{{ (($sort ?? 'created_at') === 'type') ? 'active-sort' : '' }}">
            <a class="text-decoration-none text-light" href="{{ route('openai.orders.index', array_merge($base,['sort'=>'type','dir'=>$flip('type')])) }}">Tipo {{ $icon('type') }}</a>
          </th>
          <th style="width:14%" class="text-end {{ (($sort ?? 'created_at') === 'quantity') ? 'active-sort' : '' }}">
            <a class="text-decoration-none text-light" href="{{ route('openai.orders.index', array_merge($base,['sort'=>'quantity','dir'=>$flip('quantity')])) }}">Quantidade {{ $icon('quantity') }}</a>
          </th>
          <th style="width:14%" class="text-end {{ (($sort ?? 'created_at') === 'value') ? 'active-sort' : '' }}">
            <a class="text-decoration-none text-light" href="{{ route('openai.orders.index', array_merge($base,['sort'=>'value','dir'=>$flip('value')])) }}">Valor {{ $icon('value') }}</a>
          </th>
          <th style="width:18%" class="{{ (($sort ?? 'created_at') === 'account') ? 'active-sort' : '' }}">
            <a class="text-decoration-none text-light" href="{{ route('openai.orders.index', array_merge($base,['sort'=>'account','dir'=>$flip('account')])) }}">Conta {{ $icon('account') }}</a>
          </th>
          <th class="{{ (($sort ?? 'created_at') === 'chat') ? 'active-sort' : '' }}">
            <a class="text-decoration-none text-light" href="{{ route('openai.orders.index', array_merge($base,['sort'=>'chat','dir'=>$flip('chat')])) }}">Conversa {{ $icon('chat') }}</a>
          </th>
          <th style="width:12%">Cotação</th>
          <th style="width:18%" class="{{ (($sort ?? 'created_at') === 'created_at') ? 'active-sort' : '' }}">
            <a class="text-decoration-none text-light" href="{{ route('openai.orders.index', array_merge($base,['sort'=>'created_at','dir'=>$flip('created_at')])) }}">Criado em {{ $icon('created_at') }}</a>
          </th>
          <th style="width:16%" class="text-center">Ações</th>
        </tr>
      </thead>
      <tbody>
        @forelse($orders as $o)
          <tr>
            <td><span class="badge bg-dark">{{ $o->code }}</span></td>
            <td>
              @php $cls = $o->type === 'compra' ? 'success' : 'danger'; @endphp
              <span class="badge bg-{{ $cls }}">{{ ucfirst($o->type) }}</span>
            </td>
            <td class="text-end">{{ rtrim(rtrim(number_format((float)$o->quantity, 6, ',', '.'), '0'), ',') }}</td>
            <td class="text-end">@if(!is_null($o->value)) {{ number_format((float)$o->value, 2, ',', '.') }} @else — @endif</td>
            <td>
              @php
                $accName = $o->derived_account_name ?? null;
                $accBroker = $o->derived_account_broker ?? null;
                if(!$accName && isset($firstUserAccount)){
                  $accName = $firstUserAccount->account_name;
                  $accBroker = $firstUserAccount->broker;
                }
              @endphp
              @if($accName)
                <span class="text-primary" title="Conta de investimento">{{ $accName }}</span>
                @if($accBroker)
                  <small class="text-danger">— {{ $accBroker }}</small>
                @endif
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
            <td>{{ $o->chat?->title ?? '—' }}</td>
            <td>
              @if(!is_null($o->quote_value))
                <span class="text-success" id="cell_quote_saved_{{ $o->id }}">{{ number_format((float)$o->quote_value, 6, ',', '.') }}</span>
                @if($o->quote_updated_at)
                  <small class="text-muted" id="cell_quote_saved_at_{{ $o->id }}" title="{{ $o->quote_updated_at->timezone(config('app.timezone'))->format('d/m/Y H:i:s') }}">({{ $o->quote_updated_at->timezone(config('app.timezone'))->diffForHumans() }})</small>
                @endif
              @else
                <span class="text-muted">—</span>
              @endif
              <div id="cell_quote_var_{{ $o->id }}" class="small mt-1"></div>
            </td>
            <td>
              @php $cdt = $o->created_at ? $o->created_at->timezone(config('app.timezone')) : null; @endphp
              @if($cdt)
                <span title="{{ $cdt->format('d/m/Y H:i:s') }}">{{ $cdt->format('d/m/Y H:i') }}</span>
              @else — @endif
            </td>
            <td class="text-center">
              <button type="button" class="btn btn-sm btn-outline-info me-1 row-quote-btn" data-symbol="{{ $o->code }}" data-order-id="{{ $o->id }}" data-saved-quote="{{ is_null($o->quote_value) ? '' : (float)$o->quote_value }}">Cotação</button>
              <a href="{{ route('openai.records.index', ['chat_id' => $o->chat_id]) }}" class="btn btn-sm btn-outline-secondary">Registros</a>
              <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editOrderModal_{{ $o->id }}">Editar</button>
              <form action="{{ route('openai.records.codeOrder.destroy', $o->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir esta ordem?');">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">Excluir</button>
              </form>
              <div class="mt-1">
                <span id="row_quote_result_{{ $o->id }}" class="small text-muted" aria-live="polite"></span>
                <form id="row_save_quote_{{ $o->id }}" action="{{ route('openai.records.codeOrder.refreshQuote', $o->id) }}" method="POST" class="mt-1 d-none">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-success">Salvar Cotação</button>
                </form>
              </div>
            </td>
          </tr>
          @include('openai.partials.code_order_modal', ['order' => $o])
        @empty
          <tr><td colspan="8" class="text-center text-muted">Nenhuma ordem.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-3">
    {{ $orders->links() }}
  </div>
</div>
@endsection

@push('scripts')
<!-- Componente x-market.badge já inclui script de status/toggle -->
<script>
document.addEventListener('DOMContentLoaded', function(){
  const editId = @json(session('edit_order_id'));
  if (editId) {
    const modalEl = document.getElementById('editOrderModal_' + editId);
    if (modalEl && window.bootstrap) {
      const modal = new bootstrap.Modal(modalEl);
      modal.show();
    } else if (modalEl) { // fallback
      modalEl.classList.add('show');
      modalEl.style.display = 'block';
    }
  }
});
</script>
<script>
// Botão de cotação (como nas outras rotinas)
document.addEventListener('DOMContentLoaded', function(){
  // Toggle de autossalvar cotação (persistência em localStorage)
  const autoKey = 'orders.autosaveQuotes';
  const autoToggle = document.getElementById('autosave_quotes_toggle');
  let autoSaveEnabled = (localStorage.getItem(autoKey) === '1');
  if (autoToggle) {
    autoToggle.checked = autoSaveEnabled;
    autoToggle.addEventListener('change', function(){
      autoSaveEnabled = !!autoToggle.checked;
      localStorage.setItem(autoKey, autoSaveEnabled ? '1' : '0');
      // também expõe globalmente para handlers já anexados
      window.ordersAutoSaveQuotes = autoSaveEnabled;
    });
  }
  // expõe global para ser lido mais abaixo
  window.ordersAutoSaveQuotes = autoSaveEnabled;

  const btn = document.getElementById('mdq_btn');
  const out = document.getElementById('mdq_result');
  const symbol = btn ? (btn.getAttribute('data-symbol') || '').trim() : '';

  async function fetchQuote(){
    if (!btn || !out || !symbol) return;
    out.textContent = 'Consultando…';
    try {
      const url = '{{ route('api.market.quote') }}' + '?symbol=' + encodeURIComponent(symbol);
      const resp = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      if (!resp.ok) throw new Error('HTTP ' + resp.status);
      const data = await resp.json();
      if (data && typeof data.price !== 'undefined' && data.price !== null) {
        const display = Number(data.price).toLocaleString('en-US', { style: 'currency', currency: (data.currency||'USD') });
        out.innerHTML = `<span class="text-success">${display}</span>` + (data.updated_at ? ` <small class="text-muted">(${data.updated_at})</small>` : '');
        // Se houver modal aberto, preenche campo Valor (pt-BR) se vazio
        const openModal = document.querySelector('.modal.show');
        if (openModal) {
          const valueInput = openModal.querySelector('input[name="value"]');
          if (valueInput && (!valueInput.value || valueInput.value.trim() === '')) {
            valueInput.value = Number(data.price).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 6 });
          }
        }
      } else {
        out.innerHTML = '<span class="text-warning">Sem dados</span>';
      }
    } catch(_e) {
      out.innerHTML = '<span class="text-danger">Falha ao consultar</span>';
    }
  }

  if (btn) {
    btn.addEventListener('click', fetchQuote);
    // dispara automaticamente ao carregar
    setTimeout(fetchQuote, 50);
  }

  // Botões de cotação por linha
  const rowBtns = document.querySelectorAll('.row-quote-btn');
  rowBtns.forEach(function(rb){
    rb.addEventListener('click', async function(){
      const symbol = (rb.getAttribute('data-symbol') || '').trim();
      const id = rb.getAttribute('data-order-id');
      const out = document.getElementById('row_quote_result_' + id);
      const saveForm = document.getElementById('row_save_quote_' + id);
      const cellVar = document.getElementById('cell_quote_var_' + id);
      const savedQuoteAttr = rb.getAttribute('data-saved-quote');
      const savedQuote = savedQuoteAttr ? parseFloat(savedQuoteAttr) : null;
      if (!symbol || !out) return;
      out.textContent = 'Consultando…';
      try {
        const url = '{{ route('api.market.quote') }}' + '?symbol=' + encodeURIComponent(symbol);
        const resp = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!resp.ok) throw new Error('HTTP ' + resp.status);
        const data = await resp.json();
        if (data && typeof data.price !== 'undefined' && data.price !== null) {
          const display = Number(data.price).toLocaleString('en-US', { style: 'currency', currency: (data.currency||'USD') });
          out.innerHTML = `<span class="text-success">${display}</span>` + (data.updated_at ? ` <small class=\"text-muted\">(${data.updated_at})</small>` : '');
          if (saveForm) saveForm.classList.remove('d-none');
          // Autossalvar opcional: só executa se toggle estiver ligado
          if (saveForm && window.ordersAutoSaveQuotes) {
            try {
              const token = saveForm.querySelector('input[name="_token"]').value;
              const respSave = await fetch(saveForm.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token },
              });
              if (respSave.ok) {
                // Atualiza UI como se o usuário tivesse clicado para salvar
                const saved = document.getElementById('cell_quote_saved_' + id);
                if (saved) { saved.textContent = Number(data.price).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 6 }); }
                let savedAt = document.getElementById('cell_quote_saved_at_' + id);
                const now = new Date();
                const pad = n => String(n).padStart(2,'0');
                const formatted = `${pad(now.getDate())}/${pad(now.getMonth()+1)}/${now.getFullYear()} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
                if (!savedAt) {
                  const savedEl = saved || document.getElementById('cell_quote_saved_' + id);
                  if (savedEl && savedEl.parentElement) {
                    const small = document.createElement('small');
                    small.className = 'text-muted';
                    small.id = 'cell_quote_saved_at_' + id;
                    small.textContent = `(${formatted})`;
                    small.title = formatted;
                    savedEl.insertAdjacentElement('afterend', small);
                  }
                } else {
                  savedAt.textContent = `(${formatted})`;
                  savedAt.title = formatted;
                }
                // Atualiza referência para futuras variações
                const btn = document.querySelector(`.row-quote-btn[data-order-id="${id}"]`);
                if (btn) { btn.setAttribute('data-saved-quote', String(Number(data.price))); }
                // Oculta o botão manual após autosave
                saveForm.classList.add('d-none');
              }
            } catch(_e) {
              // Se falhar autosave, mantém o botão visível para ação manual
            }
          }
          // Calcula variação percentual se houver valor salvo
          if (cellVar) {
            if (savedQuote !== null && !isNaN(savedQuote) && Number(data.price) > 0) {
              const pct = ((Number(data.price) - savedQuote) / savedQuote) * 100;
              const sign = pct >= 0 ? '+' : '';
              const cls = pct >= 0 ? 'text-success' : 'text-danger';
              cellVar.innerHTML = `<span class=\"${cls}\">${sign}${pct.toFixed(2)}%</span>`;
            } else {
              cellVar.innerHTML = '';
            }
          }
          // Preenche o input de Valor no modal correspondente, se aberto
          const modalEl = document.getElementById('editOrderModal_' + id);
          if (modalEl && modalEl.classList.contains('show')) {
            const valueInput = modalEl.querySelector('input[name="value"]');
            if (valueInput && (!valueInput.value || valueInput.value.trim() === '')) {
              valueInput.value = Number(data.price).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 6 });
            }
          }
        } else {
          out.innerHTML = '<span class="text-warning">Sem dados</span>';
          if (saveForm) saveForm.classList.add('d-none');
          if (cellVar) cellVar.innerHTML = '';
        }
      } catch(_e) {
        out.innerHTML = '<span class="text-danger">Falha ao consultar</span>';
        if (saveForm) saveForm.classList.add('d-none');
        if (cellVar) cellVar.innerHTML = '';
      }
    });
  });

  // Intercepta 'Salvar Cotação' para atualizar UI sem recarregar
  document.querySelectorAll('form[id^="row_save_quote_"]').forEach(function(f){
    f.addEventListener('submit', async function(ev){
      ev.preventDefault();
      const form = ev.currentTarget;
      const id = form.id.replace('row_save_quote_', '');
      try {
        const resp = await fetch(form.action, {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value },
        });
        if (!resp.ok) throw new Error('HTTP ' + resp.status);
        // Atualiza UI: pega o último valor mostrado em out (ou mantém salvo)
        const out = document.getElementById('row_quote_result_' + id);
        // extrai número do out (em en-US currency) preservando ponto decimal
        let lastPrice = null;
        if (out && out.textContent) {
          // remove tudo que não for dígito, ponto, vírgula ou sinal
          let raw = out.textContent.replace(/[^0-9,\.\-]/g, '');
          if (raw) {
            if (raw.indexOf('.') !== -1) {
              // Formato en-US provável: 1,234.56 -> remove vírgulas (milhar), mantém ponto como decimal
              raw = raw.replace(/,/g, '');
              lastPrice = parseFloat(raw);
            } else if (raw.indexOf(',') !== -1) {
              // Formato pt-BR provável: 1.234,56 -> troca vírgula por ponto (e remove pontos se houver)
              raw = raw.replace(/\./g, '').replace(/,/g, '.');
              lastPrice = parseFloat(raw);
            } else {
              lastPrice = parseFloat(raw);
            }
          }
        }
        // Atualiza valores salvos na célula
        if (lastPrice !== null && !isNaN(lastPrice)) {
          const saved = document.getElementById('cell_quote_saved_' + id);
          if (saved) { saved.textContent = lastPrice.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 6 }); }
          const savedAt = document.getElementById('cell_quote_saved_at_' + id);
          {
            const now = new Date();
            const pad = n => String(n).padStart(2,'0');
            const formatted = `${pad(now.getDate())}/${pad(now.getMonth()+1)}/${now.getFullYear()} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
            let savedAt = document.getElementById('cell_quote_saved_at_' + id);
            if (!savedAt) {
              // cria o elemento se não existir ainda
              const savedEl = document.getElementById('cell_quote_saved_' + id);
              if (savedEl && savedEl.parentElement) {
                const small = document.createElement('small');
                small.className = 'text-muted';
                small.id = 'cell_quote_saved_at_' + id;
                small.textContent = `(${formatted})`;
                small.title = formatted;
                savedEl.insertAdjacentElement('afterend', small);
              }
            } else {
              savedAt.textContent = `(${formatted})`;
              savedAt.title = formatted;
            }
          }
          // Atualiza o data-saved-quote no botão para futuras variações
          const btn = document.querySelector(`.row-quote-btn[data-order-id="${id}"]`);
          if (btn) { btn.setAttribute('data-saved-quote', String(lastPrice)); }
        }
        // Oculta o botão após salvar para evitar engano
        form.classList.add('d-none');
      } catch(_e) {
        // fallback: recarrega em caso de erro para exibir flash message
        form.submit();
      }
    });
  });
});
</script>
@endpush
