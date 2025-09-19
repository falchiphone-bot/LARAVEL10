@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
  <div class="container">
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">SAF - Tipos de Prestadores</h5>
        @can('SAF_TIPOS_PRESTADORES - INCLUIR')
        <a href="{{ route('SafTiposPrestadores.create') }}" class="btn btn-primary btn-sm">Incluir</a>
        @endcan
      </div>
      <div class="card-body">
        @if (session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @elseif (session('error'))
          <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

  <form method="GET" class="row g-2 align-items-center mb-3">
          <input type="hidden" name="sort" value="{{ $sort ?? 'nome' }}">
          <input type="hidden" name="dir" value="{{ $dir ?? 'asc' }}">
          <input type="hidden" name="per_page" value="{{ request('per_page', $model->perPage()) }}">
          <div class="col-md-4">
            <input type="text" name="q" class="form-control" placeholder="Buscar por nome, cidade, UF ou país" value="{{ $q ?? '' }}">
          </div>
          <div class="col-md-4">
            <select name="funcao_profissional_id" class="form-select">
              <option value="">-- todas as funções --</option>
              @foreach(($funcoes ?? []) as $id => $nome)
                <option value="{{ $id }}" {{ (string)($funcaoId ?? '') === (string)$id ? 'selected' : '' }}>{{ $nome }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-auto">
            <button class="btn btn-primary" type="submit">Buscar</button>
          </div>
          @if(($q ?? '') !== '')
            <div class="col-auto">
              <a class="btn btn-outline-secondary" href="{{ route('SafTiposPrestadores.index', ['sort' => $sort ?? 'nome', 'dir' => $dir ?? 'asc', 'per_page' => request('per_page', $model->perPage())]) }}">Limpar</a>
            </div>
          @endif
        </form>

        @can('SAF_TIPOS_PRESTADORES - EXPORTAR')
        <div class="mb-3">
          <a href="{{ route('SafTiposPrestadores.export', request()->all()) }}" class="btn btn-outline-success btn-sm">Exportar CSV</a>
          <a href="{{ route('SafTiposPrestadores.exportXlsx', request()->all()) }}" class="btn btn-outline-success btn-sm">Exportar XLSX</a>
          <a href="{{ route('SafTiposPrestadores.exportPdf', request()->all()) }}" class="btn btn-outline-danger btn-sm">Exportar PDF</a>
          <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#exportPdfAdvancedModal">Exportar PDF (avançado)</button>
        </div>
        @endcan

        @can('SAF_TIPOS_PRESTADORES - EXPORTAR')
        <!-- Modal: Exportar PDF (avançado) -->
        <div class="modal fade" id="exportPdfAdvancedModal" tabindex="-1" aria-labelledby="exportPdfAdvancedModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exportPdfAdvancedModalLabel">Exportar PDF (opções avançadas)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form method="GET" action="{{ route('SafTiposPrestadores.exportPdf') }}" target="_blank">
                <div class="modal-body">
                  <!-- Preservar filtros atuais -->
                  <input type="hidden" name="q" value="{{ $q ?? '' }}">
                  <input type="hidden" name="funcao_profissional_id" value="{{ $funcaoId ?? '' }}">
                  <input type="hidden" name="sort" value="{{ $sort ?? 'nome' }}">
                  <input type="hidden" name="dir" value="{{ $dir ?? 'asc' }}">

                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label">Título do cabeçalho</label>
                      <input type="text" name="header_title" class="form-control" placeholder="Ex.: SAF - Tipos de Prestadores" value="SAF - Tipos de Prestadores">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Subtítulo do cabeçalho</label>
                      <input type="text" name="header_subtitle" class="form-control" placeholder="Ex.: Relatório gerado em {{ now()->format('d/m/Y H:i') }}">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Rodapé (lado esquerdo)</label>
                      <input type="text" name="footer_left" class="form-control" placeholder="Texto do rodapé à esquerda">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Rodapé (lado direito)</label>
                      <input type="text" name="footer_right" class="form-control" placeholder="Texto do rodapé à direita">
                    </div>
                    <div class="col-12">
                      <label class="form-label">URL do logo (opcional)</label>
                      <input type="url" name="logo_url" class="form-control" placeholder="https://exemplo.com/logo.png">
                      <div class="form-text">Deixe em branco para usar o logo padrão do sistema (public/images/logo.png).</div>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button type="submit" class="btn btn-danger">Exportar PDF</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        @endcan

        <p class="text-muted">Total: {{ $model->total() }}</p>

        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                @php $nextDir = ($dir ?? 'asc') === 'asc' ? 'desc' : 'asc'; @endphp
                <th>
                  <a href="{{ route('SafTiposPrestadores.index', ['sort' => 'nome', 'dir' => ($sort ?? 'nome') === 'nome' ? $nextDir : 'asc', 'per_page' => request('per_page', $model->perPage()), 'q' => $q ?? null]) }}">Nome
                    @if(($sort ?? 'nome') === 'nome')
                      <small>{!! ($dir ?? 'asc') === 'asc' ? '&#9650;' : '&#9660;' !!}</small>
                    @endif
                  </a>
                </th>
                <th>
                  <a href="{{ route('SafTiposPrestadores.index', ['sort' => 'funcao', 'dir' => ($sort ?? 'nome') === 'funcao' ? $nextDir : 'asc', 'per_page' => request('per_page', $model->perPage()), 'q' => $q ?? null, 'funcao_profissional_id' => $funcaoId ?? null]) }}">Função Profissional
                    @if(($sort ?? 'nome') === 'funcao')
                      <small>{!! ($dir ?? 'asc') === 'asc' ? '&#9650;' : '&#9660;' !!}</small>
                    @endif
                  </a>
                </th>
                <th>
                  <a href="{{ route('SafTiposPrestadores.index', ['sort' => 'cidade', 'dir' => ($sort ?? 'nome') === 'cidade' ? $nextDir : 'asc', 'per_page' => request('per_page', $model->perPage()), 'q' => $q ?? null]) }}">Cidade
                    @if(($sort ?? 'nome') === 'cidade')
                      <small>{!! ($dir ?? 'asc') === 'asc' ? '&#9650;' : '&#9660;' !!}</small>
                    @endif
                  </a>
                </th>
                <th>
                  <a href="{{ route('SafTiposPrestadores.index', ['sort' => 'uf', 'dir' => ($sort ?? 'nome') === 'uf' ? $nextDir : 'asc', 'per_page' => request('per_page', $model->perPage()), 'q' => $q ?? null]) }}">UF
                    @if(($sort ?? 'nome') === 'uf')
                      <small>{!! ($dir ?? 'asc') === 'asc' ? '&#9650;' : '&#9660;' !!}</small>
                    @endif
                  </a>
                </th>
                <th>
                  <a href="{{ route('SafTiposPrestadores.index', ['sort' => 'pais', 'dir' => ($sort ?? 'nome') === 'pais' ? $nextDir : 'asc', 'per_page' => request('per_page', $model->perPage()), 'q' => $q ?? null]) }}">País
                    @if(($sort ?? 'nome') === 'pais')
                      <small>{!! ($dir ?? 'asc') === 'asc' ? '&#9650;' : '&#9660;' !!}</small>
                    @endif
                  </a>
                </th>
                <th class="text-end">Ações</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($model as $item)
                <tr>
                  <td>{{ $item->nome }}</td>
                  <td>{{ optional($item->funcaoProfissional)->nome }}</td>
                  <td>{{ $item->cidade }}</td>
                  <td>{{ $item->uf }}</td>
                  <td>{{ $item->pais }}</td>
                  <td class="text-end">
                    @can('SAF_TIPOS_PRESTADORES - VER')
                    <a href="{{ route('SafTiposPrestadores.show', $item->id) }}" class="btn btn-secondary btn-sm">Ver</a>
                    @endcan
                    @can('SAF_TIPOS_PRESTADORES - EDITAR')
                    <a href="{{ route('SafTiposPrestadores.edit', $item->id) }}" class="btn btn-success btn-sm">Editar</a>
                    @endcan
                    @can('SAF_TIPOS_PRESTADORES - EXCLUIR')
                    <form action="{{ route('SafTiposPrestadores.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirma exclusão?');">
                      @csrf @method('DELETE')
                      <button class="btn btn-danger btn-sm">Excluir</button>
                    </form>
                    @endcan
                  </td>
                </tr>
              @empty
                <tr><td colspan="6" class="text-center text-muted">Nenhum registro.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between align-items-center gap-3">
          <div class="text-muted">Exibindo {{ $model->firstItem() }}–{{ $model->lastItem() }} de {{ $model->total() }}</div>
          <form method="GET" class="d-flex align-items-center gap-2">
            <input type="hidden" name="sort" value="{{ $sort ?? 'nome' }}">
            <input type="hidden" name="dir" value="{{ $dir ?? 'asc' }}">
            <input type="hidden" name="q" value="{{ $q ?? '' }}">
            <input type="hidden" name="funcao_profissional_id" value="{{ $funcaoId ?? '' }}">
            <label for="per_page" class="form-label m-0">por página</label>
            <select id="per_page" name="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
              @foreach ([10,20,50,100] as $n)
                <option value="{{ $n }}" {{ (int)request('per_page', $model->perPage()) === $n ? 'selected' : '' }}>{{ $n }}</option>
              @endforeach
            </select>
          </form>
          <div>
            {{ $model->appends(['sort' => $sort ?? null, 'dir' => $dir ?? null, 'per_page' => request('per_page', $model->perPage()), 'q' => $q ?? null, 'funcao_profissional_id' => $funcaoId ?? null])->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
