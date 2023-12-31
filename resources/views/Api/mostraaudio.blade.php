

    @if ($item->messagesType == 'audio' || $item->type == 'audio')
        @if ($item->url_arquivo == null)
                Imagem ID:
                {{ $item->audio_id }}
                <a href="{{ route('whatsapp.Pegar_URL_Arquivo', ['id' =>$item->audio_id, 'entry_id' => $entry_id]) }}"
                    class="btn btn-warning" tabindex="-1" role="button"
                    aria-disabled="true">Ver arquivo áudio</a>
        @else

            <audio id="my-audio" class="audio-js" controls preload="auto" width="200" height="200">
                <source src="{{ asset($item->url_arquivo) }}" type="audio/mpeg">
                Seu navegador não suporta o elemento de áudio.
            </audio>

        @endif
    @endif

