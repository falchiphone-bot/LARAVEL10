@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">
            {{-- <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Users</a></li>
              <li class="breadcrumb-item active" aria-current="page">show</li>
            </ol>
          </nav> --}}

            <div class="card">
                <div class="card-header">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Permissões para : {{ $cadastro->name }}
                        </h2>

                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Email : {{ $cadastro->email }}
                        </p>
                        <div class="card-footer">
                            <a href="{{ route('Usuarios.index') }}" class="btn btn-warning">Retornar para
                                lista de usuários</a>
                        </div>
                    </header>
                </div>
                <form method="post" action="/Usuarios/salvarpermissao/{{ $cadastro->id }}">
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @elseif (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif
                        @csrf
                        <div class="row">
                            <div class="col-sm-12">
                                <select multiple id="permissao" name="permissao[]" class="select2 form-control">
                                    @php
                                        // Usa IDs já atribuídos ao usuário para evitar hasPermissionTo() que pode lançar exceção
                                        $userPermissionIds = $cadastro->permissions ? $cadastro->permissions->pluck('id')->all() : [];
                                    @endphp
                                    @foreach ($permissoes as $id => $name)
                                        <option value="{{ $id }}" @if (in_array($id, $userPermissionIds, true)) selected @endif>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-4">
                                <button type="submite" class="btn btn-success">Salvar</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Funções:
                        </h2>
                    </header>
                </div>
                <form method="post" action="/Usuarios/salvarfuncao/{{ $cadastro->id }}" class="mt-6 space-y-6">
                    <div class="card-body">
                        @csrf
                        <select multiple id="funcao" name="funcao[]" autocomplete="funcao-name"
                            class="select2 form-control">

                            @foreach ($funcoes as $id => $name)
                                <option @if ($cadastro->hasRole($name)) selected @endif value={{ $id }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="card-footer">
                        <button type="submite" class="btn btn-success">Salvar</button>

                    </div>
                </form>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Empresas
                        </h2>
                    </header>
                </div>
                <form method="post" action="/Usuarios/salvar-empresa/{{ $cadastro->id }}" class="mt-6 space-y-6">
                    <div class="card-body">
                        @csrf
                        <select multiple id="empresa" name="empresa[]" class="select2 form-control">
                            @foreach ($empresas as $id => $descricao)
                                <option @if ($empresaUsuarios->where('EmpresaID',$id)->first()) selected @endif value={{ $id }}>
                                    {{ $descricao }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="card-footer">
                        <button type="submite" class="btn btn-success">Salvar</button>

                    </div>
                </form>

                <div class="card-footer">

                    <a href="{{ route('Usuarios.index') }}" class="btn btn-warning">Retornar para
                        lista de usuários</a>
                </div>
            </div>


        @endsection

        @push('scripts')
            <link rel="stylesheet"
                href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
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
