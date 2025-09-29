@extends('layouts.bootstrap5')
@section('content')
<div class="py-4 bg-light"><div class="container">
  <div class="card"><div class="card-body">
    <h5 class="mb-3">Editar Envio</h5>
    @if (session()->has('public_links_created') || session()->has('public_link'))
      @php
        $pl = session('public_links_created');
        if (!$pl && session('public_link')) {
          $one = session('public_link');
          $lnks = [];
          if (!empty($one['view'] ?? null)) { $lnks[] = ['label' => 'Visualizar', 'url' => $one['view']]; }
          if (!empty($one['download'] ?? null)) { $lnks[] = ['label' => 'Download', 'url' => $one['download']]; }
          $pl = [ 'expires_at' => $one['expires_at'] ?? '—', 'links' => $lnks ];
        }
      @endphp
      <div class="alert alert-success d-print-none">
        <div class="mb-1"><strong>Links públicos gerados</strong> (expira em: {{ $pl['expires_at'] ?? '—' }})</div>
        <div class="d-flex flex-column gap-1">
          @foreach(($pl['links'] ?? []) as $lnk)
            <div class="input-group input-group-sm">
              <span class="input-group-text">{{ $lnk['label'] ?? 'Link' }}</span>
              <input type="text" class="form-control" value="{{ $lnk['url'] ?? '' }}" readonly>
              <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText('{{ $lnk['url'] ?? '' }}').then(()=>{this.textContent='Copiado'; setTimeout(()=>this.textContent='Copiar',1500);})">Copiar</button>
              <a class="btn btn-outline-primary" target="_blank" href="{{ $lnk['url'] ?? '' }}">Abrir</a>
            </div>
          @endforeach
        </div>
      </div>
    @endif
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
          <tr>
            <td></td>
            <td colspan="4">
              @php $canShare = auth()->id() === $envio->user_id || auth()->id() === $arq->uploaded_by || auth()->user()?->hasPermissionTo('ENVIOS - EDITAR'); @endphp
              @if($canShare)
                <div class="border rounded p-2 bg-light">
                  <div class="d-flex align-items-end gap-2 flex-wrap">
                    <div>
                      <label class="form-label small mb-1">Compartilhar com usuário (e-mail)</label>
                      <form class="d-flex gap-2" method="POST" action="{{ route('Envios.arquivos.share', [$envio, $arq]) }}">
                        @csrf
                        <input type="email" name="email" class="form-control form-control-sm" placeholder="usuario@dominio.com" required>
                        <button class="btn btn-sm btn-outline-primary">Compartilhar</button>
                      </form>
                    </div>
                    @if(($arq->sharedUsers ?? null) && $arq->sharedUsers->count())
                      <div class="ms-auto">
                        <div class="small text-muted">Compartilhado com:</div>
                        <div class="d-flex flex-wrap gap-2">
                          @foreach($arq->sharedUsers as $u)
                            <form method="POST" action="{{ route('Envios.arquivos.unshare', [$envio, $arq, $u]) }}" class="d-inline">
                              @csrf @method('DELETE')
                              <span class="badge text-bg-secondary">
                                {{ $u->name ?? $u->email }}
                                <button class="btn btn-link btn-sm text-white p-0 ms-1 align-baseline" title="Remover" onclick="this.closest('form').submit(); return false;">×</button>
                              </span>
                            </form>
                          @endforeach
                        </div>
                      </div>
                    @endif
                  </div>
                  <div class="mt-2 border-top pt-2">
                    <div class="d-flex align-items-end gap-2 flex-wrap">
                      <div>
                        <label class="form-label small mb-1">Link público temporário</label>
                        <form class="d-flex gap-2" method="POST" action="{{ route('Envios.arquivos.publicLink.create', [$envio, $arq]) }}">
                          @csrf
                          <input type="number" min="1" max="168" name="hours" class="form-control form-control-sm" placeholder="Horas (ex: 24)">
                          <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="allow_download" id="allowdl-{{ $arq->id }}" checked>
                            <label class="form-check-label small" for="allowdl-{{ $arq->id }}">Permitir download</label>
                          </div>
                          <button class="btn btn-sm btn-outline-primary">Gerar link</button>
                        </form>
                      </div>
                      @if(($arq->tokens ?? null) && $arq->tokens->count())
                        <div class="w-100"></div>
                        <div class="small text-muted">Links ativos:</div>
                        <div class="d-flex flex-column gap-1 w-100">
                          @foreach($arq->tokens as $t)
                            <div class="d-flex align-items-center justify-content-between gap-2 border rounded p-2">
                              <div class="small">
                                <div>Visualizar: <a href="{{ route('Envios.public.view', [$t->token]) }}" target="_blank">{{ route('Envios.public.view', [$t->token]) }}</a></div>
                                @if($t->allow_download)
                                  <div>Download: <a href="{{ route('Envios.public.download', [$t->token]) }}" target="_blank">{{ route('Envios.public.download', [$t->token]) }}</a></div>
                                @endif
                                <div>Expira: {{ optional($t->expires_at)->format('d/m/Y H:i') ?? '—' }}</div>
                              </div>
                              <form method="POST" action="{{ route('Envios.arquivos.publicLink.revoke', [$envio, $arq, $t]) }}" onsubmit="return confirm('Revogar este link?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Revogar</button>
                              </form>
                            </div>
                          @endforeach
                        </div>
                      @endif
                    </div>
                  </div>
                </div>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="text-muted">Nenhum arquivo.</td></tr>
        @endforelse
      </tbody>
    </table>
    @endif
    <hr>
    <h6>Custos de Registro</h6>
    @can('ENVIOS - CUSTOS - LISTAR')
    <a href="{{ route('Envios.custos.pdf', ['Envio'=>$envio->getKey()]) }}" class="btn btn-outline-secondary btn-sm mb-2" target="_blank">
        <i class="fa fa-file-pdf"></i> Baixar PDF dos Custos
    </a>
    @endcan
    <div class="row g-3 mb-3">
      <div class="col-12 col-lg-6">
      @can('ENVIOS - CUSTOS - INCLUIR')
  <form method="POST" action="{{ route('Envios.custos.store', ['Envio'=>$envio->getKey()]) }}" class="row g-2 align-items-end">
          @csrf
          <div class="col-12 col-md-4">
            <label class="form-label small mb-1">Data</label>
            <input type="date" name="data" class="form-control form-control-sm" value="{{ old('data', now()->format('Y-m-d')) }}" required>
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label small mb-1">Nome do Custo</label>
            <input type="text" name="nome" class="form-control form-control-sm" maxlength="150" required placeholder="Ex: Taxa cartório" value="{{ old('nome') }}">
          </div>
            <div class="col-12 col-md-3">
            <label class="form-label small mb-1">Valor (R$)</label>
            <input type="number" step="0.01" min="0" name="valor" class="form-control form-control-sm" required value="{{ old('valor','0.00') }}">
          </div>
          <div class="col-12 col-md-1 d-grid">
            <button class="btn btn-sm btn-primary">Adicionar</button>
          </div>
        </form>
      @endcan
      </div>
      <div class="col-12 col-lg-6">
        @php $totalCustos = $envio->custos?->sum('valor') ?? 0; @endphp
        <div class="alert alert-info py-2 mb-0 small"><strong>Total:</strong> R$ {{ number_format($totalCustos,2,',','.') }}</div>
      </div>
    </div>
    <table class="table table-sm align-middle">
      <thead><tr><th>Data</th><th>Nome</th><th class="text-end">Valor (R$)</th><th></th></tr></thead>
      <tbody>
        @forelse(($envio->custos ?? []) as $c)
          <tr data-custo-id="{{ $c->id }}">
            <td class="c-data" data-value="{{ optional($c->data)->format('Y-m-d') }}">{{ optional($c->data)->format('d/m/Y') }}</td>
            <td class="c-nome">{{ $c->nome }}</td>
            <td class="text-end c-valor" data-raw="{{ $c->valor }}">{{ number_format($c->valor,2,',','.') }}</td>
            <td class="text-end c-acoes">
                <div class="btn-group btn-group-sm" role="group">
                  @can('ENVIOS - CUSTOS - EDITAR')
                  <a href="{{ route('Envios.custos.edit', ['Envio'=>$envio->getKey(),'custo'=>$c->getKey()]) }}" class="btn btn-outline-secondary">Editar</a>
                  @endcan
                  @can('ENVIOS - CUSTOS - EXCLUIR')
                  <form method="POST" action="{{ route('Envios.custos.destroy', ['Envio'=>$envio->getKey(),'custo'=>$c->getKey()]) }}" onsubmit="return confirm('Remover este custo?')" class="d-inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger">Excluir</button>
                  </form>
                  @endcan
                </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="text-muted small">Nenhum custo registrado.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div></div>
</div></div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  const table = document.querySelector('table.table');
  if(!table) return;
  table.addEventListener('submit', async function(ev){
    const form = ev.target;
    if(!form.matches('form[action*="/custos/"]')) return;
    // Já confirmou via onsubmit; impedir submit padrão
    ev.preventDefault();
    const row = form.closest('tr');
    const id = row?.getAttribute('data-custo-id');
    try{
      const resp = await fetch(form.action, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
        },
        body: new URLSearchParams([['_method','DELETE']])
      });
      const data = await resp.json().catch(()=>null);
      if(resp.ok && data?.ok){
        if(row) row.remove();
        const totalBox = document.querySelector('.alert.alert-info');
        if(totalBox && data.total_formatted){
          totalBox.innerHTML = '<strong>Total:</strong> R$ ' + data.total_formatted;
        }
      } else {
        alert(data?.message || 'Falha ao remover custo.');
      }
    }catch(e){
      alert('Erro de rede ao remover custo.');
    }
  });
});
</script>
@endpush
@endsection
