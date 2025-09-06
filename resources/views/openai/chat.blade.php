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
        @if($currentChat)
            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#editMeta">Editar Meta</button>
        @endif
    </div>
    @if($currentChat)
    <div id="editMeta" class="collapse mb-4">
        <div class="card border-danger-subtle">
            <div class="card-body py-3">
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
                    <div class="col-sm-12 col-md-3 d-flex gap-2">
                        <button class="btn btn-sm btn-danger" type="submit">Salvar Meta</button>
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#editMeta">Fechar</button>
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

    @if(isset($messages) && count($messages) > 1)
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3">Histórico da Conversa:</h2>
                <div class="vstack gap-3">
                    @foreach(array_reverse($messages) as $message)
                        @if($message['role'] === 'system')
                            @continue
                        @endif
                        <div class="p-3 rounded {{ $message['role'] === 'user' ? 'bg-primary bg-opacity-10 border border-primary text-primary' : 'bg-light border' }}">
                            <div class="fw-bold mb-1">{{ $message['role'] === 'user' ? 'Você' : 'Assistente' }}:</div>
                            <div style="white-space: pre-wrap;">{{ $message['content'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
