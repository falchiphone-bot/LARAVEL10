<div class="card-body" style="max-width: 1024px; max-height: 900px; overflow: hidden;">

    @include('Api.atendimento.nomecontato')

    @if ($tempo_em_segundos != null)
        {{-- <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
            Tempo da sessão em horas: {{ $tempo_em_horas }} // Tempo em minutos: {{ $tempo_em_minutos }} // Tempo em
            segundos: {{ $tempo_em_segundos }}<br>
        </nav> --}}

        {{-- Este momento: {{ strtotime(now()) }}
        Última mensagem: {{ $NomeAtendido->timestamp . ' Data:' . $NomeAtendido->updated_at }}
        <br> --}}
        <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
             Tempo de sessão: {{ $parte_inteira }} horas  e  {{ $parte_decimal_minutos }}   minutos
         </nav>
        <br>

    @endif


    @include('Api.atendimento.clientesendoatendido')

    @if ($NomeAtendido->user_atendimento === Auth::user()->email)
        @include('Api.atendimento.transferiratendimento')
    @endif


    @include('Api.atendimento.enviarMensagemEncerramentoAtendimento')

    {{-- @if ($NomeAtendido->quantidade_nao_lida > 0)
        @can('WHATSAPP - ATENDIMENTO - INICIAR ATENDIMENTO')
            @include('Api.atendimento.enviarinicioatendimento')
        @endcan
    @endif --}}


    @if ($parte_inteira < 24 && $tempo_em_segundos != null && $NomeAtendido->user_atendimento == null)
        @can('WHATSAPP - ATENDIMENTO - REABRIR ATENDIMENTO')
            @include('Api.atendimento.reabrirencerramentoatendimento')
        @endcan
    @else
        @if (
            $Ultimo_atendente === null ||
                ($parte_inteira > 24 &&
                    $NomeAtendido->quantidade_nao_lida == 0 &&
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
