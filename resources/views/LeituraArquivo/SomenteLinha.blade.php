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
                    SELECIONAR SOMENTE LINHA DETERMINADA NO SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>


                <div class="card-body">

                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                        <a class="btn btn-warning" href="\LeituraArquivo">Retornar a lista de opções</a>

                    </nav>
                </div>

                <div class="badge bg-warning text-wrap"
                    style="width: 100%; font-size: 24px; color: black; text-align: center;">
                    <div class="card">
                        <nav class="navbar navbar-success" style="background-color: hsla(234, 92%, 47%, 0.096);">
                            SELECIONAR ARQUIVO SELECIONADO CONFORME OPÇÕES DISPONÍVEIS
                        </nav>
                    </div>

                </div>
                <div class="card-body">
                    <div class="row">

                            <form method="POST" action="/LeituraArquivo/SelecionaLinha" enctype="multipart/form-data">
                                @csrf
                                {{-- <textarea required name="linha" id="linha" cols="5" rows="1" class="form-control" style="background-color: green; color: white;"></textarea> --}}



                                <div class="badge bg-info text-wrap" style="width: 10%; font-size: 16px; color: black; text-align: center;">
                                    <label for="linha">Linha para selecionar</label>
                                    <input type="number" required name="linha" id="linha" class="form-control" style="background-color: green; color: white;">
                                </div>


                                <div class="badge bg-warning text-wrap" style="width: 100%; font-size: 24px; color: black; text-align: center;">

                                        <input type="file" required class="btn btn-danger" name="arquivo">
                                        <p class="my-2">
                                            <button type="submit" class="btn btn-info">Enviar o arquivo para a pasta do sistema e consultar por linha.</button>
                                        </p>

                                </div>
                            </form>
                    </div>

                    <div class="row">
                        <form method="POST" action="/LeituraArquivo/SelecionaDatas" enctype="multipart/form-data">
                            @csrf
                            <label for="fim">Arquivo para selecionar</label>
                            <div class="badge bg-warning text-wrap"
                                style="width: 100%; font-size: 24px; color: black; text-align: center;">
                                <input type="file" required class="btn btn-danger" name="arquivo">



                            <p class="my-2">
                                <button type="submit" class="btn btn-success">Enviar o arquivo para a pasta do sistema e
                                    consulta o arquivo total.</button>
                            </p>
                        </form>
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
