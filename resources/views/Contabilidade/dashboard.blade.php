@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="card-header">
                    <div class="badge bg-primary text-wrap" style="width: 100%;
                    ;font-size: 24px;lign=˜Center˜">
                        Menu Principal do sistema administrativo e contábil - CONTABILIDADE

                    </div>


                </div>
                <div class="card-body">
                    <table class="table table-success table-striped">
                        <thead class="table-light">
                            <div class="badge bg-warning text-wrap" style="width: 100%;
                            ; font-size: 16px;a lign=˜Center˜ ">
                                Opções para o sistema de contabilidade
                            </div>
                        </thead>
                        <tbody>


                            @can('CONTABILIDADE - LISTAR')
                                <tr>

                                    <th>

                                        <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                            <a class="btn btn-success" href="/Historicos">Históricos para lançamentos contábeis</a>
                                        </nav>

                                        <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                            <a class="btn btn-success" href="/PlanoContas">Plano de Contas</a>
                                        </nav>

                                        @can('FATURAMENTOS - LISTAR')
                                        <tr>
                                            <th>

                                                <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                                    <a class="btn btn-primary" href="/Faturamentos">Faturamentos registrados</a>
                                                </nav>

                                            </th>
                                        </tr>
                                    @endcan
                                        {{-- @can('PESQUISA AVANCADA')
                                        <tr>
                                            <th> --}}

                                                <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                                    <a class="btn btn-success" href="/PlanoContas/pesquisaavancada">Pesquisa avançada em
                                                        lançamentos
                                                        contábeis</a>
                                                </nav>

                                            {{-- </th>
                                        </tr>
                                    @endcan --}}
                                    </th>


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
