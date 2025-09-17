@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;font-size: 24px;lign=˜Center˜">
                    PREPARADORES/PROFESSORES/TREINADORES PARA SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
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

                <nav class="navbar navbar-red" style="background-color: hsla(244, 92%, 27%, 0.096);">
                    <a class="btn btn-warning" href="Cadastros">Retornar a lista de opções</a> </nav>


                @can('PREPARADORES - INCLUIR')
                    <a href="{{ route('Preparadores.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
                        aria-disabled="true">Incluir preparadores/professores/treinadores</a>
                @endcan
                <div class="card-header">
                    <div class="badge bg-info text-wrap" style="width: 100%;font-size: 24px">
                        <p>Total de tipos de preparadores/professores/treinadores cadastrados no sistema de gerenciamento administrativo e contábil:
                            {{ $total ?? ($model->count() ?? 0) }}</p>
                    </div>
                </div>

                <form method="GET" action="{{ route('Preparadores.index') }}" class="mb-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-md-3">
                            <label class="form-label">Nome</label>
                            <input type="text" name="nome" value="{{ request('nome') }}" class="form-control" placeholder="Buscar por nome">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Email</label>
                            <input type="text" name="email" value="{{ request('email') }}" class="form-control" placeholder="Buscar por email">
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="telefone" value="{{ request('telefone') }}" class="form-control" placeholder="Telefone">
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label">Licença CBF</label>
                            <input type="text" name="licencaCBF" value="{{ request('licencaCBF') }}" class="form-control" placeholder="Licença CBF">
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label">Por página</label>
                            <select name="per_page" class="form-select">
                                @foreach([10,25,50,100] as $pp)
                                    <option value="{{ $pp }}" {{ (string)request('per_page', $perPage ?? 25) === (string)$pp ? 'selected' : '' }}>{{ $pp }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-5 mt-2">
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                            <a href="{{ route('Preparadores.index', array_merge(request()->except(['page']), ['clear'=>1])) }}" class="btn btn-outline-secondary">Limpar</a>
                            <a href="{{ route('Preparadores.export', request()->all()) }}" class="btn btn-outline-success">Exportar CSV</a>
                            <a href="{{ route('Preparadores.exportXlsx', request()->all()) }}" class="btn btn-outline-success">Exportar XLSX</a>
                        </div>
                        <div class="col-12 col-md-3 form-check mt-4">
                            <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember" {{ request('remember', old('remember', session()->has('preparadores.index.filters') ? '1' : '')) ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">Lembrar filtros</label>
                        </div>
                    </div>
                </form>



            </div>

            <tbody>
                <table class="table" style="background-color: rgb(185, 215, 240);">
                    <thead>
                        <tr>
                            <th scope="col" class="px-6 py-4">
                                @php($isCol = request('sort')==='nome')
                                <a href="{{ route('Preparadores.index', array_merge(request()->except(['page']), ['sort' => 'nome', 'dir' => request('dir')==='asc' && request('sort')==='nome' ? 'desc' : 'asc'])) }}">
                                    NOME {!! $isCol ? (request('dir')==='desc' ? '▼' : '▲') : '' !!}
                                </a>
                            </th>
                            <th scope="col" class="px-6 py-4">
                                @php($isCol = request('sort')==='email')
                                <a href="{{ route('Preparadores.index', array_merge(request()->except(['page']), ['sort' => 'email', 'dir' => request('dir')==='asc' && request('sort')==='email' ? 'desc' : 'asc'])) }}">
                                    EMAIL {!! $isCol ? (request('dir')==='desc' ? '▼' : '▲') : '' !!}
                                </a>
                            </th>
                            <th scope="col" class="px-6 py-4">
                                @php($isCol = request('sort')==='telefone')
                                <a href="{{ route('Preparadores.index', array_merge(request()->except(['page']), ['sort' => 'telefone', 'dir' => request('dir')==='asc' && request('sort')==='telefone' ? 'desc' : 'asc'])) }}">
                                    TELEFONE {!! $isCol ? (request('dir')==='desc' ? '▼' : '▲') : '' !!}
                                </a>
                            </th>
                            <th scope="col" class="px-6 py-4">
                                @php($isCol = request('sort')==='licencaCBF')
                                <a href="{{ route('Preparadores.index', array_merge(request()->except(['page']), ['sort' => 'licencaCBF', 'dir' => request('dir')==='asc' && request('sort')==='licencaCBF' ? 'desc' : 'asc'])) }}">
                                    LICENÇA CBF {!! $isCol ? (request('dir')==='desc' ? '▼' : '▲') : '' !!}
                                </a>
                            </th>

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
                                    {{ $Model->email }}
                                </td>
                                <td class="">
                                    {{ $Model->telefone }}
                                </td>
                                <td class="">
                                    {{ $Model->licencaCBF }}
                                </td>


                                @can('PREPARADORES - EDITAR')
                                    <td>
                                        <a href="{{ route('Preparadores.edit', $Model->id) }}" class="btn btn-success" tabindex="-1"
                                            role="button" aria-disabled="true">Editar</a>
                                    </td>
                                @endcan

                                @can('PREPARADORES - VER')
                                    <td>
                                        <a href="{{ route('Preparadores.show', $Model->id) }}" class="btn btn-info" tabindex="-1"
                                            role="button" aria-disabled="true">Ver</a>
                                    </td>
                                @endcan

                                @can('PREPARADORES - EXCLUIR')
                                    <td>
                                        {{-- <form method="POST" action="{{ route('Preparadores.destroy', $Model->id)->with($Model->nome) }}">
                                            @csrf
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="btn btn-danger">
                                                Excluir
                                            </button>
                                        </form> --}}


                                        <form method="POST" action="{{ route('Preparadores.destroy', [$Model->id]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="nome" value="{{ $Model->nome }}">
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
