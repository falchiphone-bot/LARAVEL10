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
                        @php
                            $refBruta = $resultado['data_referencia'] ?? (isset($dataRef) ? $dataRef : null);
                            $refFmt = '-';
                            if($refBruta){
                                try {
                                    // Tenta d/m/Y primeiro
                                    $refFmt = \Carbon\Carbon::createFromFormat('d/m/Y', $refBruta)->format('d/m/Y') .
                                             ' (' . \Carbon\Carbon::createFromFormat('d/m/Y', $refBruta)->locale('pt_BR')->translatedFormat('l') . ')';
                                } catch (\Throwable $e) {
                                    // Fallback parse flexível
                                    $refFmt = \Carbon\Carbon::parse($refBruta)->format('d/m/Y') .
                                             ' (' . \Carbon\Carbon::parse($refBruta)->locale('pt_BR')->translatedFormat('l') . ')';
                                }
                            }
                        @endphp
                        <li class="list-group-item">Data de referência: <strong>{!! $refFmt !!}</strong></li>
                        @if(($resultado['data_utilizada'] ?? null) && ($resultado['data_referencia'] ?? null) && $resultado['data_utilizada'] !== $resultado['data_referencia'])
                            @php
                                $utilFmt = $resultado['data_utilizada'];
                                try {
                                    $utilFmt = \Carbon\Carbon::createFromFormat('d/m/Y', $resultado['data_utilizada'])->format('d/m/Y') .
                                              ' (' . \Carbon\Carbon::createFromFormat('d/m/Y', $resultado['data_utilizada'])->locale('pt_BR')->translatedFormat('l') . ')';
                                } catch (\Throwable $e) {
                                    try {
                                        $utilFmt = \Carbon\Carbon::parse($resultado['data_utilizada'])->format('d/m/Y') .
                                                  ' (' . \Carbon\Carbon::parse($resultado['data_utilizada'])->locale('pt_BR')->translatedFormat('l') . ')';
                                    } catch (\Throwable $e2) {
                                        // mantém string original
                                    }
                                }
                            @endphp
                            <li class="list-group-item">Data utilizada na API: <strong>{!! $utilFmt !!}</strong>
                                <small class="text-muted">(sem cotação no dia de referência; usada a última disponível)</small>
                            </li>
                        @endif
                        <li class="list-group-item">Fonte:
                            <span class="badge {{ ($resultado['origem'] ?? $fonte ?? 'api') === 'api' ? 'bg-success' : 'bg-secondary' }}">{{ strtoupper($resultado['origem'] ?? $fonte ?? 'api') }}</span>
                            @if(!empty($resultado['provider']))
                                <span class="badge bg-info ms-2">{{ $resultado['provider'] }}</span>
                            @endif
                        </li>
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
                    @if(($resultado['status'] ?? null) === 'success' && ($resultado['origem'] ?? null) === 'api')
                        <form method="POST" action="{{ route('moedas.consultarSalvar') }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="idmoeda" value="{{ $moeda->id }}">
                            <input type="hidden" name="data" value="{{ \Carbon\Carbon::createFromFormat('d/m/Y', $resultado['data_utilizada'] ?? $resultado['data_referencia'])->format('Y-m-d') }}">
                            <input type="hidden" name="valor" value="{{ str_replace(['.',','], ['','.' ], $resultado['valor_formatado'] ?? '0') }}">
                            <button type="submit" class="btn btn-success" title="Salvar este valor no banco">Inserir registro com este valor</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
