
@if ($item->messagesType == 'image' || $item->type == 'image'|| $item->messagesType == 'sticker')
    @if (empty(trim($item->url_arquivo)) || $item->url_arquivo === 'null' || $item->url_arquivo === 'NULL')
        Imagem ID: {{ $item->image_id }}
        {{ $item->image_caption }}


        @if ($item->messagesType == 'image')
            <div class="text-left">
                <a href="{{ route('whatsapp.Pegar_URL_Arquivo', $item->image_id ?? null) }}" target="_blank" rel="noopener noreferrer">
                    <img src="{{ '...' . $item->url_arquivo }}" alt="Imagem" style="max-width: 100px;">
                </a>
            </div>
        @endif
        @if ($item->messagesType == 'sticker')
            <a href="{{ route('whatsapp.Pegar_URL_Arquivo', $id = $item->sticker_id ?? '6578240662284548') }}" target="_blank" rel="noopener noreferrer">
                <img src="{{ '...' . $item->url_arquivo }}" alt="Imagem" style="max-width: 100px;">
            </a>
        @endif
    @else







        <a href="{{ '../' . $item->url_arquivo }}" target="_blank" rel="noopener noreferrer">
            @if (file_exists($item->url_arquivo))
                 <img src="{{ '../' . $item->url_arquivo }}" alt="Imagem" style="max-width: 100px;">
            @endif

        </a>
    @endif

@endif

 @can('LANCAMENTOS DOCUMENTOS - INCLUIR - WHATSAPP')
    @if ($item->url_arquivo)
        @include('Api.incluirdocumentos')
    @endif
 @endcan



