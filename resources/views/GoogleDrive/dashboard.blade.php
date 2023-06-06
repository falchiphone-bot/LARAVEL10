@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="card-header">
                    <div class="badge bg-primary text-wrap"
                        style="width: 100%;
                    ;font-size: 24px;lign=˜Center˜">
                        Menu Principal do sistema administrativo e contábil - GOOGLE DRIVE
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-success table-striped">
                        <thead class="table-light">
                            <div class="badge bg-warning text-wrap"
                                style="width: 100%;
                            ; font-size: 16px;a lign=˜Center˜ ">
                                Opções para o sistema do Google Drive
                            </div>
                        </thead>
                        <tbody>
                            @php
                                session(['retornar' => 'googledrive.dashboard']);
                            @endphp
                            {{-- @if (session('googleUser'))
                                CONECTADO
                            @else
                                NÃO CONECTADO AO GOOGLE
                            @endif --}}


                            @can('GoogleDrive - Opções')
                                <tr>
                                    <th>
                                        @can('LANCAMENTOS DOCUMENTOS - LISTAR')
                                        <div class="col-2">
                                                        <a class="btn btn-primary" href="/LancamentosDocumentos">Documentos</a>
                                        </div>
                                        @endcan

                                        @if (session('googleUserDrive'))
                                            <nav class="navbar navbar-red"
                                                style="background-color: hsla(234, 92%, 47%, 0.096);">
                                                <a class="btn btn-success" href="showGoogleClientInfo">Dados do cliente
                                                    Google</a>
                                            </nav>

                                            <nav class="navbar navbar-red"
                                                style="background-color: hsla(234, 92%, 47%, 0.096);">
                                                <a class="btn btn-success" href="/drive/UploadArquivo">Upload de arquivo para
                                                    Google Drive</a>
                                            </nav>
                                            <nav class="navbar navbar-red"
                                                style="background-color: hsla(234, 92%, 47%, 0.096);">
                                                <a class="btn btn-success" href="/drive/ConsultarArquivo">Consultar arquivo do
                                                    Google
                                                    Drive</a>
                                            </nav>
                                            <nav class="navbar navbar-red"
                                                style="background-color: hsla(234, 92%, 47%, 0.096);">
                                                <a class="btn btn-success" href="/drive/AlterarNomeArquivo">Alterar nome do arquivo no
                                                    Google
                                                    Drive</a>
                                            </nav>
                                            <nav class="navbar navbar-red"
                                            style="background-color: hsla(234, 92%, 47%, 0.096);">
                                            <a class="btn btn-success" href="/drive/ComentarioArquivo">Alterar comentário arquivo no
                                                Google
                                                Drive</a>
                                        </nav>
                                            {{-- <nav class="navbar navbar-red"
                                                style="background-color: hsla(234, 92%, 47%, 0.096);">
                                                <a class="btn btn-success" href="/drive/MoverArquivo">Mover arquivo do Google Drive para pasta de guarda temporária</a>
                                            </nav> --}}

                                            <nav class="navbar navbar-red"
                                                style="background-color: hsla(234, 92%, 47%, 0.096);">
                                                <a class="btn btn-success" href="/drive/DeleteArquivo">Excluir arquivo do Google
                                                    Drive para a lixeira</a>
                                            </nav>
                                            <nav class="navbar navbar-red"
                                                style="background-color: hsla(234, 92%, 47%, 0.096);">
                                                <a class="btn btn-success" href="/drive/DeleteArquivoDefinitivo">Excluir arquivo do Google
                                                    Drive DEFINITIVAMENTE</a>
                                            </nav>
                                            <nav class="navbar navbar-red"
                                                style="background-color: hsla(234, 92%, 47%, 0.096);">

                                                <a class="btn btn-success" target="_blank"

                                                    href="https://drive.google.com/drive/folders/{{ config('services.google_drive.folder') }}">Consultar
                                                    pasta principal do sistema no Google Drive</a>
                                            </nav>
                                        @else
                                            <nav class="navbar navbar-red"
                                                style="background-color: hsla(234, 92%, 47%, 0.096);">
                                                <a class="btn btn-success" href="/drive/google/login/">Autenticar no Google
                                                    Drive</a>
                                            </nav>
                                        @endif

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
