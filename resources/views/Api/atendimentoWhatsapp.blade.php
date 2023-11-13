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


            <div class="row">
               <div class="col-4">
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($Contatos as $item)
                            <tr>


                                <td><a
                                        href="{{ route('whatsapp.atendimentoWhatsappFiltroTelefone', $item->Contato->recipient_id) }}">{{ $item->Contato->contactName }}</a>
                                </td>
                                <td>


                                    @if ($item->Contato->quantidade_nao_lida > 0)
                                        {{ $item->Contato->updated_at->format("d/m/Y h:m")}}
                                        <button class="bg-success text-white">
                                            {{ $item->Contato->quantidade_nao_lida }}
                                        </button>
                                    @else
                                     {{ $item->Contato->updated_at->format("d/m/Y")}}

                                    @endif
                                </td>


                            </tr>
                        @endforeach
                        </tbody>
                    </table>
               </div>
               <div class="col-8">
                <div class="card">
                    <div class="card-footer">
                        <a href="{{ route('whatsapp.indexlista') }}">Retornar para a lista</a>
                    </div>

                    <div class="card-body">

                        <div class="container">
                            <h1 class="text-center bg-success text-white">{{ $NomeAtendido->contactName ?? null }}</h1>
                        </div>


                        <div class="col-12">
                             <table class="table">
                                <thead>
                                    <tr>
                                        <th class="table-warning">Data</th>
                                        <th class="table-success">Recebida</th>
                                        <th class="table-success">Enviada</th>

                                    </tr>
                                </thead>
                                @if($selecao)
                                <tbody>
                                    @foreach ($selecao as $item)
                                        <tr>
                                            <td>
                                                <?php

                                                $dateString = $item['created_at'];
                                                $dateTime = new DateTime($dateString);
                                                $formattedDate = $dateTime->format('d/m/Y H:i:s');
                                                ?>
                                                {{ $formattedDate ?? null }}
                                            </td>
                                            <td>
                                                @if ($item->messagesFrom)
                                                    {{ $item->body }}
                                                @endif
                                            </td>
                                            <td>
                                                @if ($item->status == 'sent')
                                                    {{ $item->body }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                @endif
                            </table>
                        </div>



                    </div>
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
