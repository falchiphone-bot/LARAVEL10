@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                @if (session('Lancamento'))
                    <div class="alert alert-success">
                        {{ session('Lancamento') }}
                    </div>
                    {{ session(['Lancamento' => null]) }}
                @endif
                <div class="badge bg-primary text-wrap" style="width: 100%;
                ;font-size: 24px; lign=˜Center˜">
                    IMPORTAR ARQUIVO EXCEL TRADE IDEA
                </div>


                <div class="card-body">

                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                        <a class="btn btn-warning" href="\dashboard">Retornar a lista de opções</a>
                        @can('TRADEIDEA - LISTAR')
                            <a class="btn btn-success" href="/Tradeidea">Lista de trade idea</a>
                        @endcan
                    </nav>

                </div>

                <div class="badge bg-warning text-wrap"
                    style="width: 100%; font-size: 24px; color: black; text-align: center;">
                    <div class="card">
                        <nav class="navbar navbar-success" style="background-color: hsla(234, 92%, 47%, 0.096);">
                            SELECIONAR ARQUIVO CONFORME OPÇÕES DISPONÍVEIS
                        </nav>
                    </div>

                </div>
                <div class="card-body">

                    @can('TRADEIDEA - IMPORTAR ARQUIVO EXCEL TRADE IDEA')
                            <div class="row">
                                <form method="POST" action="/Tradeidea/ImportaArquivoExcelTradeIdea"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <label for="fim"></label>
                                    <div class="badge bg-success text-wrap"
                                        style="width: 100%; font-size: 24px; color: white; text-align: center;">

                                        <label for="fim">Arquivo excel trade idea</label>
                                        <br>
                                                    {{-- <input type="checkbox" name="DESCONSIDERAR_BLOQUEIOS_EMPRESAS" value='true'>
                                                    <label for="checkbox_enviar">DESCONSIDERAR BLOQUEIOS DA EMPRESA</label>
                                                    <br> --}}

                                                    {{-- <input type="checkbox" name="DESCONSIDERAR_BLOQUEIOS_CONTAS" value='true'>
                                                    <label for="checkbox_enviar">DESCONSIDERAR BLOQUEIOS DAS CONTAS</label>
                                                    <br> --}}

                                                    {{-- <input type="checkbox" name="filtrarnaolocalizou" value='true'>
                                                    <label for="checkbox_Filtrar_nao_localizou">FILTRAR SOMENTE OS QUE NÃO LOCALIZOU</label> --}}

                                                    {{-- <input type="checkbox" name="verarray" value='true'>
                                                    <label for="checkbox_verarray">MOSTRAR TABELA - ARRAY</label>
                                                    <br> --}}


                                                    {{-- <input type="checkbox" name="Conciliar_Data_Descricao_Valor" value='true'>
                                                    <label for="checkbox_enviar">Conciliar por Data, Descrição e Valor</label>
                                                    <br> --}}


                                <input type="file" required class="btn btn-danger" name="arquivo">
                                <br>

                                                        {{-- <input type="checkbox" name="vercriarlancamentocomhistorico" value="1">
                                                        <label for="checkbox_enviar">Ver se vai criar lançamento com histórico ou não</label>
                                                        <br>

                                                        <input type="checkbox" name="vercriarlancamento" value="1">
                                                        <label for="checkbox_enviar">Ver se vai criar lançamento sem histórico
                                                            pré-programado</label>
                                                        <br> --}}

                                                        {{-- @can('LEITURA DE ARQUIVO - ENVIAR ARQUIVO PARA CONCILIACA0 BANCARIA E AUTORIZAR CRIAR
                                                            LANCAMENTO')
                                                            <input type="checkbox" name="criarlancamentosemhistorico" value="1">
                                                            <label for="checkbox_enviar">Autorizar criar lançamento sem histórico
                                                                pré-programado</label>
                                                            <br>
                                                        @endcan --}}


                                <p class="my-2">
                                    <button type="submit" class="btn btn-danger">Enviar o arquivo</button>
                                </p>
                        </form>
                    </div>
                @endcan

 





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
        function validateFile(fileInput) {
            var filePath = fileInput.value;
            var allowedExtensions = /(\.csv)$/i;
            if (!allowedExtensions.exec(filePath)) {
                alert('Selecione apenas arquivos com extensão .csv.');
                fileInput.value = '';
                return false;
            }
        }


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
