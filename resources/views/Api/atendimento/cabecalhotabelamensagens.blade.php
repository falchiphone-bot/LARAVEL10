<div class="card-body" style="max-width: 1024px; max-height: 900px; overflow: hidden;">

    @include('Api.atendimento.nomecontato')
    @include('Api.atendimento.temposessao')
    @include('Api.atendimento.clientesendoatendido')

    @if ($NomeAtendido->user_atendimento === Auth::user()->email && $NomeAtendido->transferido_para !== null)
        @include('Api.atendimento.cancelartransferenciaatendimento')
    @else
        @if ($NomeAtendido->user_atendimento !== null || $NomeAtendido->user_atendimento !== Auth::user()->email)
            @can('WHATSAPP - ATENDIMENTO - TRANSFERIR SIMULTANEAMENTE')
                @if ($NomeAtendido->transferido_para !== null)
                    @include('Api.atendimento.cancelartransferenciaatendimento')
                @endif
            @endcan
            @can('WHATSAPP - ATENDIMENTO - ATENDER SIMULTANEAMENTE')
                @include('Api.atendimento.mensagemaserenviada')
            @endcan
            @can('WHATSAPP - ATENDIMENTO - ENCERRAR SIMULTANEAMENTE')
                 @include('Api.atendimento.encerramentodoatendimento')
            @endcan
        @endif
    @endif


    @if ($NomeAtendido->user_atendimento === Auth::user()->email && $NomeAtendido->transferido_para === null)
        @include('Api.atendimento.transferiratendimento')
        @include('Api.atendimento.enviarMensagemEncerramentoAtendimento')
    @endif




    @if ($parte_inteira < 24 && $tempo_em_segundos != null && $NomeAtendido->user_atendimento == null)
        @can('WHATSAPP - ATENDIMENTO - REABRIR ATENDIMENTO')
            @include('Api.atendimento.reabrirencerramentoatendimento')
        @endcan
    @else
        @if (
            $parte_inteira > 24 ||
                ($NomeAtendido->quantidade_nao_lida == 0 &&
                    $NomeAtendido->user_atendimento == null &&
                    $Ultimo_atendente !== null))
            @can('WHATSAPP - MENSAGEMAPROVADA')
                @can('WHATSAPP - ATENDIMENTO - INICIAR ATENDIMENTO COM MENSAGEM NAO LIDA')
                    <a href="{{ route('whatsapp.ConvidarMensagemAprovada', $id) }}" class="btn btn-secondary" tabindex="-1"
                        role="button" aria-disabled="true">Selecionar mensagem aprovada para enviar e iniciar contato</a>
                @endcan
            @endcan
        @else
            @if ($NomeAtendido->quantidade_nao_lida > 0)
                @can('WHATSAPP - ATENDIMENTO - INICIAR ATENDIMENTO')
                    @include('Api.atendimento.enviarinicioatendimento')
                @endcan
            @endif
        @endif
    @endif

</div>
