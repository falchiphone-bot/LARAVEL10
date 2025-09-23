@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4" id="records-index">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h4 mb-0 d-flex align-items-center gap-2">
      Registros de Conversas
      <x-market.badge storageKey="records.localBadge.visible" idPrefix="records" />
    </h1>
    <div class="d-flex gap-2">
      <a href="{{ route('openai.menu') }}" class="btn btn-outline-secondary">← Menu</a>
      <a href="{{ route('openai.chats', ['view'=>'table']) }}" class="btn btn-outline-primary">Ver Conversas</a>
  <a href="{{ route('openai.orders.index', array_filter(['chat_id'=>$chatId?:null])) }}" class="btn btn-outline-success">Ordens</a>
  <a href="{{ route('openai.investments.index') }}" class="btn btn-outline-info">Investimentos</a>
      <a href="{{ route('openai.chat') }}" class="btn btn-outline-dark">Chat</a>
  <a href="{{ route('openai.records.assets') }}" class="btn btn-outline-success">Ativos (únicos)</a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success py-2">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
  @endif

  <div class="card shadow-sm mb-4">
    <div class="card-body">
  <form class="row g-2 align-items-end" method="GET" action="{{ route('openai.records.index') }}">
        <div class="col-sm-4 col-md-3">
          <label class="form-label small mb-1">Conversa</label>
          <select name="chat_id" class="form-select form-select-sm">
            <option value="">Todas</option>
            @foreach($chats as $c)
              <option value="{{ $c->id }}" {{ ($chatId ?? 0) == $c->id ? 'selected' : '' }}>{{ $c->title }} @if($c->code) ({{ $c->code }}) @endif</option>
            @endforeach
          </select>
        </div>
        <div class="col-sm-4 col-md-3">
          <label class="form-label small mb-1">Ativo (código/título)</label>
          <input list="asset-options" name="asset" value="{{ $asset ?? '' }}" class="form-control form-control-sm" placeholder="Digite para buscar">
          @isset($assetOptions)
            <datalist id="asset-options">
              @foreach($assetOptions as $opt)
                <option value="{{ $opt['label'] }}">{{ $opt['text'] }}</option>
              @endforeach
            </datalist>
          @endisset
        </div>
        <div class="col-sm-4 col-md-3">
          <label class="form-label small mb-1">Conta de investimento</label>
          <select name="investment_account_id" class="form-select form-select-sm">
            <option value="" {{ ($invAccId ?? '') === '' ? 'selected' : '' }}>Todas</option>
            <option value="0" {{ ($invAccId ?? '') === '0' ? 'selected' : '' }}>Sem associação</option>
            @isset($investmentAccounts)
              @foreach($investmentAccounts as $ia)
                <option value="{{ $ia->id }}" {{ (string)($invAccId ?? '') === (string)$ia->id ? 'selected' : '' }}>
                  {{ $ia->account_name }} @if($ia->broker) — {{ $ia->broker }} @endif
                </option>
              @endforeach
            @endisset
          </select>
        </div>
        <div class="col-sm-4 col-md-3">
          <label class="form-label small mb-1">Status de compra</label>
          <select name="buy" class="form-select form-select-sm">
            <option value="" {{ (string)request('buy')==='' ? 'selected' : '' }}>Todos</option>
            <option value="compra" {{ request('buy')==='compra' ? 'selected' : '' }}>COMPRAR</option>
            <option value="nao" {{ request('buy')==='nao' ? 'selected' : '' }}>NÃO COMPRAR</option>
          </select>
        </div>
        <div class="col-sm-3 col-md-2">
          <label class="form-label small mb-1">De</label>
          <input type="date" name="from" value="{{ $from ?? '' }}" class="form-control form-control-sm">
        </div>
        <div class="col-sm-3 col-md-2">
          <label class="form-label small mb-1">Até</label>
          <input type="date" name="to" value="{{ $to ?? '' }}" class="form-control form-control-sm">
        </div>
        <div class="col-sm-2 col-md-2 d-grid gap-2">
          <button class="btn btn-sm btn-outline-primary" type="submit">Filtrar</button>
          @if(!($showAll ?? false))
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('openai.records.index', array_filter(['chat_id'=>$chatId?:null,'from'=>$from?:null,'to'=>$to?:null,'asset'=>($asset??'')!==''?$asset:null,'all'=>1,'investment_account_id'=>($invAccId!==null && $invAccId!=='')?$invAccId:null,'buy'=>request('buy')?:null])) }}">Todos</a>
          @else
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('openai.records.index', array_filter(['chat_id'=>$chatId?:null,'from'=>$from?:null,'to'=>$to?:null,'asset'=>($asset??'')!==''?$asset:null,'investment_account_id'=>($invAccId!==null && $invAccId!=='')?$invAccId:null,'buy'=>request('buy')?:null])) }}">Paginar</a>
          @endif
          @if(!empty($savedFilters))
            <a class="btn btn-sm btn-outline-warning" href="{{ route('openai.records.index', array_filter(['clear_saved'=>1,'chat_id'=>$chatId?:null,'from'=>$from?:null,'to'=>$to?:null,'asset'=>($asset??'')!==''?$asset:null, 'all'=>($showAll??false)?1:null,'investment_account_id'=>($invAccId!==null && $invAccId!=='')?$invAccId:null,'buy'=>request('buy')?:null])) }}" title="Remover filtro salvo">Limpar Salvo</a>
          @endif
        </div>
        @if(request()->hasAny(['chat_id','from','to','investment_account_id','asset','buy']) && (request('chat_id')||request('from')||request('to')||request('investment_account_id')!==null||request('asset')||request('buy')!==null))
          <div class="col-sm-2 col-md-2">
            <a href="{{ route('openai.records.index') }}" class="btn btn-sm btn-outline-dark w-100">Limpar</a>
          </div>
        @endif
      </form>
      @if(!empty($datesReapplied))
        <div class="mt-2 small text-muted">
          Datas reaplicadas automaticamente do último filtro. <a href="{{ route('openai.records.index', array_filter(['chat_id'=>$chatId?:null,'asset'=>($asset??'')!==''?$asset:null,'investment_account_id'=>($invAccId!==null && $invAccId!=='')?$invAccId:null,'buy'=>request('buy')?:null])) }}" class="text-decoration-none">Limpar datas</a>
        </div>
      @endif
    </div>
  </div>

  @push('scripts')
  <!-- Componente x-market.badge já inclui script de status/toggle -->
  @endpush

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h2 class="h6 mb-3">Novo Registro</h2>
      @if(($chatId ?? 0) > 0 && $selectedChat)
        <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
          <span class="small text-muted">Conversa atual:</span>
          <strong>{{ $selectedChat->title }}</strong>
          @if($selectedChat->code)
            <span class="badge bg-dark">{{ $selectedChat->code }}</span>
            <span id="mdq_box" class="small ms-2">
              <button class="btn btn-sm btn-outline-info" type="button" id="mdq_btn">Cotação</button>
              <span id="mdq_result" class="ms-2 text-muted" aria-live="polite"></span>
            </span>
          @endif
          <a href="{{ route('openai.chat.load', $selectedChat->id) }}" class="btn btn-sm btn-outline-danger" title="Ir para o chat desta conversa">Ir para Chat</a>
          <button type="button" id="btnOpenCodeOrder" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#codeOrderModal">
            Cadastrar Código (Compra/Venda)
          </button>
          <button type="button" id="btnToggleCanBuy" class="btn btn-sm btn-success text-white" aria-pressed="false" title="Alternar entre COMPRAR e NÃO COMPRAR">
            COMPRAR
          </button>
        </div>
      @endif
  <form id="newRecordForm" method="POST" action="{{ route('openai.records.store') }}" class="row g-2 align-items-end">
        @csrf
        <div class="col-sm-4 col-md-3">
          <label class="form-label small mb-1">Conversa *</label>
          <select name="chat_id" class="form-select form-select-sm" required>
            <option value="">Selecionar...</option>
            @foreach($chats as $c)
              <option value="{{ $c->id }}" {{ ($chatId ?? 0) == $c->id ? 'selected' : '' }}>{{ $c->title }} @if($c->code) ({{ $c->code }}) @endif</option>
            @endforeach
          </select>
        </div>
        <div class="col-sm-5 col-md-4">
          <label class="form-label small mb-1">Data/Hora * <span class="text-muted">dd/mm/aaaa HH:MM:SS</span></label>
          <div class="vstack gap-1 position-relative">
            <div class="input-group input-group-sm">
              <input type="text" id="new_date_br" class="form-control" placeholder="dd/mm/aaaa" value="{{ old('date_br') ? old('date_br') : now()->format('d/m/Y') }}" autocomplete="off" required style="max-width:140px;">
              <button class="btn btn-outline-secondary" type="button" id="new_btnCal" title="Calendário">📅</button>
              <input type="text" id="new_time_br" class="form-control" placeholder="HH:MM:SS" value="{{ old('time_br') ? old('time_br') : now()->format('H:i:s') }}" autocomplete="off" required style="max-width:120px;">
            </div>
            <input type="hidden" name="occurred_at" id="new_occurred_at_hidden" value="{{ old('occurred_at') ?? now()->format('d/m/Y H:i:s') }}">
            <div id="new_brCalendar" class="br-calendar shadow-sm border rounded p-2 bg-white" style="display:none; position:absolute; top:100%; left:0; z-index:50; width:220px;">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <button type="button" class="btn btn-sm btn-light" id="new_prevCal" aria-label="Mês anterior">«</button>
                <strong class="small" id="new_calMonthLabel"></strong>
                <button type="button" class="btn btn-sm btn-light" id="new_nextCal" aria-label="Próximo mês">»</button>
              </div>
              <table class="table table-sm table-bordered mb-0 align-middle text-center" style="font-size:.70rem;">
                <thead class="table-light">
                  <tr>
                    <th>Do</th><th>Se</th><th>Te</th><th>Qu</th><th>Qu</th><th>Se</th><th>Sa</th>
                  </tr>
                </thead>
                <tbody id="new_calBody"></tbody>
              </table>
              <div class="mt-2 text-end">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="new_closeCal">Fechar</button>
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-3 col-md-2">
          <label class="form-label small mb-1">Valor * <span class="text-muted">(R$)</span></label>
          <input type="text" inputmode="decimal" name="amount" class="form-control form-control-sm mask-money-br" required placeholder="0,00">
        </div>
        <div class="col-sm-5 col-md-3">
          <label class="form-label small mb-1">Conta de investimento (opcional)</label>
          <select name="investment_account_id" class="form-select form-select-sm">
            <option value="">— Não associar —</option>
            @isset($investmentAccounts)
              @php $prefAcc = old('investment_account_id') ?? ($invAccId !== null && $invAccId !== '' ? (string)$invAccId : ($lastInvestmentAccountId ?? '')); @endphp
              @foreach($investmentAccounts as $ia)
                <option value="{{ $ia->id }}" {{ (string)$prefAcc === (string)$ia->id ? 'selected' : '' }}>{{ $ia->account_name }} @if($ia->broker) — {{ $ia->broker }} @endif</option>
              @endforeach
            @endisset
          </select>
        </div>
        <div class="col-sm-5 col-md-3">
          <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" value="1" id="check_update" name="check_update">
            <label class="form-check-label small" for="check_update">
              CHECK: se já existir registro para esta conversa na mesma data, atualizar o valor em vez de criar um novo.
            </label>
          </div>
        </div>
        <div class="col-sm-2 col-md-2">
          <button type="submit" class="btn btn-sm btn-success w-100">Adicionar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal: Cadastrar ordem de código -->
  <div class="modal fade" id="codeOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Cadastrar Código</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <form method="POST" action="{{ route('openai.records.codeOrder.store') }}">
          @csrf
          <div class="modal-body vstack gap-2">
            <input type="hidden" name="chat_id" value="{{ $selectedChat->id ?? ($chatId ?? '') }}">
            <div>
              <label class="form-label small mb-1">Código</label>
              @php $hasCode = isset($selectedChat) && $selectedChat && $selectedChat->code; @endphp
              <input type="text" name="code" class="form-control" value="{{ $selectedChat->code ?? '' }}" maxlength="50" {{ $hasCode ? 'readonly' : 'required' }}>
              @if($hasCode)
                <div class="form-text">O código será vinculado à conversa selecionada.</div>
              @endif
            </div>
            <div>
              <label class="form-label small mb-1">Tipo</label>
              <select name="type" class="form-select" required>
                <option value="compra">Compra</option>
                <option value="venda">Venda</option>
              </select>
            </div>
            <div>
              <label class="form-label small mb-1">Quantidade</label>
              <input type="text" name="quantity" class="form-control" inputmode="decimal" placeholder="Ex: 100 ou 100,5" required>
            </div>
            <div>
              <label class="form-label small mb-1">Valor</label>
              <input type="text" name="value" class="form-control mask-money-br" inputmode="decimal" placeholder="Ex: 10,50">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Salvar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="table-responsive">
  @push('scripts')
  <script>
  (function(){
    const dateInput = document.getElementById('new_date_br');
    const timeInput = document.getElementById('new_time_br');
    const hidden = document.getElementById('new_occurred_at_hidden');
    const calBox = document.getElementById('new_brCalendar');
    const calBody = document.getElementById('new_calBody');
    const calLabel = document.getElementById('new_calMonthLabel');
    const btnPrev = document.getElementById('new_prevCal');
    const btnNext = document.getElementById('new_nextCal');
    const btnCal = document.getElementById('new_btnCal');
    const btnClose = document.getElementById('new_closeCal');

    function pad(n){return n.toString().padStart(2,'0');}
    function maskDate(v){
      v = v.replace(/\D/g,'').slice(0,8);
      let o='';
      if(v.length>=2) o+=v.slice(0,2); else return v;
      if(v.length>=4) o+='/'+v.slice(2,4); else return o;
      if(v.length>4) o+='/'+v.slice(4,8);
      return o;
    }
    function maskTime(v){
      v = v.replace(/\D/g,'').slice(0,6);
      let o='';
      if(v.length>=2) o+=v.slice(0,2); else return v;
      if(v.length>=4) o+=':'+v.slice(2,4); else return o;
      if(v.length>4) o+=':'+v.slice(4,6);
      return o;
    }
    function syncHidden(){
      if(dateInput.value && timeInput.value){
        hidden.value = dateInput.value+' '+timeInput.value;
      }
    }
    dateInput.addEventListener('input',()=>{ dateInput.value = maskDate(dateInput.value); syncHidden();});
    timeInput.addEventListener('input',()=>{ timeInput.value = maskTime(timeInput.value); syncHidden();});

    let viewYear, viewMonth;
    function initView(){
      const parts = dateInput.value.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
      let d = new Date();
      if(parts){ d = new Date(parseInt(parts[3]), parseInt(parts[2])-1, parseInt(parts[1])); }
      viewYear = d.getFullYear();
      viewMonth = d.getMonth();
    }
    function renderCalendar(){
      const first = new Date(viewYear, viewMonth, 1);
      const startDay = first.getDay();
      const daysInMonth = new Date(viewYear, viewMonth+1,0).getDate();
      calLabel.textContent = first.toLocaleDateString('pt-BR',{month:'long', year:'numeric'}).toUpperCase();
      let html=''; let dayNum=1;
      for(let r=0;r<6;r++){
        html+='<tr>';
        for(let c=0;c<7;c++){
          if(r===0 && c<startDay){ html+='<td class="text-muted">&nbsp;</td>'; }
          else if(dayNum>daysInMonth){ html+='<td>&nbsp;</td>'; }
          else {
            const dd = pad(dayNum); const mm = pad(viewMonth+1); const yyyy = viewYear;
            const sel = dateInput.value===dd+'/'+mm+'/'+yyyy;
            html+='<td><button type="button" data-day="'+dayNum+'" class="btn btn-sm '+(sel?'btn-primary':'btn-light')+' w-100 p-0" style="font-size:.65rem;">'+dayNum+'</button></td>';
            dayNum++;
          }
        }
        html+='</tr>';
        if(dayNum>daysInMonth) break;
      }
      calBody.innerHTML = html;
    }
    function openCal(){ initView(); renderCalendar(); calBox.style.display='block'; }
    function closeCal(){ calBox.style.display='none'; }
    btnCal.addEventListener('click', ()=>{ calBox.style.display==='block'?closeCal():openCal(); });
    btnClose.addEventListener('click', closeCal);
    btnPrev.addEventListener('click', ()=>{ viewMonth--; if(viewMonth<0){viewMonth=11;viewYear--;} renderCalendar(); });
    btnNext.addEventListener('click', ()=>{ viewMonth++; if(viewMonth>11){viewMonth=0;viewYear++;} renderCalendar(); });
    calBody.addEventListener('click', e=>{
      const b = e.target.closest('button[data-day]');
      if(!b) return;
      const day = parseInt(b.getAttribute('data-day'));
      dateInput.value = pad(day)+'/'+pad(viewMonth+1)+'/'+viewYear;
      syncHidden();
      renderCalendar();
      closeCal();
    });
    document.addEventListener('click', e=>{
      if(!calBox.contains(e.target) && e.target!==btnCal && e.target!==dateInput){ closeCal(); }
    });
    syncHidden();
  })();
  // Máscara moeda BR (2 casas)
  (function(){
    function formatMoneyBR(v){
      v = (v+"").replace(/[^0-9]/g,'');
      if(!v) return '';
      if(v.length===1) return '0,0'+v;
      if(v.length===2) return '0,'+v;
      return v.slice(0,-2).replace(/^0+/,'') + ',' + v.slice(-2);
    }
    document.querySelectorAll('.mask-money-br').forEach(el=>{
      el.addEventListener('input', e=>{
        const pos = el.selectionStart;
        el.value = formatMoneyBR(el.value);
      });
      el.addEventListener('blur', ()=>{ if(el.value==='') el.value='0,00'; });
      // converter ao enviar
      el.form?.addEventListener('submit', ()=>{
        if(el.value){ el.value = el.value.replace(/\./g,'').replace(',','.'); }
      });
    });
  })();
  </script>
  @endpush
  @push('scripts')
  <script>
  // Toggle por linha na tabela (por código)
  (function(){
    try {
      const TOGGLE_URL = @json(route('openai.assets.noBuy.toggle'));
      function applyBtn(btn, noBuy){
        btn.classList.remove('btn-success','btn-danger');
        btn.classList.add(noBuy ? 'btn-danger' : 'btn-success');
        btn.setAttribute('aria-pressed', noBuy ? 'true' : 'false');
        btn.textContent = noBuy ? 'NÃO COMPRAR' : 'COMPRAR';
        if (!btn.classList.contains('text-white')) btn.classList.add('text-white');
      }
      document.querySelectorAll('.btn-toggle-row[data-code]').forEach(btn => {
        btn.addEventListener('click', async function(){
          const code = this.getAttribute('data-code');
          if (!code) return;
          const prev = this.getAttribute('aria-pressed') === 'true';
          const next = !prev; // true = NÃO COMPRAR
          applyBtn(this, next);
          try {
            const resp = await fetch(TOGGLE_URL, {
              method: 'POST',
              headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With':'XMLHttpRequest' },
              credentials: 'same-origin',
              body: JSON.stringify({ code, no_buy: next })
            });
            if (!resp.ok) throw new Error('HTTP '+resp.status);
            const data = await resp.json().catch(()=>null);
            if (!data || data.ok !== true) throw new Error('Bad payload');
          } catch(e){
            // reverte
            applyBtn(this, prev);
            try { window.alert('Falha ao salvar preferência do código '+code+'.'); } catch(_ee){}
          }
        });
      });
    } catch(_e){}
  })();
  // Toggle "Não comprar" por conversa/ativo
  (function(){
    try {
      @php
        $safeChatId = isset($selectedChat) && $selectedChat ? (int)$selectedChat->id : (int)($chatId ?? 0);
        $safeCode = isset($selectedChat) && $selectedChat ? strtoupper(trim((string)($selectedChat->code ?? ''))) : '';
      @endphp
  var chatId = {{ $safeChatId }};
      var btn = document.getElementById('btnToggleCanBuy');
  if (!btn) return;
  var code = (@json($safeCode) || '').toString();
  var KEY = code ? ('records.cantBuy.code.' + code) : ('records.cantBuy.chat.' + chatId); // usa code quando disponível
      var OLD_KEY = 'records.canBuy.chat.' + chatId; // migração
      function get(){
        // Sempre tenta backend no init(); aqui apenas sinalizamos async
        return null;
      }
  async function set(v){
        // Tenta backend primeiro (se ao menos um identificador existir)
        if (code || chatId){
          const TOGGLE_URL = @json(route('openai.assets.noBuy.toggle'));
          const resp = await fetch(TOGGLE_URL, {
              method: 'POST',
              headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With':'XMLHttpRequest' },
              credentials: 'same-origin',
              body: JSON.stringify({ code: code || '', chat_id: chatId || 0, no_buy: !!v })
            });
          if (resp.status === 422) {
            // Sem código no chat -> salva no localStorage
            try { localStorage.setItem(KEY, v ? '1' : '0'); try { localStorage.removeItem(OLD_KEY); } catch(_e){} } catch(_e){}
            return;
          }
          if (!resp.ok) { throw new Error('HTTP '+resp.status); }
          const data = await resp.json().catch(()=>null);
          if (!data || data.ok !== true) { throw new Error('Save failed'); }
          return;
        }
        // Fallback: sem identificador, só localStorage
        try { localStorage.setItem(KEY, v ? '1' : '0'); try { localStorage.removeItem(OLD_KEY); } catch(_e){} } catch(_e){}
      }
      function apply(v){
        // Reset classes relevantes
        btn.classList.remove('btn-success','btn-outline-success','btn-danger','btn-outline-danger','btn-outline-secondary','text-white');
        // Estado visual: verde = pode comprar, vermelho = não comprar
        if (v) { // NÃO comprar
          btn.classList.add('btn-danger');
        } else { // pode comprar
          btn.classList.add('btn-success');
        }
        // Texto sempre em branco
        if (!btn.classList.contains('text-white')) btn.classList.add('text-white');
        btn.textContent = v ? 'NÃO COMPRAR' : 'COMPRAR';
        btn.setAttribute('aria-pressed', v ? 'true' : 'false');
      }
      var val = get();
  (async function init(){
        // Busca backend se possível; se 422 (sem código), cai pro localStorage
        try {
          const GET_URL = @json(route('openai.assets.noBuy.get'));
          const params = new URLSearchParams();
          if (code) params.set('code', code);
          if (chatId) params.set('chat_id', String(chatId));
          const canBackend = (code || chatId) && params.toString() !== '';
          if (val === null && canBackend){
            const resp = await fetch(GET_URL + "?" + params.toString(), { headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' }, credentials: 'same-origin' });
            if (resp.status === 422) {
              // usa localStorage
              try { var lv = localStorage.getItem(KEY); if (lv !== null) val = (lv === '1'); else { var old = localStorage.getItem(OLD_KEY); if (old !== null) val = (old !== '1'); } } catch(_e){}
              if (typeof val === 'undefined' || val === null) val = false;
            } else if (resp.ok) {
              const data = await resp.json().catch(()=>null);
              val = !!(data && data.no_buy);
            } else {
              val = false;
            }
          }
        } catch(_e){ if (val === null) val = false; }
        try { apply(!!val); } catch(_e) {}
      })();
      btn.addEventListener('click', async function(){
        const prev = !!val;
        try {
          val = !val;
          apply(val);
          await set(val);
        } catch(_e){
          // Reverte visual e estado se falhar persistência
          val = prev;
          apply(val);
          const hasCode = !!code;
          const msg = hasCode ? 'Falha ao salvar preferência (NÃO COMPRAR/COMPRAR). Tente novamente.' : 'Este chat não tem código vinculado. Cadastre um código para salvar no banco. A preferência atual foi salva apenas neste navegador.';
          try { window.alert(msg); } catch(_ee){}
        }
      });
    } catch(_e){ /* noop */ }
  })();
  </script>
  @endpush
    @if($selectedChat)
      @php
        $ticker = trim($selectedChat->code ?? '');
        $titleChat = trim($selectedChat->title ?? '');
        // Monta consultas para análise temporal de 5 anos
        $queryBase = trim(($ticker ? $ticker.' ' : '').$titleChat.' 5 year financial performance growth revenue');
        $queryPrice = $ticker ? ($ticker.' 5 year stock price chart') : (($titleChat?:'').' 5 year stock performance');
        $querySec   = $ticker ? ($ticker.' SEC filings 10-K 10Q') : (($titleChat?:'').' SEC filings');
  $queryInvest = $ticker ? ($ticker.' investor relations presentations CAGR') : (($titleChat?:'').' investor relations');
  // Consultas foco analistas/mercado
  $queryAnalyst = ($ticker ? $ticker.' analyst ratings target price consensus' : ($titleChat?:'').' analyst ratings target price');
  $queryEarnings = ($ticker ? $ticker.' quarterly earnings EPS history last 5 years' : ($titleChat?:'').' quarterly earnings EPS history');
  $queryTranscript = ($ticker ? $ticker.' earnings call transcript' : ($titleChat?:'').' earnings call transcript');
  $queryOwnership = ($ticker ? $ticker.' institutional ownership shareholders' : ($titleChat?:'').' institutional ownership');
  // Queries adicionais para aprofundar
  $queryCashFlow = ($ticker ? $ticker.' free cash flow trend 5 years' : ($titleChat?:'').' free cash flow trend 5 years');
  $queryMargins = ($ticker ? $ticker.' gross margin operating margin net margin 5 years' : ($titleChat?:'').' margin evolution 5 years');
  $queryDebt = ($ticker ? $ticker.' debt to equity leverage ratio 10-K' : ($titleChat?:'').' leverage ratio');
  $queryGuidance = ($ticker ? $ticker.' guidance outlook next year' : ($titleChat?:'').' guidance outlook');
  $queryValuation = ($ticker ? $ticker.' valuation multiples PE PS EV EBITDA history' : ($titleChat?:'').' valuation multiples');
  $queryDividends = ($ticker ? $ticker.' dividend history payout ratio 5 years' : ($titleChat?:'').' dividend history');
  $queryBuybacks = ($ticker ? $ticker.' share repurchase buyback program' : ($titleChat?:'').' share repurchase');
  $queryShortInterest = ($ticker ? $ticker.' short interest days to cover' : ($titleChat?:'').' short interest');
  $queryESG = ($ticker ? $ticker.' ESG sustainability score report' : ($titleChat?:'').' ESG sustainability');
  $queryMoat = ($ticker ? $ticker.' competitive advantage market share' : ($titleChat?:'').' competitive advantage');
      @endphp
      <div class="alert alert-info py-2 mb-2 d-flex flex-column gap-2">
        <div>
          <strong>Conversa Selecionada:</strong> {{ $selectedChat->title }} @if($ticker)<span class="badge bg-dark ms-1">{{ $ticker }}</span>@endif
        </div>
        <div class="d-flex flex-wrap gap-1">
          <a class="btn btn-sm btn-outline-success" target="_blank" rel="noopener" href="https://www.google.com/search?q={{ urlencode($queryBase) }}" title="Pesquisar panorama financeiro 5 anos">🌐 Análise 5 anos</a>
          <a class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener" href="https://www.google.com/search?q={{ urlencode($queryPrice) }}" title="Histórico de preço 5 anos">📈 Preço 5y</a>
          <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener" href="https://www.google.com/search?q={{ urlencode($queryInvest) }}" title="Relações com investidores / apresentações">💼 IR / Apresentações</a>
          <a class="btn btn-sm btn-outline-dark" target="_blank" rel="noopener" href="https://www.google.com/search?q={{ urlencode($querySec) }}" title="Filings regulatórios (EDGAR / SEC)">📝 SEC Filings</a>
          <a class="btn btn-sm btn-outline-info" target="_blank" rel="noopener" href="https://www.google.com/search?q={{ urlencode($queryAnalyst) }}" title="Opiniões / ratings de analistas">🧠 Analyst Ratings</a>
          <a class="btn btn-sm btn-outline-warning" target="_blank" rel="noopener" href="https://www.google.com/search?q={{ urlencode($queryEarnings) }}" title="Histórico de resultados e EPS">📊 Earnings 5y</a>
          <a class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener" href="https://www.google.com/search?q={{ urlencode($queryTranscript) }}" title="Transcrições de conference calls">🗣️ Transcripts</a>
          <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener" href="https://www.google.com/search?q={{ urlencode($queryOwnership) }}" title="Estrutura de acionistas institucionais">🏦 Ownership</a>
          <a class="btn btn-sm btn-outline-success" target="_blank" rel="noopener" href="https://www.google.com/search?q={{ urlencode($queryCashFlow) }}" title="Evolução do fluxo de caixa livre">💧 FCF</a>
          <a class="btn btn-sm btn-outline-success" target="_blank" rel="noopener" href="https://www.google.com/search?q={{ urlencode($queryMargins) }}" title="Margens (bruta / operacional / líquida)">📐 Margens</a>
          <a class="btn btn-sm btn-outline-danger" target="_blank" rel="noopener" href="https://www.google.com/search?q={{ urlencode($queryDebt) }}" title="Alavancagem / dívida">⚖️ Dívida</a>
          <a class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener" href="https://www.google.com/search?q={{ urlencode($queryGuidance) }}" title="Guidance / Outlook">🧭 Guidance</a>
          <a class="btn btn-sm btn-outline-dark" target="_blank" rel="noopener" href="https://www.google.com/search?q={{ urlencode($queryValuation) }}" title="Múltiplos de Valuation">💲 Valuation</a>
          <a class="btn btn-sm btn-outline-warning" target="_blank" rel="noopener" href="https://www.google.com/search?q={{ urlencode($queryDividends) }}" title="Histórico de dividendos">💰 Dividendos</a>
          <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener" href="https://www.google.com/search?q={{ urlencode($queryBuybacks) }}" title="Programas de recompra de ações">🔄 Buybacks</a>
          <a class="btn btn-sm btn-outline-danger" target="_blank" rel="noopener" href="https://www.google.com/search?q={{ urlencode($queryShortInterest) }}" title="Short interest / days to cover">🧪 Short Interest</a>
          <a class="btn btn-sm btn-outline-success" target="_blank" rel="noopener" href="https://www.google.com/search?q={{ urlencode($queryESG) }}" title="ESG / Sustentabilidade">🌱 ESG</a>
          <a class="btn btn-sm btn-outline-info" target="_blank" rel="noopener" href="https://www.google.com/search?q={{ urlencode($queryMoat) }}" title="Vantagem competitiva / Moat">🛡️ Moat</a>
          @if($ticker)
            <a class="btn btn-sm btn-outline-warning" target="_blank" rel="noopener" href="https://finance.yahoo.com/quote/{{ urlencode($ticker) }}" title="Yahoo Finance (resumo)">🗞️ Yahoo</a>
            <a class="btn btn-sm btn-outline-danger" target="_blank" rel="noopener" href="https://www.google.com/finance/quote/{{ urlencode($ticker) }}" title="Google Finance">🔎 G.Finance</a>
            <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener" href="https://www.morningstar.com/search?query={{ urlencode($ticker) }}" title="Morningstar">⭐ Morningstar</a>
            <a class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener" href="https://seekingalpha.com/symbol/{{ urlencode($ticker) }}" title="Seeking Alpha">📰 SeekingAlpha</a>
            <a class="btn btn-sm btn-outline-dark" target="_blank" rel="noopener" href="https://tipranks.com/stocks/{{ strtolower($ticker) }}" title="TipRanks (analistas)">📊 TipRanks</a>
            <a class="btn btn-sm btn-outline-info" target="_blank" rel="noopener" href="https://www.marketwatch.com/investing/stock/{{ strtolower($ticker) }}" title="MarketWatch">🕒 MarketWatch</a>
            <a class="btn btn-sm btn-outline-success" target="_blank" rel="noopener" href="https://www.macrotrends.net/stocks/charts/{{ strtoupper($ticker) }}/" title="Macrotrends (históricos)">📈 Macrotrends</a>
          @endif
        </div>
        <div class="small text-muted">Abre pesquisas externas para compor análise temporal (últimos 5 anos). Verifique sempre fontes oficiais antes de decisões.</div>
      </div>
    @endif
    @php
      $flipDir = fn($c)=> ($sort === $c && $dir==='asc') ? 'desc' : 'asc';
      $sortIcon = function($c) use ($sort,$dir){
        if($sort!==$c) return '↕';
        return $dir==='asc' ? '↑' : '↓';
      };
      $baseParams = array_filter([
        'chat_id'=>$chatId?:null,
        'from'=>$from?:null,
        'to'=>$to?:null,
        'asset'=>($asset??'')!==''?$asset:null,
        'all'=>($showAll??false)?1:null,
        'investment_account_id'=>($invAccId!==null && $invAccId!=='')?$invAccId:null,
        'buy'=>request('buy')?:null,
      ]);
    @endphp
    @php
      $mode = $varMode ?? request('var_mode','seq'); // seq | acum
      $isSeq = $mode === 'seq';
    @endphp
    <div class="d-flex justify-content-end mb-2 gap-2">
      @if(($chatId ?? 0) > 0)
        @php
          $modeParams = array_filter([
            'chat_id'=>$chatId?:null,
            'from'=>$from?:null,
            'to'=>$to?:null,
            'asset'=>($asset??'')!==''?$asset:null,
            'all'=>($showAll??false)?1:null,
            'sort'=>$sort!=='occurred_at'?$sort:null,
            'dir'=>$dir!=='desc'?$dir:null,
            'var_mode'=>$isSeq?'acum':'seq',
            'investment_account_id'=>($invAccId!==null && $invAccId!=='')?$invAccId:null,
            'buy'=>request('buy')?:null
          ]);
        @endphp
        <a href="{{ route('openai.records.index',$modeParams) }}" class="btn btn-sm btn-outline-secondary" title="Alternar modo de variação">
          Modo: {{ $isSeq ? 'Sequencial' : 'Acumulada' }} (trocar)
        </a>
      @endif
    </div>
    @if(($chatId ?? 0) > 0)
      @php
        // Calcula média dos percentuais exibidos (baseado nos registros atuais)
        $allListAvg = collect($records instanceof \Illuminate\Pagination\AbstractPaginator ? $records->items() : $records);
        $sortedAvg = $allListAvg->sortBy(fn($item)=>$item->occurred_at)->values();
        $prevAvg = null; $firstValAvg = null; $variationMapAvg = []; $accumMapAvg = [];
        foreach($sortedAvg as $it){
          $cur = (float)$it->amount;
          if($prevAvg !== null && $prevAvg != 0){
            $variationMapAvg[$it->id] = (($cur - $prevAvg) / $prevAvg) * 100.0;
          } else {
            $variationMapAvg[$it->id] = null;
          }
          if($firstValAvg === null){
            $firstValAvg = $cur;
            $accumMapAvg[$it->id] = null;
          } else if($firstValAvg != 0) {
            $accumMapAvg[$it->id] = (($cur - $firstValAvg)/$firstValAvg)*100.0;
          } else {
            $accumMapAvg[$it->id] = null;
          }
          $prevAvg = $cur;
        }
        $sumAvg = 0.0; $cntAvg = 0;
        foreach($allListAvg as $it){
          $v = $isSeq ? ($variationMapAvg[$it->id] ?? null) : ($accumMapAvg[$it->id] ?? null);
          if(!is_null($v)) { $sumAvg += (float)$v; $cntAvg++; }
        }
        $avgPercent = $cntAvg > 0 ? ($sumAvg / $cntAvg) : null;
      @endphp
      <div class="alert alert-secondary py-2 mb-2">
        <strong>Média dos percentuais ({{ $isSeq ? 'Sequencial' : 'Acumulada' }}):</strong>
        @if(!is_null($avgPercent))
          {{ number_format($avgPercent, 2, ',', '.') }}%
        @else
          —
        @endif
      </div>
    @endif
    <table class="table table-sm table-bordered align-middle">
      <thead class="table-dark">
        <tr>
          <th style="width:22%">
            <a class="text-decoration-none text-light" href="{{ route('openai.records.index', array_merge($baseParams,['sort'=>'chat','dir'=>$flipDir('chat')])) }}">Conversa {{ $sortIcon('chat') }}</a>
          </th>
          <th style="width:10%">
            <a class="text-decoration-none text-light" href="{{ route('openai.records.index', array_merge($baseParams,['sort'=>'code','dir'=>$flipDir('code')])) }}">Código {{ $sortIcon('code') }}</a>
          </th>
          <th style="width:18%">
            <a class="text-decoration-none text-light" href="{{ route('openai.records.index', array_merge($baseParams,['sort'=>'occurred_at','dir'=>$flipDir('occurred_at')])) }}">Data/Hora {{ $sortIcon('occurred_at') }}</a>
          </th>
          <th style="width:15%" class="text-end">
            <a class="text-decoration-none text-light" href="{{ route('openai.records.index', array_merge($baseParams,['sort'=>'amount','dir'=>$flipDir('amount')])) }}">Valor {{ $sortIcon('amount') }}</a>
          </th>
          <th style="width:18%">
            <a class="text-decoration-none text-light" href="{{ route('openai.records.index', array_merge($baseParams,['sort'=>'investment','dir'=>$flipDir('investment')])) }}">Investimento {{ $sortIcon('investment') }}</a>
          </th>
          <th style="width:20%">
            <a class="text-decoration-none text-light" href="{{ route('openai.records.index', array_merge($baseParams,['sort'=>'user','dir'=>$flipDir('user')])) }}">Usuário {{ $sortIcon('user') }}</a>
          </th>
          <th style="width:15%" class="text-center">Ações</th>
        </tr>
      </thead>
      <tbody>
    @php
      // Pré-processa variações se filtrado por uma conversa específica
      $variationMap = [];
      $accumMap = [];
      if(($chatId ?? 0) > 0){
        // Extrai coleção plana (pagination ou collection) e ordena por occurred_at asc
        $allList = collect($records instanceof \Illuminate\Pagination\AbstractPaginator ? $records->items() : $records);
        $sorted = $allList->sortBy(fn($item)=>$item->occurred_at)->values();
        $prev = null;
        $firstVal = null;
        foreach($sorted as $item){
          $cur = (float)$item->amount;
          if($prev !== null && $prev != 0){
            $variationMap[$item->id] = (($cur - $prev) / $prev) * 100.0;
          } else {
            $variationMap[$item->id] = null; // primeira linha ou divisão por zero
          }
          if($firstVal === null){
            $firstVal = $cur;
            $accumMap[$item->id] = null; // primeira não tem acumulada (ou 0%)
          } else if($firstVal != 0) {
            $accumMap[$item->id] = (($cur - $firstVal)/$firstVal)*100.0;
          } else {
            $accumMap[$item->id] = null;
          }
          $prev = $cur;
        }
      }
    @endphp
    @forelse($records as $r)
          <tr>
            <td>
              @if($r->chat)
                @php $day = $r->occurred_at->format('Y-m-d'); @endphp
                <a href="{{ route('openai.records.index', array_filter(['chat_id'=>$r->chat_id,'from'=>$day,'to'=>$day,'remember'=>1,'sort'=>$sort!=='occurred_at'?$sort:null,'dir'=>$dir!=='desc'?$dir:null,'all'=>($showAll??false)?1:null,'investment_account_id'=>($invAccId!==null && $invAccId!=='')?$invAccId:null,'buy'=>request('buy')?:null])) }}" class="text-decoration-none">
                  {{ $r->chat->title }}
                </a>
              @else
                —
              @endif
            </td>
            <td class="text-center">{{ $r->chat?->code ?? '—' }}</td>
            @php
              $dt = $r->occurred_at;
              $formatted = $dt?->format('d/m/Y H:i:s');
              $d = $dt? (int)$dt->format('d') : null;
              $m = $dt? (int)$dt->format('m') : null;
              $suspect = $d && $m && $d <= 12 && $m <= 12 && $d !== $m; // ambígua
            @endphp
            <td class="{{ $suspect ? 'table-warning' : '' }}"
            @if($suspect) title="Data potencialmente invertida (dia/mês). Edite para confirmar." @endif>
              {{-- {{ $formatted }} --}}
              @if($suspect)
                <span class="badge bg-warning text-dark ms-1">?</span>
              @endif
                 {{ $r->occurred_at->format('d/m/Y H:i:s') }}

            </td>
            <td class="text-end">
              {{ number_format((float)$r->amount, 2, ',', '.') }}
              @if(($chatId ?? 0) > 0)
                  @php
                    $val = $isSeq ? ($variationMap[$r->id] ?? null) : ($accumMap[$r->id] ?? null);
                  @endphp
                  @if(!is_null($val))
                      @php
                        $cls = $val > 0 ? 'text-success' : ($val < 0 ? 'text-danger' : 'text-muted');
                        $arrow = $val > 0 ? '▲' : ($val < 0 ? '▼' : '▶');
                        $title = $isSeq ? 'Variação percentual vs registro anterior (ordem crescente de data)' : 'Variação acumulada desde o primeiro registro';
                      @endphp
                      <div class="small {{$cls}}" title="{{$title}}">
                        {{$arrow}} {{ number_format($val,2,',','.') }}%
                      </div>
                  @else
                      <div class="small text-muted" title="Primeiro registro">—</div>
                  @endif
              @endif
            </td>
            <td>
              @if($r->investmentAccount)
                <a class="text-decoration-none" href="{{ route('openai.investments.index', ['account'=>$r->investmentAccount->account_name, 'broker'=>$r->investmentAccount->broker]) }}">
                  {{ $r->investmentAccount->account_name }}
                  @if($r->investmentAccount->broker)
                    <span class="text-muted">— {{ $r->investmentAccount->broker }}</span>
                  @endif
                </a>
              @else
                —
              @endif
            </td>
            <td>{{ $r->user?->name }}</td>
            <td class="text-center">
              @php $rowCode = strtoupper(trim((string)($r->chat?->code ?? ''))); @endphp
              @if($rowCode !== '')
                @php $rowNoBuy = (bool) (($flagsMap ?? collect())->get($rowCode, false)); @endphp
                <button type="button"
                        class="btn btn-xs {{ $rowNoBuy ? 'btn-danger' : 'btn-success' }} text-white me-1 btn-toggle-row"
                        data-code="{{ $rowCode }}"
                        aria-pressed="{{ $rowNoBuy ? 'true' : 'false' }}"
                        title="Alternar entre COMPRAR e NÃO COMPRAR">
                  {{ $rowNoBuy ? 'NÃO COMPRAR' : 'COMPRAR' }}
                </button>
              @endif
              <button type="button" class="btn btn-sm btn-success me-1" onclick="prepQuickAdd({{ $r->chat_id }})" title="Adicionar novo registro desta conversa">➕</button>
              <a href="{{ route('openai.records.edit', $r) }}" class="btn btn-sm btn-outline-primary me-1">Editar</a>
              @if($r->investmentAccount)
                @php
                  $prefill = [
                    'open_new' => 1,
                    'date' => optional($r->occurred_at)->format('Y-m-d'),
                    'total_invested' => number_format((float)$r->amount, 2, ',', '.'),
                    'account_name' => $r->investmentAccount->account_name,
                    'broker' => $r->investmentAccount->broker,
                    // manter filtros de listagem da página destino úteis
                    'account' => $r->investmentAccount->account_name,
                  ];
                @endphp
                <a href="{{ route('openai.investments.index', $prefill) }}" class="btn btn-sm btn-outline-info me-1" title="Lançar em Investimentos">Lançar Inv.</a>
              @endif
              <form action="{{ route('openai.records.destroy', $r) }}" method="POST" onsubmit="return confirm('Remover registro?');" class="d-inline">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">Excluir</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="text-center text-muted">Nenhum registro.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    @if(!($showAll ?? false))
      <div>{{ $records->links() }}</div>
    @else
      <div class="small text-muted">Listando todos (máx 2000) — exibidos: {{ $records->count() }}</div>
    @endif
    <div>
      @if(!empty($savedFilters))
        <span class="badge bg-info text-dark me-2" title="Filtro salvo em sessão">Filtro salvo</span>
      @endif
      @if(!($showAll ?? false))
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('openai.records.index', array_filter(['chat_id'=>$chatId?:null,'from'=>$from?:null,'to'=>$to?:null,'all'=>1,'investment_account_id'=>($invAccId!==null && $invAccId!=='')?$invAccId:null,'buy'=>request('buy')?:null])) }}">Ver Todos</a>
      @else
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('openai.records.index', array_filter(['chat_id'=>$chatId?:null,'from'=>$from?:null,'to'=>$to?:null,'investment_account_id'=>($invAccId!==null && $invAccId!=='')?$invAccId:null,'buy'=>request('buy')?:null])) }}">Voltar à Paginação</a>
      @endif
      @if(!empty($savedFilters))
        <a class="btn btn-sm btn-outline-warning ms-2" href="{{ route('openai.records.index', array_filter(['clear_saved'=>1,'all'=>($showAll??false)?1:null,'investment_account_id'=>($invAccId!==null && $invAccId!=='')?$invAccId:null,'buy'=>request('buy')?:null])) }}" title="Remover filtro salvo da sessão">Limpar Filtro Salvo</a>
      @endif
    </div>
  </div>

  @if(isset($codeOrders) && $codeOrders->count())
    <div class="card shadow-sm mt-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h2 class="h6 mb-0">Ordens cadastradas</h2>
          @if(($chatId ?? 0) > 0 && $selectedChat)
            <span class="small text-muted">Conversa: {{ $selectedChat->title }} @if($selectedChat->code)<span class="badge bg-dark ms-1">{{ $selectedChat->code }}</span>@endif</span>
          @endif
        </div>
        <div class="table-responsive">
          <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width:18%">Código</th>
                <th style="width:12%">Tipo</th>
                <th style="width:14%" class="text-end">Quantidade</th>
                <th style="width:14%" class="text-end">Valor</th>
                <th>Conversa</th>
                <th style="width:16%">Criado em</th>
                <th style="width:16%" class="text-center">Ações</th>
              </tr>
            </thead>
            <tbody>
              @foreach($codeOrders as $o)
                <tr>
                  <td><span class="badge bg-dark">{{ $o->code }}</span></td>
                  <td>
                    @php $cls = $o->type === 'compra' ? 'success' : 'danger'; @endphp
                    <span class="badge bg-{{ $cls }}">{{ ucfirst($o->type) }}</span>
                  </td>
                  <td class="text-end">{{ rtrim(rtrim(number_format((float)$o->quantity, 6, ',', '.'), '0'), ',') }}</td>
                  <td class="text-end">
                    @if(!is_null($o->value))
                      {{ number_format((float)$o->value, 2, ',', '.') }}
                    @else
                      —
                    @endif
                  </td>
                  <td>{{ $o->chat?->title ?? '—' }}</td>
                  <td>
                    @php $cdt = $o->created_at ? $o->created_at->timezone(config('app.timezone')) : null; @endphp
                    @if($cdt)
                      <span title="{{ $cdt->format('d/m/Y H:i:s') }}">{{ $cdt->format('d/m/Y H:i:s') }}</span>
                    @else — @endif
                  </td>
                  <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editOrderModal_{{ $o->id }}">Editar</button>
                    <form action="{{ route('openai.records.codeOrder.destroy', $o->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir esta ordem?');">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-sm btn-outline-danger">Excluir</button>
                    </form>
                  </td>
                </tr>
                @include('openai.partials.code_order_modal', ['order' => $o])
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  @endif
</div>
@endsection
@push('scripts')
<script>
function prepQuickAdd(chatId){
  const sel = document.querySelector('#newRecordForm select[name="chat_id"]');
  if(sel){ sel.value = chatId; }
  const dateEl = document.getElementById('new_date_br');
  const timeEl = document.getElementById('new_time_br');
  const hidden = document.getElementById('new_occurred_at_hidden');
  const now = new Date();
  const pad = n=> n.toString().padStart(2,'0');
  if(dateEl && !dateEl.value){
    dateEl.value = pad(now.getDate())+'/'+pad(now.getMonth()+1)+'/'+now.getFullYear();
  }
  if(timeEl && !timeEl.value){
    timeEl.value = pad(now.getHours())+':'+pad(now.getMinutes())+':'+pad(now.getSeconds());
  }
  if(dateEl && timeEl && hidden){ hidden.value = dateEl.value+' '+timeEl.value; }
  document.getElementById('newRecordForm').scrollIntoView({behavior:'smooth', block:'center'});
  if(timeEl) timeEl.focus();
}

