@extends('layouts.bootstrap5')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-semibold mb-4">Logs do Backup FTP</h1>

    <div class="mb-3 d-flex gap-2 flex-wrap align-items-end">
        <a href="{{ url('/backup/storage-to-ftp') }}" class="btn btn-outline-primary btn-sm">Executar backup agora</a>
        <a href="{{ url('/backup/ftp-logs') }}" class="btn btn-outline-secondary btn-sm">Atualizar</a>
        <form method="post" action="{{ url('/backup/ftp-logs/clear') }}" onsubmit="return confirm('Arquivar e limpar logs atuais?');">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm">Arquivar e limpar logs</button>
        </form>

        <a href="{{ url('/backup/ftp-logs/download') }}" class="btn btn-outline-success btn-sm">Baixar log completo (NDJSON)</a>

        <form method="get" action="{{ url('/backup/ftp-logs/download-last') }}" class="d-flex gap-2 align-items-end">
            <div>
                <label class="form-label">Últimos N</label>
                <input type="number" name="n" value="100" min="1" max="5000" class="form-control form-control-sm" style="width:110px">
            </div>
            <div>
                <label class="form-label">Formato</label>
                <select name="format" class="form-select form-select-sm">
                    <option value="json" selected>JSON</option>
                    <option value="ndjson">NDJSON</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-outline-success btn-sm">Baixar últimos</button>
            </div>
        </form>
    </div>

    @if (session('status'))
        <div class="alert alert-info py-1 px-2 mb-2">{{ session('status') }}</div>
    @endif

    @if(!$exists)
        <div class="p-3 bg-yellow-100 text-yellow-900 rounded">Nenhum arquivo de log encontrado em storage/logs/backup_ftp.jsonl</div>
    @endif

    <div class="text-sm text-gray-600 mb-2">Tamanho do log: {{ number_format($size) }} bytes</div>

    <form method="get" class="mb-3 d-flex gap-2 align-items-end flex-wrap">
        <div>
            <label class="form-label">Evento</label>
            <select name="event" class="form-select form-select-sm">
                <option value="">Todos</option>
                @foreach(['start','sent','skipped','error','fatal','dry-run','end'] as $opt)
                    <option value="{{ $opt }}" @if(($event ?? '')===$opt) selected @endif>{{ $opt }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Busca</label>
            <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control form-control-sm" placeholder="arquivo, mensagem…">
        </div>
        <div>
            <label class="form-label">Por página</label>
            <input type="number" name="perPage" value="{{ $perPage ?? 50 }}" min="1" max="500" class="form-control form-control-sm" style="width:100px">
        </div>
        <div>
            <button class="btn btn-sm btn-primary" type="submit">Filtrar</button>
            <a class="btn btn-sm btn-outline-secondary" href="{{ url('/backup/ftp-logs') }}">Limpar</a>
        </div>
    </form>

    <div class="mb-2 text-sm">
        <span class="badge text-bg-success">Enviados: {{ $stats['sent'] ?? 0 }}</span>
        <span class="badge text-bg-secondary">Ignorados: {{ $stats['skipped'] ?? 0 }}</span>
        <span class="badge text-bg-danger">Erros: {{ $stats['error'] ?? 0 }}</span>
        <span class="badge text-bg-dark">Fatais: {{ $stats['fatal'] ?? 0 }}</span>
        <span class="badge text-bg-info">Total: {{ $stats['total'] ?? 0 }}</span>
    </div>

    <div class="overflow-x-auto border rounded">
        <table class="min-w-full text-sm table table-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-3 py-2 text-left">Data/Hora</th>
                    <th class="px-3 py-2 text-left">Evento</th>
                    <th class="px-3 py-2 text-left">Arquivo</th>
                    <th class="px-3 py-2 text-left">Detalhes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr class="border-top">
                        <td class="px-3 py-2 whitespace-nowrap">{{ $row['ts'] ?? '-' }}</td>
                        <td class="px-3 py-2">{{ $row['event'] ?? ($row['type'] ?? '-') }}</td>
                        <td class="px-3 py-2">{{ $row['file'] ?? ($row['remote'] ?? '-') }}</td>
                        <td class="px-3 py-2">
                            <pre class="whitespace-pre-wrap text-xs">{{ json_encode($row, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-3 py-4 text-center text-gray-500">Nenhum registro</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-2 d-flex justify-content-between align-items-center">
        <div class="text-sm">Página {{ $page ?? 1 }} de {{ $pages ?? 1 }}</div>
        <div class="btn-group">
            @php
                $query = request()->query();
                $prevQuery = $query; $prevQuery['page'] = max(1, ($page ?? 1) - 1);
                $nextQuery = $query; $nextQuery['page'] = min(($pages ?? 1), ($page ?? 1) + 1);
            @endphp
            <a class="btn btn-sm btn-outline-secondary @if(($page ?? 1) <= 1) disabled @endif" href="{{ url('/backup/ftp-logs') . '?' . http_build_query($prevQuery) }}">Anterior</a>
            <a class="btn btn-sm btn-outline-secondary @if(($page ?? 1) >= ($pages ?? 1)) disabled @endif" href="{{ url('/backup/ftp-logs') . '?' . http_build_query($nextQuery) }}">Próxima</a>
        </div>
    </div>
</div>
@endsection
