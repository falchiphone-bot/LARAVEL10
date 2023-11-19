{{-- @can('LANCAMENTOS DOCUMENTOS - INCLUIR') --}}
    @if (session('googleUserDrive'))
        <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
            <a class="btn btn-primary" href="{{ route('google.drive.file.uploadWhatsapp', $item->id) }}">Upload de arquivo para
                Google Drive</a>
        </nav>

        {{-- <a class="btn btn-primary" href="{{ route('whatsapp.UploadGoogleDrive', $item->id) }}">Upload de arquivo para
            Google Drive</a> --}}


    @else
        <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
            <a class="btn btn-success" href="/drive/google/login/">Autenticar Google
                para armazenar no drive</a>
        </nav>
    @endif
{{-- @endcan --}}
