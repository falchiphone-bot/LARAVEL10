@extends('layouts.bootstrap5')
@section('content')
<div class="container" style="max-width:760px">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 mb-0">Importar Caixa (Avenue Screen)</h1>
    <a href="{{ route('cash.events.index') }}#gsc.tab=0" class="btn btn-sm btn-outline-secondary" title="Voltar para eventos de caixa">Voltar</a>
  </div>
  <div class="card shadow-sm">
    <form method="post" action="{{ route('cash.import.store') }}">
      @csrf
      <div class="card-body">
        @if(session('success'))
          <div class="alert alert-success py-2 small mb-2">{{ session('success') }}</div>
        @endif
        @if($errors->any())
          <div class="alert alert-danger py-2 small mb-2"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif
        <p class="small text-muted mb-2">Cole o bloco bruto contendo saldo disponível e histórico (datas, dividendos, impostos). Parser identifica snapshot e eventos novos. Valores em USD.</p>
        <div class="mb-3">
          <label class="form-label small">Conta destino</label>
          <select name="account_id" class="form-select form-select-sm" required>
            <option value="">— selecione —</option>
            @foreach($accounts as $acc)
              <option value="{{ $acc->id }}">{{ $acc->account_name }} @if($acc->broker) ({{ $acc->broker }}) @endif</option>
            @endforeach
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label small">Bloco de Caixa (Avenue)</label>
          <textarea name="cash_raw" id="cash_raw" class="form-control form-control-sm" rows="14" placeholder="Cole aqui..." required></textarea>
          <div id="cash-email-detect" class="form-text mt-1"></div>
        </div>
        <div class="row g-2 mb-2">
          <div class="col-md-4"><label class="form-label small">Moeda</label><input type="text" readonly class="form-control form-control-sm" value="USD" /></div>
          <div class="col-md-8 small text-muted d-flex align-items-end">Snapshot inclui saldo disponível e valor futuro (se presente).</div>
        </div>
        <div class="form-check form-switch mb-3">
          <input class="form-check-input" type="checkbox" id="stay-switch" name="stay" value="1">
          <label class="form-check-label small" for="stay-switch">Permanecer nesta tela após importar</label>
        </div>
      </div>
      <div class="card-footer d-flex justify-content-between">
  <a href="{{ route('cash.events.index') }}#gsc.tab=0" class="btn btn-sm btn-secondary">Voltar</a>
        <button class="btn btn-sm btn-primary" id="btn-process-cash" disabled title="Necessário e-mail correspondente na primeira linha">Processar</button>
      </div>
    </form>
  </div>
  <div class="mt-3 small">
    <strong>Notas:</strong>
    <ul class="mb-0">
      <li>Deduplicação por hash do conteúdo (snapshot e eventos).</li>
      <li>Eventos reconhecidos: Dividendos, Imposto, Adicionar, Retirar.</li>
      <li>Outros títulos entram como categoria "other".</li>
    </ul>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const ta = document.getElementById('cash_raw');
  const accSelect = document.querySelector('select[name="account_id"]');
  const info = document.getElementById('cash-email-detect');
  const btn = document.getElementById('btn-process-cash');
  if(!ta || !accSelect || !info || !btn) return;

  function parseAccountParts(optText){
    const m = optText.match(/^\s*([^()]+?)(?:\s*\((.+)\))?\s*$/);
    if(!m) return {name: optText.trim().toLowerCase(), broker:null};
    return {name:(m[1]||'').trim().toLowerCase(), broker: m[2]? m[2].trim().toLowerCase(): null};
  }
  function detectEmailFromText(text){
    const lines = text.split(/\r?\n/);
    for(const raw of lines){
      const l = raw.trim();
      if(!l) continue;
      const emailPattern = /^[\w.+-]+@[\w.-]+\.[A-Za-z]{2,}$/;
      if(emailPattern.test(l)) return l.toLowerCase();
      return null; // primeira linha não vazia não é email
    }
    return null;
  }
  function setBtn(enabled, reason){
    if(enabled){
      btn.removeAttribute('disabled');
      btn.removeAttribute('title');
    } else {
      btn.setAttribute('disabled','disabled');
      if(reason) btn.setAttribute('title',reason);
    }
  }
  function refreshStatus(){
    const email = detectEmailFromText(ta.value || '');
    const selOpt = accSelect && accSelect.value ? accSelect.options[accSelect.selectedIndex] : null;
    const whitelist = ['sem@falchi.com.br','falchiphone@gmail.com.br'];
    if(!email){
      info.innerHTML = '<span class="text-muted">Nenhum e-mail detectado na primeira linha (obrigatório para habilitar).</span>';
      setBtn(false,'Informe bloco começando com e-mail correspondente');
      return;
    }
    let matchStatus = 'pending'; let detail = '';
    if(selOpt){
      const parts = parseAccountParts(selOpt.textContent||'');
      if(email === parts.name || (parts.broker && email === parts.broker)){
        matchStatus = 'ok';
      } else {
        matchStatus = 'mismatch';
        detail = ' (Conta: '+(parts.name || '-')+(parts.broker? ' / Broker: '+parts.broker:'')+')';
      }
    }
    if(matchStatus === 'ok' || whitelist.includes(email)){
      if(whitelist.includes(email) && matchStatus !== 'ok'){
        info.innerHTML = '<span class="text-success">E-mail detectado (whitelist): '+email+' ✔ autorizado.</span>';
      } else {
        info.innerHTML = '<span class="text-success">E-mail detectado: '+email+' ✔ corresponde à conta selecionada.</span>';
      }
      setBtn(true);
    } else if(matchStatus === 'mismatch'){
      info.innerHTML = '<span class="text-danger">E-mail detectado: '+email+' não corresponde ao nome ou corretora da conta selecionada'+detail+'.</span>';
      setBtn(false,'E-mail não corresponde à conta');
    } else {
      info.innerHTML = '<span class="text-warning">E-mail detectado: '+email+' — selecione a conta correspondente.</span>';
      setBtn(false,'Selecione a conta correspondente');
    }
  }
  ta.addEventListener('input', refreshStatus);
  accSelect.addEventListener('change', refreshStatus);
  refreshStatus();
})();
</script>
@endpush
