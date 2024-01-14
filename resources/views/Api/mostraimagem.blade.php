@if($NomeAtendido->carregamento_multimidia == true)
@if ($item->messagesType == 'image' || $item->messagesType == 'sticker')

    @if (empty($item->url_arquivo) || $item->url_arquivo === 'null' || $item->url_arquivo === 'NULL')
        Imagem ID: {{ $item->image_id }}
        {{ $item->image_caption }}
        1


        @if ($item->messagesType == 'image')


                <a href="{{  route('whatsapp.Pegar_URL_Arquivo', ['id' =>$item->image_id, 'entry_id' => $entry_id]) }}" target="_blank"


                    rel="noopener noreferrer">
                    <img src="{{ '/storage/' . $item->url_arquivo }}" alt="Imagem" style="max-width: 100px;">
                </a>

        @endif

        @if ($item->messagesType == 'sticker')
            <a href="{{ route('whatsapp.Pegar_URL_Arquivo', $id = $item->sticker_id ?? '6578240662284548') }}"
                target="_blank" rel="noopener noreferrer">
                <img src="{{ '/storage/'. $item->url_arquivo }}" alt="Imagem" style="max-width: 100px;">
            </a>
        @endif
    @else
            @if (file_exists($item->url_arquivo))
            <a href="{{ '/storage/' . $item->url_arquivo }}" target="_blank" rel="noopener noreferrer">

                <img src="{{ '/storage/'  . $item->url_arquivo }}" alt="Imagem" style="max-width: 100px;">
            </a>
        @endif

    @endif

@endif

@endif

@can('LANCAMENTOS DOCUMENTOS - INCLUIR - WHATSAPP')
    @if ($item->url_arquivo)
        @include('Api.incluirdocumentos')
    @endif
@endcan
