

@if ($item->messagesFrom)

    @if ($item->messagesType == 'interactive')
        CADASTRO
    @endif
    @if(!empty($item->body))
       {{trim($item->body) }}
    @endif

@elseif($item->status == 'failed')
    Problema no envio por não autorização do cliente.
    Deve enviar mensagem convidando-o a autorizar o envio de
    mensagens.
    @can('WHATSAPP - MENSAGEMAPROVADA')
        <a href="{{ route('whatsapp.ConvidarMensagemAprovada', $id = $item->recipient_id) }}" class="btn btn-secondary"
            tabindex="-1" role="button" aria-disabled="true">Selecionar
            mensagem aprovada para
            enviar</a>
    @endcan
@endif
