@extends('layouts.bootstrap5')
@section('content')
<div class="py-4 bg-light"><div class="container">
  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="m-0">Envios</h5>
        @can('ENVIOS - INCLUIR')
          <a href="{{ route('Envios.create') }}" class="btn btn-primary btn-sm">Novo Envio</a>
        @endcan
      </div>
      @can('ENVIOS - CUSTOS - LISTAR')
      <form method="GET" action="{{ route('Envios.custos.pdf.filtro') }}" class="row g-2 align-items-end mb-3">
          <div class="col-md-4">
              <label class="form-label small mb-1">Representante</label>
              <select name="representante_id" class="form-select form-select-sm" required>
          <option value="">Selecione...</option>
          @foreach(\App\Models\Representantes::orderBy('nome')->get() as $rep)
            <option value="{{ $rep->id }}">{{ $rep->nome }}</option>
          @endforeach
              </select>
          </div>
          <div class="col-md-3">
              <label class="form-label small mb-1">Data inicial</label>
              <input type="date" name="data_ini" class="form-control form-control-sm" required>
          </div>
          <div class="col-md-3">
              <label class="form-label small mb-1">Data final</label>
              <input type="date" name="data_fim" class="form-control form-control-sm" required>
          </div>
          <div class="col-md-2 d-grid">
              <button class="btn btn-outline-primary btn-sm"><i class="fa fa-file-pdf"></i> PDF de custos por representante</button>
          </div>
      </form>
      @endcan
      @can('ENVIOS - CUSTOS - LISTAR')
      <div class="mb-3">
        <a href="{{ route('Envios.custos.pdf.faixa') }}" class="btn btn-outline-success btn-sm">
          <i class="fa fa-file-pdf"></i> PDF Faixas (Valor Mínimo)
        </a>
      </div>
      @endcan
      @can('ENVIOS - CUSTOS - LISTAR')
      <form method="GET" action="{{ route('Envios.faixas.pdf.filtro') }}" class="row g-2 align-items-end mb-3">
          <div class="col-md-4">
              <label class="form-label small mb-1">Representante</label>
              <select name="representante_id" class="form-select form-select-sm" required>
                  <option value="">Selecione...</option>
                  @foreach(\App\Models\Representantes::orderBy('nome')->get() as $rep)
                      <option value="{{ $rep->id }}">{{ $rep->nome }}</option>
                  @endforeach
              </select>
          </div>
          <div class="col-md-3">
              <label class="form-label small mb-1">Data inicial</label>
              <input type="date" name="data_ini" class="form-control form-control-sm" required>
          </div>
          <div class="col-md-3">
              <label class="form-label small mb-1">Data final</label>
              <input type="date" name="data_fim" class="form-control form-control-sm" required>
          </div>
          <div class="col-md-2 d-grid">
              <button class="btn btn-outline-success btn-sm"><i class="fa fa-file-pdf"></i> PDF de faixas por representante</button>
          </div>
      </form>
      @endcan
      @can('ENVIOS - CUSTOS - LISTAR')
  <form method="GET" action="{{ route('Envios.faixas.pdf.filtro.semvalor') }}" class="row g-2 align-items-end mb-3">
          <div class="col-md-4">
              <label class="form-label small mb-1">Representante</label>
              <select name="representante_id" class="form-select form-select-sm">
                  <option value="">Selecione...</option>
                  @foreach(\App\Models\Representantes::orderBy('nome')->get() as $rep)
                      <option value="{{ $rep->id }}">{{ $rep->nome }}</option>
                  @endforeach
              </select>
          </div>
          <div class="col-md-3">
              <label class="form-label small mb-1">Data inicial</label>
              <input type="date" name="data_ini" class="form-control form-control-sm" required>
          </div>
          <div class="col-md-3">
              <label class="form-label small mb-1">Data final</label>
              <input type="date" name="data_fim" class="form-control form-control-sm" required>
          </div>
          <div class="col-md-2 d-grid">
              <button class="btn btn-outline-danger btn-sm"><i class="fa fa-file-pdf"></i> PDF envios sem faixa (legado sem valor)</button>
          </div>
      </form>
      @endcan
      @can('ENVIOS - CUSTOS - LISTAR')
  <form method="GET" action="{{ route('Envios.faixas.pdf.filtro.semfaixa') }}" class="row g-2 align-items-end mb-3">
          <div class="col-md-4">
              <label class="form-label small mb-1">Representante</label>
              <select name="representante_id" class="form-select form-select-sm">
                  <option value="">Selecione...</option>
                  @foreach(\App\Models\Representantes::orderBy('nome')->get() as $rep)
                      <option value="{{ $rep->id }}">{{ $rep->nome }}</option>
                  @endforeach
              </select>
          </div>
          <div class="col-md-3">
              <label class="form-label small mb-1">Data inicial</label>
              <input type="date" name="data_ini" class="form-control form-control-sm" required>
          </div>
          <div class="col-md-3">
              <label class="form-label small mb-1">Data final</label>
              <input type="date" name="data_fim" class="form-control form-control-sm" required>
          </div>
          <div class="col-md-2 d-grid">
              <button class="btn btn-outline-warning btn-sm"><i class="fa fa-file-pdf"></i> PDF envios sem faixa</button>
          </div>
      </form>
      @endcan
      <form method="GET" class="row g-2 mb-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Buscar</label>
          <input name="q" class="form-control" placeholder="Buscar por nome" value="{{ $q }}">
        </div>
        <div class="col-md-2">
          <label class="form-label">De</label>
          <input type="date" name="created_from" class="form-control" value="{{ $createdFrom ?? '' }}">
        </div>
        <div class="col-md-2">
          <label class="form-label">Até</label>
          <input type="date" name="created_to" class="form-control" value="{{ $createdTo ?? '' }}">
        </div>
        <div class="col-md-3">
          <label class="form-label">Tipo de arquivo</label>
          <select name="tipo" class="form-select">
            <option value="">Todos</option>
            @foreach ([
              'imagem' => 'Imagens',
              'pdf' => 'PDFs',
              'video' => 'Vídeos',
              'audio' => 'Áudios',
              'doc' => 'Word',
              'xls' => 'Excel',
              'ppt' => 'PowerPoint',
              'txt' => 'Texto',
              'zip' => 'Compactados',
            ] as $k=>$label)
              <option value="{{ $k }}" {{ ($tipo ?? '')===$k ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        @if(!empty($isAdmin) && $isAdmin)
        <div class="col-md-1">
          <label class="form-label">Escopo</label>
          <select name="escopo" class="form-select">
            <option value="todos" {{ ($escopo ?? 'todos')==='todos' ? 'selected' : '' }}>Todos</option>
            <option value="meus" {{ ($escopo ?? '')==='meus' ? 'selected' : '' }}>Meus</option>
          </select>
        </div>
        @endif
        <div class="col-auto">
          <button class="btn btn-outline-secondary" type="submit">Buscar</button>
        </div>
        @if(($q ?? '')!=='' || ($createdFrom ?? '')!=='' || ($createdTo ?? '')!=='' || ($tipo ?? '')!=='' || (!empty($isAdmin) && ($escopo ?? '')!=='todos'))
          <div class="col-auto">
            <a class="btn btn-link" href="{{ route('Envios.index') }}">Limpar</a>
          </div>
        @endif
      </form>
      <table class="table table-striped">
        <thead><tr><th>Nome</th><th>Descrição</th><th>Última tentativa</th><th>Criado em</th><th>Arquivos</th><th></th></tr></thead>
        <tbody>
          @forelse($envios as $e)
          <tr>
            <td>{{ $e->nome }}</td>
            <td class="text-muted">{{ Str::limit($e->descricao,80) }}</td>
            <td>
              @php
                $lt = $e->last_transcode_at ?? null;
                try { $ltFmt = $lt ? (\Illuminate\Support\Carbon::parse($lt)->format('d/m/Y H:i')) : null; } catch (\Throwable $ex) { $ltFmt = null; }
              @endphp
              {{ $ltFmt ?? '—' }}
            </td>
            <td>{{ optional($e->created_at)->format('d/m/Y H:i') }}</td>
            <td>
              @if(!empty($isAdmin) && $isAdmin)
                @if(($escopo ?? 'todos') === 'meus')
                  {{ $e->arquivos_user_count ?? 0 }}
                @else
                  {{ $e->arquivos_count ?? 0 }}
                @endif
              @else
                {{ $e->arquivos_user_count ?? 0 }}
              @endif
            </td>
            <td class="text-end">
              @can('ENVIOS - VER')<a href="{{ route('Envios.show',$e) }}" class="btn btn-sm btn-outline-secondary">Ver</a>@endcan
              @can('ENVIOS - EDITAR')<a href="{{ route('Envios.edit',$e) }}" class="btn btn-sm btn-outline-primary">Editar</a>@endcan
              @can('ENVIOS - EXCLUIR')
              <form class="d-inline" method="POST" action="{{ route('Envios.destroy',$e) }}">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Excluir envio e todos os arquivos?')">Excluir</button>
              </form>
              @endcan
            </td>
          </tr>
          @empty
            <tr><td colspan="6" class="text-muted">Nenhum envio encontrado.</td></tr>
          @endforelse
        </tbody>
      </table>
      <div class="d-flex justify-content-between align-items-center">
        <div class="text-muted">Exibindo {{ $envios->firstItem() }}–{{ $envios->lastItem() }} de {{ $envios->total() }}</div>
        {{ $envios->appends(['q'=>$q])->links() }}
      </div>
    </div>
  </div>
</div></div>
@endsection
