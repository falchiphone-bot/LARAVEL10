@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;
                ;font-size: 24px; lign=˜Center˜">
                    GOOGLE DRIVE NO SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>


                <div class="card-body">

                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                        <a class="btn btn-warning" href="dashboard">Retornar a lista de opções</a>

                    </nav>
                </div>

                <div class="badge bg-warning text-wrap"
                    style="width: 100%; font-size: 24px; color: black; text-align: center;">
                    <div class="card">
                        <nav class="navbar navbar-success" style="background-color: hsla(234, 92%, 47%, 0.096);">
                            INFORMAÇÃO DO GOOGLE DRIVE
                        </nav>
                    </div>

                </div>




                <div class="card-body">

                    <div class="card">

                                            <div class="badge bg-danger text-wrap"
                            style="width: 100%;
                        ;font-size: 24px; lign=˜Center˜">
                            {{-- {{ session('InformacaoArquivo') }}. <img src="{{ asset(session('avatarProprietário')) }}"> --}}

                        <h2>Informações do arquivo:</h2>
                        <div style="text-align: left;">
                        <p>ID do arquivo: {{ session('InformacaoArquivo')['fileIdConsultar'] }}</p>
                        <p>Proprietário: {{ session('InformacaoArquivo')['ownerDisplayName'] }}</p>
                        <p>Email do proprietário: {{ session('InformacaoArquivo')['emailAddress'] }}</p>
                        <p>Link do arquivo: <a href="{{ session('InformacaoArquivo')['webContentLink'] }}">{{ session('InformacaoArquivo')['webContentLink'] }}</a></p>
                        <p>Descrição do arquivo: {{ session('InformacaoArquivo')['description'] }}</p>
                        </div>
                            {{ session([
                                'InformacaoArquivoProprietário' => null,
                            ]) }}
                        </div>

                    </div>
                    <div class="card">
                        <div class="badge bg-success text-wrap"
                            style="width: 100%;
                        ;font-size: 24px; lign=˜Center˜">
                            {{ session('InformacaoArquivoProprietário') }}

                            {{ session([
                                'InformacaoArquivoProprietário' => null,
                            ]) }}
                        </div>
                    </div>

                </div>
            </div>
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
