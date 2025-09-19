@extends('layouts.bootstrap5')
@section('content')
<div class="py-4 bg-light"><div class="container">
  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="m-0">Envios</h5>
        @can('ENVIOS - INCLUIR')
          <a href="{{ route('Envios.create') }}" class="btn btn-primary btn-sm">Novo Envio</a>
        @endcan
      </div>
      <form method="GET" class="row g-2 mb-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Buscar</label>
          <input name="q" class="form-control" placeholder="Buscar por nome" value="{{ $q }}">
        </div>
        <div class="col-md-2">
          <label class="form-label">De</label>
          <input type="date" name="created_from" class="form-control" value="{{ $createdFrom ?? '' }}">
        </div>
        <div class="col-md-2">
          <label class="form-label">Até</label>
          <input type="date" name="created_to" class="form-control" value="{{ $createdTo ?? '' }}">
        </div>
        <div class="col-md-3">
          <label class="form-label">Tipo de arquivo</label>
          <select name="tipo" class="form-select">
            <option value="">Todos</option>
            @foreach ([
              'imagem' => 'Imagens',
              'pdf' => 'PDFs',
              'video' => 'Vídeos',
              'audio' => 'Áudios',
              'doc' => 'Word',
              'xls' => 'Excel',
              'ppt' => 'PowerPoint',
              'txt' => 'Texto',
              'zip' => 'Compactados',
            ] as $k=>$label)
              <option value="{{ $k }}" {{ ($tipo ?? '')===$k ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        @if(!empty($isAdmin) && $isAdmin)
        <div class="col-md-1">
          <label class="form-label">Escopo</label>
          <select name="escopo" class="form-select">
            <option value="todos" {{ ($escopo ?? 'todos')==='todos' ? 'selected' : '' }}>Todos</option>
            <option value="meus" {{ ($escopo ?? '')==='meus' ? 'selected' : '' }}>Meus</option>
          </select>
        </div>
        @endif
        <div class="col-auto">
          <button class="btn btn-outline-secondary" type="submit">Buscar</button>
        </div>
        @if(($q ?? '')!=='' || ($createdFrom ?? '')!=='' || ($createdTo ?? '')!=='' || ($tipo ?? '')!=='' || (!empty($isAdmin) && ($escopo ?? '')!=='todos'))
          <div class="col-auto">
            <a class="btn btn-link" href="{{ route('Envios.index') }}">Limpar</a>
          </div>
        @endif
      </form>
      <table class="table table-striped">
        <thead><tr><th>Nome</th><th>Descrição</th><th>Criado em</th><th>Arquivos</th><th></th></tr></thead>
        <tbody>
          @forelse($envios as $e)
          <tr>
            <td>{{ $e->nome }}</td>
            <td class="text-muted">{{ Str::limit($e->descricao,80) }}</td>
            <td>{{ optional($e->created_at)->format('d/m/Y H:i') }}</td>
            <td>
              @if(!empty($isAdmin) && $isAdmin)
                @if(($escopo ?? 'todos') === 'meus')
                  {{ $e->arquivos_user_count ?? 0 }}
                @else
                  {{ $e->arquivos_count ?? 0 }}
                @endif
              @else
                {{ $e->arquivos_user_count ?? 0 }}
              @endif
            </td>
            <td class="text-end">
              @can('ENVIOS - VER')<a href="{{ route('Envios.show',$e) }}" class="btn btn-sm btn-outline-secondary">Ver</a>@endcan
              @can('ENVIOS - EDITAR')<a href="{{ route('Envios.edit',$e) }}" class="btn btn-sm btn-outline-primary">Editar</a>@endcan
              @can('ENVIOS - EXCLUIR')
              <form class="d-inline" method="POST" action="{{ route('Envios.destroy',$e) }}">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Excluir envio e todos os arquivos?')">Excluir</button>
              </form>
              @endcan
            </td>
          </tr>
          @empty
            <tr><td colspan="5" class="text-muted">Nenhum envio encontrado.</td></tr>
          @endforelse
        </tbody>
      </table>
      <div class="d-flex justify-content-between align-items-center">
        <div class="text-muted">Exibindo {{ $envios->firstItem() }}–{{ $envios->lastItem() }} de {{ $envios->total() }}</div>
        {{ $envios->appends(['q'=>$q])->links() }}
      </div>
    </div>
  </div>
</div></div>
@endsection
