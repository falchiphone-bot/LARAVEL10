@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;font-size: 24px;lign=˜Center˜">
                    POSIÇÕES ESPORTES PARA SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>
            </div>


            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    {{ session(['success' =>  null ]) }}

                @elseif (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    {{ session(['error' => NULL])}}

                @endif

                <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                    <a class="btn btn-warning" href="Cadastros">Retornar a lista de opções</a> </nav>


                @can('POSICOES - INCLUIR')
                    <a href="{{ route('Posicoes.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
                        aria-disabled="true">Incluir posição esportiva</a>
                @endcan
                <div class="card-header">
                    <div class="badge bg-info text-wrap" style="width: 100%;font-size: 24px">
                        <p>Total de posições esportivas cadastradas no sistema de gerenciamento administrativo e contábil:
                            {{ $total ?? ($model->count() ?? 0) }}</p>
                    </div>
                </div>
                <form method="GET" action="{{ route('Posicoes.index') }}" class="mb-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-md-4">
                            <label class="form-label">Nome</label>
                            <input type="text" name="nome" value="{{ request('nome') }}" class="form-control" placeholder="Buscar por nome">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Esporte</label>
                            <select name="tipo_esporte" class="form-select">
                                <option value="">Todos</option>
                                @isset($TipoEsporte)
                                    @foreach($TipoEsporte as $esporte)
                                        <option value="{{ $esporte->id }}" {{ (string)request('tipo_esporte') === (string)$esporte->id ? 'selected' : '' }}>{{ $esporte->nome }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label">Por página</label>
                            <select name="per_page" class="form-select">
                                @foreach([10,25,50,100] as $pp)
                                    <option value="{{ $pp }}" {{ (string)request('per_page', $perPage ?? 25) === (string)$pp ? 'selected' : '' }}>{{ $pp }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6 mt-2">
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                            <a href="{{ route('Posicoes.index', array_merge(request()->except(['page']), ['clear'=>1])) }}" class="btn btn-outline-secondary">Limpar</a>
                            <a href="{{ route('Posicoes.export', request()->all()) }}" class="btn btn-outline-success">Exportar CSV</a>
                            <a href="{{ route('Posicoes.exportXlsx', request()->all()) }}" class="btn btn-outline-success">Exportar XLSX</a>
                        </div>
                        <div class="col-12 col-md-3 form-check mt-2">
                            <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember" {{ request('remember', old('remember', session()->has('posicoes.index.filters') ? '1' : '')) ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">Lembrar filtros</label>
                        </div>
                    </div>
                </form>

            </div>

                <table class="table" style="background-color: rgb(147, 247, 113);">
                    <thead>
                        <tr>
                            <th scope="col" class="px-6 py-4">
                                @php($isCol = request('sort')==='nome')
                                <a href="{{ route('Posicoes.index', array_merge(request()->except(['page']), ['sort' => 'nome', 'dir' => request('dir')==='asc' && request('sort')==='nome' ? 'desc' : 'asc'])) }}">
                                    NOME {!! $isCol ? (request('dir')==='desc' ? '▼' : '▲') : '' !!}
                                </a>
                            </th>

                            <th scope="col" class="px-6 py-4">ESPORTE</th>


                            <th scope="col" class="px-6 py-4"></th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($model as $Model)
                            <tr>

                                <td class="">
                                    {{ $Model->nome }}
                                </td>
                       <td class="">
                           {{ $Model->MostraTipoEsporte->nome ?? null}}
                      </td>


                                @can('POSICOES - EDITAR')
                                    <td>
                                        <a href="{{ route('Posicoes.edit', $Model->id) }}" class="btn btn-success" tabindex="-1"
                                            role="button" aria-disabled="true">Editar</a>
                                    </td>
                                @endcan

                                @can('POSICOES - VER')
                                    <td>
                                        <a href="{{ route('Posicoes.show', $Model->id) }}" class="btn btn-info" tabindex="-1"
                                            role="button" aria-disabled="true">Ver</a>
                                    </td>
                                @endcan

                                @can('POSICOES - EXCLUIR')
                                    <td>
                                        <form method="POST" action="{{ route('Posicoes.destroy', $Model->id)  }}">
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
                    @if(method_exists($model, 'links'))
                        <div class="d-flex justify-content-center">
                            {!! $model->onEachSide(1)->links('pagination::bootstrap-5') !!}
                        </div>
                    @endif
                                <div class="badge bg-primary text-wrap" style="width: 100%;">
        </div>
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

        $('form').submit(function(e) {
            e.preventDefault();
            $.confirm({
                title: 'Confirmar!',
                content: 'Confirma?',
                buttons: {
                    confirmar: function() {
                        // $.alert('Confirmar!');
                        $.confirm({
                            title: 'Confirmar!',
                            content: 'Deseja realmente continuar?',
                            buttons: {
                                confirmar: function() {
                                    // $.alert('Confirmar!');
                                    e.currentTarget.submit()
                                },
                                cancelar: function() {
                                    // $.alert('Cancelar!');
                                },

                            }
                        });

                    },
                    cancelar: function() {
                        // $.alert('Cancelar!');
                    },

                }
            });
        });
    </script>
@endpush
