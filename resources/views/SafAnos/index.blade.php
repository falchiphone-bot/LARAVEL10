@extends('layouts.bootstrap5')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">SAF - Temporadas</h1>
    @can('SAF_ANOS - INCLUIR')
      <a href="{{ route('SafAnos.create') }}" class="btn btn-primary">Nova Temporada</a>
    @endcan
  </div>

  <form method="GET" class="row g-2 mb-3">
    <input type="hidden" name="sort" value="{{ $sort ?? 'ano' }}">
    <input type="hidden" name="dir" value="{{ $dir ?? 'asc' }}">
    <div class="col-md-4">
      <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="Buscar temporada...">
    </div>
    <div class="col-md-3">
      <select name="per_page" class="form-select" onchange="this.form.submit()">
        @foreach([10,20,25,50,100] as $n)
          <option value="{{ $n }}" {{ (isset($perPage) && (int)$perPage === $n) ? 'selected' : '' }}>{{ $n }} por página</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-3">
      <button class="btn btn-outline-secondary" type="submit">Filtrar</button>
      <a class="btn btn-link" href="{{ route('SafAnos.index') }}">Limpar</a>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>
            @php $nextDir = ($sort ?? 'ano') === 'ano' && ($dir ?? 'asc') === 'asc' ? 'desc' : 'asc'; @endphp
            <a href="{{ route('SafAnos.index', ['sort' => 'ano', 'dir' => $nextDir, 'per_page' => request('per_page', $perPage ?? 20), 'q' => $q ?? null]) }}">Temporada
              @if(($sort ?? 'ano') === 'ano')
                <small>{!! ($dir ?? 'asc') === 'asc' ? '&#9650;' : '&#9660;' !!}</small>
              @endif
            </a>
          </th>
          <th class="text-end">Ações</th>
        </tr>
      </thead>
      <tbody>
        @forelse($model as $row)
          <tr>
            <td>{{ $row->ano }}</td>
            <td class="text-end">
              @can('SAF_ANOS - VER')
              <a href="{{ route('SafAnos.show', $row->id) }}" class="btn btn-sm btn-secondary">Ver</a>
              @endcan
              @can('SAF_ANOS - EDITAR')
              <a href="{{ route('SafAnos.edit', $row->id) }}" class="btn btn-sm btn-warning">Editar</a>
              @endcan
              @can('SAF_ANOS - EXCLUIR')
              <form method="POST" action="{{ route('SafAnos.destroy', $row->id) }}" class="d-inline" onsubmit="return confirm('Confirma exclusão?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-danger">Excluir</button>
              </form>
              @endcan
            </td>
          </tr>
        @empty
          <tr><td colspan="2">Nenhum registro.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div>
    {{ $model->appends(['sort' => $sort ?? null, 'dir' => $dir ?? null, 'per_page' => request('per_page', $perPage ?? 20), 'q' => $q ?? null])->onEachSide(1)->links() }}
  </div>
</div>
@endsection
