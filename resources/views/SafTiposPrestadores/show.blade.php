@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
  <div class="container">
    <div class="card shadow-sm">
      <div class="card-header"><h5 class="mb-0">Detalhes - Tipo de Prestador</h5></div>
      <div class="card-body">
        <dl class="row">
          <dt class="col-sm-3">Nome</dt>
          <dd class="col-sm-9">{{ $cadastro->nome }}</dd>
          <dt class="col-sm-3">Função Profissional</dt>
          <dd class="col-sm-9">{{ optional($cadastro->funcaoProfissional)->nome }}</dd>
          <dt class="col-sm-3">Cidade</dt>
          <dd class="col-sm-9">{{ $cadastro->cidade }}</dd>
          <dt class="col-sm-3">UF</dt>
          <dd class="col-sm-9">{{ $cadastro->uf }}</dd>
          <dt class="col-sm-3">País</dt>
          <dd class="col-sm-9">{{ $cadastro->pais }}</dd>
        </dl>
        <a class="btn btn-secondary" href="{{ route('SafTiposPrestadores.index') }}">Voltar</a>
      </div>
    </div>
  </div>
</div>
@endsection
