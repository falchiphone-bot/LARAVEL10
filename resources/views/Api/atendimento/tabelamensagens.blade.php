
<div class="card-body"">
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


                                @can('WHATSAPP - ATUALIZAR REGISTRO - BAIXAR URL MIDIA')
                                    @include('Api.baixarmidiacriaurl')
                                    @include('Api.mostraimagem')
                                    @include('Api.mostradocumento')
                                    @include('Api.mostravideo')
                                    @include('Api.mostraaudio')
                                @else
                                    @if($NomeAtendido->user_atendimento !== NULL)
                                        @include('Api.baixarmidiacriaurl')
                                        @include('Api.mostraimagem')
                                        @include('Api.mostradocumento')
                                        @include('Api.mostravideo')
                                        @include('Api.mostraaudio')
                                    @endif
                                @endcan


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
 
