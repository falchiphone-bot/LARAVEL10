@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h4 mb-0">Registros de Conversas</h1>
    <div class="d-flex gap-2">
      <a href="{{ route('openai.menu') }}" class="btn btn-outline-secondary">← Menu</a>
      <a href="{{ route('openai.chats', ['view'=>'table']) }}" class="btn btn-outline-primary">Ver Conversas</a>
      <a href="{{ route('openai.chat') }}" class="btn btn-outline-dark">Chat</a>
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
        <div class="col-sm-3 col-md-2">
          <label class="form-label small mb-1">De</label>
          <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
        </div>
        <div class="col-sm-3 col-md-2">
          <label class="form-label small mb-1">Até</label>
          <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
        </div>
        <div class="col-sm-2 col-md-2 d-grid gap-2">
          <button class="btn btn-sm btn-outline-primary" type="submit">Filtrar</button>
          @if(!($showAll ?? false))
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('openai.records.index', array_filter(['chat_id'=>$chatId?:null,'from'=>$from?:null,'to'=>$to?:null,'all'=>1])) }}">Todos</a>
          @else
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('openai.records.index', array_filter(['chat_id'=>$chatId?:null,'from'=>$from?:null,'to'=>$to?:null])) }}">Paginar</a>
          @endif
          @if(!empty($savedFilters))
            <a class="btn btn-sm btn-outline-warning" href="{{ route('openai.records.index', array_filter(['clear_saved'=>1,'chat_id'=>$chatId?:null,'from'=>$from?:null,'to'=>$to?:null, 'all'=>($showAll??false)?1:null])) }}" title="Remover filtro salvo">Limpar Salvo</a>
          @endif
        </div>
        @if(request()->hasAny(['chat_id','from','to']) && (request('chat_id')||request('from')||request('to')))
          <div class="col-sm-2 col-md-2">
            <a href="{{ route('openai.records.index') }}" class="btn btn-sm btn-outline-dark w-100">Limpar</a>
          </div>
        @endif
      </form>
    </div>
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h2 class="h6 mb-3">Novo Registro</h2>
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
        <div class="col-sm-2 col-md-2">
          <button type="submit" class="btn btn-sm btn-success w-100">Adicionar</button>
        </div>
      </form>
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
        'all'=>($showAll??false)?1:null,
      ]);
    @endphp
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
          <th style="width:20%">
            <a class="text-decoration-none text-light" href="{{ route('openai.records.index', array_merge($baseParams,['sort'=>'user','dir'=>$flipDir('user')])) }}">Usuário {{ $sortIcon('user') }}</a>
          </th>
          <th style="width:15%" class="text-center">Ações</th>
        </tr>
      </thead>
      <tbody>
        @forelse($records as $r)
          <tr>
            <td>
              @if($r->chat)
                @php $day = $r->occurred_at->format('Y-m-d'); @endphp
                <a href="{{ route('openai.records.index', array_filter(['chat_id'=>$r->chat_id,'from'=>$day,'to'=>$day,'remember'=>1,'sort'=>$sort!=='occurred_at'?$sort:null,'dir'=>$dir!=='desc'?$dir:null,'all'=>($showAll??false)?1:null])) }}" class="text-decoration-none">
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
            <td class="text-end">{{ number_format((float)$r->amount, 2, ',', '.') }}</td>
            <td>{{ $r->user?->name }}</td>
            <td class="text-center">
              <button type="button" class="btn btn-sm btn-success me-1" onclick="prepQuickAdd({{ $r->chat_id }})" title="Adicionar novo registro desta conversa">➕</button>
              <a href="{{ route('openai.records.edit', $r) }}" class="btn btn-sm btn-outline-primary me-1">Editar</a>
              <form action="{{ route('openai.records.destroy', $r) }}" method="POST" onsubmit="return confirm('Remover registro?');" class="d-inline">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">Excluir</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted">Nenhum registro.</td></tr>
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
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('openai.records.index', array_filter(['chat_id'=>$chatId?:null,'from'=>$from?:null,'to'=>$to?:null,'all'=>1])) }}">Ver Todos</a>
      @else
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('openai.records.index', array_filter(['chat_id'=>$chatId?:null,'from'=>$from?:null,'to'=>$to?:null])) }}">Voltar à Paginação</a>
      @endif
      @if(!empty($savedFilters))
        <a class="btn btn-sm btn-outline-warning ms-2" href="{{ route('openai.records.index', array_filter(['clear_saved'=>1,'all'=>($showAll??false)?1:null])) }}" title="Remover filtro salvo da sessão">Limpar Filtro Salvo</a>
      @endif
    </div>
  </div>
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
</script>
@endpush
