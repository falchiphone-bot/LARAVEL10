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
                               IDENTIFICAÇÃO DA MENSAGEM: {{ $messageId }}
                            </p>
                            <p>
                                Objeto: {{ $data['object'] }}
                            </p>
                            <p>
                                Identificação do registro: {{ htmlspecialchars($data['entry'][0]['id']) }}
                            </p>

                            <p>
                                Produto: {{ $messagingProduct }}
                            </p>
                            <p>
                                Telefone: {{ $displayPhoneNumber }}
                            </p>
                            <p>
                                Id Telefone: {{ $phoneNumberId  }}
                            </p>


                            <p>
                                Contato: {{   $contactName }}
                            </p>
                            <p>
                               Telefone: {{ $waId }}
                            </p>
                            <p>
                               De: {{ $from }}
                            </p>
                            <p>
                               Tipo de arquivo: {{ $field }}
                            </p>

                            <p>
                               Mensagem: {{ $body }}
                            </p>
                            <p>
                               Tipo da mensagem: {{ $messageType }}
                            </p>
                            <p>
                               Status da mensagem: {{  $status }}
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
