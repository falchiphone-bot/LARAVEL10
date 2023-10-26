@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">

        <div class="card">
            <div class="badge bg-primary text-wrap" style="width: 100%;">
                SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
            </div>


            <div class="row">
                <div class="card">
                    <div class="card-footer">
                        <a href="{{ route('whatsapp.indexlista') }}">Retornar para a lista</a>
                    </div>
                    <div class="card-header">
                        EXIBIÇÃO DO REGISTRO
                    </div>
                    <div class="card-body">

                        <p>
                            <?php

                            $dateString = $model->created_at;
                            $dateTime = new DateTime($dateString);
                            $formattedDate = $dateTime->format("d/m/Y H:i:s");
                            ?>

                            Data registro: {{ $formattedDate }}
                        </p>

                        <p>
                            <?php

                            $dateString = $model->updated_at;
                            $dateTime = new DateTime($dateString);
                            $formattedDate = $dateTime->format("d/m/Y H:i:s");
                            ?>

                            Data atualização: {{ $formattedDate }}
                        </p>



                        <!DOCTYPE html>
                        <html>

                        <head>
                            <title>Visualização em JSON</title>
                            <script>
                                function convertToJSON() {
                                    const cardContainer = document.querySelector('.card-container');
                                    const jsonOutput = {
                                        container: {
                                            row: {
                                                col: {
                                                    card: {
                                                        cardBody: {
                                                            cardTitle: "",
                                                            cardText: ""
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    };

                                    jsonOutput.container.row.col.card.cardBody.cardTitle = cardContainer.querySelector('.card-title').innerText;
                                    jsonOutput.container.row.col.card.cardBody.cardText = cardContainer.querySelector('.card-text').innerText;

                                    const jsonString = JSON.stringify(jsonOutput, null, 2);
                                    const jsonOutputContainer = document.getElementById('json-output');
                                    jsonOutputContainer.textContent = jsonString;
                                }
                            </script>
                        </head>

                        <body>
                            <div class="container">
                                <div class="row">
                                    <div class="col-md-6 mx-auto card-container">
                                        <div class="card border border-danger">
                                            <div class="card-body bg-light">
                                                <h5 class="card-title">Código de Recebimento</h5>
                                                <p class="card-text font-weight-bold">
                                                    {{ $model['webhook'] }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button onclick="convertToJSON()">Converter para JSON</button>

                            <pre id="json-output"></pre>
                        </body>

                        </html>

                        <p>
                            IDENTIFICAÇÃO DA MENSAGEM: {{ $model['messageId'] }}
                        </p>
                        <p>
                            Objeto: {{ $model['object'] }}
                        </p>
                        <p>
                            Identificação do registro: {{ $model->entry_id }}
                        </p>
                        <p>
                            Tempo da entrada: {{ $model->entry_time }}
                        </p>
                        <p>
                            Tempo da saida: {{ htmlspecialchars($model->messages_Timestamp) }}
                        </p>
                        <p>
                            Contexto From: {{ $model->context_From}}
                        </p>
                        <p>
                            Contexto id: {{ $model->context_Id}}
                        </p>
                        <p>
                            Produto: {{ $model->value_messaging_product}}
                        </p>
                        <p>
                            Telefone: {{ $model->changes_value_metadata_display_phone_number }}
                        </p>
                        <p>
                            Id Telefone: {{ $model->changes_value_metadata_display_phone_id }}
                        </p>
                        <p>
                            Status banimento: {{ $model->changes_value_ban_info_waba_ban_state }}

                        </p>
                        <p>
                            Data banimento: {{ $model->changes_value_ban_info_waba_ban_date }}
                        </p>
                        <p>
                            Contato: {{ $model->contactName  }}
                        </p>
                        <p>
                            Telefone: {{ $model->waId  }}
                        </p>
                        <p>
                            De: {{ $model->from  }}
                        </p>
                        <p>
                            Tipo de arquivo: {{ $model->changes_field   }}
                        </p>
                        <p>
                            Evento: {{ $model->event   }}
                        </p>
                        <p>
                            Id do template: {{ $model->message_template_id   }}
                        </p>
                        <p>
                            Nome do templete: {{ $model->message_template_name   }}
                        </p>

                        <p>
                            Lingua do template: {{ $model->message_template_language   }}
                        </p>

                        <p>
                            Motivo: {{ $model->reason   }}
                        </p>

                        <p>
                            Tipo da mensagem: {{ $model->messageType   }}
                        </p>

                        <p>
                            Mensagem: {{ $model->body  ?? $model->caption   }}
                        </p>





                        <p>
                            Nome do documento: {{ $model->filename }}
                        </p>
                        <p>
                            Tipo do documento: {{ $model->mime_type }}
                        </p>
                        <p>
                            Animado: {{ $model->animated }}
                        </p>
                        <p>
                            Tipo do documento: {{ $model->sha256 }}
                        </p>
                        <p>
                            Id do documento: {{ $model->iddocument }}
                        </p>

                        <p>
                            Status da mensagem: {{ $model->status   }}
                        </p>

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
