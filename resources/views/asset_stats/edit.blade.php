@extends('layouts.bootstrap5')
@section('content')
{{-- <div class="container py-4"> --}}
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h5 mb-0">Editar Estatístico Diário</h1>
    <a href="{{ route('asset-stats.index', ['symbol'=>$model->symbol]) }}" class="btn btn-outline-secondary">Voltar</a>
  </div>
  <form method="POST" action="{{ route('asset-stats.update', $model) }}">
    @csrf
    @method('PUT')
    @include('asset_stats._form', ['model'=>$model])
    <div class="mt-3 d-flex gap-2">
      <button class="btn btn-primary">Salvar</button>
      <a href="{{ route('asset-stats.index', ['symbol'=>$model->symbol]) }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
  </form>
{{-- </div> --}}
@endsection
