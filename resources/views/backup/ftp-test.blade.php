@extends('layouts.bootstrap5')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0"><i class="fa-solid fa-plug-circle-check me-2"></i>Teste de Conexão FTP (Dry-run)</h1>
    <button id="run-test" class="btn btn-primary">
      <i class="fa-solid fa-play me-1"></i> Executar teste
    </button>
  </div>

  <div class="alert alert-info" id="info-box">
    Este teste faz uma execução <strong>dry-run</strong> do comando de backup FTP (limite 1 arquivo) apenas para validar acesso, DNS e porta.
  </div>

  <div id="result" class="d-none">
    <div class="card shadow-sm mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Resultado</span>
        <span id="status-badge" class="badge text-bg-secondary">Aguardando…</span>
      </div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-sm-3">Host</dt>
          <dd class="col-sm-9" id="r-host">—</dd>
          <dt class="col-sm-3">Porta</dt>
          <dd class="col-sm-9" id="r-port">—</dd>
        </dl>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Saída (log)</span>
        <div class="btn-group btn-group-sm">
          <button class="btn btn-outline-secondary" id="copy-output"><i class="fa-solid fa-copy"></i></button>
          <button class="btn btn-outline-secondary" id="clear-output"><i class="fa-solid fa-eraser"></i></button>
        </div>
      </div>
      <div class="card-body p-0">
        <pre class="mb-0 bg-dark text-light" style="max-height:420px; overflow:auto; font-size: .8rem;" id="r-output"><code>—</code></pre>
      </div>
    </div>
  </div>

  <div id="error" class="alert alert-danger d-none mt-3"></div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const btn = document.getElementById('run-test');
  const resultBlock = document.getElementById('result');
  const errorBlock = document.getElementById('error');
  const hostEl = document.getElementById('r-host');
  const portEl = document.getElementById('r-port');
  const outputEl = document.getElementById('r-output').querySelector('code');
  const statusBadge = document.getElementById('status-badge');
  const copyBtn = document.getElementById('copy-output');
  const clearBtn = document.getElementById('clear-output');

  function setStatus(text, cls){
    statusBadge.textContent = text;
    statusBadge.className = 'badge ' + cls;
  }

  async function run(){
    errorBlock.classList.add('d-none');
    resultBlock.classList.remove('d-none');
    setStatus('Executando…','text-bg-warning');
    hostEl.textContent = '…';
    portEl.textContent = '…';
    outputEl.textContent = '';
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Testando';
    try {
      const resp = await fetch(@json(route('backup.ftp-test')), { headers: { 'Accept':'application/json' } });
      const data = await resp.json().catch(()=>null);
      if(!resp.ok || !data){
        throw new Error(data && data.message ? data.message : 'Falha inesperada');
      }
      hostEl.textContent = data.host || '—';
      portEl.textContent = data.port || '—';
      outputEl.textContent = (data.output || 'Sem saída').trim();
      if(data.ok){
        setStatus('OK','text-bg-success');
      } else {
        setStatus('Falhou','text-bg-danger');
      }
    } catch (e){
      setStatus('Erro','text-bg-danger');
      errorBlock.textContent = e.message || 'Erro desconhecido';
      errorBlock.classList.remove('d-none');
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-play me-1"></i> Executar teste';
    }
  }

  btn.addEventListener('click', run);
  copyBtn.addEventListener('click', function(){
    try { navigator.clipboard.writeText(outputEl.textContent).then(()=>{ copyBtn.innerHTML = '<i class="fa-solid fa-check"></i>'; setTimeout(()=>copyBtn.innerHTML='<i class=\'fa-solid fa-copy\'></i>',1200); }); }catch(e){}
  });
  clearBtn.addEventListener('click', function(){ outputEl.textContent=''; });
})();
</script>
@endpush
