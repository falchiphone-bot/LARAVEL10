@php(/** @var string $dir */ '')
@php(/** @var string|null $parent */ '')
@php(/** @var array<int,array{basename:string,path:string}> $directories */ '')
@php(/** @var array<int,array{basename:string,path:string,size:int|null,size_human:?string}> $files */ '')
@php(/** @var callable $encoded */ '')

@extends('layouts.bootstrap5')

@section('content')
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
        <div class="mb-2">
            <h1 class="h4 mb-1">Navegação FTP</h1>
            <div class="small text-muted">Diretório atual: <code class="fw-semibold">/{{ $dir === '' ? '' : $dir }}</code></div>
            @if($parent !== null)
                <div class="mt-1">
                    <a href="?p={{ $encoded($parent) }}" class="link-primary small"><i class="fa fa-arrow-left me-1"></i>Voltar</a>
                </div>
            @endif
        </div>
        <div class="text-end small text-secondary mb-2">
            Downloads bloqueados a partir do IP <span class="fw-bold">186.237.225.6</span>
        </div>
    </div>

        @can('backup.executar.ftp')
        <div class="card mb-4 shadow-sm">
            <div class="card-header py-2 d-flex align-items-center gap-2">
                <i class="fa fa-cloud-download-alt text-success"></i>
                <span class="fw-semibold">Sincronização (FTP → Local)</span>
                <span id="pull-status" class="ms-auto small text-success"></span>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <button id="run-pull-btn" class="btn btn-sm btn-success d-inline-flex align-items-center gap-1">
                        <i class="fa fa-download"></i><span>Iniciar Sincronização</span>
                    </button>
                    <button id="cancel-pull-btn" class="btn btn-sm btn-warning d-inline-flex align-items-center gap-1">
                        <i class="fa fa-stop"></i><span>Cancelar</span>
                    </button>
                    <button id="reset-pull-btn" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1">
                        <i class="fa fa-rotate-left"></i><span>Reset</span>
                    </button>
                    <button id="refresh-pull-logs-btn" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
                        <i class="fa fa-rotate"></i><span>Atualizar Logs</span>
                    </button>
                    <div class="form-check form-switch ms-2">
                        <input class="form-check-input" type="checkbox" checked id="autoscroll-toggle">
                        <label class="form-check-label small" for="autoscroll-toggle">Auto-scroll</label>
                    </div>
                    <small class="text-muted">Copia apenas arquivos novos ou alterados (comparação por tamanho).</small>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span>Progresso</span>
                        <span id="pull-progress-label" class="text-muted">0%</span>
                    </div>
                    <div class="progress" style="height:14px;">
                        <div id="pull-progress-bar" class="progress-bar progress-bar-striped" role="progressbar" style="width:0%">&nbsp;</div>
                    </div>
                    <div class="row row-cols-2 row-cols-md-4 g-2 mt-2" style="font-size:11px;">
                        <div><span class="text-muted">Total:</span> <span id="stat-total">0</span></div>
                        <div><span class="text-muted">Processados:</span> <span id="stat-processed">0</span></div>
                        <div><span class="text-muted">Baixados:</span> <span id="stat-downloaded">0</span></div>
                        <div><span class="text-muted">Pulados:</span> <span id="stat-skipped">0</span></div>
                        <div><span class="text-muted">Erros:</span> <span id="stat-errors" class="text-danger">0</span></div>
                        <div><span class="text-muted">Dirs:</span> <span id="stat-dirs">0</span></div>
                        <div><span class="text-muted">Velocidade:</span> <span id="stat-speed" class="text-monospace">0 B/s</span></div>
                        <div><span class="text-muted">ETA:</span> <span id="stat-eta" class="text-monospace">--</span></div>
                        <div><span class="text-muted">Duração:</span> <span id="stat-duration" class="text-monospace">--</span></div>
                        <div class="col-12"><span class="text-muted">Atual:</span> <span id="stat-current" class="text-monospace"></span></div>
                    </div>
                </div>
                <div>
                    <h6 class="text-uppercase small text-muted mb-2">Últimos eventos (pull)</h6>
                    <div id="pull-logs-box" class="border rounded bg-light p-2" style="max-height: 210px; overflow:auto; font-size:11px; font-family: monospace;"></div>
                </div>
            </div>
        </div>
        @endcan

    @if(!empty($error))
        <div class="alert alert-danger border-2 border-danger-subtle">
            <strong>Não foi possível listar o diretório raiz do FTP.</strong><br>
            <span class="d-block mt-1">Mensagem técnica: <code>{{ $error }}</code></span>
        </div>
    @endif

    @if(empty($error))
        <div class="card mb-4 shadow-sm">
            <div class="card-header py-2 d-flex align-items-center gap-2">
                <i class="fa fa-folder-open text-primary"></i>
                <span class="fw-semibold">Conteúdo</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th class="text-end">Tamanho</th>
                            <th class="text-end" style="width: 120px;">Ações</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($directories as $d)
                            <tr>
                                <td><i class="fa fa-folder text-warning me-1"></i> <a href="?p={{ $encoded($d['path']) }}" class="text-decoration-none">{{ $d['basename'] }}</a></td>
                                <td class="text-end text-muted">—</td>
                                <td class="text-end small text-muted">&nbsp;</td>
                            </tr>
                        @empty
                        @endforelse
                        @forelse($files as $f)
                            <tr>
                                <td><i class="fa fa-file text-secondary me-1"></i> {{ $f['basename'] }}</td>
                                <td class="text-end"><span class="text-monospace small">{{ $f['size_human'] ?? $f['size'] ?? '' }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('ftp.download', ['path' => $f['path']]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted small py-3">Nenhum arquivo encontrado.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

@can('backup.executar.ftp')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const pullBtn = document.getElementById('run-pull-btn');
    const pullStatus = document.getElementById('pull-status');
    const pullLogsBox = document.getElementById('pull-logs-box');
    const pullRefreshBtn = document.getElementById('refresh-pull-logs-btn');
    const cancelBtn = document.getElementById('cancel-pull-btn');
    const resetBtn = document.getElementById('reset-pull-btn');
    const autoScrollToggle = document.getElementById('autoscroll-toggle');
    let pullPolling = null;
    let pullStatusTimer = null;

    function pullAppend(line){
        if (!pullLogsBox) return;
        const div = document.createElement('div');
        const evt = line.event || 'evt';
        let cls = 'text-dark';
        if (evt === 'download') cls = 'text-success';
        else if (evt === 'skip') cls = 'text-warning';
        else if (evt === 'error') cls = 'text-danger';
        else if (evt === 'mkdir') cls = 'text-primary';
        else if (evt === 'end') cls = 'fw-bold text-success';
        div.className = cls;
        const remote = line.remote || '';
        const local = line.local ? (' → ' + line.local) : '';
        div.textContent = '['+evt+'] ' + remote + local;
        pullLogsBox.appendChild(div);
        if (autoScrollToggle?.checked) {
            pullLogsBox.scrollTop = pullLogsBox.scrollHeight;
        }
        if (pullLogsBox.children.length > 400) pullLogsBox.removeChild(pullLogsBox.firstChild);
    }

    function loadPullLogs(){
        fetch("{{ route('ftp.pull.logs') }}?n=120")
            .then(r=>r.json())
            .then(arr => { if(!Array.isArray(arr)) return; pullLogsBox.innerHTML=''; arr.forEach(o=>pullAppend(o)); })
            .catch(()=>{});
    }

    function startPullPolling(){
        if (pullPolling) return; pullPolling = setInterval(loadPullLogs, 5000);
    }

    // ---- Status / Progresso ----
    const bar = document.getElementById('pull-progress-bar');
    const label = document.getElementById('pull-progress-label');
    const statTotal = document.getElementById('stat-total');
    const statProcessed = document.getElementById('stat-processed');
    const statDownloaded = document.getElementById('stat-downloaded');
    const statSkipped = document.getElementById('stat-skipped');
    const statErrors = document.getElementById('stat-errors');
    const statDirs = document.getElementById('stat-dirs');
    const statCurrent = document.getElementById('stat-current');
    const statSpeed = document.getElementById('stat-speed');
    const statEta = document.getElementById('stat-eta');
    const statDuration = document.getElementById('stat-duration');

    function updateProgressUI(data){
        if(!data) return;
        const total = Number(data.files_total||0);
        const processed = Number(data.processed||0);
        const pct = total>0 ? Math.min(100, Math.round(processed*100/total)) : (data.state==='finished'?100:0);
        if (bar){
            bar.style.width = pct+'%';
            bar.classList.toggle('bg-success', data.state==='finished');
            bar.classList.toggle('progress-bar-animated', data.state==='running');
        }
        if (label) label.textContent = pct+'%';
        if (statTotal) statTotal.textContent = total;
        if (statProcessed) statProcessed.textContent = processed;
        if (statDownloaded) statDownloaded.textContent = data.downloaded||0;
        if (statSkipped) statSkipped.textContent = data.skipped||0;
        if (statErrors) statErrors.textContent = data.errors||0;
        if (statDirs) statDirs.textContent = data.dirs||0;
    if (statCurrent) statCurrent.textContent = data.current||'';
    if (statSpeed) statSpeed.textContent = humanSpeed(data.avg_bytes_per_sec||0);
    if (statEta) statEta.textContent = humanEta(data.eta_seconds);
    if (statDuration) statDuration.textContent = data.duration_human || (data.state==='running' ? '--' : statDuration.textContent);
        if (pullStatus){
            if (data.state==='finished') pullStatus.textContent = 'Concluído';
            else if (data.state==='running') pullStatus.textContent = 'Em execução';
            else if (data.state==='limit') pullStatus.textContent = 'Limite atingido';
            else if (data.state==='cancelled') pullStatus.textContent = 'Cancelado';
            else pullStatus.textContent = '';
        }
    }

    function fetchStatus(){
        fetch("{{ route('ftp.pull.status') }}")
            .then(r=>r.json())
            .then(js => {
                updateProgressUI(js);
                // Se concluído, parar timer
                if (js && (js.state==='finished' || js.state==='idle')) {
                    if (pullStatusTimer){ clearInterval(pullStatusTimer); pullStatusTimer=null; }
                }
            })
            .catch(()=>{});
    }

    function startStatusTimer(){
        if (pullStatusTimer) return; pullStatusTimer = setInterval(fetchStatus, 3000); fetchStatus();
    }

    function humanSpeed(b){
        const units=['B/s','KB/s','MB/s','GB/s'];
        let u=0; let val=b;
        while(val>1024 && u<units.length-1){ val/=1024; u++; }
        return (val>10?val.toFixed(0):val.toFixed(1))+' '+units[u];
    }
    function humanEta(s){
        if(s==null) return '--';
        if(s<0) return '--';
        const h=Math.floor(s/3600); s%=3600; const m=Math.floor(s/60); const sec=s%60;
        if(h>0) return `${h}h ${m}m`; if(m>0) return `${m}m ${sec}s`; return `${sec}s`;
    }

    if (pullRefreshBtn){ pullRefreshBtn.addEventListener('click', e=>{ e.preventDefault(); loadPullLogs(); }); }

    if (pullBtn){
        pullBtn.addEventListener('click', ()=>{
            if (pullBtn.disabled) return;
            pullBtn.disabled = true;
            pullStatus.textContent = 'Enfileirando sincronização...';
            fetch("{{ route('ftp.pull.start') }}", { method: 'POST', headers: { 'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' } })
                .then(r=>r.json().catch(()=>null))
                .then(data => {
                    if (data && data.status === 'ok') {
                        pullStatus.textContent = data.message || 'Sincronização iniciada.';
                        startPullPolling();
                        startStatusTimer();
                        setTimeout(()=>{ pullBtn.disabled = false; }, 4000);
                    } else {
                        pullStatus.textContent = 'Falha ao iniciar.';
                        pullBtn.disabled = false;
                    }
                })
                .catch(()=>{ pullStatus.textContent = 'Erro na requisição.'; pullBtn.disabled = false; });
        });
    }

    if (cancelBtn){
        cancelBtn.addEventListener('click', ()=>{
            fetch("{{ route('ftp.pull.cancel') }}", { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' } })
                .then(()=>fetchStatus());
        });
    }
    if (resetBtn){
        resetBtn.addEventListener('click', ()=>{
            fetch("{{ route('ftp.pull.reset') }}", { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' } })
                .then(()=>{ fetchStatus(); pullLogsBox.innerHTML=''; });
        });
    }

    loadPullLogs();
    startPullPolling();
    startStatusTimer();
});
</script>
@endcan

@endsection
