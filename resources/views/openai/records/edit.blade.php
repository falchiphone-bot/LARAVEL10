@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h5 mb-0">Editar Registro</h1>
    <div class="d-flex gap-2">
      <a href="{{ route('openai.records.index', ['chat_id' => $record->chat_id, 'from'=> $record->occurred_at->format('Y-m-d'), 'to'=>$record->occurred_at->format('Y-m-d')]) }}" class="btn btn-outline-secondary">← Voltar</a>
    </div>
  </div>

  @if($errors->any())
    <div class="alert alert-danger py-2 small mb-3">{{ $errors->first() }}</div>
  @endif
  @if(session('success'))
    <div class="alert alert-success py-2 small mb-3">{{ session('success') }}</div>
  @endif

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="POST" action="{{ route('openai.records.update', $record) }}" class="row g-3">
        @csrf
        @method('PUT')
        <div class="col-md-5">
          <label class="form-label small mb-1">Conversa *</label>
          <select name="chat_id" class="form-select form-select-sm" required>
            @foreach($chats as $c)
              <option value="{{ $c->id }}" {{ $record->chat_id == $c->id ? 'selected' : '' }}>{{ $c->title }} @if($c->code) ({{ $c->code }}) @endif</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label small mb-1">Data/Hora * <span class="text-muted">dd/mm/aaaa HH:MM:SS</span></label>
          <div class="vstack gap-1 position-relative">
            <div class="input-group input-group-sm">
              <input type="text" id="date_br" class="form-control" placeholder="dd/mm/aaaa" value="{{ old('date_br', $record->occurred_at->format('d/m/Y')) }}" autocomplete="off" required style="max-width:140px;">
              <button class="btn btn-outline-secondary" type="button" id="btnCal" title="Calendário">📅</button>
              <input type="text" id="time_br" class="form-control" placeholder="HH:MM:SS" value="{{ old('time_br', $record->occurred_at->format('H:i:s')) }}" autocomplete="off" required style="max-width:120px;">
            </div>
            <input type="hidden" name="occurred_at" id="occurred_at_hidden" value="{{ old('occurred_at', $record->occurred_at->format('d/m/Y H:i:s')) }}">
            <div id="brCalendar" class="br-calendar shadow-sm border rounded p-2 bg-white" style="display:none; position:absolute; top:100%; left:0; z-index:50; width:220px;">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <button type="button" class="btn btn-sm btn-light" id="prevCal" aria-label="Mês anterior">«</button>
                <strong class="small" id="calMonthLabel"></strong>
                <button type="button" class="btn btn-sm btn-light" id="nextCal" aria-label="Próximo mês">»</button>
              </div>
              <table class="table table-sm table-bordered mb-0 align-middle text-center" style="font-size:.70rem;">
                <thead class="table-light">
                  <tr>
                    <th>Do</th><th>Se</th><th>Te</th><th>Qu</th><th>Qu</th><th>Se</th><th>Sa</th>
                  </tr>
                </thead>
                <tbody id="calBody"></tbody>
              </table>
              <div class="mt-2 text-end">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="closeCal">Fechar</button>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <label class="form-label small mb-1">Valor * <span class="text-muted">(R$)</span></label>
          <input type="text" name="amount" class="form-control form-control-sm mask-money-br" required value="{{ old('amount', number_format((float)$record->amount,2,',','.')) }}" placeholder="0,00">
        </div>
        <div class="col-md-6">
          <label class="form-label small mb-1">Conta de investimento</label>
          <select name="investment_account_id" class="form-select form-select-sm">
            <option value="">— Não associar —</option>
            @isset($investmentAccounts)
              @foreach($investmentAccounts as $acc)
                <option value="{{ $acc->id }}" {{ (string)old('investment_account_id', (string)($record->investment_account_id ?? '')) === (string)$acc->id ? 'selected' : '' }}>
                  {{ $acc->account_name }} @if($acc->broker) — {{ $acc->broker }} @endif @if($acc->date) ({{ optional($acc->date)->format('d/m/Y') }}) @endif
                </option>
              @endforeach
            @endisset
          </select>
          <div class="form-text">Opcional: vincule este registro a uma conta de investimentos.</div>
        </div>
        <div class="col-12 d-flex justify-content-between mt-2">
          <button type="submit" class="btn btn-sm btn-primary">Salvar</button>
          <a href="{{ route('openai.records.index', ['chat_id' => $record->chat_id]) }}" class="btn btn-sm btn-outline-secondary">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const dateInput = document.getElementById('date_br');
  const timeInput = document.getElementById('time_br');
  const hidden = document.getElementById('occurred_at_hidden');
  const calBox = document.getElementById('brCalendar');
  const calBody = document.getElementById('calBody');
  const calLabel = document.getElementById('calMonthLabel');
  const btnPrev = document.getElementById('prevCal');
  const btnNext = document.getElementById('nextCal');
  const btnCal = document.getElementById('btnCal');
  const btnClose = document.getElementById('closeCal');

  function pad(n){return n.toString().padStart(2,'0');}
  function maskDate(v){
    v = v.replace(/\D/g,'').slice(0,8);
    let o='';
    if(v.length>=2) o+=v.slice(0,2);
    else return v;
    if(v.length>=4) o+='/'+v.slice(2,4);
    else return o;
    if(v.length>4) o+='/'+v.slice(4,8);
    return o; // dd/mm/aaaa
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

  // Calendar state
  let viewYear, viewMonth; // month: 0-11
  function initView(){
    const parts = dateInput.value.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    let d = new Date();
    if(parts){ d = new Date(parseInt(parts[3]), parseInt(parts[2])-1, parseInt(parts[1])); }
    viewYear = d.getFullYear();
    viewMonth = d.getMonth();
  }
  function renderCalendar(){
    const first = new Date(viewYear, viewMonth, 1);
    const startDay = first.getDay(); // 0=Dom
    const daysInMonth = new Date(viewYear, viewMonth+1,0).getDate();
    calLabel.textContent = first.toLocaleDateString('pt-BR',{month:'long', year:'numeric'}).toUpperCase();
    let html='';
    let cell=0; let dayNum=1;
    for(let r=0;r<6;r++){
      html+='<tr>';
      for(let c=0;c<7;c++){
        if(r===0 && c<startDay){ html+='<td class="text-muted">&nbsp;</td>'; }
        else if(dayNum>daysInMonth){ html+='<td>&nbsp;</td>'; }
        else {
          const dd = pad(dayNum);
            const mm = pad(viewMonth+1);
            const yyyy = viewYear;
            const sel = dateInput.value===dd+'/'+mm+'/'+yyyy;
            html+='<td><button type="button" data-day="'+dayNum+'" class="btn btn-sm '+(sel?'btn-primary':'btn-light')+' w-100 p-0" style="font-size:.65rem;">'+dayNum+'</button></td>';
          dayNum++;
        }
        cell++;
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
  // Initialize hidden composition
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
    el.addEventListener('input', ()=>{ el.value = formatMoneyBR(el.value); });
    el.addEventListener('blur', ()=>{ if(el.value==='') el.value='0,00'; });
    el.form?.addEventListener('submit', ()=>{ if(el.value){ el.value = el.value.replace(/\./g,'').replace(',','.'); }});
  });
})();
</script>
@endpush
