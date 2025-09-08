@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
    <div class="mb-3 d-flex gap-2 align-items-center">
        @canany(['OPENAI - CHAT', 'OPENAI - TRANSCRIBE - ESPANHOL'])
        <a href="{{ route('openai.menu') }}" class="btn btn-outline-secondary">← Voltar ao Menu</a>
        @endcanany
        <a href="{{ route('openai.chat.new') }}" class="btn btn-primary">Novo Chat</a>
        @php
            $lastParams = session('openai_chats_last_params', []);
            $baseParams = array_filter([
                'q' => $lastParams['q'] ?? null,
                'type_id' => $lastParams['type_id'] ?? null,
                'sort' => $lastParams['sort'] ?? null,
                'dir' => $lastParams['dir'] ?? null,
                'per_page' => $lastParams['per_page'] ?? null,
            ], fn($v) => !is_null($v) && $v !== '');
        @endphp
        @php $lastView = $lastParams['view'] ?? session('openai_chats_last_view', 'cards'); @endphp
        <div class="btn-group" role="group" aria-label="Visualização de conversas">
            <a href="{{ route('openai.chats', array_merge($baseParams, ['view' => 'cards'])) }}" class="btn {{ $lastView==='cards' ? 'btn-primary' : 'btn-outline-secondary' }}">Conversas (Cartões)</a>
            <a href="{{ route('openai.chats', array_merge($baseParams, ['view' => 'table'])) }}" class="btn {{ $lastView==='table' ? 'btn-primary' : 'btn-outline-secondary' }}">Conversas (Tabela)</a>
        </div>
        @if($currentChat && $currentChat->type && strtoupper($currentChat->type->name) === 'BOLSA DE VALORES AMERICANA')
            <a href="{{ route('openai.records.index', ['chat_id'=>$currentChat->id]) }}#newRecordForm" class="btn btn-outline-danger">Registros</a>
        @endif
        <form action="{{ route('openai.chat.save') }}" method="POST" class="d-inline-flex align-items-center gap-2">
            @csrf
            <input type="text" name="title" class="form-control form-control-sm" placeholder="Título (opcional)" style="width: 220px;">
            <select name="type_id" class="form-select form-select-sm" style="width: 200px;">
                <option value="">Tipo (opcional)</option>
                @foreach(($types ?? []) as $type)
                    @php
                        $selected = isset($currentChat) && $currentChat && (int)$currentChat->type_id === (int)$type->id;
                    @endphp
                    <option value="{{ $type->id }}" {{ $selected ? 'selected' : '' }}>{{ $type->name }}</option>
                @endforeach
            </select>
            @if(session('openai_current_chat_id'))
                <div class="form-check m-0">
                    <input class="form-check-input" type="checkbox" id="saveAsNew" name="mode" value="new">
                    <label class="form-check-label small" for="saveAsNew">Salvar como nova</label>
                </div>
            @endif
            <button type="submit" class="btn btn-sm btn-success">Salvar conversa</button>
        </form>
        @if(session('openai_current_chat_id'))
            <span class="badge bg-success">Conversa salva ativa</span>
            <small class="text-muted">Novas mensagens serão gravadas automaticamente.</small>
        @endif
    </div>

    @php
        $chatTitle = $currentChat?->title ?? 'Nova Conversa';
        $chatCode  = $currentChat?->code ?? null;
    @endphp
    <div class="d-flex align-items-center flex-wrap gap-3 mb-3">
        <h1 class="h4 mb-0" title="Título da conversa">{{ $chatTitle }}</h1>
        @if($chatCode)
            <span class="badge" style="background:#b30000; font-size:.70rem; letter-spacing:1px;">CÓD: {{ $chatCode }}</span>
        @endif
        @if($currentChat && $currentChat->type && strtoupper($currentChat->type->name) === 'BOLSA DE VALORES AMERICANA')
            @php $imgPath = public_path('images/bolsa-americana.png'); @endphp
            @if(file_exists($imgPath))
                <img src="{{ asset('images/bolsa-americana.png') }}" alt="Bolsa de Valores Americana" style="height:32px" />
            @else
                <span class="badge bg-light text-dark border" title="Bolsa de Valores Americana" style="display:inline-flex; align-items:center; gap:.35rem;">
                    <span style="font-size:1.05rem; line-height:1;">🇺🇸</span>
                    Bolsa de Valores Americana
                </span>
            @endif
        @endif
                @if($currentChat && ($currentChat->target_min !== null || $currentChat->target_avg !== null || $currentChat->target_max !== null))
                        <span class="badge bg-info text-dark" title="Intervalo de preço alvo">
                                Alvo:
                                @php
                                    $parts=[];
                                    if($currentChat->target_min !== null) $parts[]='Min '.number_format($currentChat->target_min,2,',','.');
                                    if($currentChat->target_avg !== null) $parts[]='Méd '.number_format($currentChat->target_avg,2,',','.');
                                    if($currentChat->target_max !== null) $parts[]='Máx '.number_format($currentChat->target_max,2,',','.');
                                    echo implode(' | ', $parts);
                                @endphp
                        </span>
                @endif
        @if($currentChat)
            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#editMeta">Editar Meta</button>
        @endif
    </div>
    @if($currentChat)
    <div id="editMeta" class="collapse mb-4">
        <div class="card border-danger-subtle">
            <div class="card-body py-3">
                                @php
                                    $hasColMin = Schema::hasColumn('open_a_i_chats','target_min');
                                    $hasColAvg = Schema::hasColumn('open_a_i_chats','target_avg');
                                    $hasColMax = Schema::hasColumn('open_a_i_chats','target_max');
                                @endphp
                                @if(!$hasColMin || !$hasColAvg || !$hasColMax)
                                    <div class="alert alert-warning py-2 mb-3">
                                        Colunas de preço alvo não existem na tabela. Rode a migration:<br>
                                        <code>php artisan migrate --path=database/migrations/2025_09_07_000500_add_targets_to_open_a_i_chats_table.php</code>
                                    </div>
                                @endif
                <form action="{{ route('openai.chat.update', $currentChat->id) }}" method="POST" class="row g-2 align-items-end">
                    @csrf
                    @method('PATCH')
                    <div class="col-sm-5 col-md-4">
                        <label class="form-label small mb-1">Título</label>
                        <input type="text" name="title" value="{{ $currentChat->title }}" maxlength="100" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-sm-3 col-md-2">
                        <label class="form-label small mb-1">Código (5)</label>
                        <input type="text" name="code" value="{{ $currentChat->code }}" maxlength="5" class="form-control form-control-sm" pattern="[A-Za-z0-9]{0,5}" title="Até 5 caracteres alfanuméricos">
                    </div>
                    <div class="col-sm-4 col-md-3">
                        <label class="form-label small mb-1">Tipo</label>
                        <select name="type_id" class="form-select form-select-sm">
                            <option value="">Sem tipo</option>
                            @foreach(($types ?? []) as $type)
                                <option value="{{ $type->id }}" {{ (int)$currentChat->type_id === (int)$type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12"></div>
                    <div class="col-sm-4 col-md-2">
                        <label class="form-label small mb-1">Preço Alvo Mín (R$)</label>
                        <input type="text" name="target_min" value="{{ $currentChat->target_min !== null ? number_format($currentChat->target_min,2,',','.') : '' }}" class="form-control form-control-sm" placeholder="Ex: 5001 ou 5.001,50" inputmode="decimal" autocomplete="off">
                    </div>
                    <div class="col-sm-4 col-md-2">
                        <label class="form-label small mb-1">Preço Alvo Méd (R$)</label>
                        <input type="text" name="target_avg" value="{{ $currentChat->target_avg !== null ? number_format($currentChat->target_avg,2,',','.') : '' }}" class="form-control form-control-sm" placeholder="Ex: 5001 ou 5.001,50" inputmode="decimal" autocomplete="off">
                    </div>
                    <div class="col-sm-4 col-md-2">
                        <label class="form-label small mb-1">Preço Alvo Máx (R$)</label>
                        <input type="text" name="target_max" value="{{ $currentChat->target_max !== null ? number_format($currentChat->target_max,2,',','.') : '' }}" class="form-control form-control-sm" placeholder="Ex: 5001 ou 5.001,50" inputmode="decimal" autocomplete="off">
                    </div>
                    <div class="col-12 small text-muted" style="margin-top:-2px;">
                        Formatos aceitos: 5001 (→ 5.001,00), 5.001 (→ 5.001,00), 5.001,50 ou 5001,50. Use vírgula para decimais.
                    </div>
                    <div class="col-sm-12 col-md-3 d-flex gap-2">
                        <button class="btn btn-sm btn-danger" type="submit">Salvar Meta</button>
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#editMeta">Fechar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @if($currentChat && $currentChat->type && strtoupper($currentChat->type->name) === 'BOLSA DE VALORES AMERICANA')
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                <h2 class="h6 mb-0">Adicionar Registro Rápido</h2>
                <a class="small text-decoration-none" data-bs-toggle="collapse" href="#quickRecordForm" role="button" aria-expanded="false">Mostrar / Ocultar</a>
            </div>
            <div class="collapse show" id="quickRecordForm">
                <form action="{{ route('openai.records.store') }}" method="POST" class="row g-2 align-items-end">
                    @csrf
                    <input type="hidden" name="chat_id" value="{{ $currentChat->id }}">
                    <div class="col-sm-5 col-md-4">
                        <label class="form-label small mb-1">Data/Hora <span class="text-muted">dd/mm/aaaa HH:MM:SS</span></label>
                        <div class="vstack gap-1 position-relative">
                            <div class="input-group input-group-sm">
                                <input type="text" id="qr_date_br" class="form-control" placeholder="dd/mm/aaaa" value="{{ now()->format('d/m/Y') }}" autocomplete="off" required style="max-width:140px;">
                                <button class="btn btn-outline-secondary" type="button" id="qr_btnCal" title="Calendário">📅</button>
                                <input type="text" id="qr_time_br" class="form-control" placeholder="HH:MM:SS" value="{{ now()->format('H:i:s') }}" autocomplete="off" required style="max-width:120px;">
                            </div>
                            <input type="hidden" name="occurred_at" id="qr_occurred_at_hidden" value="{{ now()->format('d/m/Y H:i:s') }}">
                            <div id="qr_brCalendar" class="br-calendar shadow-sm border rounded p-2 bg-white" style="display:none; position:absolute; top:100%; left:0; z-index:60; width:220px;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <button type="button" class="btn btn-sm btn-light" id="qr_prevCal" aria-label="Mês anterior">«</button>
                                    <strong class="small" id="qr_calMonthLabel"></strong>
                                    <button type="button" class="btn btn-sm btn-light" id="qr_nextCal" aria-label="Próximo mês">»</button>
                                </div>
                                <table class="table table-sm table-bordered mb-0 align-middle text-center" style="font-size:.70rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Do</th><th>Se</th><th>Te</th><th>Qu</th><th>Qu</th><th>Se</th><th>Sa</th>
                                        </tr>
                                    </thead>
                                    <tbody id="qr_calBody"></tbody>
                                </table>
                                <div class="mt-2 text-end">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="qr_closeCal">Fechar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 col-md-2">
                        <label class="form-label small mb-1">Valor <span class="text-muted">(R$)</span></label>
                        <input type="text" name="amount" class="form-control form-control-sm mask-money-br" required placeholder="0,00">
                    </div>
                    <div class="col-sm-12 col-md-3">
                        <button class="btn btn-sm btn-danger mt-2 mt-md-0">Salvar Registro</button>
                        <a href="{{ route('openai.records.index', ['chat_id'=>$currentChat->id]) }}" class="btn btn-sm btn-outline-secondary mt-2 mt-md-0">Ver Todos</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger" role="alert">
            <strong>Erro!</strong> {{ session('error') }}
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif
    @if(session('openai_web_last_warning'))
        <div class="alert alert-warning d-flex align-items-start gap-2" role="alert">
            <span>⚠️ {{ session('openai_web_last_warning') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('openai.chat') }}" method="POST" class="vstack gap-2">
                @csrf
                <div>
                    <label for="prompt" class="form-label">Digite seu prompt:</label>
                    <textarea id="prompt" name="prompt" rows="4" class="form-control" placeholder="Ex: Qual a capital do Brasil?">{{ old('prompt') }}</textarea>
                    @error('prompt')
                        <div class="form-text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="form-check m-0">
                        @php
                            $checkedPref = isset($searchInChats) ? (bool)$searchInChats : false;
                            $checkedOld = (bool) old('search_in_chats');
                            $checked = $checkedOld || $checkedPref;
                        @endphp
                        <input type="checkbox" class="form-check-input" id="search_in_chats" name="search_in_chats" value="1" {{ $checked ? 'checked' : '' }}>
                        <label class="form-check-label" for="search_in_chats">Buscar em conversas salvas</label>
                    </div>
                    @php
                        $allowAll = config('openai.chat.search.allow_all');
                        $perm = config('openai.chat.search.allow_all_permission');
                        $canAll = $allowAll && (!$perm || auth()->user()->can($perm));
                    @endphp
                    @if($canAll)
                        <div class="d-flex align-items-center gap-2">
                            <label for="search_scope" class="form-label m-0">Escopo:</label>
                            @php
                                $scopePref = isset($searchScope) ? (string)$searchScope : 'mine';
                                $scopeValue = old('search_scope', $scopePref);
                            @endphp
                            <select id="search_scope" name="search_scope" class="form-select form-select-sm" style="width: 180px;">
                                <option value="mine" {{ $scopeValue === 'mine' ? 'selected' : '' }}>Minhas conversas</option>
                                <option value="all" {{ $scopeValue === 'all' ? 'selected' : '' }}>Todas as conversas</option>
                            </select>
                        </div>
                    @endif
                    @php $webEnabled = config('openai.chat.web.enabled'); @endphp
                    @if($webEnabled)
                        @php $webChecked = old('web_search', isset($webSearch) ? (int)$webSearch : (int)config('openai.chat.web.default_enabled')); @endphp
                        <div class="form-check m-0">
                            <input type="checkbox" class="form-check-input" id="web_search" name="web_search" value="1" {{ $webChecked ? 'checked' : '' }}>
                            <label class="form-check-label" for="web_search">Buscar na Web</label>
                        </div>
                    @endif
                </div>
                <button type="submit" class="btn btn-dark mt-1">Enviar</button>
            </form>
        </div>
    </div>

    @php $hasActive = session('openai_current_chat_id'); @endphp
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h6 mb-3">Anexos da Conversa</h2>
            <form action="{{ route('openai.chat.attachment.upload') }}" method="POST" enctype="multipart/form-data" class="d-flex gap-2 align-items-center flex-wrap mb-3">
                @csrf
                <input type="file" name="file" class="form-control" style="max-width: 360px;">
                <button type="submit" class="btn btn-outline-success">Enviar arquivo</button>
            </form>
            @error('file')
                <div class="text-danger small mb-2">{{ $message }}</div>
            @enderror

            @if(isset($attachments) && $attachments->count())
                <ul class="list-group list-group-flush">
                    @foreach($attachments as $att)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                {{ $att->original_name }}
                                <small class="text-muted">({{ $att->mime_type ?? 'arquivo' }}, {{ number_format($att->size/1024, 1) }} KB)</small>
                            </span>
                            <span class="d-flex gap-2">
                                <a href="{{ route('openai.chat.attachment.view', $att) }}" target="_blank" class="btn btn-sm btn-outline-info">Visualizar</a>
                                <a href="{{ route('openai.chat.attachment.download', $att) }}" class="btn btn-sm btn-outline-secondary">Baixar</a>
                                <form action="{{ route('openai.chat.attachment.delete', $att) }}" method="POST" onsubmit="return confirm('Remover este anexo?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Excluir</button>
                                </form>
                            </span>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="text-muted">Nenhum anexo nesta conversa.</div>
            @endif
        </div>
    </div>

    <div class="d-flex justify-content-end mb-3">
        <form action="{{ route('openai.chat.clear') }}" method="POST" onsubmit="return confirm('Tem certeza que deseja limpar o histórico?');">
            @csrf
            <button type="submit" class="btn btn-danger @if(!isset($messages) || count($messages) <= 1) disabled @endif" @if(!isset($messages) || count($messages) <= 1) disabled @endif>
                Limpar Histórico
            </button>
        </form>
    </div>

    @if(isset($messagesIndexed) && count($messagesIndexed) > 1)
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h2 class="h5 mb-0">Histórico da Conversa</h2>
                    <form method="GET" action="{{ route('openai.chat') }}" class="d-flex align-items-center gap-2">
                        <input type="hidden" name="order" value="{{ $messagesOrder === 'desc' ? 'asc' : 'desc' }}">
                        <button class="btn btn-sm btn-outline-secondary" title="Alterar ordenação">
                            Ordem: {{ $messagesOrder === 'desc' ? 'Mais recentes ↓' : 'Mais antigas ↑' }}
                        </button>
                    </form>
                </div>
                <div class="vstack gap-3">
                    @foreach($messagesIndexed as $row)
                        @php $message = $row; $idxOriginal = $row['_idx']; @endphp
                        @if(($message['role'] ?? '') === 'system') @continue @endif
                        @php
                            $ts = $message['ts'] ?? null;
                            try { $dt = $ts ? \Carbon\Carbon::parse($ts)->timezone(config('app.timezone')) : null; } catch (Exception $e) { $dt=null; }
                        @endphp
                        <div class="p-3 rounded position-relative {{ $message['role'] === 'user' ? 'bg-primary bg-opacity-10 border border-primary text-primary' : 'bg-light border' }}">
                            <div class="d-flex justify-content-between align-items-start mb-1 gap-2 flex-wrap">
                                <div class="d-flex flex-column">
                                    <strong>{{ $message['role'] === 'user' ? 'Você' : 'Assistente' }}</strong>
                                    @if($dt)
                                        @php
                                            try { $rel = $dt->copy()->locale('pt_BR')->diffForHumans(); } catch (Exception $e) { $rel = null; }
                                        @endphp
                                        <small class="text-muted" title="{{ $dt->toIso8601String() }}">{{ $dt->format('d/m/Y H:i:s') }}@if($rel) · {{ $rel }} @endif</small>
                                    @else
                                        <small class="text-muted">(sem data)</small>
                                    @endif
                                </div>
                                <form action="{{ route('openai.chat.message.delete', ['index'=>$idxOriginal]) }}" method="POST" onsubmit="return confirm('Excluir esta mensagem?');" class="m-0 p-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" title="Excluir mensagem" style="font-size:.65rem;">✕</button>
                                </form>
                            </div>
                            <div style="white-space: pre-wrap;">{{ $message['content'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if(!empty($webMeta))
        <div class="card mb-4 border-info">
            <div class="card-header py-2 bg-info bg-opacity-10">
                <strong class="small">Status da Busca Web</strong>
            </div>
            <div class="card-body p-2">
                <div class="table-responsive mb-0">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr class="small text-muted">
                                <th>Provedor</th>
                                <th>Status</th>
                                <th>Resultados</th>
                                <th>Auth</th>
                                <th>Cache</th>
                                <th>Mensagem</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @foreach($webMeta as $m)
                                @php
                                    $providerRaw = $m['provider'] ?? '-';
                                    $providerLabel = [
                                        'serpapi' => 'SerpAPI',
                                        'bing' => 'Bing',
                                        'google_cse' => 'Google CSE',
                                    ][$providerRaw] ?? $providerRaw;
                                    $st = $m['status'] ?? null;
                                    $authErr = !empty($m['auth_error']);
                                    $cached = !empty($m['cached']);
                                    $msg = $m['message'] ?? '';
                                    // Classe de status
                                    $statusClass = 'bg-secondary';
                                    if ($st==='CACHED')      $statusClass='bg-info';
                                    elseif ($st===200)       $statusClass='bg-success';
                                    elseif (in_array($st, [400,401,403])) $statusClass='bg-danger';
                                    elseif (in_array($st, [429])) $statusClass='bg-warning text-dark';
                                    elseif ($st>=500)        $statusClass='bg-danger';
                                @endphp
                                <tr @if($authErr) class="table-danger" @elseif($st==='CACHED') class="table-info" @endif>
                                    <td>{{ $providerLabel }}</td>
                                    <td>
                                        <span class="badge {{ $statusClass }}" title="Status HTTP / Indicador">{{ $st ?? '-' }}</span>
                                    </td>
                                    <td>
                                        @php $res = (int)($m['results'] ?? 0); @endphp
                                        <span class="badge {{ $res>0 ? 'bg-success' : 'bg-light text-muted' }}">{{ $res }}</span>
                                    </td>
                                    <td>
                                        @if($authErr)
                                            <span class="badge bg-danger" title="Falha de autenticação / chave">auth</span>
                                        @else
                                            <span class="badge bg-success" title="Autenticação OK">ok</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($cached)
                                            <span class="badge bg-primary" title="Servido do cache">sim</span>
                                        @else
                                            <span class="badge bg-light text-muted">não</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="small" @if($msg) title="{{ $msg }}" @endif>
                                            {{ $msg ?: '-' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
@push('scripts')
<script>
(function(){
    function formatDateTimeBR(raw){
        let v = (raw||'').replace(/\D/g,'').slice(0,14);
        let o='';
        if(v.length>0) o+=v.slice(0,2);
        if(v.length>=3) o+='/'+v.slice(2,4);
        if(v.length>=5) o+='/'+v.slice(4,8);
        if(v.length>=9) o+=' '+v.slice(8,10);
        if(v.length>=11) o+=':'+v.slice(10,12);
        if(v.length>=13) o+=':'+v.slice(12,14);
        return o;
    }
    function applyMask(el){ el.value = formatDateTimeBR(el.value); }
    const sel = document.querySelectorAll('.mask-datetime-br');
    sel.forEach(el=>{
        el.addEventListener('input', ()=>applyMask(el));
        el.addEventListener('paste', ()=> setTimeout(()=>applyMask(el),0));
        el.addEventListener('blur', ()=>{ if(/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}$/.test(el.value)) el.value+=':00'; });
        if(!el.value){
            const now=new Date();
            const pad=n=>n.toString().padStart(2,'0');
            el.value = pad(now.getDate())+'/'+pad(now.getMonth()+1)+'/'+now.getFullYear()+' '+pad(now.getHours())+':'+pad(now.getMinutes())+':'+pad(now.getSeconds());
        }
    });
})();
// Máscara moeda BR (2 casas) para registro rápido
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
// (Removido JS de normalização de targets: backend fará todo o parsing)

// Calendário BR para registro rápido
(function(){
    const dateInput = document.getElementById('qr_date_br');
    const timeInput = document.getElementById('qr_time_br');
    const hidden = document.getElementById('qr_occurred_at_hidden');
    if(!dateInput || !timeInput || !hidden) return; // segurança
    const calBox = document.getElementById('qr_brCalendar');
    const calBody = document.getElementById('qr_calBody');
    const calLabel = document.getElementById('qr_calMonthLabel');
    const btnPrev = document.getElementById('qr_prevCal');
    const btnNext = document.getElementById('qr_nextCal');
    const btnCal = document.getElementById('qr_btnCal');
    const btnClose = document.getElementById('qr_closeCal');

    function pad(n){return n.toString().padStart(2,'0');}
    function maskDate(v){
        v = v.replace(/\D/g,'').slice(0,8);
        let o='';
        if(v.length>=2) o+=v.slice(0,2); else return v;
        if(v.length>=4) o+='/'+v.slice(2,4); else return o;
        if(v.length>4) o+='/'+v.slice(4,8); return o;
    }
    function maskTime(v){
        v = v.replace(/\D/g,'').slice(0,6);
        let o='';
        if(v.length>=2) o+=v.slice(0,2); else return v;
        if(v.length>=4) o+=':'+v.slice(2,4); else return o;
        if(v.length>4) o+=':'+v.slice(4,6); return o;
    }
    function syncHidden(){ if(dateInput.value && timeInput.value){ hidden.value = dateInput.value+' '+timeInput.value; } }
    dateInput.addEventListener('input',()=>{ dateInput.value = maskDate(dateInput.value); syncHidden();});
    timeInput.addEventListener('input',()=>{ timeInput.value = maskTime(timeInput.value); syncHidden();});

    let viewYear, viewMonth;
    function initView(){
        const parts = dateInput.value.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
        let d = new Date();
        if(parts){ d = new Date(parseInt(parts[3]), parseInt(parts[2])-1, parseInt(parts[1])); }
        viewYear = d.getFullYear(); viewMonth = d.getMonth();
    }
    function renderCalendar(){
        const first = new Date(viewYear, viewMonth, 1);
        const startDay = first.getDay();
        const daysInMonth = new Date(viewYear, viewMonth+1,0).getDate();
        calLabel.textContent = first.toLocaleDateString('pt-BR',{month:'long',year:'numeric'}).toUpperCase();
        let html=''; let dayNum=1;
        for(let r=0;r<6;r++){
            html+='<tr>';
            for(let c=0;c<7;c++){
                if(r===0 && c<startDay){ html+='<td class="text-muted">&nbsp;</td>'; }
                else if(dayNum>daysInMonth){ html+='<td>&nbsp;</td>'; }
                else {
                    const dd=pad(dayNum); const mm=pad(viewMonth+1); const yyyy=viewYear;
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
    btnCal.addEventListener('click',()=>{ calBox.style.display==='block'?closeCal():openCal();});
    btnClose.addEventListener('click',closeCal);
    btnPrev.addEventListener('click',()=>{ viewMonth--; if(viewMonth<0){viewMonth=11;viewYear--;} renderCalendar(); });
    btnNext.addEventListener('click',()=>{ viewMonth++; if(viewMonth>11){viewMonth=0;viewYear++;} renderCalendar(); });
    calBody.addEventListener('click', e=>{
        const b=e.target.closest('button[data-day]'); if(!b) return;
        const day=parseInt(b.getAttribute('data-day'));
        dateInput.value = pad(day)+'/'+pad(viewMonth+1)+'/'+viewYear; syncHidden(); renderCalendar(); closeCal();
    });
    document.addEventListener('click', e=>{ if(!calBox.contains(e.target) && e.target!==btnCal && e.target!==dateInput){ closeCal(); }});
    syncHidden();
})();
// Normalizar valores de preços alvo (máscara BR) ao enviar o formulário de meta
(function(){
    const metaForm = document.querySelector('#editMeta form');
    if(metaForm){
        metaForm.addEventListener('submit', ()=>{
            ['target_min','target_avg','target_max'].forEach(name=>{
                const el = metaForm.querySelector(`[name="${name}"]`);
                if(el && el.value){
                    let v = el.value.trim();
                    // mantém formato BR durante digitação; aqui apenas converte para padrão ponto para backend
                    // remove pontos de milhares antes da vírgula
                    if(v.includes(',')){
                        v = v.replace(/\.(?=\d{3}(?:\D|$))/g,'');
                    }
                    v = v.replace(/\./g,''); // remove restantes
                    v = v.replace(/,/g,'.'); // vírgula decimal -> ponto
                    // agora garante duas casas
                        if(/^\d+$/.test(v)) { v = v + '.00'; }
                        else if(/^\d+\.\d$/.test(v)) { v = v + '0'; }
                    el.value = v;
                }
            });
        });
    }
})();
</script>
@endpush
