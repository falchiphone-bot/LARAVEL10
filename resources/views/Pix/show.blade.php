@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
  <div class="container">
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">PIX: {{ $model->nome }}</h5>
        <div class="d-flex gap-2">
          @can('PIX - EDITAR')
          <a href="{{ route('Pix.edit', ['pix' => $model->getRouteKey()]) }}" class="btn btn-success btn-sm">Editar</a>
          @endcan
          <a href="{{ route('Pix.index') }}" class="btn btn-outline-secondary btn-sm">Voltar</a>
        </div>
      </div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-sm-2">Nome</dt>
          <dd class="col-sm-10">{{ $model->nome }}</dd>
        </dl>
      </div>
    </div>
  </div>
</div>
@endsection
