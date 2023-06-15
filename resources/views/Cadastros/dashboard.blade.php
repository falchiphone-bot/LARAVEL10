@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                @if (session('Lancamento'))
                    <div class="alert alert-success">
                        {{ session('Lancamento') }}
                    </div>
                    {{session(['Lancamento' => null]) }}
                 @endif

                <div class="card-header">
                    <div class="badge bg-primary text-wrap"
                        style="width: 100%;
                    ;font-size: 24px;lign=˜Center˜">
                        Menu Principal do sistema administrativo e contábil - CADASTROS

                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-success table-striped">
                        <thead class="table-light">
                            <div class="badge bg-warning text-wrap"
                                style="width: 100%;
                            ; font-size: 16px;a lign=˜Center˜ ">
                                Opções para o sistema de cadastros
                            </div>
                        </thead>
                        <tbody>


                            @can('CADASTROS - LISTAR')
                            <tr>
                                @can('FORMANDOBASE - LISTAR')

                                    <th>
                                        <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                            <a class="btn btn-success" href="/FormandoBase">Formandos - atletas </a>
                                        </nav>
                                    </th>
                                @endcan
                            </tr>
                                <tr>
                                    @can('REPRESENTANTES - LISTAR')

                                        <th>
                                            <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                                <a class="btn btn-success" href="/Representantes">Representantes</a>
                                            </nav>
                                        </th>
                                    @endcan
                                </tr>

                                <tr>
                                    @can('TIPOREPRESENTANTES - LISTAR')

                                        <th>
                                            <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                                <a class="btn btn-success" href="/TipoRepresentantes">Tipo de representantes</a>
                                            </nav>
                                        </th>
                                    @endcan
                                </tr>

                                <tr>
                                    @can('TIPOESPORTE - LISTAR')
                                        <th>
                                            <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                                <a class="btn btn-success" href="/TipoEsporte">Tipo de esportes</a>
                                            </nav>
                                        </th>
                                    @endcan
                                </tr>

                                <tr>
                                    @can('POSICOES - LISTAR')
                                        <th>
                                            <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                                <a class="btn btn-success" href="/Posicoes">Posições</a>
                                            </nav>
                                        </th>
                                    @endcan
                                </tr>

                                <tr>
                                    @can('REDESOCIAL - LISTAR')
                                        <th>
                                            <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                                <a class="btn btn-success" href="/RedeSocial">Redes sociais</a>
                                            </nav>
                                        </th>
                                    @endcan
                                </tr>
                            @endcan

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="badge bg-warning text-wrap" style="width: 100%;
            ; font-size: 16px;a lign=˜Center˜ ">
            </div>
            <div class="b-example-divider"></div>
        </div>
    @endsection

    @push('scripts')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        {{-- <script>
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
        </script> --}}
    @endpush
