@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Minhas Conversas
      @if(isset($q) && trim($q) !== '')
        <span class="badge text-bg-info ms-2">Resultados: {{ $chats->total() }}</span>
      @endif
    </h1>
    <div class="d-flex align-items-center gap-2">
      <form action="{{ route('openai.chats') }}" method="GET" class="d-flex gap-2 align-items-center">
        <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control form-control-sm" placeholder="Buscar por título..." style="width: 220px;">
        <select name="type_id" class="form-select form-select-sm" style="width: 180px;">
          <option value="">Todos os tipos</option>
          @foreach(($types ?? []) as $type)
            <option value="{{ $type->id }}" {{ (isset($typeId) && (int)$typeId === (int)$type->id) ? 'selected' : '' }}>{{ $type->name }}</option>
          @endforeach
        </select>
        <button class="btn btn-sm btn-outline-secondary" type="submit">Pesquisar</button>
        @if((isset($q) && trim($q) !== '') || (isset($typeId) && (int)$typeId > 0))
          <a href="{{ route('openai.chats') }}" class="btn btn-sm btn-outline-dark">Limpar filtro</a>
        @endif
      </form>
            <a href="{{ route('openai.menu') }}" class="btn btn-outline-secondary">← Menu OpenAI</a>
            <a href="{{ route('openai.types.index') }}" class="btn btn-outline-dark">Gerenciar Tipos</a>
            <a href="{{ route('openai.chat.new') }}" class="btn btn-primary">Novo Chat</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($chats->count())
        <div class="row g-3">
            @foreach($chats as $chat)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
              @php
                $titleHighlighted = e($chat->title);
                if (isset($q) && trim($q) !== '') {
                  $needle = trim((string) $q);
                  if ($needle !== '') {
                    $pattern = '/' . preg_quote($needle, '/') . '/i';
                    $titleHighlighted = preg_replace_callback($pattern, function ($m) {
                      return '<mark>' . e($m[0]) . '</mark>';
                    }, e($chat->title));
                  }
                }
              @endphp
              <h5 class="card-title d-flex align-items-center gap-2">
                <span>{!! $titleHighlighted !!}</span>
                @if($chat->type)
                  <span class="badge text-bg-secondary">{{ $chat->type->name }}</span>
                @endif
              </h5>
                            <p class="text-muted small mb-2">Atualizado em {{ $chat->updated_at->format('d/m/Y H:i') }}</p>
                            <div class="mt-auto d-flex gap-2">
                                <a href="{{ route('openai.chat.load', $chat) }}" class="btn btn-sm btn-secondary">Carregar</a>

                                <!-- Renomear -->
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#renameModal{{ $chat->id }}">Renomear</button>

                                <!-- Excluir -->
                                <form action="{{ route('openai.chat.delete', $chat) }}" method="POST" onsubmit="return confirm('Excluir esta conversa?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Renomear -->
                <div class="modal fade" id="renameModal{{ $chat->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Renomear conversa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <form action="{{ route('openai.chat.update', $chat) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="modal-body">
                          <div class="mb-3">
                            <label for="title{{ $chat->id }}" class="form-label">Novo título</label>
                            <input type="text" name="title" id="title{{ $chat->id }}" class="form-control" maxlength="100" value="{{ $chat->title }}" required>
                          </div>
                          <div class="mb-3">
                            <label for="type{{ $chat->id }}" class="form-label">Tipo</label>
                            <select name="type_id" id="type{{ $chat->id }}" class="form-select">
                              <option value="">Sem tipo</option>
                              @foreach(($types ?? []) as $type)
                                <option value="{{ $type->id }}" {{ ((int)($chat->type_id) === (int)$type->id) ? 'selected' : '' }}>{{ $type->name }}</option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                          <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
            @endforeach
        </div>

        <div class="mt-3">{{ $chats->links() }}</div>
    @else
        <div class="alert alert-info">Você ainda não salvou nenhuma conversa.</div>
    @endif
</div>
@endsection
