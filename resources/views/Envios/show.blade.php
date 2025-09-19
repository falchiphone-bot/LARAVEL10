@extends('layouts.bootstrap5')
@section('content')
<div class="py-4 bg-light"><div class="container">
  <div class="card"><div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h5 class="m-0">Envio: {{ $envio->nome }}</h5>
      <div>
        @can('ENVIOS - EDITAR')<a href="{{ route('Envios.edit',$envio) }}" class="btn btn-sm btn-outline-primary">Editar</a>@endcan
        @can('ENVIOS - VER')<a href="{{ route('Envios.zip',$envio) }}" class="btn btn-sm btn-outline-success">Baixar tudo (ZIP)</a>@endcan
        <a href="{{ route('Envios.index') }}" class="btn btn-sm btn-outline-secondary">Voltar</a>
      </div>
    </div>
    <p class="text-muted">{{ $envio->descricao }}</p>

    @can('ENVIOS - INCLUIR')
    <hr>
    <h6>Enviar mais arquivos</h6>
    <form method="POST" action="{{ route('Envios.update', $envio) }}" enctype="multipart/form-data" class="mb-3">
      @csrf @method('PUT')
      <input type="hidden" name="nome" value="{{ $envio->nome }}">
      <input type="hidden" name="descricao" value="{{ $envio->descricao }}">
      <input type="file" name="files[]" class="form-control" multiple>
      @error('files')<div class="text-danger small">{{ $message }}</div>@enderror
      @error('files.*')<div class="text-danger small">{{ $message }}</div>@enderror
      <div class="form-text">Qualquer tipo, até 100 MB por arquivo.</div>
      <button class="btn btn-primary mt-2">Enviar</button>
    </form>
    @endcan

    <h6 class="mt-3">Arquivos</h6>
    <table class="table table-striped table-sm align-middle">
      <thead><tr><th></th><th>Arquivo</th><th>Tamanho</th><th>Tipo</th><th>Última tentativa</th><th>Enviado em</th><th></th></tr></thead>
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
            <div class="fw-semibold d-flex align-items-center gap-2">
              <span>{{ $arq->original_name }}</span>
              @php
                $status = $arq->transcode_status ?? null;
                $hasHls = !empty($arq->hls_path);
                $hasMp4 = !empty($arq->mp4_path);
                $lastRaw = $arq->last_transcode_at ?? null;
                try {
                  $lastTryStr = $lastRaw ? (\Illuminate\Support\Carbon::parse($lastRaw)->format('d/m/Y H:i')) : null;
                } catch (\Throwable $e) { $lastTryStr = null; }
              @endphp
              @if($hasHls)
                <span class="badge bg-success" title="Streaming HLS disponível{{ $lastTryStr ? ' • Última tentativa: '.$lastTryStr : '' }}">HLS</span>
              @elseif($hasMp4)
                <span class="badge bg-success" title="MP4 compatível disponível{{ $lastTryStr ? ' • Última tentativa: '.$lastTryStr : '' }}">MP4</span>
              @elseif($status === 'processing')
                <span class="badge bg-info text-dark" title="Convertendo...{{ $lastTryStr ? ' • Última tentativa: '.$lastTryStr : '' }}">Convertendo</span>
              @elseif($status === 'failed')
                <span class="badge bg-danger me-2" title="Falha na conversão{{ $lastTryStr ? ' • Última tentativa: '.$lastTryStr : '' }}">Falha</span>
                  <form method="POST" action="{{ route('Envios.arquivos.transcode', [$envio, $arq]) }}" class="d-inline" onsubmit="this.querySelector('button').disabled=true;">
                  @csrf
                  <button class="btn btn-xs btn-outline-danger" title="Tentar converter novamente">Reprocessar</button>
                </form>
              @endif
            </div>
            <button class="btn btn-link btn-sm p-0" type="button" data-bs-toggle="collapse" data-bs-target="#preview-{{ $arq->id }}" aria-expanded="false">Pré-visualizar</button>
            <div class="collapse mt-2" id="preview-{{ $arq->id }}">
              @if(Str::startsWith($mime,'image/'))
                <img src="{{ route('Envios.arquivos.view', [$envio, $arq]) }}" alt="preview" class="img-fluid rounded border">
              @elseif($mime==='application/pdf')
                <div class="ratio ratio-16x9">
                  <iframe src="{{ route('Envios.arquivos.view', [$envio, $arq]) }}#toolbar=1" title="PDF" allowfullscreen></iframe>
                </div>
              @elseif(Str::contains($mime,'spreadsheet') || in_array(Str::lower(pathinfo($arq->original_name, PATHINFO_EXTENSION)), ['xls','xlsx']))
                @php $sheetId = 'sheet-prev-'.$arq->id; @endphp
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <div class="small text-muted">Prévia: primeira planilha (apenas leitura)</div>
                  <div class="small text-muted">
                    <a target="_blank" href="{{ route('Envios.arquivos.view', [$envio, $arq]) }}">Abrir original</a> ·
                    <a href="{{ route('Envios.arquivos.download', [$envio, $arq]) }}">Baixar</a>
                  </div>
                </div>
                <div id="{{ $sheetId }}" data-xls-src="{{ route('Envios.arquivos.view', [$envio, $arq]) }}" class="border rounded p-2" style="max-height: 420px; overflow: auto;">
                  Carregando planilha…
                </div>
              @elseif(Str::startsWith($mime,'text/') || in_array(Str::lower(pathinfo($arq->original_name, PATHINFO_EXTENSION)), ['txt','log','md','csv','json','xml','js','css','html','htm','php','py','sh','ini','conf','yml','yaml','sql']))
                @php
                  $ext = Str::lower(pathinfo($arq->original_name, PATHINFO_EXTENSION));
                  $map = [
                    'md'=>'markdown','json'=>'json','xml'=>'xml','js'=>'javascript','css'=>'css','html'=>'markup','htm'=>'markup','php'=>'php','py'=>'python','sh'=>'bash','ini'=>'ini','conf'=>'ini','yml'=>'yaml','yaml'=>'yaml','sql'=>'sql'
                  ];
                  $lang = $map[$ext] ?? ($ext==='csv' ? 'none' : 'none');
                  $codeId = 'text-prev-'.$arq->id;
                @endphp
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <div class="small text-muted">Prévia: {{ $arq->original_name }}</div>
                  <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary" data-action="copy" data-target="#{{ $codeId }}">Copiar</button>
                    <button type="button" class="btn btn-outline-secondary" data-action="wrap" data-target="#{{ $codeId }}" data-wrapped="0">Quebrar linhas</button>
                  </div>
                </div>
                <div class="border rounded" style="max-height: 420px; overflow: auto;">
                  <pre id="{{ $codeId }}" class="line-numbers m-0"><code class="language-{{ $lang }}" data-text-src="{{ route('Envios.arquivos.view', [$envio, $arq]) }}"></code></pre>
                </div>
                <div class="mt-1 small text-muted">
                  <a target="_blank" href="{{ route('Envios.arquivos.view', [$envio, $arq]) }}">Abrir original</a> ·
                  <a href="{{ route('Envios.arquivos.download', [$envio, $arq]) }}">Baixar</a>
                </div>
              @elseif(Str::contains($mime,'wordprocessingml') || Str::endsWith(Str::lower($arq->original_name), '.docx'))
                @php $docxId = 'docx-prev-'.$arq->id; @endphp
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <div class="small text-muted">Prévia: DOCX (formatado)</div>
                  <div class="small text-muted">
                    <a target="_blank" href="{{ route('Envios.arquivos.view', [$envio, $arq]) }}">Abrir original</a> ·
                    <a href="{{ route('Envios.arquivos.download', [$envio, $arq]) }}">Baixar</a>
                  </div>
                </div>
                <div id="{{ $docxId }}" data-docx-src="{{ route('Envios.arquivos.view', [$envio, $arq]) }}" class="border rounded p-3 bg-white" style="max-height: 420px; overflow: auto;">
                  Carregando documento…
                </div>
              @elseif($mime==='application/msword' || Str::endsWith(Str::lower($arq->original_name), '.doc'))
                <div class="alert alert-secondary small mb-2">
                  Prévia para arquivos .doc (Word antigo) não é suportada no navegador. Você pode <a href="{{ route('Envios.arquivos.download', [$envio, $arq]) }}">baixar</a> e abrir no Word/LibreOffice ou converter para .docx para visualizar aqui.
                </div>
              @elseif(Str::startsWith($mime,'video/'))
                <div class="ratio ratio-16x9 border rounded">
                  @php
                    $hasHls = !empty($arq->hls_path);
                    $hasMp4 = !empty($arq->mp4_path);
                  @endphp
                  @if($hasHls)
                    <video controls preload="metadata" style="width:100%; height:100%; border-radius:.375rem;" playsinline
                      src="{{ asset(Str::startsWith($arq->hls_path,'public/') ? ('storage/'.Str::after($arq->hls_path,'public/')) : $arq->hls_path) }}">
                    </video>
                  @elseif($hasMp4)
                    <video controls preload="metadata" style="width:100%; height:100%; border-radius:.375rem;" playsinline>
                      <source src="{{ asset(Str::startsWith($arq->mp4_path,'public/') ? ('storage/'.Str::after($arq->mp4_path,'public/')) : $arq->mp4_path) }}" type="video/mp4">
                    </video>
                  @else
                    <video controls preload="metadata" style="width:100%; height:100%; border-radius:.375rem;" playsinline>
                      <source src="{{ route('Envios.arquivos.view', [$envio, $arq]) }}" type="{{ $arq->mime_type }}">
                      Seu navegador não suporta vídeo HTML5.
                      <a href="{{ route('Envios.arquivos.download', [$envio, $arq]) }}">Baixar arquivo</a>.
                    </video>
                  @endif
                </div>
                <div class="form-text">
                  Observação: arquivos .mov usam normalmente o tipo "video/quicktime" e podem não tocar em alguns navegadores (ex.: Chrome) dependendo do codec. Se não reproduzir, tente abrir no Safari ou faça o download.
                </div>
                <div class="mt-2">
                  @if(empty($arq->hls_path) && empty($arq->mp4_path))
                    @if(($arq->transcode_status ?? null) === 'processing')
                      @php
                        $lastRaw2 = $arq->last_transcode_at ?? null;
                        try { $lastTryStr2 = $lastRaw2 ? (\Illuminate\Support\Carbon::parse($lastRaw2)->format('d/m/Y H:i')) : null; } catch (\Throwable $e) { $lastTryStr2 = null; }
                      @endphp
                      <span class="badge bg-info text-dark" title="Convertendo...{{ $lastTryStr2 ? ' • Última tentativa: '.$lastTryStr2 : '' }}">Convertendo...</span>
                    @elseif(($arq->transcode_status ?? null) === 'failed')
                      @php
                        $lastRaw2 = $arq->last_transcode_at ?? null;
                        try { $lastTryStr2 = $lastRaw2 ? (\Illuminate\Support\Carbon::parse($lastRaw2)->format('d/m/Y H:i')) : null; } catch (\Throwable $e) { $lastTryStr2 = null; }
                      @endphp
                      <span class="badge bg-danger" title="Falha na conversão{{ $lastTryStr2 ? ' • Última tentativa: '.$lastTryStr2 : '' }}">Falha na conversão</span>
                    @else
                        <form method="POST" action="{{ route('Envios.arquivos.transcode', [$envio, $arq]) }}" onsubmit="this.querySelector('button').disabled=true;">
                        @csrf
                        <button class="btn btn-sm btn-outline-primary">Converter para MP4/HLS</button>
                      </form>
                    @endif
                  @else
                    @php
                      $lastRaw2 = $arq->last_transcode_at ?? null;
                      try { $lastTryStr2 = $lastRaw2 ? (\Illuminate\Support\Carbon::parse($lastRaw2)->format('d/m/Y H:i')) : null; } catch (\Throwable $e) { $lastTryStr2 = null; }
                    @endphp
                    <span class="badge bg-success" title="Versões compatíveis disponíveis{{ $lastTryStr2 ? ' • Última tentativa: '.$lastTryStr2 : '' }}">Versões compatíveis disponíveis</span>
                  @endif
                </div>
              @else
                <div class="text-muted small">Prévia não disponível para este tipo.</div>
              @endif
            </div>
          </td>
          <td>{{ number_format($arq->size/1024/1024, 2) }} MB</td>
          <td>{{ $arq->mime_type }}</td>
          <td>
            @php
              $lt = $arq->last_transcode_at ?? null;
              try { $ltFmt = $lt ? (\Illuminate\Support\Carbon::parse($lt)->format('d/m/Y H:i')) : null; } catch (\Throwable $e) { $ltFmt = null; }
            @endphp
            {{ $ltFmt ?? '—' }}
          </td>
          <td>{{ optional($arq->created_at)->format('d/m/Y H:i') }}</td>
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
        <tr><td colspan="7" class="text-muted">Nenhum arquivo enviado.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div></div>
