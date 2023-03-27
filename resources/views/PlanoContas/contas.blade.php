@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">
            {{-- <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Permissions</a></li>
              <li class="breadcrumb-item active" aria-current="page">edit</li>
            </ol>
          </nav> --}}

            <div class="card">
                <h1 class="text-center">Contas de {{ session('Empresa')->Descricao }}</h1>
                <hr>
                {{-- @cannot('PLANO DE CONTAS - LISTAR')
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
                @endcan --}}


                {{-- @can('PLANO DE CONTAS - INCLUIR')
                    <a href="{{ route('PlanoContas.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1"
                        role="button" aria-disabled="true">Incluir contas no plano de contas padrão</a>
                @endcan --}}
                {{-- <p>Total de contas: {{ $linhas }}</p> --}}

                <table class="table table-bordered">

                    <tr>
                        <th>Descrição</th>
                        <th>Classificação</th>
                        <th>Grau</th>

                    </tr>
                    @foreach ($contasEmpresa as $conta)
                        <tr>
                            <td>
                                @php($c = 0)
                                @while ($c < substr_count($conta->Codigo, '.'))
                                    &nbsp;&nbsp;
                                    @php($c++)
                                @endwhile
                                {{ $conta->Descricao }}
                            </td>
                            <td>
                                {{ $conta->Codigo }}
                            </td>
                            <td>
                                {{ $conta->Grau }}
                            </td>
                        </tr>
                    @endforeach
                </table>
            @endsection

            @push('scripts')
                <link rel="stylesheet"
                    href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
                <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>

                <script>
                    $('form').submit(function(e) {
                        e.preventDefault();
                        $.confirm({
                            title: 'Confirmar!',
                            content: 'Confirma a exclusão? Não terá retorno.',
                            buttons: {
                                confirmar: function() {
                                    // $.alert('Confirmar!');
                                    $.confirm({
                                        title: 'Confirmar!',
                                        content: 'Deseja realmente continuar com a exclusão? Não terá retorno.',
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
