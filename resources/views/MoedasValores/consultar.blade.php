@extends('layouts.bootstrap5')

@section('content')
<div class="py-5 bg-light">
    <div class="container">
        <div class="card">
            <div class="badge bg-primary text-wrap" style="width:100%;font-size:20px;">
                Resultado da Consulta de Moeda
            </div>

            <div class="card-body">
                @if(($resultado['status'] ?? null) === 'success')
                    <div class="alert alert-success d-flex align-items-center mt-2 fw-bold border-2 border-success shadow-sm alert-dismissible fade show" role="alert">
                        <span class="me-2" aria-hidden="true">✅</span>
                        <div>{{ $resultado['mensagem'] ?? '' }}</div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @else
                    <div class="alert alert-danger d-flex align-items-center mt-2 fw-bold border-2 border-danger shadow-sm alert-dismissible fade show" role="alert">
                        <span class="me-2" aria-hidden="true">⚠️</span>
                        <div>{{ $resultado['mensagem'] ?? 'Nenhum valor encontrado para a moeda até a data informada.' }}</div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="mt-3">
                    <h5>Detalhes</h5>
                    <ul class="list-group">
                        <li class="list-group-item">Moeda: <strong>{{ $resultado['moeda_nome'] ?? ($moeda->nome ?? '-') }}</strong></li>
                        <li class="list-group-item">Data de referência: <strong>{{ $resultado['data_formatada'] ?? (isset($dataRef) ? \Carbon\Carbon::parse($dataRef)->format('d/m/Y') : '-') }}</strong></li>
                        <li class="list-group-item">Fonte: <span class="badge {{ ($resultado['origem'] ?? $fonte ?? 'api') === 'api' ? 'bg-success' : 'bg-secondary' }}">{{ strtoupper($resultado['origem'] ?? $fonte ?? 'api') }}</span></li>
                        @if(($resultado['status'] ?? null) === 'success')
                            <li class="list-group-item">Valor: <strong>{{ $resultado['valor_formatado'] ?? '-' }}</strong></li>
                            @if(!empty($resultado['codigo']))
                                <li class="list-group-item">Paridade: <strong>{{ $resultado['codigo'] }} → BRL</strong></li>
                            @endif
                        @endif
                    </ul>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <a href="{{ route('MoedasValores.index') }}" class="btn btn-outline-primary">Voltar à lista</a>
                    <a href="{{ route('MoedasValores.index') }}#consulta" class="btn btn-primary">Nova consulta</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
