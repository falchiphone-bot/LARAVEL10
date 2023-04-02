@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="card-header">
                    Menu Principal do sistema administrativo e contábil
                </div>
                <div class="card-body">
                    <table class="table table-success table-striped">
                        <thead class="table-light">
                            Opções para o sistema
                        </thead>
                        <tbody>

                            @can('USUARIOS - LISTAR')
                                <tr>
                                    <th>

                                        <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                            <a class="btn btn-primary" href="/Usuarios">Usuários</a>
                                        </nav>

                                    </th>

                                </tr>
                            @endcan


                            @can('PERMISSOES - LISTAR')
                                <tr>
                                    <th>

                                        <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                            <a class="btn btn-primary" href="/Permissoes">Permissões</a>
                                        </nav>

                                    </th>
                                </tr>
                            @endcan


                            @can('FUNCOES - LISTAR')
                                <tr>
                                    <th>

                                        <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                            <a class="btn btn-primary" href="/Funcoes">Funcões</a>

                                    </th>
                                </tr>
                            @endcan


                            @can('PLANO DE CONTAS - LISTAR')
                                <tr>
                                    <th>

                                        <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                            <a class="btn btn-primary" href="/PlanoContas">Plano de contas padrão</a>
                                        </nav>

                                    </th>
                                </tr>
                            @endcan

                            @can('EMPRESAS - LISTAR')
                                <tr>
                                    <th>

                                        <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                            <a class="btn btn-primary" href="/Empresas">Empresas</a>
                                        </nav>

                                    </th>
                                </tr>
                            @endcan

                            @can('PESQUISA AVANCADA')
                                <tr>
                                    <th>

                                        <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                            <a class="btn btn-primary" href="/PlanoContas/pesquisaavancada">Pesquisa avançada em
                                                lançamentos
                                                contábeis</a>
                                        </nav>

                                    </th>
                                </tr>
                            @endcan

                            @can('MOEDAS - LISTAS')
                            <tr>
                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" href="/Moedas">Moedas</a>
                                    </nav>

                                </th>
                            </tr>
                        @endcan

                        </tbody>
                    </table>
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
