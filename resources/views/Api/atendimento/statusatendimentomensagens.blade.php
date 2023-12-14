@if ($item->status == 'sent')

    @if ($NomeAtendido->status_mensagem_entregue == null || $NomeAtendido->status_mensagem_entregue == false)
        <img src="/icones/visto2azul.png" alt="lido">
    @elseif(\Carbon\Carbon::parse($item['created_at']) > \Carbon\Carbon::parse($NomeAtendido->ultima_leitura))

        <img src="/icones/enviado2opaco.png" alt="entregue">
    @elseif(\Carbon\Carbon::parse($item['created_at']) < \Carbon\Carbon::parse($NomeAtendido->ultima_entrega))

        <img src="/icones/visto2azul.png" alt="lido">
    @endif
@elseif($item->status == 'delivered')
    Entregue
@elseif($item->status == 'read')
    Lido
    @if ($NomeAtendido->status_mensagem_enviada == null || $NomeAtendido->status_mensagem_enviada == false)
        <form action="{{ route('whatsapp.StatusMensagemEnviada', $id = $item->recipient_id) }}" method="GET"
            enctype="multipart/form-data">
            @csrf
            <button type="submit" class="btn btn-success">Confirma
                lida</button>
        </form>
    @endif
    @if ($NomeAtendido->status_mensagem_entregue == true)

        <img src="/icones/visto2azul.png" alt="Lido">
    @endif
@elseif($item->status == 'failed')
    Falhou
@elseif($item->status == 'received')
 
    @if ($item->statusconfirmado == false)
        @if ($NomeAtendido->user_atendimento == Auth::user()->email)
            <form action="{{ route('whatsapp.ConfirmaRecebimentoMensagem', $item->id) }}" method="get"
                enctype="multipart/form-data">
                @csrf
                <button type="submit" class="btn btn-success">Confirma
                    recebimento</button>
            </form>
        @endif

        @if ($NomeAtendido->user_atendimento !== Auth::user()->email)
        @can('WHATSAPP - ATENDIMENTO - ATENDER SIMULTANEAMENTE')
            <form action="{{ route('whatsapp.ConfirmaRecebimentoMensagem', $item->id) }}" method="get"
                enctype="multipart/form-data">
                @csrf
                <button type="submit" class="btn btn-success">Confirma
                    recebimento</button>
            </form>
            @endcan
        @endif
    @endif
@endif
