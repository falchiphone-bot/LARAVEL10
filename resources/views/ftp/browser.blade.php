@php(/** @var string $dir */ '')
@php(/** @var string|null $parent */ '')
@php(/** @var array<int,array{basename:string,path:string}> $directories */ '')
@php(/** @var array<int,array{basename:string,path:string,size:int|null,size_human:?string}> $files */ '')
@php(/** @var callable $encoded */ '')

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Navegação FTP
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm text-gray-600">Diretório atual: <span class="font-mono">/{{ $dir === '' ? '' : $dir }}</span></p>
                        @if($parent !== null)
                            <a href="?p={{ $encoded($parent) }}" class="text-indigo-600 text-sm hover:underline">&larr; Voltar</a>
                        @endif
                    </div>
                    <div class="text-right text-xs text-gray-500">
                        <p>Downloads bloqueados a partir do IP 186.237.225.6</p>
                    </div>
                </div>

                @can('backup.executar.ftp')
                <div class="mb-6 border border-indigo-200 rounded p-4 bg-indigo-50">
                    <div class="flex flex-wrap items-center gap-3">
                        <button id="run-backup-btn" class="px-3 py-2 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            Iniciar Backup (Storage → FTP)
                        </button>
                        <button id="refresh-logs-btn" class="px-3 py-2 bg-white border border-indigo-400 text-indigo-700 text-sm rounded hover:bg-indigo-100">
                            Atualizar Logs
                        </button>
                        <span id="backup-status" class="text-sm text-indigo-700"></span>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-xs font-semibold text-indigo-900 uppercase tracking-wide">Últimos eventos (log)</h3>
                        <div id="logs-box" class="mt-2 max-h-48 overflow-auto text-[11px] font-mono bg-white border border-indigo-100 rounded p-2 space-y-0.5"></div>
                        <div class="mt-2 text-right">
                            <a href="{{ url('/backup/ftp-logs') }}" target="_blank" class="text-indigo-600 hover:underline text-xs">Ver log completo &rarr;</a>
                        </div>
                    </div>
                </div>
                @endcan

                <table class="min-w-full text-sm border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 text-left">Nome</th>
                            <th class="px-3 py-2 text-left w-32">Tamanho</th>
                            <th class="px-3 py-2 w-20">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($directories as $d)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2">
                                    <a href="?p={{ $encoded($d['path']) }}" class="text-indigo-600 font-semibold">📁 {{ $d['basename'] }}</a>
                                </td>
                                <td class="px-3 py-2 text-gray-400">—</td>
                                <td class="px-3 py-2 text-center">—</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-3 py-2 text-gray-400">Nenhum diretório</td>
                            </tr>
                        @endforelse
                        @foreach($files as $f)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 font-mono">📄 {{ $f['basename'] }}</td>
                                <td class="px-3 py-2">{{ $f['size_human'] ?? '—' }}</td>
                                <td class="px-3 py-2 text-center">
                                    <a href="{{ route('ftp.download', ['f' => $encoded($f['path'])]) }}" class="text-indigo-600 hover:underline">Baixar</a>
                                </td>
                            </tr>
                        @endforeach
                        @if(empty($files) && empty($directories))
                            <tr>
                                <td colspan="3" class="px-3 py-2 text-gray-400">Diretório vazio</td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                <p class="mt-4 text-xs text-gray-500">As listagens são obtidas diretamente do FTP a cada requisição. Para otimizar, poderemos adicionar cache posteriormente.</p>
            </div>
        </div>
    </div>
</x-app-layout>

@can('backup.executar.ftp')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('run-backup-btn');
    const statusEl = document.getElementById('backup-status');
    const logsBox = document.getElementById('logs-box');
    const refreshBtn = document.getElementById('refresh-logs-btn');
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
</script>
@endcan
