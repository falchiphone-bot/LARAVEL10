@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('SafCampeonatos.index') }}">Voltar</a>
                <h5 class="mb-0">Detalhes do Campeonato</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-2">Nome</dt>
                    <dd class="col-sm-10">{{ $cadastro->nome }}</dd>
                    <dt class="col-sm-2">Cidade</dt>
                    <dd class="col-sm-10">{{ $cadastro->cidade }}</dd>
                    <dt class="col-sm-2">UF</dt>
                    <dd class="col-sm-10">{{ $cadastro->uf }}</dd>
                    <div class="col-md-4">
                    <dd class="col-sm-10">{{ $cadastro->pais }}</dd>
                    <dt class="col-sm-3">Ano</dt>
                    <dd class="col-sm-9">{{ optional($cadastro->ano)->ano ?? '—' }}</dd>
                    <dt class="col-sm-2">Categorias</dt>
                    <dd class="col-sm-10">
                    <div class="col-md-8">
                        <label class="form-label">Federação</label>
                        <input type="text" class="form-control" value="{{ optional($cadastro->federacao)->nome }}" disabled>
                    </div>
                        @if($cadastro->categorias && $cadastro->categorias->count())
                            {{ $cadastro->categorias->pluck('nome')->join(', ') }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
