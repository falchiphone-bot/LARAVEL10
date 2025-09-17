@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
    <h2>Faixa Salarial #{{ $cadastro->id }}</h2>
    <div class="card mt-3">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Nome</dt>
                <dd class="col-sm-9">{{ $cadastro->nome }}</dd>

                <dt class="col-sm-3">Função</dt>
                <dd class="col-sm-9">{{ optional($cadastro->funcaoProfissional)->nome }}</dd>

                <dt class="col-sm-3">Tipo de Prestador</dt>
                <dd class="col-sm-9">{{ optional($cadastro->tipoPrestador)->nome }}</dd>

                <dt class="col-sm-3">Senioridade</dt>
                <dd class="col-sm-9">{{ $cadastro->senioridade }}</dd>

                <dt class="col-sm-3">Tipo de Contrato</dt>
                <dd class="col-sm-9">{{ $cadastro->tipo_contrato }}</dd>

                <dt class="col-sm-3">Periodicidade</dt>
                <dd class="col-sm-9">{{ $cadastro->periodicidade }}</dd>

                <dt class="col-sm-3">Valores</dt>
                <dd class="col-sm-9">
                    Mín.: {{ number_format($cadastro->valor_minimo, 2, ',', '.') }} | Máx.: {{ number_format($cadastro->valor_maximo, 2, ',', '.') }} ({{ $cadastro->moeda }})
                </dd>

                <dt class="col-sm-3">Vigência</dt>
                <dd class="col-sm-9">
                    {{ \Illuminate\Support\Carbon::parse($cadastro->vigencia_inicio)->format('d/m/Y') }} -
                    {{ $cadastro->vigencia_fim ? \Illuminate\Support\Carbon::parse($cadastro->vigencia_fim)->format('d/m/Y') : 'aberta' }}
                </dd>

                <dt class="col-sm-3">Ativo</dt>
                <dd class="col-sm-9">{{ $cadastro->ativo ? 'SIM' : 'NÃO' }}</dd>

                <dt class="col-sm-3">Observações</dt>
                <dd class="col-sm-9">{!! nl2br(e($cadastro->observacoes)) !!}</dd>
            </dl>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('SafFaixasSalariais.index') }}" class="btn btn-secondary">Voltar</a>
        @can('SAF_FAIXASSALARIAIS - EDITAR')
        <a href="{{ route('SafFaixasSalariais.edit',$cadastro->id) }}" class="btn btn-primary">Editar</a>
        @endcan
    </div>
</div>
@endsection
