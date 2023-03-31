@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">
            {{-- <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Usuarios</a></li>
              <li class="breadcrumb-item active" aria-current="page">Index</li>
            </ol>
          </nav> --}}
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
                        <div class="badge bg-success text-wrap" style="width: 100%; color: white;">
                        <p>Total de usuários: {{ $linhas }}</p>
                        </div>
                        <table class="table" style="background-color: rgb(247, 247, 213);">
                            <thead>
                                <tr>
                                    <th scope="col" class="px-6 py-4">NOME</th>
                                    <th scope="col" class="px-6 py-4">EMAIL
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cadastros as $cadastro)
                                    <tr>

                                        <td class="whitespace-nowrap px-6 py-0"> {{ $cadastro->name }}</td>
                                        <td class="whitespace-nowrap px-6 py-0">{{ $cadastro->email }}</td>
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
                                                            action="{{ route('Usuarios.destroy', $cadastro->id) }}">
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
