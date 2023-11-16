<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="refresh" content="10"> <!-- Atualize a cada 5 segundos -->
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
                    @elseif (session('usuarioatendente'))
                        <div class="alert alert-danger">
                            {{-- {{ session('usuarioatendente') }} --}}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-3">
                            <table>
                                <thead>
                                    {{-- <tr>
                                        <th>Nome</th> --}}
                                    {{-- <th>Telefone</th> --}}

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
                                                    {{ $item->Contato->updated_at->format('d/m/Y H:i') }}
                                                    <button class="bg-success text-white">
                                                        {{ $item->Contato->quantidade_nao_lida }}
                                                    </button>
                                                @else
                                                    {{ $item->Contato->updated_at->format('d/m/Y') }}
                                                @endif
                                            </td>


                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="col-9">
                            <div class="card">
                                @can('WHATSAPP - LISTAR')
                                    <div class="card-footer">

                                        <a href="{{ route('whatsapp.indexlista') }}">Retornar para a lista</a>

                                    </div>
                                @endcan

                                <div class="card-body" style="max-width: 1024px; max-height: 500px; overflow: hidden;">
                                    {{-- @if (session('usuarioatendente') !== null) --}}
                                    <div class="container">
                                        <h1 class="text-center bg-success text-white">
                                            {{ $NomeAtendido->contactName ?? null }}</h1>
                                    </div>

                                    @if ( $NomeAtendido->user_atendimento != NULL && $NomeAtendido->user_atendimento != trim(Auth::user()->email))

                                        <span style="color: green;"> Cliente sendo atendido por: </span>
                                        <span style="color: blue;">{{ $NomeAtendido->user_atendimento }}</span>
                                    @endif

                                        @if ($NomeAtendido->user_atendimento === trim(Auth::user()->email))
                                            <form
                                                action="{{ route('whatsapp.enviarMensagemEncerramentoAtendimento',  $id) }}"
                                                method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="card" style="background-color: #ffffcc; padding: 20px;">
                                                    <input type="hidden" name="recipient_id"
                                                        value="{{ $NomeAtendido->recipient_id ?? null }}">
                                                    <input type="hidden" name="contactName"
                                                        value="{{ $NomeAtendido->contactName ?? null }}">
                                                    <input type="hidden" name="status_mensagem_enviada"
                                                        value="{{ $NomeAtendido->status_mensagem_enviada ?? null }}">

                                                    <button type="submit" class="btn btn-danger">Encerramento do
                                                        atendimento</button>
                                                </div>
                                            </form>

                                            <form action="{{ route('whatsapp.enviarMensagemRespostaAtendimento',  $id) }}"
                                                method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="card" style="background-color: #ffffcc; padding: 20px;">
                                                    <div class="form-group">
                                                        <label for="mensagem">Mensagem a ser enviada</label>
                                                        <textarea id="mensagem" name="mensagem" rows="4" cols="50" class="form-control" onfocus="stopPageRefresh();"
                                                            onblur="allowPageRefresh();"></textarea>
                                                    </div>

                                                    <!-- Adicione um campo oculto para enviar recipient_id -->
                                                    <input type="hidden" name="recipient_id"
                                                        value="{{ $NomeAtendido->recipient_id ?? null }}">
                                                    <input type="hidden" name="contactName"
                                                        value="{{ $NomeAtendido->contactName ?? null }}">
                                                    <input type="hidden" name="status_mensagem_enviada"
                                                        value="{{ $NomeAtendido->status_mensagem_enviada ?? null }}">

                                                    <div class="form-group">
                                                        <label for="arquivo">Selecionar um arquivo:</label>
                                                        <input type="file" id="arquivo" name="arquivo"
                                                            class="form-control-file">
                                                    </div>
                                                    <button type="submit" class="btn btn-success">Enviar a
                                                        mensagem</button>
                                                </div>
                                            </form>
                                        @endif





                                    @if ($NomeAtendido->user_atendimento == NULL)
                                            <form action="{{ route('whatsapp.enviarMensagemInicioAtendimento', $id) }}"
                                                method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="card" style="background-color: #ffffcc; padding: 20px;">
                                                    <input type="hidden" name="recipient_id"
                                                        value="{{ $NomeAtendido->recipient_id ?? null }}">
                                                    <input type="hidden" name="contactName"
                                                        value="{{ $NomeAtendido->contactName ?? null }}">
                                                    <input type="hidden" name="status_mensagem_enviada"
                                                        value="{{ $NomeAtendido->status_mensagem_enviada ?? null }}">


                                                    <button type="submit" class="btn btn-primary">Iniciar o
                                                        atendimento</button>
                                                </div>
                                            </form>
                                            @endif
                                </div>


                                <div class="card-body" style="max-width: 1024px; max-height: 3096px;">



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
                                            @if ($selecao)
                                                <tbody>
                                                    @foreach ($selecao as $item)
                                                        {{-- @if ($NomeAtendido->status_mensagem_enviada == 1)
                                                @continue
                                        @endif --}}

                                                        @if ($item->status === 'delivered' || $item->status === 'read')
                                                            @continue
                                                        @endif

                                                        @if ($item->status == 'sent' && $item->conversation_id !== null)
                                                            @continue
                                                        @endif

                                                        <tr>
                                                            <?php
                                                            $dateString = $item['created_at'];
                                                            $dateTime = new DateTime($dateString);
                                                            $formattedDate = $dateTime->format('d/m/Y H:i:s');
                                                            ?>
                                                            @if (
                                                                $NomeAtendido->status_mensagem_enviada == 0 &&
                                                                    $item->status == 'sent' &&
                                                                    $item['created_at'] > $NomeAtendido->ultima_leitura)
                                                                <td style="background-color: red;">
                                                                    {{ $formattedDate ?? null }}
                                                                </td>
                                                            @else
                                                                <td>
                                                                    {{ $formattedDate ?? null }}
                                                                </td>
                                                            @endif




                                                            <td>
                                                                @if ($item->status == 'sent')
                                                                    {{-- Enviado --}}
                                                                    @if ($NomeAtendido->status_mensagem_entregue == null || $NomeAtendido->status_mensagem_entregue == false)
                                                                        <img src="/icones/visto2azul.png" alt="lido">
                                                                    @elseif(\Carbon\Carbon::parse($item['created_at']) > \Carbon\Carbon::parse($NomeAtendido->ultima_leitura))
                                                                        {{-- <button type="submit" class="btn btn-secondary">//2</button> --}}
                                                                        <img src="/icones/enviado2opaco.png"
                                                                            alt="entregue">
                                                                    @elseif(\Carbon\Carbon::parse($item['created_at']) < \Carbon\Carbon::parse($NomeAtendido->ultima_entrega))
                                                                        {{-- <button type="submit" class="btn btn-primary">///3</button> --}}
                                                                        <img src="/icones/visto2azul.png" alt="lido">
                                                                    @endif
                                                                @elseif($item->status == 'delivered')
                                                                    Entregue
                                                                @elseif($item->status == 'read')
                                                                    Lido
                                                                    @if ($NomeAtendido->status_mensagem_enviada == null || $NomeAtendido->status_mensagem_enviada == false)
                                                                        <form
                                                                            action="{{ route('whatsapp.StatusMensagemEnviada', $id = $item->recipient_id) }}"
                                                                            method="GET" enctype="multipart/form-data">
                                                                            @csrf
                                                                            <button type="submit"
                                                                                class="btn btn-success">Confirma
                                                                                lida</button>
                                                                        </form>
                                                                    @endif
                                                                    @if ($NomeAtendido->status_mensagem_entregue == true)
                                                                        {{-- @if ($item['created_at'] < $NomeAtendido->ultima_entrega)) --}}
                                                                        <img src="/icones/visto2azul.png" alt="Lido">
                                                                    @endif
                                                                @elseif($item->status == 'failed')
                                                                    Falhou
                                                                @elseif($item->status == 'received')
                                                                    {{-- Recebido --}}
                                                                    @if ($item->statusconfirmado == false)
                                                                        @if ($NomeAtendido->user_atendimento == Auth::user()->email)
                                                                            <form
                                                                                action="{{ route('whatsapp.ConfirmaRecebimentoMensagem', $item->id) }}"
                                                                                method="get"
                                                                                enctype="multipart/form-data">
                                                                                @csrf
                                                                                <button type="submit"
                                                                                    class="btn btn-success">Confirma
                                                                                    recebimento</button>
                                                                            </form>
                                                                        @endif
                                                                    @endif
                                                                @endif
                                                            </td>


                                                            <td>
                                                                @if ($item->messagesFrom)
                                                                    {{ $item->body }}
                                                                @elseif($item->status == 'failed')
                                                                    Problema no envio por não autorização do cliente.
                                                                    Deve enviar mensagem convidando-o a autorizar o envio de
                                                                    mensagens.
                                                                    @can('WHATSAPP - MENSAGEMAPROVADA')
                                                                        <a href="{{ route('whatsapp.ConvidarMensagemAprovada', $id = $item->recipient_id) }}"
                                                                            class="btn btn-secondary" tabindex="-1"
                                                                            role="button" aria-disabled="true">Selecionar
                                                                            mensagem aprovada para
                                                                            enviar</a>
                                                                    @endcan
                                                                @endif


                                                                @include('Api.baixarmidiacriaurl')
                                                                @include('Api.mostraimagem')
                                                                @include('Api.mostradocumento')
                                                                @include('Api.mostravideo')
                                                                @include('Api.mostraaudio')

                                                            </td>

                                                            <td>
                                                                @if ($item->status == 'sent')
                                                                    {{ $item->message_template_name }} <br>
                                                                    {{ $item->body }}
                                                                    {{ $item->image_caption }}


                                                                    @if ($item->message_template_name)
                                                                        @can('WHATSAPP - MENSAGEMAPROVADA')
                                                                            <a href="{{ route('Templates.index') }}"
                                                                                class="btn btn-secondary" tabindex="-1"
                                                                                role="button" aria-disabled="true">Mensagens
                                                                                aprovadas</a>
                                                                        @endcan
                                                                    @endif

                                                                    @if ($item->user_atendimento)
                                                                        <p>
                                                                            <span style="color: green;"> Atendente:
                                                                            </span><span
                                                                                style="color: blue;">{{ $item->user_atendimento }}</span>
                                                                        </p>
                                                                    @endif
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

                {{ session(['usuarioatendente' => null]) }}
            @endsection

            @push('scripts')
                <link rel="stylesheet"
                    href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
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

                    window.onbeforeunload = function() {
                        if (!pageRefreshAllowed) {
                            return "Você tem campos não salvos no formulário. Tem certeza de que deseja sair da página?";
                        }
                    };
                </script>
            @endpush