</div></div>
@endsection

@push('scripts')
<!-- SheetJS (XLS/XLSX) e Mammoth (DOCX) para pré-visualização -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/mammoth@1.6.0/mammoth.browser.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function(){
    // Prévia de Excel (XLS/XLSX)
    document.querySelectorAll('[data-xls-src]').forEach(async function(el){
      const url = el.getAttribute('data-xls-src');
      try{
        const resp = await fetch(url);
        const ab = await resp.arrayBuffer();
        const wb = XLSX.read(ab, { type:'array' });
        const name = wb.SheetNames[0];
        const ws = wb.Sheets[name];
        // Gera HTML da planilha
        const html = XLSX.utils.sheet_to_html(ws, { header:'<table class="table table-sm table-striped table-bordered">', footer:'</table>' });
        el.innerHTML = html;
        // Ajustes visuais
        const table = el.querySelector('table');
        if (table) {
          table.style.width = '100%';
        }
      }catch(e){
        el.classList.add('text-danger');
        el.textContent = 'Não foi possível carregar a planilha.';
      }
    });

    // Prévia de DOCX
    document.querySelectorAll('[data-docx-src]').forEach(async function(el){
      const url = el.getAttribute('data-docx-src');
      try{
        const resp = await fetch(url);
        const arrayBuffer = await resp.arrayBuffer();
        const result = await window.mammoth.convertToHtml({ arrayBuffer });
        el.innerHTML = '<div class="docx-content">'+ result.value +'</div>';
      }catch(e){
        el.classList.add('text-danger');
        el.textContent = 'Não foi possível carregar o documento.';
      }
    });
  });
