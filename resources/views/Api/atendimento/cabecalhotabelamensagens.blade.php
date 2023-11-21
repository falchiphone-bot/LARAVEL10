<div class="card-body" style="max-width: 1024px; max-height: 900px; overflow: hidden;">

    @include('Api.atendimento.nomecontato')


    @include('Api.atendimento.clientesendoatendido')

    @include('Api.atendimento.transferiratendimento')

    @include('Api.atendimento.enviarMensagemEncerramentoAtendimento')

    @if ($NomeAtendido->quantidade_nao_lida > 0)
        @can('WHATSAPP - ATENDIMENTO - INICIAR ATENDIMENTO')
            @include('Api.atendimento.enviarinicioatendimento')
        @endcan
     @endif

    @if ($NomeAtendido->quantidade_nao_lida == 0 && $NomeAtendido->user_atendimento == null)
        {{-- @can('WHATSAPP - ATENDIMENTO - INICIAR ATENDIMENTO COM MENSAGEM NAO LIDA')
            @include('Api.atendimento.enviarinicioatendimento')
        @endcan --}}
        @can('WHATSAPP - MENSAGEMAPROVADA')
            @can('WHATSAPP - ATENDIMENTO - INICIAR ATENDIMENTO COM MENSAGEM NAO LIDA')

                        <a href="{{ route('whatsapp.ConvidarMensagemAprovada', $id) }}" class="btn btn-secondary"
                            tabindex="-1" role="button" aria-disabled="true">Selecionar
                            mensagem aprovada para
                            enviar e iniciar contato</a>
                        @endcan
            @endcan
        @endif
</div>
