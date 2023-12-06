@extends('layouts.bootstrap5')

@section('content')
<div class="py-5 bg-light">
    <div class="whatsapp-container">
        <div class="card">
            <div class="badge bg-primary text-wrap" style="width: 100%;">
                ATENDIMENTO - WHATSAPP - SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
            </div>

            <div class="card-body">
            @if (session('MensagemNaoPreenchida'))
                <div class="alert alert-danger">
                    {{ session('MensagemNaoPreenchida') }}
                </div>
            @elseif (session('error'))
            <div class="alert alert-danger">
                {{ session('errordesta') }}
            </div>
            @endif


                 @include('Api.atendimento.tabelacontatos')

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
            content: 'Confirma o envio?',
            buttons: {
                confirmar: function() {
                    $.confirm({
                        title: 'Confirmar!',
                        content: 'Deseja realmente continuar com o envio?',
                        buttons: {
                            confirmar: function() {
                                e.currentTarget.submit();
                            },
                            cancelar: function() {
                                // Você pode adicionar ações aqui, se necessário.
                            },
                        }
                    });
                },
                cancelar: function() {
                    // Você pode adicionar ações aqui, se necessário.
                },
            }
        });
    });
</script>
@endpush
