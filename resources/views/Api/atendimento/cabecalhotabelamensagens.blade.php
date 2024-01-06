<div class="card-body" style="max-width: 1600px; max-height: 900px; overflow: hidden; padding: 1px; margin-top: 1px;">
    @include('Api.atendimento.nomecontato')
    @include('Api.atendimento.temposessao')
    @include('Api.atendimento.clientesendoatendido')


    @if ($NomeAtendido->user_atendimento === Auth::user()->email && $NomeAtendido->transferido_para)
        @include('Api.atendimento.cancelartransferenciaatendimento')
        @include('Api.atendimento.mensagemaserenviada')

    @elseif ($NomeAtendido->user_atendimento === Auth::user()->email &&  $parte_inteira < 24)
         @include('Api.atendimento.transferiratendimento')
cccasa
         @include('Api.atendimento.mensagemaserenviada')

         @include('Api.atendimento.encerramentodoatendimento')

    @else
        @if ($NomeAtendido->user_atendimento !== Auth::user()->email)
            @can('WHATSAPP - ATENDIMENTO - TRANSFERIR SIMULTANEAMENTE')
                @if ($NomeAtendido->transferido_para !== null)
                    @include('Api.atendimento.cancelartransferenciaatendimento')
                @endif
            @endcan

            @if ($NomeAtendido->user_atendimento)
                @can('WHATSAPP - ATENDIMENTO - TRANSFERIR SIMULTANEAMENTE')
                    @if ($NomeAtendido->transferido_para === null)
                        @include('Api.atendimento.transferiratendimento')
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
    @endif


    {{-- @if ($NomeAtendido->user_atendimento === Auth::user()->email && $NomeAtendido->transferido_para === null)
        @include('Api.atendimento.transferiratendimento')
        @include('Api.atendimento.enviarMensagemEncerramentoAtendimento')
    @endif --}}




    @if ($parte_inteira < 23 && $tempo_em_segundos != null && $NomeAtendido->user_atendimento == null)
        @can('WHATSAPP - ATENDIMENTO - REABRIR ATENDIMENTO')
            @include('Api.atendimento.reabrirencerramentoatendimento')
        @endcan
    @else
        @if (
            $parte_inteira > 23 ||
                ($NomeAtendido->quantidade_nao_lida == 0 &&
                    $NomeAtendido->user_atendimento == null))
            @can('WHATSAPP - MENSAGEMAPROVADA')
                @can('WHATSAPP - ATENDIMENTO - INICIAR ATENDIMENTO COM MENSAGEM NAO LIDA')
                    <a href="{{ route('whatsapp.ConvidarMensagemAprovada', $id) }}" class="btn btn-secondary" tabindex="-1"
                        role="button" aria-disabled="true">Selecionar mensagem aprovada para enviar e iniciar contato</a>
                @endcan
            @endcan
            @if (
                $parte_inteira > 23 &&
                          $NomeAtendido->user_atendimento === Auth::user()->email)
                @can('WHATSAPP - MENSAGEMAPROVADA')
                    @can('WHATSAPP - ATENDIMENTO - ENCERRAR SIMULTANEAMENTE')
                     @include('Api.atendimento.encerramentodoatendimentosemaviso')
                    @endcan
                @endcan
             @endif
            @else
            @if ($NomeAtendido->quantidade_nao_lida > 0)
                @can('WHATSAPP - ATENDIMENTO - INICIAR ATENDIMENTO')
                    @include('Api.atendimento.enviarinicioatendimento')
                @endcan
            @endif
        @endif
    @endif

</div>
