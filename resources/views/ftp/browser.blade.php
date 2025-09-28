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
                    <button id="refresh-pull-logs-btn" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
                        <i class="fa fa-rotate"></i><span>Atualizar Logs</span>
                    </button>
                    <small class="text-muted">Copia apenas arquivos novos ou alterados (comparação por tamanho).</small>
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
            {{-- Card de backup removido conforme solicitação --}}
    let polling = null;

    function appendLog(lineObj) {
        if (!logsBox) return;
        const div = document.createElement('div');
        const evt = lineObj.event || 'evt';
        let color = 'text-gray-700';
        if (evt === 'sent') color = 'text-green-700';
        else if (evt === 'skipped') color = 'text-yellow-700';
        else if (evt === 'error' || evt === 'fatal') color = 'text-red-700';
        else if (evt === 'dry-run') color = 'text-blue-700';
        div.className = color;
        div.textContent = `[${evt}] ${lineObj.file || lineObj.remote || ''}`;
        logsBox.appendChild(div);
        logsBox.scrollTop = logsBox.scrollHeight;
        // Limitar a 300 linhas
        if (logsBox.children.length > 300) logsBox.removeChild(logsBox.firstChild);
    }

    function loadLastLogs() {
        fetch("{{ url('/backup/ftp-logs/download-last?n=40') }}")
            .then(r => r.json())
            .then(arr => {
                if (!Array.isArray(arr)) return;
                logsBox.innerHTML = '';
                arr.slice(-40).forEach(o => appendLog(o));
            })
            .catch(() => {});
    }

    function startPolling() {
        if (polling) return;
        polling = setInterval(loadLastLogs, 4000);
    }

    if (refreshBtn) {
        refreshBtn.addEventListener('click', e => {
            e.preventDefault();
            loadLastLogs();
        });
    }

    if (btn) {
        btn.addEventListener('click', () => {
            if (btn.disabled) return;
            btn.disabled = true;
            statusEl.textContent = 'Enfileirando backup...';
            fetch("{{ url('/backup/storage-to-ftp') }}", { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'ok') {
                        statusEl.textContent = data.mensagem || 'Backup enfileirado.';
                        startPolling();
                        setTimeout(() => { btn.disabled = false; }, 4000);
                    } else {
                        statusEl.textContent = 'Falha ao enfileirar backup.';
                        btn.disabled = false;
                    }
                })
                .catch(() => {
                    statusEl.textContent = 'Erro na requisição.';
                    btn.disabled = false;
                });
        });
    }

    loadLastLogs();
    startPolling();
});
});
</script>
@endcan

@can('backup.executar.ftp')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const pullBtn = document.getElementById('run-pull-btn');
    const pullStatus = document.getElementById('pull-status');
    const pullLogsBox = document.getElementById('pull-logs-box');
    const pullRefreshBtn = document.getElementById('refresh-pull-logs-btn');
    let pullPolling = null;

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
        pullLogsBox.scrollTop = pullLogsBox.scrollHeight;
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
                        setTimeout(()=>{ pullBtn.disabled = false; }, 4000);
                    } else {
                        pullStatus.textContent = 'Falha ao iniciar.';
                        pullBtn.disabled = false;
                    }
                })
                .catch(()=>{ pullStatus.textContent = 'Erro na requisição.'; pullBtn.disabled = false; });
        });
    }

    loadPullLogs();
    startPullPolling();
});
</script>
@endcan
