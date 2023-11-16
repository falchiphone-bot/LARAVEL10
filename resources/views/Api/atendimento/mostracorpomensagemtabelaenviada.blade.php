@if ($item->status == 'sent')
    {{ $item->message_template_name }} <br>
    {{ $item->body }}
    {{ $item->image_caption }}


    @if ($item->message_template_name)
        @can('WHATSAPP - MENSAGEMAPROVADA')
            <a href="{{ route('Templates.index') }}" class="btn btn-secondary" tabindex="-1" role="button"
                aria-disabled="true">Mensagens
                aprovadas</a>
        @endcan
    @endif

    @if ($item->user_atendimento)
        <p>
            <span style="color: green;"> Atendente:
            </span><span style="color: blue;">{{ $item->user_atendimento }}</span>
        </p>
    @endif
@endif
