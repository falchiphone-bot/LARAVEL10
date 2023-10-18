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
                    <div class="card-header">
                        EXIBIÇÃO DO REGISTRO
                    </div>
                    <div class="card-body">

                        <p>
                            IDENTIFICAÇÃO DA MENSAGEM: {{ $model['messageId'] }}
                        </p>
                        <p>
                            Objeto: {{ $model['object'] }}
                        </p>
                        <p>
                            Identificação do registro: {{ htmlspecialchars($model['entry'][0]['id']) }}
                        </p>

                        <p>
                            Produto: {{ $model['messagingProduct']}}
                        </p>
                        <p>
                            Telefone: {{ $model['displayPhoneNumber'] }}
                        </p>
                        <p>
                            Id Telefone: {{ $model['phoneNumberId'] }}
                        </p>


                        <p>
                            Contato: {{ $model['contactName']  }}
                        </p>
                        <p>
                            Telefone: {{ $model['waId']  }}
                        </p>
                        <p>
                            De: {{ $model['from']  }}
                        </p>
                        <p>
                            Tipo de arquivo: {{ $model['field']   }}
                        </p>





                        <p>
                            Tipo da mensagem: {{ $model['messageType']   }}
                        </p>

                        <p>
                            Mensagem: {{ $model['body']  ?? $model['caption']   }}
                        </p>





                        <p>
                            Nome do documento: {{ $model['filename'] }}
                        </p>
                        <p>
                            Tipo do documento: {{ $model['mime_type'] }}
                        </p>
                        <p>
                            Animado: {{ $model['animated'] }}
                        </p>
                        <p>
                            Tipo do documento: {{ $model['sha256'] }}
                        </p>
                        <p>
                            Id do documento: {{ $model['iddocument'] }}
                        </p>




                        <p>
                            Status da mensagem: {{ $model['status']   }}
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
