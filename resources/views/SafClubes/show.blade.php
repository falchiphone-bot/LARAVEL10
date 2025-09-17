@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detalhes do Clube</h5>
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('SafClubes.index') }}">Voltar</a>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Nome</dt>
                    <dd class="col-sm-9">{{ $cadastro->nome }}</dd>

                    <dt class="col-sm-3">Cidade</dt>
                    <dd class="col-sm-9">{{ $cadastro->cidade }}</dd>

                    <dt class="col-sm-3">UF</dt>
                    <dd class="col-sm-9">{{ $cadastro->uf }}</dd>

                    <dt class="col-sm-3">País</dt>
                    <dd class="col-sm-9">{{ $cadastro->pais }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
