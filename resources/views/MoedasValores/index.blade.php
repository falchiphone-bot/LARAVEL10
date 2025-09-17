@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                 <div class="badge bg-primary text-wrap" style="width: 100%;
                ;font-size: 24px; lign=˜Center˜">
                    MOEDAS E VALORES DO SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>


                <div class="card-body">

                     <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                     <a class="btn btn-warning" href="/Moedas/dashboard">Retornar a lista de opções</a> </nav>

                    @can('MOEDASVALORES- INCLUIR')
                        <a href="{{ route('MoedasValores.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1"
                            role="button" aria-disabled="true">Incluir valor de moedas</a>
                    @endcan
                    <div class="card-header">
                        <div class="badge bg-primary text-wrap" style="width: 100%;
                ;font-size: 16px; lign=˜Center˜">
                        <p>Total de moedas com valores cadastradas no sistema de gerenciamento administrativo e contábil:
                            {{ $moedasvalores->count() ?? 0 }}</p>
                        </div>
                    </div>
                </div>


                <h1><span class="me-2" aria-hidden="true">🪙</span>Selecione uma Moeda</h1>
                <form id="form-moedas" action="{{ route('moedas.selecionar') }}" method="POST">
                    @csrf
                    <input type="hidden" name="per_page" value="{{ $perPage ?? 25 }}">
                    <label for="moeda">Moeda:</label>
                    <select name="moeda_id" id="moeda" class="select2" style="width: 260px;">
                        @foreach ($moedas as $moeda)
                            {{-- <option value="{{ $moeda->id }}">{{ $moeda->nome }}</option> --}}
                            <option value="{{ $moeda->id }}" {{ (old('moeda_id', ($moedaSelecionada ?? request('moeda_id'))) == $moeda->id) ? 'selected' : '' }}>
                                {{ $moeda->nome }}
                            </option>
                        @endforeach
                    </select>

                    <label for="data_referencia" style="margin-left:16px;">Data referência:</label>
                    <input type="date" id="data_referencia" name="data_referencia"
                           value="{{ old('data_referencia', now()->toDateString()) }}">

                    <label for="fonte" style="margin-left:16px;">Fonte:</label>
                    <select name="fonte" id="fonte">
                        <option value="api" {{ old('fonte', 'api') === 'api' ? 'selected' : '' }}>API (com fallback)</option>
                        <option value="local" {{ old('fonte') === 'local' ? 'selected' : '' }}>Base local</option>
                    </select>


                    <label for="ordem">Ordenar por Data:</label>
                    <select name="ordem" id="ordem" onchange="this.form.submit()">
                        <option value="asc" {{ $ordem == 'asc' ? 'selected' : '' }}>Crescente</option>
                        <option value="desc" {{ $ordem == 'desc' ? 'selected' : '' }}>Decrescente</option>
                    </select>

                    <label for="base_variacao" style="margin-left:16px;">Base variação:</label>
                    <select name="base_variacao" id="base_variacao" onchange="this.form.submit()">
                        <option value="posterior" {{ ($baseVariacao ?? 'anterior') === 'posterior' ? 'selected' : '' }}>Dia posterior</option>
                        <option value="anterior" {{ ($baseVariacao ?? 'anterior') === 'anterior' ? 'selected' : '' }}>Dia anterior</option>
                    </select>

                    <button type="submit">Selecionar</button>
                    <button type="submit" formaction="{{ route('moedas.consultarValor') }}" class="btn btn-info">
                        Consultar valor (atual/anteriores)
                    </button>
                </form>

                <div class="mt-3 d-flex gap-2 flex-wrap">
                    <form method="POST" action="{{ route('moedasvalores.clearCache') }}" onsubmit="return confirm('Limpar cache?');">
                        @csrf
                        <button class="btn btn-outline-warning btn-sm" type="submit">Limpar Cache</button>
                    </form>
                    <form method="GET" action="{{ route('moedasvalores.exportCsv') }}">
                        <input type="hidden" name="moeda_id" value="{{ $moedaSelecionada ?? request('moeda_id') }}">
                        <input type="hidden" name="ordem" value="{{ $ordem }}">
                        <input type="hidden" name="base_variacao" value="{{ $baseVariacao ?? 'anterior' }}">
                        <button class="btn btn-outline-secondary btn-sm" type="submit">Exportar CSV</button>
                    </form>
                    <form method="POST" action="{{ route('moedas.selecionar') }}" class="d-inline">
                        @csrf
                        <input type="hidden" name="moeda_id" value="{{ $moedaSelecionada ?? request('moeda_id') }}">
                        <input type="hidden" name="ordem" value="{{ $ordem }}">
                        <input type="hidden" name="base_variacao" value="{{ $baseVariacao ?? 'anterior' }}">
                        <select name="per_page" onchange="this.form.submit()" class="form-select form-select-sm" style="width:auto;display:inline-block;">
                            @foreach([10,25,50,100,150,200] as $pp)
                                <option value="{{ $pp }}" {{ ($perPage ?? 25) == $pp ? 'selected' : '' }}>{{ $pp }} por página</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                @if (session('success'))
                    <div class="alert alert-success d-flex align-items-center mt-3 fw-bold border-2 border-success shadow-sm alert-dismissible fade show" role="alert">
                        <span class="me-2" aria-hidden="true">✅</span>
                        <div>{{ session('success') }}</div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger d-flex align-items-center mt-3 fw-bold border-2 border-danger shadow-sm alert-dismissible fade show" role="alert">
                        <span class="me-2" aria-hidden="true">⚠️</span>
                        <div>{{ session('error') }}</div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif


                    <table class="table" style="background-color: rgb(247, 247, 213);">
                        <thead>
                            <tr>
                                <th scope="col" class="px-6 py-4">DATA</th>
                                <th scope="col" class="px-6 py-4">VALOR</th>
                                <th scope="col" class="px-6 py-4">MOEDA</th>
                                <th scope="col" class="px-6 py-4">Variação % (dia {{ ($baseVariacao ?? 'anterior') === 'posterior' ? 'posterior' : 'anterior' }})</th>
                                <th scope="col" class="px-6 py-4"></th>
                                <th scope="col" class="px-6 py-4"></th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($moedasvalores as $moedavalores)
                                <tr>
                                    <td class="">
                                        {{ $moedavalores->data->format('d/m/Y')  }}

                                        </a>
                                    </td>
                                    <td class="">
                                        {{ $moedavalores->valor }}
                                    </td>
                                    <td class="">
                                        {{ $moedavalores->ValoresComMoeda->nome }}
                                    </td>
                                    <td class="">
                                        @php
                                            $v = $moedavalores->variacao_percentual ?? null;
                                            $tipo = $moedavalores->variacao_tipo ?? ($baseVariacao ?? 'posterior');
                                            $valorAtual = $moedavalores->variacao_valor_atual ?? null;
                                            $valorComp = $moedavalores->variacao_valor_comparacao ?? null;
                                            $dataComp = $moedavalores->variacao_data_comparacao ?? null;
                                            $tooltip = null;
                                            if(!is_null($v) && !is_null($valorAtual) && !is_null($valorComp)) {
                                                $tooltip = ($tipo==='posterior' ? 'Comparado com dia posterior' : 'Comparado com dia anterior') . ( $dataComp ? ' (' . \Carbon\Carbon::parse($dataComp)->format('d/m/Y') . ')' : '' ) . "\\nValor atual: $valorAtual\\nValor comparação: $valorComp";
                                            }
                                        @endphp
                                        @if($v === null)
                                            -
                                        @else
                                            <span title="{{ $tooltip }}" class="fw-bold {{ $v > 0 ? 'text-success' : ($v < 0 ? 'text-danger' : 'text-muted') }}">
                                                {{ number_format($v, 2, ',', '.') }}%
                                            </span>
                                        @endif
                                    </td>


                                    @can('MOEDASVALORES- EDITAR')
                                        <td>
                                            <a href="{{ route('MoedasValores.edit', $moedavalores->id) }}" class="btn btn-success"
                                                tabindex="-1" role="button" aria-disabled="true">Editar</a>
                                        </td>
                                    @endcan

                                    @can('MOEDASVALORES- VER')
                                    <td>
                                        <a href="{{ route('MoedasValores.show', $moedavalores->id) }}" class="btn btn-info"
                                            tabindex="-1" role="button" aria-disabled="true">Ver</a>
                                    </td>
                                    @endcan

                                    @can('MOEDASVALORES- EXCLUIR')
                                    <td>
                                        <form method="POST" action="{{ route('MoedasValores.destroy', $moedavalores->id) }}">
                                            @csrf
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="btn btn-danger">
                                                Excluir
                                            </button>
                                        </form>
                                    </td>
                                    @endcan
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="badge bg-info text-wrap" style="width: 100%;">
            </div>
            @isset($paginacao)
                <div class="mt-3">
                    {{ $paginacao->appends([
                        'ordem' => $ordem,
                        'base_variacao' => $baseVariacao ?? 'anterior',
                        'moeda_id' => $moedaSelecionada ?? request('moeda_id'),
                        'per_page' => $perPage ?? 25,
                    ])->links() }}
                </div>
            @endisset
        </div>

    </div>
    <div class="b-example-divider"></div>
    </div>
@endsection

@push('scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });

        (function() {
            let lastSubmitter = null;
            const form = document.getElementById('form-moedas');

            if (!form) return;

            // Captura qual botão de submit foi clicado
            form.addEventListener('click', function(ev) {
                const t = ev.target;
                if (t && (t.matches('button[type="submit"]') || t.matches('input[type="submit"]'))) {
                    lastSubmitter = t;
                }
            });

            $('#form-moedas').on('submit', function(e) {
                e.preventDefault();
                const submitter = lastSubmitter || e.originalEvent?.submitter || null;
                $.confirm({
                    title: 'Confirmar!',
                    content: 'Deseja realmente continuar?',
                    buttons: {
                        confirmar: function() {
                            if (submitter && submitter.hasAttribute('formaction')) {
                                e.currentTarget.setAttribute('action', submitter.getAttribute('formaction'));
                            }
                            lastSubmitter = null;
                            e.currentTarget.submit();
                        },
                        cancelar: function() {}
                    }
                });
            });
        })();
    </script>
@endpush
