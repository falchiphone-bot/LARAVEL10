@if ($item->messagesType == 'document' || $item->type == 'document')

    @if ($item->url_arquivo == null)
        Documento ID:
        {{ $item->document_id }}
        <a href="{{ route('whatsapp.Pegar_URL_Arquivo', $item->document_id) }}" class="btn btn-warning" tabindex="-1"
            role="button" aria-disabled="true">Ver arquivo documento</a>
    @else
        Documento
        @if ($item->document_mime_type == 'application/pdf')
            <a href="{{ '../' . $item->url_arquivo }}" target="_blank" style="display: block; text-align: center;">

                @if ($item->document_mime_type == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
                    <p>Documento do Microsoft Word (DOCX)</p>
                @endif
                @if ($item->document_mime_type == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                    <p>Documento do Microsoft Excel (XLSX)</p>
                @endif
                @if ($item->document_mime_type == 'text/rtf')
                    <p>Documento do Editor de texto (RTF)</p>
                @endif
                @if ($item->document_mime_type == 'text/csv')
                    <p>Documento texto (CSV)</p>
                @endif
                @if ($item->document_mime_type == 'text/txt')
                    <p>Documento do (TXT)</p>
                @endif
        @endif
        @if (file_exists($item->url_arquivo))
            Documento qualquer format
            <iframe src="{{ '/storage/' . $item->url_arquivo }}" width="100%" height="400" target="_blank"
                style="border: none;"></iframe>
            <a href="{{ '/storage/' . $item->url_arquivo }}" download>{{ $item->document_filename }}</a>
            </td>
        @endif
    @endif
@endif
