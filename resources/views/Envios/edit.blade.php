@extends('layouts.bootstrap5')
@section('content')
<div class="py-4 bg-light"><div class="container">
  <div class="card"><div class="card-body">
    <h5 class="mb-3">Editar Envio</h5>
    <form method="POST" action="{{ route('Envios.update', $envio) }}" enctype="multipart/form-data">
      @method('PUT')
      @include('Envios._form')
    </form>

    @if(($envio->arquivos ?? null))
    <hr>
    <h6>Arquivos já enviados</h6>
    <table class="table table-sm align-middle">
      <thead><tr><th></th><th>Arquivo</th><th>Tamanho</th><th>Tipo</th><th></th></tr></thead>
      <tbody>
        @forelse($envio->arquivos as $arq)
          <tr>
            <td class="text-center" style="width:32px">
              @php
                $mime = $arq->mime_type ?? '';
                $icon = 'fa-file';
                if (str_starts_with($mime,'image/')) $icon='fa-file-image';
                elseif ($mime==='application/pdf') $icon='fa-file-pdf';
                elseif (str_starts_with($mime,'video/')) $icon='fa-file-video';
                elseif (str_starts_with($mime,'audio/')) $icon='fa-file-audio';
                elseif (str_contains($mime,'spreadsheet')) $icon='fa-file-excel';
                elseif (str_contains($mime,'presentation')) $icon='fa-file-powerpoint';
                elseif (str_contains($mime,'wordprocessing')) $icon='fa-file-word';
                elseif ($mime==='text/plain') $icon='fa-file-lines';
                elseif (str_contains($mime,'zip')||str_contains($mime,'rar')||str_contains($mime,'7z')) $icon='fa-file-zipper';
              @endphp
              <i class="fa-solid {{ $icon }} text-secondary"></i>
            </td>
            <td>
              <div class="fw-semibold">{{ $arq->original_name }}</div>
              <button class="btn btn-link btn-sm p-0" type="button" data-bs-toggle="collapse" data-bs-target="#preview-{{ $arq->id }}" aria-expanded="false">Pré-visualizar</button>
              <div class="collapse mt-2" id="preview-{{ $arq->id }}">
                @if(Str::startsWith($mime,'image/'))
                  <img src="{{ route('Envios.arquivos.view', [$envio, $arq]) }}" alt="preview" class="img-fluid rounded border">
                @elseif($mime==='application/pdf')
                  <div class="ratio ratio-16x9">
                    <iframe src="{{ route('Envios.arquivos.view', [$envio, $arq]) }}#toolbar=1" title="PDF" allowfullscreen></iframe>
                  </div>
                @else
                  <div class="text-muted small">Prévia não disponível para este tipo.</div>
                @endif
              </div>
            </td>
            <td>{{ number_format($arq->size/1024/1024, 2) }} MB</td>
            <td>{{ $arq->mime_type }}</td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-secondary" target="_blank" href="{{ route('Envios.arquivos.view', [$envio, $arq]) }}">Visualizar</a>
              <a class="btn btn-sm btn-outline-secondary" href="{{ route('Envios.arquivos.download', [$envio, $arq]) }}">Baixar</a>
              @can('ENVIOS - EXCLUIR')
                <form class="d-inline" method="POST" action="{{ route('Envios.arquivos.destroy', [$envio,$arq]) }}" onsubmit="return confirm('Remover este arquivo?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger">Excluir</button>
                </form>
              @endcan
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="text-muted">Nenhum arquivo.</td></tr>
        @endforelse
      </tbody>
    </table>
    @endif
  </div></div>
</div></div>
@endsection
