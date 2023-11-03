<tr>
    @if ($model->messagesType == 'image' || $model->messagesType == 'sticker')
    @if (empty(trim($model->url_arquivo)) || $model->url_arquivo === 'null' || $model->url_arquivo === 'NULL')


            <td>Imagem ID:</td>
            <td>{{ $model->image_id }}</td>
            <h3>{{ $model->image_caption }}</h3>

            @if ($model->messagesType == 'image')
            <div class="text-center">
                <a href="{{ route('whatsapp.Pegar_URL_Arquivo', $model->image_id) }}"
                   class="btn btn-warning mx-auto" tabindex="-1" role="button" aria-disabled="true">
                    Ver arquivo imagem
                </a>
            </div>
        @endif


            @if ($model->messagesType == 'sticker')
                <a href="{{ route('whatsapp.Pegar_URL_Arquivo', $model->sticker_id) }}"
                class="btn btn-warning" tabindex="-1" role="button"
                aria-disabled="true">Ver arquivo sticker</a>
             @endif

        @else
            <td>Imagem</td>
            <img src="{{ '../' . $model->url_arquivo }}" alt="Imagem" style="display: block; margin: 0 auto;">

        </div>
        @endif
    @endif
</tr>

<tr>
    @if ($model->messagesType == 'video')
        @if ($model->url_arquivo == null)
            <td>Imagem ID:</td>
            <td>{{ $model->video_id }}</td>
            <a href="{{ route('whatsapp.Pegar_URL_Arquivo', $model->video_id) }}"
                class="btn btn-warning" tabindex="-1" role="button"
                aria-disabled="true">Ver arquivo de video</a>

                <h3>{{ $model->video_caption }}</h3>

        @else
             <h3>{{ $model->video_caption }}</h3>
            <video id="my-video" class="video-js" controls preload="auto" width="500"
                height="500">
                <source src="{{ '../' . $model->url_arquivo }}" type="video/mp4">
            </video>
        @endif
    @endif
</tr>


<tr>

    @if ($model->messagesType == 'document')

        @if ($model->url_arquivo == null)
            <td>Documento ID:</td>
            <td>{{ $model->document_id }}</td>
            <a href="{{ route('whatsapp.Pegar_URL_Arquivo', $model->document_id) }}"
                class="btn btn-warning" tabindex="-1" role="button"
                aria-disabled="true">Ver arquivo documento</a>
        @else
            <td>Documento</td>
            @if ($model->document_mime_type == 'application/pdf')
                <a href="{{ '../' . $model->url_arquivo }}" target="_blank"
                    style="display: block; text-align: center;">

                    {{-- <td>Documento</td>
            <embed src="{{ '../'.$model->url_arquivo }}" type="application/pdf" width="300" height="300"> --}}

                    <td>Documento qualquer formato</td>
                    <iframe src="{{ '../' . $model->url_arquivo }}" width="100%"
                        height="1200" target="_blank" style="border: none;"></iframe>



                    @if ($model->document_mime_type == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
                        <p>Documento do Microsoft Word (DOCX)</p>
                    @endif
                    @if ($model->document_mime_type == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                        <p>Documento do Microsoft Excel (XLSX)</p>
                    @endif
                    @if ($model->document_mime_type == 'text/rtf')
                        <p>Documento do Editor de texto (RTF)</p>
                    @endif
                    @if ($model->document_mime_type == 'text/csv')
                        <p>Documento texto (CSV)</p>
                    @endif
                    @if ($model->document_mime_type == 'text/txt')
                        <p>Documento do (TXT)</p>
                    @endif
            @endif

            <a href="{{ '../' . $model->url_arquivo }}"
                download>{{ $model->document_filename }}</a>
            </td>

        @endif
    @endif
</tr>
