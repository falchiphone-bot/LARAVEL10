<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="refresh" content="5"> <!-- Atualize a cada 5 segundos -->
</head>

</html>
@extends('layouts.bootstrap5')

@section('content')
<div class="py-5 bg-light">
    <div class="container">
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
                                {{-- <th>Telefone</th> --}}

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($Contatos as $item)
                                <tr>


                                    <td><a href="{{ route('whatsapp.atendimentoWhatsappFiltroTelefone',
                                    $item->Contato->recipient_id) }}">{{ $item->Contato->contactName }}</a></td>
                                    {{-- <td>{{ $item->Contato->recipient_id }}</td> --}}

                                    <td>
                                        {{-- <a href="{{ route('whatsapp.enviar', $item->id) }}" class="btn btn-primary">Enviar Mensagem</a> --}}
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
                        <form action="{{ route('whatsapp.enviarMensagemRespostaAtendimento', $id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="container">
                                <h1 class="text-center bg-success text-white">{{ $NomeAtendido->contactName ?? null }}</h1>
                            </div>

                            <div class="card" style="background-color: #ffffcc; padding: 20px;">
                                <div class="form-group">
                                    <label for="mensagem">Mensagem a ser enviada</label>
                                    <textarea id="mensagem" name="mensagem" rows="4" cols="50" class="form-control" onfocus="stopPageRefresh();" onblur="allowPageRefresh();"></textarea>
                                </div>

                                <!-- Adicione um campo oculto para enviar recipient_id -->
                                <input type="hidden" name="recipient_id" value="{{ $NomeAtendido->recipient_id ?? null}}">
                                <input type="hidden" name="contactName" value="{{ $NomeAtendido->contactName ?? null}}">



                                <div class="form-group">
                                    <label for="arquivo">Selecionar um arquivo:</label>
                                    <input type="file" id="arquivo" name="arquivo" class="form-control-file">
                                </div>

                                <button type="submit" class="btn btn-success">Enviar</button>
                            </div>
                        </form>



                        <div class="col-12">
                             <table class="table">
                                <thead>
                                    <tr>
                                        <th class="table-warning">Data</th>
                                        <th class="table-success"></th>
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


                                            <td class="bg-primary">
                                                    @if($item->status == 'sent')
                                                        Enviado

                                                    @elseif($item->status == 'delivered')
                                                        Entregue

                                                    @elseif($item->status =='read')
                                                        Lido
                                                     @endif
                                            </td>

                                            <td>
                                                @if ($item->messagesFrom)
                                                    {{ $item->body }}
                                                @endif
                                                @include('Api.mostraimagem')
                                                @include('Api.mostradocumento')
                                                @include('Api.mostravideo')
                                            </td>

                                            <td class="bg-warning">
                                                @if ($item->status == 'sent')
                                                    {{ $item->body }}
                                                    {{ $item->image_caption }}
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

var pageRefreshAllowed = true;

function stopPageRefresh() {
    pageRefreshAllowed = false;
}

function allowPageRefresh() {
    pageRefreshAllowed = true;
}

window.onbeforeunload = function () {
    if (!pageRefreshAllowed) {
        return "Você tem campos não salvos no formulário. Tem certeza de que deseja sair da página?";
    }
};


</script>
@endpush
