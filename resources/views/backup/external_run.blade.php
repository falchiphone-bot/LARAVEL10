@extends('layouts.bootstrap5')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0"><i class="fa-solid fa-hard-drive me-2"></i>Backup para HD Externo</h1>
  </div>

  <div id="backup-initial" class="mb-4">
    <div class="alert alert-info d-flex align-items-center gap-2">
      <i class="fa-solid fa-circle-info fs-4"></i>
      <div>
        Clique em <strong>Iniciar backup</strong> para copiar arquivos do storage local para o HD externo.<br>
        Ao finalizar você verá quais foram copiados, ignorados e erros (se houver).
      </div>
    </div>
    <button id="btn-start-backup" class="btn btn-primary btn-lg">
      <span class="me-2"><i class="fa-solid fa-play"></i></span>Iniciar backup agora
    </button>
  </div>

  <div id="backup-runtime" class="d-none">
    <div class="row g-3 mb-3" id="cards-summary">
      <div class="col-md-3">
        <div class="card shadow-sm h-100"><div class="card-body text-center"><div class="fw-semibold text-muted">Copiados</div><div id="card-copiados" class="display-6 fw-bold text-success">0</div></div></div>
      </div>
      <div class="col-md-3">
        <div class="card shadow-sm h-100"><div class="card-body text-center"><div class="fw-semibold text-muted">Ignorados</div><div id="card-ignorados" class="display-6 fw-bold text-secondary">0</div></div></div>
      </div>
      <div class="col-md-3">
        <div class="card shadow-sm h-100"><div class="card-body text-center"><div class="fw-semibold text-muted">Erros</div><div id="card-erros" class="display-6 fw-bold text-success">0</div></div></div>
      </div>
      <div class="col-md-3">
        <div class="card shadow-sm h-100"><div class="card-body text-center"><div class="fw-semibold text-muted">Duração</div><div id="card-duracao" class="display-6 fw-bold text-info">0 ms</div></div></div>
      </div>
    </div>

    <div id="exec-window" class="alert alert-secondary py-2 small d-flex justify-content-between align-items-center">
      <div>
        <strong>Execução:</strong> <span id="exec-start">—</span> → <span id="exec-end">—</span>
      </div>
      <div class="d-flex gap-2">
        <button id="btn-export-json" class="btn btn-sm btn-outline-dark" disabled><i class="fa-solid fa-file-export me-1"></i>Exportar JSON</button>
        <button id="btn-reexecutar" class="btn btn-sm btn-outline-primary" disabled><i class="fa-solid fa-rotate me-1"></i>Reexecutar</button>
      </div>
    </div>

    <ul class="nav nav-tabs" id="backupTabs" role="tablist">
      <li class="nav-item"><button class="nav-link active" id="copiados-tab" data-bs-toggle="tab" data-bs-target="#tab-copiados" type="button" role="tab">Copiados (0)</button></li>
      <li class="nav-item"><button class="nav-link" id="ignorados-tab" data-bs-toggle="tab" data-bs-target="#tab-ignorados" type="button" role="tab">Ignorados (0)</button></li>
      <li class="nav-item"><button class="nav-link" id="erros-tab" data-bs-toggle="tab" data-bs-target="#tab-erros" type="button" role="tab">Erros (0)</button></li>
    </ul>
    <div class="tab-content border border-top-0 p-3 bg-white shadow-sm" id="backupTabsContent">
      <div class="tab-pane fade show active" id="tab-copiados" role="tabpanel">
        <p class="text-muted" id="copiados-empty">Nenhum arquivo copiado (ainda).</p>
        <div class="table-responsive d-none" style="max-height:480px; overflow:auto;" id="copiados-wrapper">
          <table class="table table-sm table-striped align-middle" id="tbl-copiados">
            <thead class="table-light"><tr><th>Arquivo</th><th class="text-end">Tamanho</th><th>Hash</th></tr></thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
      <div class="tab-pane fade" id="tab-ignorados" role="tabpanel">
        <p class="text-muted" id="ignorados-empty">Nenhum arquivo ignorado (ainda).</p>
        <div class="table-responsive d-none" style="max-height:480px; overflow:auto;" id="ignorados-wrapper">
          <table class="table table-sm table-hover align-middle" id="tbl-ignorados">
            <thead class="table-light"><tr><th>Arquivo</th><th class="text-end">Tamanho</th><th>Hash</th></tr></thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
      <div class="tab-pane fade" id="tab-erros" role="tabpanel">
        <p class="text-muted" id="erros-empty">Nenhum erro (ainda).</p>
        <div class="table-responsive d-none" style="max-height:480px; overflow:auto;" id="erros-wrapper">
          <table class="table table-sm table-bordered align-middle" id="tbl-erros">
            <thead class="table-light"><tr><th>Arquivo</th><th>Mensagem</th><th>Linha</th></tr></thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const startBtn = document.getElementById('btn-start-backup');
  const runtime = document.getElementById('backup-runtime');
  const initial = document.getElementById('backup-initial');
  const exportBtn = document.getElementById('btn-export-json');
  const reexecBtn = document.getElementById('btn-reexecutar');
  const endpoint = @json(url('/backup/storage-to-external'));
  let lastPayload = null;

  function fmtSize(n){ return (Number(n)||0).toLocaleString('pt-BR') + ' B'; }
  function fmtDate(ts){ const d = new Date(ts); return d.toLocaleString('pt-BR'); }
  function fillTable(tbody, rows){
    tbody.innerHTML = rows.map(r => `<tr><td><code class="small">${r.file||''}</code></td><td class="text-end small">${fmtSize(r.size||0)}</td><td class="small"><code>${r.hash||''}</code></td></tr>`).join('');
  }
  function fillErrors(tbody, rows){
    tbody.innerHTML = rows.map(r => `<tr class="table-danger"><td class="small"><code>${r.file||'—'}</code></td><td class="small">${(r.error||r.message||'Erro')}</td><td class="small">${r.line||'—'}</td></tr>`).join('');
  }
  function resetUI(){
    ['copiados','ignorados','erros'].forEach(k => {
      document.getElementById(`card-${k}`).textContent = '0';
      if(k==='erros'){
        document.getElementById('card-erros').classList.remove('text-danger');
        document.getElementById('card-erros').classList.add('text-success');
      }
      document.getElementById(`${k}-empty`).classList.remove('d-none');
      document.getElementById(`${k}-wrapper`).classList.add('d-none');
      document.querySelector(`#tbl-${k} tbody`)?.replaceChildren();
    });
    document.getElementById('copiados-tab').textContent = 'Copiados (0)';
    document.getElementById('ignorados-tab').textContent = 'Ignorados (0)';
    document.getElementById('erros-tab').textContent = 'Erros (0)';
    document.getElementById('card-duracao').textContent = '0 ms';
    document.getElementById('exec-start').textContent = '—';
    document.getElementById('exec-end').textContent = '—';
  }
  function updateUI(data){
    const copied = data.copied_detailed || [];
    const skipped = data.skipped_detailed || [];
    const errs = data.erros || [];
    document.getElementById('card-copiados').textContent = copied.length;
    document.getElementById('card-ignorados').textContent = skipped.length;
    document.getElementById('card-erros').textContent = errs.length;
    document.getElementById('card-erros').classList.toggle('text-danger', errs.length>0);
    document.getElementById('card-erros').classList.toggle('text-success', errs.length===0);
    document.getElementById('card-duracao').textContent = (data.duracao_ms||0)+' ms';
    const now = Date.now();
    document.getElementById('exec-start').textContent = fmtDate(now - (data.duracao_ms||0));
    document.getElementById('exec-end').textContent = fmtDate(now);

    if (copied.length){
      document.getElementById('copiados-empty').classList.add('d-none');
      document.getElementById('copiados-wrapper').classList.remove('d-none');
      fillTable(document.querySelector('#tbl-copiados tbody'), copied);
      document.getElementById('copiados-tab').textContent = `Copiados (${copied.length})`;
    }
    if (skipped.length){
      document.getElementById('ignorados-empty').classList.add('d-none');
      document.getElementById('ignorados-wrapper').classList.remove('d-none');
      fillTable(document.querySelector('#tbl-ignorados tbody'), skipped);
      document.getElementById('ignorados-tab').textContent = `Ignorados (${skipped.length})`;
    }
    if (errs.length){
      document.getElementById('erros-empty').classList.add('d-none');
      document.getElementById('erros-wrapper').classList.remove('d-none');
      fillErrors(document.querySelector('#tbl-erros tbody'), errs);
      document.getElementById('erros-tab').textContent = `Erros (${errs.length})`;
    }
  }

  async function runBackup(){
    try {
      startBtn.disabled = true;
      startBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Executando...';
      runtime.classList.remove('d-none');
      initial.classList.add('d-none');
      resetUI();
      const resp = await fetch(endpoint, { headers:{ 'Accept':'application/json' } });
      if(!resp.ok) throw new Error('Falha HTTP '+resp.status);
      const data = await resp.json();
      lastPayload = data;
      updateUI(data);
      exportBtn.disabled = false;
      reexecBtn.disabled = false;
    } catch(e){
      alert('Falha ao executar backup: '+ (e.message||e));
      initial.classList.remove('d-none');
      runtime.classList.add('d-none');
    } finally {
      startBtn.disabled = false;
      startBtn.innerHTML = '<span class="me-2"><i class="fa-solid fa-play"></i></span>Iniciar backup agora';
    }
  }

  if(startBtn) startBtn.addEventListener('click', runBackup);
  if(reexecBtn) reexecBtn.addEventListener('click', () => { if(confirm('Reexecutar agora?')) runBackup(); });
  if(exportBtn) exportBtn.addEventListener('click', () => {
    if(!lastPayload) return;
    const blob = new Blob([JSON.stringify(lastPayload,null,2)], {type:'application/json'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url; a.download = 'backup_external_run.json';
    document.body.appendChild(a); a.click();
    setTimeout(()=>{ URL.revokeObjectURL(url); a.remove(); }, 400);
  });
})();
</script>
@endpush