// Widget de cotação em tempo real (auto e botão)
(function(){
  const btn = document.getElementById('mdq_btn');
  const out = document.getElementById('mdq_result');
  const amountInput = document.querySelector('#newRecordForm input[name="amount"]');
  const symbol = @json($selectedChat->code ?? '');
  const dateEl = document.getElementById('new_date_br');
  const timeEl = document.getElementById('new_time_br');
  const hiddenDT = document.getElementById('new_occurred_at_hidden');

  function pad(n){ return String(n).padStart(2,'0'); }
  function applyDateTimeFromQuote(updated){
    if (!updated || !dateEl) return;
    // Espera formatos: 'YYYY-MM-DD' ou 'YYYY-MM-DD HH:MM:SS' ou ISO
    let y,m,d,hh,mm,ss;
    const m1 = String(updated).match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}):(\d{2}))?$/);
    if (m1) {
      y = parseInt(m1[1],10); m = parseInt(m1[2],10); d = parseInt(m1[3],10);
      if (m1[4]) { hh = parseInt(m1[4],10); mm = parseInt(m1[5],10); ss = parseInt(m1[6],10); }
    } else {
      // Fallback: tentar Date()
      const dt = new Date(updated);
      if (!isNaN(dt.getTime())) {
        y = dt.getFullYear(); m = dt.getMonth()+1; d = dt.getDate();
        hh = dt.getHours(); mm = dt.getMinutes(); ss = dt.getSeconds();
      }
    }
    if (!y || !m || !d) return;
    // Define data BR
    dateEl.value = pad(d)+'/'+pad(m)+'/'+y;
    // Define hora se disponível; se não, mantém a hora atual já digitada
    if (typeof hh === 'number' && typeof mm === 'number' && typeof ss === 'number' && timeEl) {
      timeEl.value = pad(hh)+':'+pad(mm)+':'+pad(ss);
    }
    if (hiddenDT && timeEl && timeEl.value) {
      hiddenDT.value = dateEl.value+' '+timeEl.value;
    }
  }

  async function fetchQuoteAndPrefill(){
    if (!symbol || !out) return;
    out.textContent = 'Consultando…';
    try {
  const QUOTE_URL = @json(route('api.market.quote'));
  const url = QUOTE_URL + '?symbol=' + encodeURIComponent(symbol);
      const resp = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      if (!resp.ok) throw new Error('HTTP ' + resp.status);
      const data = await resp.json();
      if (data && typeof data.price !== 'undefined' && data.price !== null) {
        // Mostra cotação (moeda do provedor)
        const display = Number(data.price).toLocaleString('en-US', { style: 'currency', currency: (data.currency||'USD') });
        out.innerHTML = `<span class="text-success">${display}</span>` + (data.updated_at ? ` <small class="text-muted">(${data.updated_at})</small>` : '');
        // Preenche o campo "Valor" (pt-BR). Não sobrescreve se já houver conteúdo digitado.
        if (amountInput && (!amountInput.value || amountInput.value.trim() === '')) {
          const br = Number(data.price).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 6 });
          amountInput.value = br;
        }
        // Aplica data/hora da cotação ao formulário, quando disponível
        if (data.updated_at) {
          applyDateTimeFromQuote(data.updated_at);
        }
      } else {
        out.innerHTML = '<span class="text-warning">Sem dados</span>';
      }
    } catch (e) {
      out.innerHTML = '<span class="text-danger">Falha ao consultar</span>';
    }
  }

  if (btn) {
    btn.addEventListener('click', fetchQuoteAndPrefill);
  }
  // Aciona automaticamente ao carregar quando há símbolo
  if (symbol) {
    // Aguarda a máscara/DOM estabilizar antes de preencher
    window.addEventListener('DOMContentLoaded', () => setTimeout(fetchQuoteAndPrefill, 50));
  }
})();
</script>
@endpush

