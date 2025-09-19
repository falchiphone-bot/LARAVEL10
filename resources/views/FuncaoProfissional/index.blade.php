@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Função Profissional</h5>
                @can('FUNCAOPROFISSIONAL - INCLUIR')
                <a href="{{ route('FuncaoProfissional.create') }}" class="btn btn-primary btn-sm">Incluir</a>
                @endcan
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @elseif (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form method="GET" class="row g-2 align-items-center mb-3" action="{{ route('FuncaoProfissional.index') }}">
                    <input type="hidden" name="sort" value="{{ $sort ?? 'nome' }}">
                    <input type="hidden" name="dir" value="{{ $dir ?? 'asc' }}">
                    <input type="hidden" name="per_page" value="{{ request('per_page', $perPage ?? $model->perPage()) }}">
                    <div class="col-md-6">
                        <input type="text" name="q" class="form-control" placeholder="Buscar por nome" value="{{ $q ?? '' }}">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary" type="submit">Buscar</button>
                    </div>
                    <div class="col-auto">
                        <a class="btn btn-outline-secondary" href="{{ route('FuncaoProfissional.index', array_merge(request()->except(['page']), ['clear'=>1])) }}">Limpar</a>
                    </div>
                    <div class="col-auto form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember" {{ request('remember', old('remember', session()->has('funcaoprofissional.index.filters') ? '1' : '')) ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">Lembrar filtros</label>
                    </div>
                    @can('FUNCAOPROFISSIONAL - EXPORTAR')
                    <div class="col-12 mt-2 d-flex gap-2 flex-wrap">
                        <a href="{{ route('FuncaoProfissional.export', request()->all()) }}" class="btn btn-outline-success">Exportar CSV</a>
                        <a href="{{ route('FuncaoProfissional.exportXlsx', request()->all()) }}" class="btn btn-outline-success">Exportar XLSX</a>
                        <a href="{{ route('FuncaoProfissional.exportPdf', request()->all()) }}" class="btn btn-outline-danger">Exportar PDF</a>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#exportPdfAdvancedModal">Exportar PDF (avançado)</button>
                    </div>
                    @endcan
                    @if(($q ?? '') !== '')
                        <div class="col-auto">
                            <a class="btn btn-outline-secondary" href="{{ route('FuncaoProfissional.index', ['sort' => $sort ?? 'nome', 'dir' => $dir ?? 'asc', 'per_page' => request('per_page', $model->perPage())]) }}">Limpar</a>
                        </div>
                    @endif
                </form>

                <p class="text-muted">Total: {{ $model->total() }}</p>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                @php $nextDir = ($dir ?? 'asc') === 'asc' ? 'desc' : 'asc'; @endphp
                                <th>
                                    <a href="{{ route('FuncaoProfissional.index', ['sort' => 'nome', 'dir' => ($sort ?? 'nome') === 'nome' ? $nextDir : 'asc', 'per_page' => request('per_page', $model->perPage()), 'q' => $q ?? null]) }}">Nome
                                        @if(($sort ?? 'nome') === 'nome')
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
                                    <td class="text-end">
                                        @can('FUNCAOPROFISSIONAL - VER')
                                        <a href="{{ route('FuncaoProfissional.show', $item->id) }}" class="btn btn-secondary btn-sm">Ver</a>
                                        @endcan
                                        @can('FUNCAOPROFISSIONAL - EDITAR')
                                        <a href="{{ route('FuncaoProfissional.edit', $item->id) }}" class="btn btn-success btn-sm">Editar</a>
                                        @endcan
                                        @can('FUNCAOPROFISSIONAL - EXCLUIR')
                                        <form action="{{ route('FuncaoProfissional.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirma exclusão?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-danger btn-sm">Excluir</button>
                                        </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted">Nenhum registro.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center gap-3">
                    <div class="text-muted">Exibindo {{ $model->firstItem() }}–{{ $model->lastItem() }} de {{ $model->total() }}</div>
                    <form method="GET" class="d-flex align-items-center gap-2" action="{{ route('FuncaoProfissional.index') }}">
                        <input type="hidden" name="sort" value="{{ $sort ?? 'nome' }}">
                        <input type="hidden" name="dir" value="{{ $dir ?? 'asc' }}">
                        <input type="hidden" name="q" value="{{ $q ?? '' }}">
                        <label for="per_page" class="form-label m-0">por página</label>
                        <select id="per_page" name="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                            @foreach ([10,20,50,100] as $n)
                                <option value="{{ $n }}" {{ (int)request('per_page', $perPage ?? $model->perPage()) === $n ? 'selected' : '' }}>{{ $n }}</option>
                            @endforeach
                        </select>
                    </form>
                    <div>
                        {{ $model->appends(['sort' => $sort ?? null, 'dir' => $dir ?? null, 'per_page' => request('per_page', $model->perPage()), 'q' => $q ?? null])->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
                @can('FUNCAOPROFISSIONAL - EXPORTAR')
                <!-- Modal PDF avançado -->
                <div class="modal fade" id="exportPdfAdvancedModal" tabindex="-1" aria-labelledby="exportPdfAdvancedModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exportPdfAdvancedModalLabel">Exportar PDF (opções avançadas)</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form method="GET" action="{{ route('FuncaoProfissional.exportPdf') }}" target="_blank">
                                <div class="modal-body">
                                    <input type="hidden" name="q" value="{{ $q ?? '' }}">
                                    <input type="hidden" name="sort" value="{{ $sort ?? 'nome' }}">
                                    <input type="hidden" name="dir" value="{{ $dir ?? 'asc' }}">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Título do cabeçalho</label>
                                            <input type="text" name="header_title" class="form-control" value="Função Profissional">
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
                                            <div class="form-text">Deixe em branco para usar o logo padrão (public/images/logo.png).</div>
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
@endsection
