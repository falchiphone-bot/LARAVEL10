@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="card-header">
                    <div class="badge bg-warning text-wrap"
                        style="width: 100%; height: 50px;
                    ; font-size: 24px;align=˜Center˜ ">
                        Menu Principal do sistema administrativo e contábil - versão: 15.08.2023 22:53
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-success table-striped">
                        @if (session('googleUser'))
                        <div class="badge bg-success text-wrap" style="width: 100%;align=˜left˜ ">
                            <div class="col-sm-1">
                                <img src="{{ asset(session('googleUser')->avatar) }}" alt="Minha imagem">

                            </div>

                        @else

                        <a href="{{ url('auth/google') }}" style="margin-top: 0px !important;background: rgb(13, 0, 128);color: #ffffff;padding: 5px;border-radius:7px;" class="ml-2 btn-google">
                            <strong>Login no Google</strong>
                          </a>
                        @endif



                        @if (session('googleUser'))
                                <div class="col-sm-12">
                                    <div class="badge bg-warning text-wrap"
                                        style="width: 40%; height: 30px;
                                             ; font-size: 16px;align=˜Center˜ ">
                                        <a href="{{ session('googleUser')->user['link'] }}" target="_blank">Abrir perfil
                                            Google
                                            do usuário em uma nova aba</a>
                                    </div>
                                </div>
                            @endif
                        </div>


                        <div class="badge bg-warning text-wrap" style="width: 100%;align=˜Center˜ ">
                            <div class="badge bg-success text-wrap" style="width: 100%;align=˜Center˜ ">
                                <thead class="table-light">
                                    <div class="badge bg-success text-wrap"
                                        style="width: 100%;
                                    ; font-size: 16px;align=˜Center˜ ">
                                        Opções para o sistema
                                    </div>
                            </div>
                        </div>

                        @can('TRADEIDEA - LISTAR')
                        <tr>
                            <th>

                                <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                    <a class="btn btn-primary" href="/Tradeidea">Investimentos</a>
                                </nav>

                            </th>

                        </tr>
                    @endcan

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


                        {{-- @can('PLANO DE CONTAS - LISTAR')
                                <tr>
                                    <th>

                                        <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                            <a class="btn btn-primary" href="/PlanoContas">Plano de contas padrão</a>
                                        </nav>

                                    </th>
                                </tr>
                            @endcan --}}

                        @can('EMPRESAS - LISTAR')
                            <tr>
                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" href="/Empresas">Empresas</a>
                                    </nav>

                                </th>
                            </tr>
                        @endcan

                        @can('CADASTROS - LISTAR')
                        <tr>
                            <th>

                                <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                    <a class="btn btn-primary" href="/Cadastros">Cadastros</a>
                                </nav>

                            </th>
                        </tr>
                    @endcan



                        @can('CONTABILIDADE - LISTAR')
                            <tr>
                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" href="/Contabilidade">Contabilidade</a>
                                    </nav>

                                </th>
                            </tr>
                        @endcan
                        @can('LANCAMENTOS DOCUMENTOS - LISTAR')
                            <tr>
                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" href="/LancamentosDocumentos">Documentos</a>
                                    </nav>

                                </th>
                            </tr>
                        @endcan

                        @can('COBRANCA - LISTAR')
                            <tr>
                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" href="/Cobranca">Cobrança</a>
                                    </nav>

                                </th>
                            </tr>
                        @endcan



                        @can('MOEDAS - LISTAR')
                            <tr>

                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" <a href="Moedas/dashboard">Moedas Dashboard</a>

                                    </nav>
                                </th>

                            </tr>
                        @endcan

                        @can('AGENDA - LISTAR')
                            <tr>

                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" <a href="Agenda/dashboard">Calendário Dashboard</a>

                                    </nav>
                                </th>

                            </tr>
                        @endcan

                        @can('GoogleDrive - Opções')
                            <tr>

                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" <a href="drive/dashboard">Google Drive Dashboard</a>

                                    </nav>
                                </th>

                            </tr>
                        @endcan

                        @can('FERIADOS - LISTAR')
                            <tr>

                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" <a href="Feriados">Feriados</a>

                                    </nav>
                                </th>

                            </tr>
                        @endcan

                        </tbody>
                    </table>
                    <div class="badge bg-warning text-wrap" style="width: 100%;align=˜Center˜ ">
                        <div class="badge bg-success text-wrap" style="width: 100%;align=˜Center˜ "> . .
                        </div>
                    </div>

                </div>
                <div class="b-example-divider"></div>
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