@push('scripts')
<script>
// Verifica existência de ordem para o chat/código antes de abrir o modal
(function(){
  const btn = document.getElementById('btnOpenCodeOrder');
  const modalEl = document.getElementById('codeOrderModal');
  const chatId = {{ ($selectedChat->id ?? $chatId ?? 0) ?: 0 }};
  const code = @json(trim($selectedChat->code ?? ''));
  if (!btn || !modalEl || !chatId) return;
  btn.addEventListener('click', async function(ev){
    ev.preventDefault();
    ev.stopPropagation();
    try {
      const url = @json(route('openai.records.codeOrder.check')) + `?chat_id=${encodeURIComponent(chatId)}&code=${encodeURIComponent(code||'')}`;
      const resp = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      if (!resp.ok) throw new Error('HTTP '+resp.status);
      const data = await resp.json();
      if (data && data.exists) {
        // Já existe: não abre modal e alerta
        const msg = code ? `Já existe ${data.count} ordem(ns) para o código ${code} nesta conversa.` : `Já existe ${data.count} ordem(ns) nesta conversa.`;
        window.alert(msg);
        return;
      }
      // Não existe: abre o modal programaticamente
      if (window.bootstrap && bootstrap.Modal) {
        const m = bootstrap.Modal.getOrCreateInstance(modalEl);
        m.show();
      } else {
        // fallback: aciona atributo data-bs-*
        modalEl.classList.add('show');
      }
    } catch (_e) {
      // Em caso de falha, abre o modal normalmente (não bloquear a ação)
      if (window.bootstrap && bootstrap.Modal) {
        const m = bootstrap.Modal.getOrCreateInstance(modalEl);
        m.show();
      }
    }
  });
})();
</script>
@endpush

