<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="refresh" content="10">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha2/css/bootstrap.min.css">
    <title>Atendimento Whatsapp - filtro por contato</title>
</head>
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
               <div class="col-6">
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
                                <input type="hidden" name="status_mensagem_enviada" value="{{ $NomeAtendido->status_mensagem_enviada  ?? null}}">

                                <div class="form-group">
                                    <label for="arquivo">Selecionar um arquivo:</label>
                                    <input type="file" id="arquivo" name="arquivo" class="form-control-file">
                                </div>

                                <button type="submit" class="btn btn-success">Enviar</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="container">
                    <div class="card-body">
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
                                        {{-- @if($NomeAtendido->status_mensagem_enviada == 1)
                                                @continue
                                        @endif --}}

                                        @if ($item->status === 'delivered' || $item->status === 'read')
                                            @continue
                                        @endif

                                        @if ($item->status == 'sent' && $item->conversation_id !== NULL)
                                            @continue
                                        @endif

                                        <tr>
                                                <?php
                                                    $dateString = $item['created_at'];
                                                    $dateTime = new DateTime($dateString);
                                                    $formattedDate = $dateTime->format('d/m/Y H:i:s');
                                                ?>
                                               @if($NomeAtendido->status_mensagem_enviada == 0 && $item->status == 'sent' &&  $item['created_at'] > $NomeAtendido->ultima_leitura)
                                                    <td style="background-color: red;">
                                                        {{ $formattedDate ?? null }}
                                                    </td>
                                                @else
                                                    <td>
                                                        {{ $formattedDate ?? null }}
                                                    </td>
                                                @endif




                                            <td class="bg-primary">
                                                    @if($item->status == 'sent')
                                                        Enviado



                                                    @elseif($item->status == 'delivered')
                                                        Entregue

                                                    @elseif($item->status =='read')
                                                        Lido
                                                        @if ($NomeAtendido->status_mensagem_enviada == NULL || $NomeAtendido->status_mensagem_enviada == false)

                                                            <form action="{{ route('whatsapp.StatusMensagemEnviada', $item->recipient_id) }}" method="GET" enctype="multipart/form-data">
                                                                @csrf
                                                                <button type="submit" class="btn btn-success">Confirma lida</button>
                                                            </form>
                                                        @endif

                                                     @elseif($item->status =='failed')
                                                        Falhou

                                                    @elseif($item->status =='received')
                                                        Recebido
                                                        @if ($item->statusconfirmado == false)
                                                                    <form action="{{ route('whatsapp.ConfirmaRecebimentoMensagem', $item->id) }}" method="POST" enctype="multipart/form-data">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-success">Confirma recebimento</button>
                                                            </form>
                                                        @endif

                                                  @endif
                                            </td>


                                            <td>
                                                @if ($item->messagesFrom)
                                                    {{ $item->body }}
                                                @endif

                                                @include('Api.baixarmidiacriaurl')
                                                @include('Api.mostraimagem')
                                                @include('Api.mostradocumento')
                                                @include('Api.mostravideo')
                                                @include('Api.mostraaudio')

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
