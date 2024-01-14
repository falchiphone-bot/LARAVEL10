@if($NomeAtendido->carregamento_multimidia == true)
@if ($item->messagesType == 'video' || $item->type == 'video')
    @if ($item->url_arquivo == null)
        Imagem ID:
        {{ $item->video_id }}
        <a href="{{ route('whatsapp.Pegar_URL_Arquivo', ['id' =>$item->video_id, 'entry_id' => $entry_id]) }}" class="btn btn-warning" tabindex="-1"


            role="button" aria-disabled="true">Ver arquivo de video</a>

        <h3>{{ $item->video_caption }}</h3>
    @else
        <h3>{{ $item->video_caption }}</h3>
        <video id="my-video" class="video-js" controls preload="auto" width="200" height="200">
            @if (file_exists($item->url_arquivo))
                <source src="{{ '../' . $item->url_arquivo }}" type="video/mp4">
            @endif
        </video>
    @endif
@endif
@endif