@push('scripts')
<script>
// Preserva o fragmento do Google CSE (#gsc.*) ao clicar em links e ao enviar formulários GET
(function(){
  const hash = window.location.hash;
  if (!hash || !hash.startsWith('#gsc.')) return;
  const root = document.getElementById('records-index') || document;
  // Acrescenta hash nos links internos sem fragmento
  root.querySelectorAll('a[href]').forEach(a => {
    const href = a.getAttribute('href');
    if (!href) return;
    if (href.startsWith('javascript:')) return;
    if (href.includes('#')) return; // já possui fragmento
    try {
      const u = new URL(href, window.location.origin);
      if (u.origin !== window.location.origin) return; // externo
      a.setAttribute('href', u.pathname + (u.search || '') + hash);
    } catch(_e) { /* ignora urls relativas estranhas */ }
  });
  // Intercepta formulários GET para reaplicar o hash na navegação
  root.querySelectorAll('form[method="GET"], form[method="get"]').forEach(form => {
    form.addEventListener('submit', function(ev){
      try {
        ev.preventDefault();
        const formData = new FormData(form);
        const params = new URLSearchParams();
        for (const [k, v] of formData.entries()) { if (v !== null && v !== '') params.append(k, v); }
        const action = form.getAttribute('action') || window.location.pathname;
        const url = action + (params.toString() ? ('?' + params.toString()) : '');
        window.location.assign(url + hash);
      } catch(_e) {
        // fallback
        setTimeout(() => form.submit(), 0);
      }
    });
  });
})();
</script>
@endpush
