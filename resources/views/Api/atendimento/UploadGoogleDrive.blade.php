
@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;
                ;font-size: 24px; lign=˜Center˜">
                    UPLOAD DE ARQUIVO PARA GOOGLE DRIVE NO SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL - WHATSAPP
                </div>


                <div class="card-body">

                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                        <a class="btn btn-warning" href="dashboard">Retornar a lista de opções</a>
                        <a href="#" onclick="window.history.back(); return false;">Voltar para página anterior</a>
                    </nav>
                    @can('WHATSAPP - ATENDIMENTO')
                                <th>
                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" href="/whatsapp/atendimentoWhatsapp">Whatsapp - atendimento</a>
                                    </nav>

                                </th>
                            @endcan
                </div>

                    <div class="badge bg-warning text-wrap" style="width: 100%; font-size: 24px; color: black; text-align: center;">
                        <div class="card">
                            <nav class="navbar navbar-success" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                  UPLOAD DE ARQUIVO SELECIONADO
                            </nav>
                      </div>

                      @can('LANCAMENTOS DOCUMENTOS - LISTAR - WHATSAPP')
                        <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                            <a class="btn btn-primary" href="/LancamentosDocumentos">Últimos 100 documentos enviados</a>
                        </nav>
                    @endcan
                </div>
                        <div class="card-body">
                            <div class="row">
                                <form method="POST" action="/drive/google-drive/file-uploadWhatsapp" enctype="multipart/form-data">
                                    @csrf
                                    <label for="fim">Complemento para o arquivo. LIMITADO A 150 CARACTERES.</label>



                                    <textarea required name="complemento" id="complemento" cols="1" rows="3" class="form-control" style="background-color: green; color: white;"></textarea>



                                    <input required type="file" class="btn btn-danger" name="arquivo">
                                    <p class="my-2">
                                        <button type="submit" class="btn btn-success">Enviar o arquivo para a pasta do sistema.</button>
                                    </p>
                                </form>
                            </div>
                        </div>

                        <div class="card-footer">
                            {{-- <button class='btn btn-primary'>Salvar o evento</button> --}}
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
