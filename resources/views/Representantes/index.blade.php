@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                     {{ session(['success' => NULL])}}
                 @elseif(session('cpf'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    {{ session(['cpf' => NULL])}}
                    @elseif(session('cnpj'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    {{ session(['cnpj' => NULL])}}
                @elseif (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    {{ session(['error' => NULL])}}
                @endif

                <div class="card">
                    <div class="badge bg-primary text-wrap" style="width: 100%;font-size: 24px;text-align: center;">
                        REPRESENTANTES PARA SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                    </div>
                </div>
                <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                    <a class="btn btn-warning" href="/Cadastros">Retornar a lista de opções</a>
                </nav>

                <div class="card-header">
                    <div class="badge bg-info text-wrap" style="width: 100%;font-size: 24px;text-align: center;">
                        <p>Total de representantes cadastrados no sistema de gerenciamento administrativo e contábil:
                            {{ $total ?? ($model->count() ?? 0) }}</p>
                    </div>
                </div>

                <form method="GET" action="{{ route('Representantes.index') }}" class="mb-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-md-3">
                            <label class="form-label">Nome</label>
                            <input type="text" name="nome" value="{{ request('nome') }}" class="form-control" placeholder="Buscar por nome">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">E-mail</label>
                            <input type="text" name="email" value="{{ request('email') }}" class="form-control" placeholder="Buscar por e-mail">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Agente FIFA</label>
                            <select name="agente_fifa" class="form-select">
                                <option value="">Todos</option>
                                <option value="1" {{ request('agente_fifa')==='1' ? 'selected' : '' }}>Sim</option>
                                <option value="0" {{ request('agente_fifa')==='0' ? 'selected' : '' }}>Não</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Oficial CBF</label>
                            <select name="oficial_cbf" class="form-select">
                                <option value="">Todos</option>
                                <option value="1" {{ request('oficial_cbf')==='1' ? 'selected' : '' }}>Sim</option>
                                <option value="0" {{ request('oficial_cbf')==='0' ? 'selected' : '' }}>Não</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Sem registro</label>
                            <select name="sem_registro" class="form-select">
                                <option value="">Todos</option>
                                <option value="1" {{ request('sem_registro')==='1' ? 'selected' : '' }}>Sim</option>
                                <option value="0" {{ request('sem_registro')==='0' ? 'selected' : '' }}>Não</option>
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
                        <div class="col-12 col-md-3">
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                            <a href="{{ route('Representantes.index', array_merge(request()->except(['page']), ['clear'=>1])) }}" class="btn btn-outline-secondary">Limpar</a>
                        </div>
                        <div class="col-12 col-md-3 form-check mt-4">
                            <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember" {{ request('remember', old('remember', session()->has('representantes.index.filters') ? '1' : '')) ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                Lembrar filtros
                            </label>
                        </div>
                    </div>
                </form>

                @can('REPRESENTANTES - INCLUIR')
                    <a href="{{ route('Representantes.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
                        aria-disabled="true">Incluir representante</a>
                @endcan

                <a href="{{ route('Representantes.export', request()->all()) }}" class="btn btn-outline-success btn-lg">Exportar CSV</a>
                <a href="{{ route('Representantes.exportXlsx', request()->all()) }}" class="btn btn-outline-success btn-lg">Exportar XLSX</a>

            </div>

            <table class="table" style="background-color: rgb(247, 247, 255);">
                <thead>
                    <tr>
                        <th scope="col" class="px-6 py-4">
                            @php($isCol = request('sort')==='nome')
                            <a href="{{ route('Representantes.index', array_merge(request()->except(['page']), ['sort' => 'nome', 'dir' => request('dir')==='asc' && request('sort')==='nome' ? 'desc' : 'asc'])) }}">
                                NOME {!! $isCol ? (request('dir')==='desc' ? '▼' : '▲') : '' !!}
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-4">TELEFONE</th>
                        <th scope="col" class="px-6 py-4">EMAIL</th>
                        <th scope="col" class="px-6 py-4">CPF</th>


                        <th scope="col" class="px-6 py-4">CNPJ</th>


                        <th scope="col" class="px-6 py-4">CLUBE</th>
                        <th scope="col" class="px-6 py-4">
                            @php($isCol = request('sort')==='agente_fifa')
                            <a href="{{ route('Representantes.index', array_merge(request()->except(['page']), ['sort' => 'agente_fifa', 'dir' => request('dir')==='asc' && request('sort')==='agente_fifa' ? 'desc' : 'asc'])) }}">
                                AGENTE FIFA {!! $isCol ? (request('dir')==='desc' ? '▼' : '▲') : '' !!}
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-4">
                            @php($isCol = request('sort')==='oficial_cbf')
                            <a href="{{ route('Representantes.index', array_merge(request()->except(['page']), ['sort' => 'oficial_cbf', 'dir' => request('dir')==='asc' && request('sort')==='oficial_cbf' ? 'desc' : 'asc'])) }}">
                                OFICIAL CBF {!! $isCol ? (request('dir')==='desc' ? '▼' : '▲') : '' !!}
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-4">
                            @php($isCol = request('sort')==='sem_registro')
                            <a href="{{ route('Representantes.index', array_merge(request()->except(['page']), ['sort' => 'sem_registro', 'dir' => request('dir')==='asc' && request('sort')==='sem_registro' ? 'desc' : 'asc'])) }}">
                                SEM REGISTRO {!! $isCol ? (request('dir')==='desc' ? '▼' : '▲') : '' !!}
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
                                {{ $Model->telefone }}
                            </td>
                            <td class="">
                                {{ $Model->email }}
                            </td>
                            <td class="">
                                {{ $Model->cpf }}
                            </td>
                            <td class="">
                                {{ $Model->cnpj }}
                            </td>
                            <td class="">
                                {{ $Model->MostraEmpresa->Descricao }}
                            </td>
                            <td class="">
                                @if ($Model->agente_fifa)
                                    <span class="badge bg-success">SIM</span>
                                @else
                                    <span class="badge bg-secondary">NÃO</span>
                                @endif
                            </td>
                            <td class="">
                                @if ($Model->oficial_cbf)
                                    <span class="badge bg-success">SIM</span>
                                @else
                                    <span class="badge bg-secondary">NÃO</span>
                                @endif
                            </td>
                            <td class="">
                                @if ($Model->sem_registro)
                                    <span class="badge bg-success">SIM</span>
                                @else
                                    <span class="badge bg-secondary">NÃO</span>
                                @endif
                            </td>
                            @can('REPRESENTANTES - EDITAR')
                                <td>
                                    <a href="{{ route('Representantes.edit', $Model->id) }}" class="btn btn-success" tabindex="-1"
                                        role="button" aria-disabled="true">Editar</a>
                                </td>
                            @endcan

                            @can('REPRESENTANTES - VER')
                                <td>
                                    <a href="{{ route('Representantes.show', $Model->id) }}" class="btn btn-info" tabindex="-1"
                                        role="button" aria-disabled="true">Ver</a>
                                </td>
                            @endcan

                            @can('REPRESENTANTES - EXCLUIR')
                                <td>
                                    <form method="POST" action="{{ route('Representantes.destroy', $Model->id) }}">
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
        </div>
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
