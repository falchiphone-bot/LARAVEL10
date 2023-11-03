@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;">
                    ATENDIMENTOS - SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>


                <div class="row">
                    <div class="card">
                        <div class="card-footer">
                            <a href="{{ route('whatsapp.indexlista') }}">Retornar para a lista</a>
                        </div>
                        <!-- inicio da tabela -->
                        <!DOCTYPE html>
                        <html>

                        <head>

                            <style>
                                table {
                                    border-collapse: collapse;
                                    width: 100%;
                                    border: 1px solid #000;
                                    /* Borda da tabela */
                                }

                                th,
                                td {
                                    border: 1px solid #000;
                                    /* Bordas das células */
                                    padding: 8px;
                                }

                                th {
                                    background-color: #33cc33;
                                    /* Cor de fundo do cabeçalho da tabela */
                                    color: white;
                                }
                            </style>
                        </head>

                        <body>

                            <table>
                                <tr>
                                    <th colspan="2">EXIBIÇÃO DO REGISTRO</th>
                                </tr>


                                <tr>
                                    <td>Data registro:</td>
                                    <td>
                                        <?php
                                        $dateString = $model->created_at;
                                        $dateTime = new DateTime($dateString);
                                        $formattedDate = $dateTime->format('d/m/Y H:i:s');
                                        ?>
                                        {{ $formattedDate }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>Data atualização:</td>
                                    <td>
                                        <?php
                                        $dateString = $model->updated_at;
                                        $dateTime = new DateTime($dateString);
                                        $formattedDate = $dateTime->format('d/m/Y H:i:s');
                                        ?>
                                        {{ $formattedDate }}
                                    </td>
                                </tr>
                                <!-- Adicione mais linhas conforme necessário -->
                            </table>


                            <table>



                                <tr>
                                    <th colspan="2">Outros Dados</th>
                                </tr>
                                <tr>
                                    <td>IDENTIFICAÇÃO DA MENSAGEM:</td>
                                    <td>{{ $model['messageId'] }}</td>
                                </tr>



                                <tr>
                                    <td>Contato:</td>
                                    <td>{{ $model->contactName }}</td>
                                </tr>
                                <tr>
                                    <td>Telefone:</td>
                                    <td>{{ $model->waId }}</td>
                                </tr>

                                <tr>
                                    <td>Mensagem:</td>
                                    <td>{{ $model->body ?? $model->caption }}</td>
                                </tr>
                                <tr>
                                    <td>Nome do documento:</td>
                                    <td>{{ $model->filename }}</td>
                                </tr>

                                @can('WHATSAPP - RESPONDER REGISTRO')
                                    @if ($model['messagesFrom'])
                                        <a href="{{ route('whatsapp.PreencherMensagemResposta', $model['id']) }}"
                                            class="btn btn-primary" tabindex="-1" role="button"
                                            aria-disabled="true">Responder</a>
                                    @endif
                                @endcan


                                {{-- <tr>
                                    <td>Status da mensagem:</td>
                                    <td>{{ $model->status }}</td>
                                </tr> --}}





                            </table>


                            <table>
                                <tr>
                                    <td>
                                    @include('Api/atendimento-dados-arquivos')
                                    </td>
                                </tr>

                            </table>
                        </body>

                        </html>


                        <!-- final da tabela -->

                    </div>
                    <div class="card-footer">
                        <a href="{{ route('whatsapp.indexlista') }}">Retornar para a lista</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@push('scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>

    <link href="https://vjs.zencdn.net/7.17.0/video-js.css" rel="stylesheet">
    <script src="https://vjs.zencdn.net/7.17.0/video.js"></script>





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
