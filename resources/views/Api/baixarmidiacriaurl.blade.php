
        @if ($item->messagesType == 'image' || $item->messagesType == 'sticker')
             @can('WHATSAPP - ATUALIZAR REGISTRO - BAIXAR URL MIDIA')

                        @if ($item->messagesType == 'image')

                            <div class="text-center">
                                <a href="{{ route('whatsapp.Pegar_URL_Arquivo', $item->image_id) }}"
                                class="btn btn-warning mx-auto" tabindex="-1" role="button" aria-disabled="true">
                                <img src="/icones/icone-download-verde.jpeg" alt="Ver arquivo de vídeo" class="img-thumbnail" width="30" height="30">
                                </a>
                            </div>
                        @endif
                        @endcan
        @endif



        @if ($item->messagesType == 'video')
            @can('WHATSAPP - ATUALIZAR REGISTRO - BAIXAR URL MIDIA')
            <a href="{{ route('whatsapp.Pegar_URL_Arquivo', $item->video_id) }}" tabindex="-1" role="button" aria-disabled="true">
                <img src="/icones/icone-download-verde.jpeg" alt="Ver arquivo de vídeo" class="img-thumbnail" width=" 30" height=" 30">
            </a>

           @endcan

        @endif


        @if ($item->messagesType == 'audio')
                @can('WHATSAPP - ATUALIZAR REGISTRO - BAIXAR URL MIDIA')
                    <a href="{{ route('whatsapp.Pegar_URL_Arquivo', $item->audio_id) }}"
                        class="btn btn-warning" tabindex="-1" role="button"
                        aria-disabled="true">
                        <img src="/icones/icone-download-verde.jpeg" alt="Ver arquivo de vídeo" class="img-thumbnail" width=" 30" height=" 30">
                    </a>
                @endcan

        @endif

        @if ($item->messagesType == 'document')
                @can('WHATSAPP - ATUALIZAR REGISTRO - BAIXAR URL MIDIA')
                        <a href="{{ route('whatsapp.Pegar_URL_Arquivo', $item->document_id) }}"
                            class="btn btn-warning" tabindex="-1" role="button"
                            aria-disabled="true">
                            <img src="/icones/icone-download-verde.jpeg" alt="Ver arquivo de vídeo" class="img-thumbnail" width=" 30" height=" 30">
                        </a>
                @endcan

        @endif

