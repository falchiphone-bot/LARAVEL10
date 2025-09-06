@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
  @php
    $viewMode = request('view', 'cards');
    $sort = $sort ?? request('sort', 'updated');
    $dir  = $dir ?? request('dir', $sort === 'updated' ? 'desc' : 'asc');
    $toggleDir = fn($c) => ($sort === $c && $dir === 'asc') ? 'desc' : 'asc';
    function sortIcon($current, $sort, $dir){
      if($current !== $sort) return '<span class="text-muted">↕</span>';
      return $dir === 'asc' ? '▲' : '▼';
    }
  @endphp
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
    <input type="number" name="per_page" min="1" max="{{ $maxPerPage ?? 500 }}" value="{{ $perPage ?? 12 }}" class="form-control form-control-sm" style="width:90px;" title="Quantidade por página">
        <input type="hidden" name="view" value="{{ $viewMode }}">
        <button class="btn btn-sm btn-outline-secondary" type="submit">Pesquisar</button>
        @if((isset($q) && trim($q) !== '') || (isset($typeId) && (int)$typeId > 0))
          <a href="{{ route('openai.chats') }}" class="btn btn-sm btn-outline-dark">Limpar filtro</a>
        @endif
      </form>
      <div class="btn-group" role="group" aria-label="Alternar visualização">
    <a href="{{ route('openai.chats', array_merge(request()->except(['page','view']), ['view' => 'cards','per_page'=>$perPage])) }}" class="btn btn-sm {{ $viewMode === 'cards' ? 'btn-success' : 'btn-outline-success' }}">Cartões</a>
    <a href="{{ route('openai.chats', array_merge(request()->except(['page','view']), ['view' => 'table','per_page'=>$perPage])) }}" class="btn btn-sm {{ $viewMode === 'table' ? 'btn-success' : 'btn-outline-success' }}">Tabela</a>
      </div>
            <a href="{{ route('openai.menu') }}" class="btn btn-outline-secondary">← Menu OpenAI</a>
            <a href="{{ route('openai.types.index') }}" class="btn btn-outline-dark">Gerenciar Tipos</a>
            <a href="{{ route('openai.chat.new') }}" class="btn btn-primary">Novo Chat</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($chats->count())
        <div class="alert alert-light border d-flex justify-content-between align-items-center py-2 px-3 small mb-3">
          <div>
            Exibindo
            <strong>{{ $chats->firstItem() }}–{{ $chats->lastItem() }}</strong>
            de <strong>{{ $chats->total() }}</strong>
            @if(isset($totalAll) && $totalAll !== $chats->total()) (filtrado de {{ $totalAll }}) @endif
          </div>
          <div>
            Página {{ $chats->currentPage() }} de {{ $chats->lastPage() }}
          </div>
        </div>
        @if($viewMode === 'cards')
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
        @else
            <style>
              .table-zebra thead th { background:#198754; color:#fff; }
              .table-zebra tbody tr:nth-child(odd){ background:#e6f8e6; }
              .table-zebra tbody tr:nth-child(even){ background:#ffffff; }
              .table-zebra tbody tr:hover{ background:#d1f2d1; }
              .table-zebra td, .table-zebra th { vertical-align: middle; }
              .table-zebra th a.sort-link { color:#dc3545; }
              .table-zebra th a.sort-link:hover { color:#a71d2a; }
              .table-zebra th a.sort-link:focus { color:#a71d2a; }
              .table-zebra th a.sort-link span.text-muted { color:#dc3545 !important; }
              @media (max-width: 992px){
                .table-responsive-stack td[data-title]:before{ content: attr(data-title) ": "; font-weight:600; }
              }
            </style>
            <div class="table-responsive mt-2">
              <table class="table table-sm table-bordered table-zebra align-middle">
                <thead>
                  <tr>
                    <th style="width:45%">
                      <a class="sort-link text-decoration-none d-flex align-items-center justify-content-between" href="{{ route('openai.chats', array_merge(request()->except('page'), ['view'=>$viewMode,'sort'=>'title','dir'=>$toggleDir('title')])) }}">
                        <span>Título</span>
                        <span>{!! sortIcon('title', $sort, $dir) !!}</span>
                      </a>
                    </th>
                    <th style="width:15%">
                      <a class="sort-link text-decoration-none d-flex align-items-center justify-content-between" href="{{ route('openai.chats', array_merge(request()->except('page'), ['view'=>$viewMode,'sort'=>'type','dir'=>$toggleDir('type')])) }}">
                        <span>Tipo</span>
                        <span>{!! sortIcon('type', $sort, $dir) !!}</span>
                      </a>
                    </th>
                    <th style="width:20%">
                      <a class="sort-link text-decoration-none d-flex align-items-center justify-content-between" href="{{ route('openai.chats', array_merge(request()->except('page'), ['view'=>$viewMode,'sort'=>'updated','dir'=>$toggleDir('updated')])) }}">
                        <span>Atualizado</span>
                        <span>{!! sortIcon('updated', $sort, $dir) !!}</span>
                      </a>
                    </th>
                    <th style="width:20%" class="text-center">Ações</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($chats as $chat)
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
                    <tr>
                      <td>{!! $titleHighlighted !!}</td>
                      <td>
                        @if($chat->type)
                          <span class="badge text-bg-secondary">{{ $chat->type->name }}</span>
                        @else
                          <span class="text-muted small">—</span>
                        @endif
                      </td>
                      <td class="small">{{ $chat->updated_at->format('d/m/Y H:i') }}</td>
                      <td class="text-center">
                        <div class="d-flex flex-wrap gap-1 justify-content-center">
                          <a href="{{ route('openai.chat.load', $chat) }}" class="btn btn-sm btn-secondary">Carregar</a>
                          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#renameModal{{ $chat->id }}">Renomear</button>
                          <form action="{{ route('openai.chat.delete', $chat) }}" method="POST" onsubmit="return confirm('Excluir esta conversa?');" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                          </form>
                        </div>
                      </td>
                    </tr>
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
                </tbody>
              </table>
            </div>
        @endif

        <div class="mt-3">{{ $chats->appends(request()->except('page'))->links() }}</div>
    @else
        <div class="alert alert-info">Você ainda não salvou nenhuma conversa.</div>
    @endif
</div>
@endsection
