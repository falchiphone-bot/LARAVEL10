@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            @if (session('status'))
                <div class="alert alert-danger">
                    {{ session('status') }}
                </div>
            @endif

            <div class="card">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @elseif (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="badge bg-warning text-wrap" style="width: 100%; color: blue;">
                    Usuários para o sistema administrativo e contábil
                </div>

                @cannot('USUARIOS - LISTAR')
                    <li>
                        <a href="/dashboard" data-bs-toggle="tooltip" data-bs-placement="center"
                            data-bs-custom-class="custom-tooltip" data-bs-title="Clique e vá para o início do sistema"
                            class="botton-link text-black">
                            <i class="fa-solid fa-house"></i>
                            <a href="{{ route('dashboard') }}" class="btn btn-danger btn-lg enabled" tabindex="-1"
                                role="button" aria-disabled="true">SEM PERMISSÃO PARA ESTE SERVIÇO. CONSULTE O ADMINISTRADOR.
                                Clique e vá para o início do sistema</a>
                        </a>
                    </li>


                @endcan


                @can('USUARIOS - LISTAR')
                    @can('USUARIOS - INCLUIR')
                        <a href="{{ route('Usuarios.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1"
                            role="button" aria-disabled="true">Incluir usuário pelo administrador</a>
                    @endcan
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="badge bg-success text-wrap" style="width: 100%; color: white;">
                                <p>Total de usuários: {{ $linhas }}</p>
                            </div>
                            <div class="ms-3 d-flex gap-2">
                                <a href="{{ route('Usuarios.export', request()->query()) }}" class="btn btn-outline-success btn-sm">Exportar CSV</a>
                                <a href="{{ route('Usuarios.exportXlsx', request()->query()) }}" class="btn btn-outline-success btn-sm">Exportar XLSX</a>
                                <a href="{{ route('Usuarios.exportPdf', request()->query()) }}" class="btn btn-outline-success btn-sm">Exportar PDF</a>
                            </div>
                        </div>
                        <form method="GET" class="row g-2 align-items-center mb-3">
                            <input type="hidden" name="sort" value="{{ $sort ?? 'name' }}">
                            <input type="hidden" name="dir" value="{{ $dir ?? 'asc' }}">
                            <input type="hidden" name="per_page" value="{{ request('per_page', $cadastros->perPage()) }}">
                            <div class="col-md-6">
                                <input type="text" name="q" class="form-control" placeholder="Buscar por nome ou email" value="{{ $q ?? '' }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Cadastrado de</label>
                                <input type="date" name="created_from" class="form-control" value="{{ $createdFrom ?? '' }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Cadastrado até</label>
                                <input type="date" name="created_to" class="form-control" value="{{ $createdTo ?? '' }}">
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-primary" type="submit">Buscar</button>
                            </div>
                            @if(($q ?? '') !== '' || ($createdFrom ?? '') !== '' || ($createdTo ?? '') !== '')
                                <div class="col-auto">
                                    <a class="btn btn-outline-secondary" href="{{ route('Usuarios.index', ['sort' => $sort ?? 'name', 'dir' => $dir ?? 'asc', 'per_page' => request('per_page', $cadastros->perPage())]) }}">Limpar</a>
                                </div>
                            @endif
                        </form>
                        <table class="table" style="background-color: rgb(247, 247, 213);">
                            <thead>
                                <tr>
                                    <th scope="col" class="px-6 py-4">
                                        @php
                                            $isName = ($sort ?? 'name') === 'name';
                                            $nextDir = ($isName && ($dir ?? 'asc') === 'asc') ? 'desc' : 'asc';
                                        @endphp
                                        <a href="{{ route('Usuarios.index', ['sort' => 'name', 'dir' => $nextDir, 'per_page' => request('per_page', $cadastros->perPage()), 'q' => $q ?? null]) }}">
                                            NOME
                                            @if($isName)
                                                <small>{!! ($dir ?? 'asc') === 'asc' ? '&#9650;' : '&#9660;' !!}</small>
                                            @endif
                                        </a>
                                    </th>
                                    <th scope="col" class="px-6 py-4">
                                        @php
                                            $isEmail = ($sort ?? 'name') === 'email';
                                            $nextDirEmail = ($isEmail && ($dir ?? 'asc') === 'asc') ? 'desc' : 'asc';
                                        @endphp
                                        <a href="{{ route('Usuarios.index', ['sort' => 'email', 'dir' => $nextDirEmail, 'per_page' => request('per_page', $cadastros->perPage()), 'q' => $q ?? null]) }}">
                                            EMAIL
                                            @if($isEmail)
                                                <small>{!! ($dir ?? 'asc') === 'asc' ? '&#9650;' : '&#9660;' !!}</small>
                                            @endif
                                        </a>
                                    </th>
                                    <th scope="col" class="px-6 py-4">
                                        @php
                                            $isCreated = ($sort ?? 'name') === 'created_at';
                                            $nextDirCreated = ($isCreated && ($dir ?? 'asc') === 'asc') ? 'desc' : 'asc';
                                        @endphp
                                        <a href="{{ route('Usuarios.index', ['sort' => 'created_at', 'dir' => $nextDirCreated, 'per_page' => request('per_page', $cadastros->perPage()), 'q' => $q ?? null]) }}">
                                            DATA CADASTRO
                                            @if($isCreated)
                                                <small>{!! ($dir ?? 'asc') === 'asc' ? '&#9650;' : '&#9660;' !!}</small>
                                            @endif
                                        </a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cadastros as $cadastro)
                                    <tr>

                                        <td class="whitespace-nowrap px-6 py-0"> {{ $cadastro->name }}</td>
                                        <td class="whitespace-nowrap px-6 py-0">{{ $cadastro->email }}</td>
                                        <td class="whitespace-nowrap px-6 py-0">{{ optional($cadastro->created_at)->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <div class="row mt-2">
                                                <div class="col-6">
                                                    @can('USUARIOS - EDITAR')
                                                        <a href="{{ route('Usuarios.edit', $cadastro->id) }}"
                                                            class="btn btn-success btn-sm enabled" tabindex="-1" role="button"
                                                            aria-disabled="true">Editar</a>
                                                    @endcan

                                                    @can('USUARIOS - EXCLUIR')

                                                    {{-- PROTEGIDO NA CONTROLLER PARA NÃO EXCLUIR CASO TIVER SENDO USADO EM ALGUMA MODEL. --}}
                                                        <form method="POST"
                                                            action="{{ route('Usuarios.destroy', $cadastro->id) }}" class="js-confirm">
                                                            @csrf
                                                            <input type="hidden" name="_method" value="DELETE">
                                                            <button class="btn btn-danger btn-sm enabled" tabindex="-1"
                                                                role="button" aria-disabled="true">Excluir</button>
                                                        </form>
                                                    @endcan

                                                    @can('USUARIOS - PERMISSOES')
                                                        <a href="{{ route('Usuarios.show', $cadastro->id) }}"
                                                            class="btn btn-info btn-sm enabled" tabindex="-1" role="button"
                                                            aria-disabled="true">Permissões</a>
                                                    @endcan
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="d-flex justify-content-between align-items-center gap-3">
                            <div class="text-muted">
                                Exibindo {{ $cadastros->firstItem() }}–{{ $cadastros->lastItem() }} de {{ $cadastros->total() }}
                            </div>
                            <form method="GET" class="d-flex align-items-center gap-2">
                                <input type="hidden" name="sort" value="{{ $sort ?? 'name' }}">
                                <input type="hidden" name="dir" value="{{ $dir ?? 'asc' }}">
                                <input type="hidden" name="q" value="{{ $q ?? '' }}">
                                <input type="hidden" name="created_from" value="{{ $createdFrom ?? '' }}">
                                <input type="hidden" name="created_to" value="{{ $createdTo ?? '' }}">
                                <label for="per_page" class="form-label m-0">por página</label>
                                <select id="per_page" name="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                                    @foreach ([10,20,50,100] as $n)
                                        <option value="{{ $n }}" {{ (int)request('per_page', $cadastros->perPage()) === $n ? 'selected' : '' }}>{{ $n }}</option>
                                    @endforeach
                                </select>
                            </form>
                            <div>
                                {{ $cadastros->appends(['sort' => $sort ?? null, 'dir' => $dir ?? null, 'per_page' => request('per_page', $cadastros->perPage()), 'q' => $q ?? null, 'created_from' => $createdFrom ?? null, 'created_to' => $createdTo ?? null])->links() }}
                            </div>
                        </div>
                    </div>
                @endcan
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

        // Confirmação apenas para formulários marcados com .js-confirm (não interfere na busca/paginação)
        $('form.js-confirm').on('submit', function(e) {
            e.preventDefault();
            const form = this;
            $.confirm({
                title: 'Confirmar!',
                content: 'Deseja realmente continuar?',
                buttons: {
                    confirmar: function() { form.submit(); },
                    cancelar: function() {}
                }
            });
        });
    </script>
@endpush