</script>
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script>
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('video').forEach(function(video){
      const src = video.getAttribute('src') || (video.querySelector('source') ? video.querySelector('source').getAttribute('src') : '');
      if (!src) return;
      if (src.endsWith('.m3u8')) {
        if (video.canPlayType('application/vnd.apple.mpegURL')) {
          // Safari iOS/macOS normalmente toca nativo
          video.src = src;
        } else if (window.Hls && Hls.isSupported()) {
          const hls = new Hls({
            maxBufferLength: 30,
          });
          hls.loadSource(src);
          hls.attachMedia(video);
        }
      }
    });
  });
  </script>
<script>
  document.addEventListener('click', async function(e){
    const btn = e.target.closest('button[data-action]');
    if (!btn) return;
    const action = btn.getAttribute('data-action');
    const targetSel = btn.getAttribute('data-target');
    const pre = document.querySelector(targetSel);
    if (!pre) return;
    if (action === 'copy') {
      const code = pre.querySelector('code');
      const text = code ? code.textContent : '';
      try {
        await navigator.clipboard.writeText(text);
        btn.textContent = 'Copiado!';
        setTimeout(() => { btn.textContent = 'Copiar'; }, 1500);
      } catch { /* ignore */ }
    } else if (action === 'wrap') {
      const wrapped = btn.getAttribute('data-wrapped') === '1';
      if (wrapped) {
        pre.style.whiteSpace = '';
        pre.style.wordBreak = '';
        btn.setAttribute('data-wrapped','0');
        btn.textContent = 'Quebrar linhas';
      } else {
        pre.style.whiteSpace = 'pre-wrap';
        pre.style.wordBreak = 'break-word';
        btn.setAttribute('data-wrapped','1');
        btn.textContent = 'Sem quebra';
      }
    }
  });
</script>
@endpush
