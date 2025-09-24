@extends('layouts.bootstrap5')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0"><i class="fa-solid fa-cloud-arrow-up me-2"></i>Backup para FTP</h1>
  </div>

  <div id="ftp-initial" class="mb-4">
    <div class="alert alert-info d-flex align-items-center gap-2">
      <i class="fa-solid fa-circle-info fs-4"></i>
      <div>
        Clique em <strong>Iniciar backup</strong> para enfileirar o job que envia arquivos do storage local para o servidor FTP.<br>
        Esta tela fará polling dos logs para mostrar progresso (arquivos enviados / ignorados / erros).
      </div>
    </div>
    <button id="btn-start-ftp" class="btn btn-primary btn-lg">
      <span class="me-2"><i class="fa-solid fa-play"></i></span>Iniciar backup agora
    </button>
  </div>

  <div id="ftp-runtime" class="d-none">
    <div class="row g-3 mb-3" id="cards-summary-ftp">
      <div class="col-md-2">
        <div class="card shadow-sm h-100"><div class="card-body text-center"><div class="fw-semibold text-muted">Enviados</div><div id="card-sent" class="display-6 fw-bold text-success">0</div></div></div>
      </div>
      <div class="col-md-2">
        <div class="card shadow-sm h-100"><div class="card-body text-center"><div class="fw-semibold text-muted">Ignorados</div><div id="card-skipped" class="display-6 fw-bold text-secondary">0</div></div></div>
      </div>
      <div class="col-md-2">
        <div class="card shadow-sm h-100"><div class="card-body text-center"><div class="fw-semibold text-muted">Erros</div><div id="card-error" class="display-6 fw-bold text-success">0</div></div></div>
      </div>
      <div class="col-md-2">
        <div class="card shadow-sm h-100"><div class="card-body text-center"><div class="fw-semibold text-muted">Fatais</div><div id="card-fatal" class="display-6 fw-bold text-success">0</div></div></div>
      </div>
      <div class="col-md-2">
        <div class="card shadow-sm h-100"><div class="card-body text-center"><div class="fw-semibold text-muted">Último evento</div><div id="card-last" class="small fw-bold text-info">—</div></div></div>
      </div>
      <div class="col-md-2">
        <div class="card shadow-sm h-100"><div class="card-body text-center"><div class="fw-semibold text-muted">Status</div><div id="card-status" class="small fw-bold text-warning">Aguardando</div></div></div>
      </div>
    </div>

    <div id="ftp-controls" class="alert alert-secondary py-2 small d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div>
        <strong>Polling:</strong> <span id="poll-status">inativo</span>
      </div>
      <div class="d-flex gap-2">
        <button id="btn-export-json-ftp" class="btn btn-sm btn-outline-dark" disabled><i class="fa-solid fa-file-export me-1"></i>Exportar JSON Logs (sessão)</button>
        <button id="btn-reiniciar-poll" class="btn btn-sm btn-outline-primary" disabled><i class="fa-solid fa-rotate me-1"></i>Reiniciar Polling</button>
        <button id="btn-stop-poll" class="btn btn-sm btn-outline-danger" disabled><i class="fa-solid fa-stop me-1"></i>Parar Polling</button>
      </div>
    </div>

    <ul class="nav nav-tabs" role="tablist">
      <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-enviados" type="button" role="tab">Enviados (0)</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-ignorados" type="button" role="tab">Ignorados (0)</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-erros" type="button" role="tab">Erros (0)</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-fatais" type="button" role="tab">Fatais (0)</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-todos" type="button" role="tab">Todos</button></li>
    </ul>
    <div class="tab-content border border-top-0 p-3 bg-white shadow-sm" style="max-height:540px; overflow:auto;" id="ftpTabsContent">
      <div class="tab-pane fade show active" id="tab-enviados" role="tabpanel">
        <p class="text-muted small" id="enviados-empty">Nenhum arquivo enviado (ainda).</p>
        <table class="table table-sm table-striped align-middle d-none" id="tbl-enviados"><thead class="table-light"><tr><th>Arquivo Local</th><th>Remoto</th><th class="text-end">Tamanho</th><th>Hash</th><th>ts</th></tr></thead><tbody></tbody></table>
      </div>
      <div class="tab-pane fade" id="tab-ignorados" role="tabpanel">
        <p class="text-muted small" id="ignorados-empty-ftp">Nenhum arquivo ignorado.</p>
        <table class="table table-sm table-hover align-middle d-none" id="tbl-ignorados-ftp"><thead class="table-light"><tr><th>Arquivo Local</th><th>Remoto</th><th class="text-end">Tamanho</th><th>Motivo</th><th>ts</th></tr></thead><tbody></tbody></table>
      </div>
      <div class="tab-pane fade" id="tab-erros" role="tabpanel">
        <p class="text-muted small" id="erros-empty-ftp">Nenhum erro.</p>
        <table class="table table-sm table-bordered align-middle d-none" id="tbl-erros-ftp"><thead class="table-light"><tr><th>Arquivo</th><th>Mensagem</th><th>Linha</th><th>ts</th></tr></thead><tbody></tbody></table>
      </div>
      <div class="tab-pane fade" id="tab-fatais" role="tabpanel">
        <p class="text-muted small" id="fatais-empty">Nenhum fatal.</p>
        <table class="table table-sm table-bordered align-middle d-none" id="tbl-fatais"><thead class="table-light"><tr><th>Mensagem</th><th>ts</th></tr></thead><tbody></tbody></table>
      </div>
      <div class="tab-pane fade" id="tab-todos" role="tabpanel">
        <p class="text-muted small" id="todos-empty">Sem eventos ainda.</p>
        <table class="table table-sm table-striped align-middle d-none" id="tbl-todos"><thead class="table-light"><tr><th>ts</th><th>Evento</th><th>Arquivo</th><th>Remoto</th><th>Info</th></tr></thead><tbody></tbody></table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const startBtn = document.getElementById('btn-start-ftp');
  const runtime = document.getElementById('ftp-runtime');
  const initial = document.getElementById('ftp-initial');
  const exportBtn = document.getElementById('btn-export-json-ftp');
  const reiniciarBtn = document.getElementById('btn-reiniciar-poll');
  const stopBtn = document.getElementById('btn-stop-poll');
  const endpointStart = @json(url('/backup/storage-to-ftp'));
  const endpointLogsLast = @json(url('/backup/ftp-logs/download-last'));
  let polling = false;
  let pollTimer = null;
  let sessionEvents = [];
  let lastCountSent = 0, lastCountSkipped = 0, lastCountErr = 0, lastCountFatal = 0;

  function setStatus(txt, cls){
    const el = document.getElementById('card-status');
    el.textContent = txt;
    el.className = 'small fw-bold ' + (cls || 'text-warning');
  }
  function setPollStatus(txt){ document.getElementById('poll-status').textContent = txt; }
  function fmtSize(n){ return (Number(n)||0).toLocaleString('pt-BR') + ' B'; }
  function ensureTableVisible(idWrapperEmpty, idTable){
    document.getElementById(idWrapperEmpty).classList.add('d-none');
    document.getElementById(idTable).classList.remove('d-none');
  }
  function appendRow(tbody, html){ tbody.insertAdjacentHTML('beforeend', html); }
  function exportSession(){
    const blob = new Blob([JSON.stringify(sessionEvents, null, 2)], {type:'application/json'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href=url; a.download='backup_ftp_session.json';
    document.body.appendChild(a); a.click();
    setTimeout(()=>{ URL.revokeObjectURL(url); a.remove(); }, 400);
  }

  function processEvents(newEvents){
    if(!Array.isArray(newEvents)) return;
    let addedSent=0, addedSkipped=0, addedErr=0, addedFatal=0;
    const tSent = document.querySelector('#tbl-enviados tbody');
    const tSkipped = document.querySelector('#tbl-ignorados-ftp tbody');
    const tErr = document.querySelector('#tbl-erros-ftp tbody');
    const tFatal = document.querySelector('#tbl-fatais tbody');
    const tTodos = document.querySelector('#tbl-todos tbody');

    newEvents.forEach(ev => {
      sessionEvents.push(ev);
      const evt = ev.event || ev.type || '';
      const ts = ev.ts || '';
      if(evt==='sent'){
        addedSent++;
        ensureTableVisible('enviados-empty','tbl-enviados');
        appendRow(tSent, `<tr><td class="small"><code>${ev.file||''}</code></td><td class="small"><code>${ev.remote||''}</code></td><td class="text-end small">${fmtSize(ev.size||0)}</td><td class="small"><code>${ev.hash||''}</code></td><td class="small">${ts}</td></tr>`);
      } else if(evt==='skipped'){
        addedSkipped++;
        ensureTableVisible('ignorados-empty-ftp','tbl-ignorados-ftp');
        appendRow(tSkipped, `<tr><td class="small"><code>${ev.file||''}</code></td><td class="small"><code>${ev.remote||''}</code></td><td class="text-end small">${fmtSize(ev.size||0)}</td><td class="small">${ev.reason||'—'}</td><td class="small">${ts}</td></tr>`);
      } else if(evt==='error'){
        addedErr++;
        ensureTableVisible('erros-empty-ftp','tbl-erros-ftp');
        appendRow(tErr, `<tr class="table-danger"><td class="small"><code>${ev.file||'—'}</code></td><td class="small">${(ev.message||'Erro')}</td><td class="small">${ev.line||'—'}</td><td class="small">${ts}</td></tr>`);
      } else if(evt==='fatal'){
        addedFatal++;
        ensureTableVisible('fatais-empty','tbl-fatais');
        appendRow(tFatal, `<tr class="table-danger"><td class="small">${ev.message||'Fatal'}</td><td class="small">${ts}</td></tr>`);
      }
      // Todos
      ensureTableVisible('todos-empty','tbl-todos');
      appendRow(tTodos, `<tr><td class="small">${ts}</td><td class="small">${evt}</td><td class="small"><code>${ev.file||''}</code></td><td class="small"><code>${ev.remote||''}</code></td><td class="small"><code>${(ev.message||ev.reason||ev.hash||'')}</code></td></tr>`);
      document.getElementById('card-last').textContent = evt + ' ' + ts;
    });

    lastCountSent += addedSent;
    lastCountSkipped += addedSkipped;
    lastCountErr += addedErr;
    lastCountFatal += addedFatal;
    document.getElementById('card-sent').textContent = lastCountSent;
    document.getElementById('card-skipped').textContent = lastCountSkipped;
    document.getElementById('card-error').textContent = lastCountErr;
    document.getElementById('card-fatal').textContent = lastCountFatal;
    document.querySelector('[data-bs-target="#tab-enviados"]').textContent = `Enviados (${lastCountSent})`;
    document.querySelector('[data-bs-target="#tab-ignorados"]').textContent = `Ignorados (${lastCountSkipped})`;
    document.querySelector('[data-bs-target="#tab-erros"]').textContent = `Erros (${lastCountErr})`;
    document.querySelector('[data-bs-target="#tab-fatais"]').textContent = `Fatais (${lastCountFatal})`;

    // classes de alerta
    document.getElementById('card-error').classList.toggle('text-danger', lastCountErr>0);
    document.getElementById('card-fatal').classList.toggle('text-danger', lastCountFatal>0);
  }

  async function pollLogs(){
    if(!polling) return; // saiu
    try {
      setPollStatus('buscando...');
      // Pega últimos 200 eventos para reduzir carga e faz diff superficial
      const url = endpointLogsLast + '?n=200&format=json&_ts=' + Date.now();
      const resp = await fetch(url, { headers:{'Accept':'application/json'} });
      if(!resp.ok) throw new Error('HTTP '+resp.status);
      const arr = await resp.json();
      // Evita reprocessar eventos iguais: compara pela soma de hashes ts+event+file
      const knownKeys = new Set(sessionEvents.map(e => (e.ts||'') + '|' + (e.event||'') + '|' + (e.file||'')));
      const newOnes = arr.filter(e => !knownKeys.has((e.ts||'') + '|' + (e.event||'') + '|' + (e.file||'')));
      if(newOnes.length){ processEvents(newOnes); }
      setPollStatus('ativo');
      // Se já chegou evento 'end', podemos parar polling automático
      if(arr.some(e => e.event === 'end')){
        setStatus('Concluído','text-success');
        stopPolling();
      } else {
        pollTimer = setTimeout(pollLogs, 3000);
      }
    } catch(e){
      setPollStatus('erro: '+(e.message||e));
      setStatus('Erro Poll','text-danger');
      pollTimer = setTimeout(pollLogs, 5000); // tenta de novo
    }
  }

  function startPolling(){
    if(polling) return;
    polling = true;
    stopBtn.disabled = false;
    reiniciarBtn.disabled = true;
    setPollStatus('iniciando...');
    setStatus('Em execução','text-warning');
    pollLogs();
  }
  function stopPolling(){
    polling = false;
    stopBtn.disabled = true;
    reiniciarBtn.disabled = false;
    if(pollTimer) clearTimeout(pollTimer);
    setPollStatus('parado');
  }
  function resetSession(){
    sessionEvents = [];
    lastCountSent=lastCountSkipped=lastCountErr=lastCountFatal=0;
    ['sent','skipped','error','fatal'].forEach(k=>{
      document.getElementById('card-'+k).textContent='0';
    });
    document.getElementById('card-last').textContent='—';
    document.getElementById('card-error').classList.remove('text-danger');
    document.getElementById('card-fatal').classList.remove('text-danger');
    // Limpa tabelas
    ['enviados','ignorados-ftp','erros-ftp','fatais','todos'].forEach(id=>{
      const t = document.querySelector(`#tbl-${id} tbody`);
      if(t) t.innerHTML='';
    });
    // Recoloca empty visível
    ['enviados-empty','ignorados-empty-ftp','erros-empty-ftp','fatais-empty','todos-empty'].forEach(id=>{
      const el = document.getElementById(id); if(el) el.classList.remove('d-none');
    });
    ['tbl-enviados','tbl-ignorados-ftp','tbl-erros-ftp','tbl-fatais','tbl-todos'].forEach(id=>{
      const el = document.getElementById(id); if(el) el.classList.add('d-none');
    });
    document.querySelector('[data-bs-target="#tab-enviados"]').textContent = 'Enviados (0)';
    document.querySelector('[data-bs-target="#tab-ignorados"]').textContent = 'Ignorados (0)';
    document.querySelector('[data-bs-target="#tab-erros"]').textContent = 'Erros (0)';
    document.querySelector('[data-bs-target="#tab-fatais"]').textContent = 'Fatais (0)';
    setStatus('Aguardando','text-warning');
  }

  async function startBackup(){
    try {
      startBtn.disabled = true;
      startBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enfileirando...';
      runtime.classList.remove('d-none');
      initial.classList.add('d-none');
      resetSession();
      exportBtn.disabled = true;
      const resp = await fetch(endpointStart, { headers:{ 'Accept':'application/json' } });
      if(!resp.ok) throw new Error('Falha HTTP '+resp.status);
      await resp.json();
      // Inicia polling
      startPolling();
      exportBtn.disabled = false;
      stopBtn.disabled = false;
    } catch(e){
      alert('Falha ao iniciar backup FTP: ' + (e.message||e));
      initial.classList.remove('d-none');
      runtime.classList.add('d-none');
    } finally {
      startBtn.disabled = false;
      startBtn.innerHTML = '<span class="me-2"><i class="fa-solid fa-play"></i></span>Iniciar backup agora';
    }
  }

  if(startBtn) startBtn.addEventListener('click', startBackup);
  if(stopBtn) stopBtn.addEventListener('click', ()=> stopPolling());
  if(reiniciarBtn) reiniciarBtn.addEventListener('click', ()=>{ resetSession(); startPolling(); });
  if(exportBtn) exportBtn.addEventListener('click', exportSession);
})();
</script>
@endpush
