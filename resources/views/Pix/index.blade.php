@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
  <div class="container">
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">PIX</h5>
        <div class="d-flex gap-2">
          @can('PIX - EXPORTAR')
          <div class="btn-group">
            <a href="{{ route('Pix.export', request()->query()) }}" class="btn btn-outline-success btn-sm">Exportar CSV</a>
            <a href="{{ route('Pix.exportPdf', request()->query()) }}" class="btn btn-outline-danger btn-sm">Exportar PDF</a>
          </div>
          @endcan
          @can('PIX - INCLUIR')
          <a href="{{ route('Pix.create') }}" class="btn btn-primary btn-sm">Incluir</a>
          @endcan
        </div>
      </div>
      <div class="card-body">
        @if (session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @elseif (session('error'))
          <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form method="GET" class="row g-2 align-items-end mb-3">
          <div class="col-md-6">
            <label class="form-label">Buscar</label>
            <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="Nome">
          </div>
          <div class="col-auto">
            <button class="btn btn-primary" type="submit">Buscar</button>
          </div>
          @if(($q ?? '') !== '')
            <div class="col-auto">
              <a class="btn btn-outline-secondary" href="{{ route('Pix.index') }}">Limpar</a>
            </div>
          @endif
        </form>

        <p class="text-muted">Total: {{ $model->total() }}</p>

        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Nome</th>
                <th class="text-end">Ações</th>
              </tr>
            </thead>
            <tbody>
            @forelse($model as $item)
              <tr>
                <td>{{ $item->nome }}</td>
                <td class="text-end">
                  @can('PIX - VER')
                  <a class="btn btn-secondary btn-sm" href="{{ route('Pix.show', ['pix' => $item->getRouteKey()]) }}">Ver</a>
                  @endcan
                  @can('PIX - EDITAR')
                  <a class="btn btn-success btn-sm" href="{{ route('Pix.edit', ['pix' => $item->getRouteKey()]) }}">Editar</a>
                  @endcan
                  @can('PIX - EXCLUIR')
                  <form action="{{ route('Pix.destroy', ['pix' => $item->getRouteKey()]) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirma exclusão?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger btn-sm">Excluir</button>
                  </form>
                  @endcan
                </td>
              </tr>
            @empty
              <tr><td colspan="2" class="text-center text-muted">Nenhum registro encontrado.</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between align-items-center gap-3">
          <div class="text-muted">Exibindo {{ $model->firstItem() }}–{{ $model->lastItem() }} de {{ $model->total() }}</div>
          <div>{{ $model->appends(request()->query())->links() }}</div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
