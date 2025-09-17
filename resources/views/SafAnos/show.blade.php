@extends('layouts.bootstrap5')
@section('content')
<div class="container">
  <h1 class="h3 mb-3">Detalhes do Ano</h1>
  <dl class="row">
    <dt class="col-sm-2">Ano</dt>
    <dd class="col-sm-10">{{ $cadastro->ano }}</dd>
  </dl>
  <a class="btn btn-secondary" href="{{ route('SafAnos.index') }}">Voltar</a>
</div>
@endsection
