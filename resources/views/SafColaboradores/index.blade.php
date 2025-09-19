@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">SAF - Colaboradores</h5>
                <div class="d-flex gap-2">
                    @can('SAF_COLABORADORES - EXPORTAR')
                    <div class="btn-group">
                        <a href="{{ route('SafColaboradores.export', request()->query()) }}" class="btn btn-outline-success btn-sm">Exportar CSV</a>
                        <a href="{{ route('SafColaboradores.exportXlsx', request()->query()) }}" class="btn btn-outline-success btn-sm">Exportar XLSX</a>
                        <a href="{{ route('SafColaboradores.exportPdf', request()->query()) }}" class="btn btn-outline-danger btn-sm">Exportar PDF</a>
                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#exportPdfAdvancedModal">Exportar PDF (avançado)</button>
                    </div>
                    @endcan
                    @can('SAF_COLABORADORES - INCLUIR')
                    <a href="{{ route('SafColaboradores.create') }}" class="btn btn-primary btn-sm">Incluir</a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @elseif (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form method="GET" class="row g-2 align-items-end mb-3">
                    <input type="hidden" name="sort" value="{{ $sort ?? 'nome' }}">
                    <input type="hidden" name="dir" value="{{ $dir ?? 'asc' }}">
                    <input type="hidden" name="per_page" value="{{ request('per_page', $model->perPage()) }}">

                    <div class="col-md-3">
                        <label class="form-label">Buscar</label>
                        <input type="text" name="q" class="form-control" placeholder="Nome, doc, email, cidade, UF, país" value="{{ $q ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">CPF</label>
                        <input type="text" name="cpf" class="form-control cpf-mask" placeholder="000.000.000-00" value="{{ request('cpf','') }}" maxlength="14" pattern="^\d{3}\.\d{3}\.\d{3}-\d{2}$|^\d{11}$">
                        <div class="form-check mt-1">
                            <input class="form-check-input" type="checkbox" value="1" id="cpfExactCheck" name="cpf_exact" {{ request('cpf_exact') ? 'checked' : '' }}>
                            <label class="form-check-label" for="cpfExactCheck">CPF exato</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Representante</label>
                        <select name="representante_id" class="form-select">
                            <option value="">-- todos --</option>
                            @foreach($representantes as $id => $nome)
                                <option value="{{ $id }}" {{ (string)$representanteId === (string)$id ? 'selected' : '' }}>{{ $nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Função Profissional</label>
                        <select name="funcao_profissional_id" class="form-select">
                            <option value="">-- todas --</option>
                            @foreach($funcoes as $id => $nome)
                                <option value="{{ $id }}" {{ (string)$funcaoId === (string)$id ? 'selected' : '' }}>{{ $nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo de Colaborador</label>
                        <select name="saf_tipo_prestador_id" class="form-select">
                            <option value="">-- todos --</option>
                            @foreach($tipos as $id => $nome)
                                <option value="{{ $id }}" {{ (string)$tipoId === (string)$id ? 'selected' : '' }}>{{ $nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Faixa Salarial</label>
                        <select name="saf_faixa_salarial_id" class="form-select">
                            <option value="">-- todas --</option>
                            @foreach($faixas as $id => $nome)
                                <option value="{{ $id }}" {{ (string)$faixaId === (string)$id ? 'selected' : '' }}>{{ $nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary" type="submit">Buscar</button>
                    </div>
                    @if(($q ?? '') !== '' || request('cpf') || request('cpf_exact') || $representanteId || $funcaoId || $tipoId || $faixaId)
                        <div class="col-auto">
                            <a class="btn btn-outline-secondary" href="{{ route('SafColaboradores.index', ['sort' => $sort ?? 'nome', 'dir' => $dir ?? 'asc', 'per_page' => request('per_page', $model->perPage())]) }}">Limpar</a>
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
                                    <a href="{{ route('SafColaboradores.index', array_merge(request()->query(), ['sort' => 'nome', 'dir' => ($sort ?? 'nome') === 'nome' ? $nextDir : 'asc'])) }}">Nome
                                        @if(($sort ?? 'nome') === 'nome')
                                            <small>{!! ($dir ?? 'asc') === 'asc' ? '&#9650;' : '&#9660;' !!}</small>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('SafColaboradores.index', array_merge(request()->query(), ['sort' => 'representante', 'dir' => ($sort ?? 'nome') === 'representante' ? $nextDir : 'asc'])) }}">Representante
                                        @if(($sort ?? 'nome') === 'representante')
                                            <small>{!! ($dir ?? 'asc') === 'asc' ? '&#9650;' : '&#9660;' !!}</small>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('SafColaboradores.index', array_merge(request()->query(), ['sort' => 'funcao', 'dir' => ($sort ?? 'nome') === 'funcao' ? $nextDir : 'asc'])) }}">Função
                                        @if(($sort ?? 'nome') === 'funcao')
                                            <small>{!! ($dir ?? 'asc') === 'asc' ? '&#9650;' : '&#9660;' !!}</small>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('SafColaboradores.index', array_merge(request()->query(), ['sort' => 'tipo', 'dir' => ($sort ?? 'nome') === 'tipo' ? $nextDir : 'asc'])) }}">Tipo
                                        @if(($sort ?? 'nome') === 'tipo')
                                            <small>{!! ($dir ?? 'asc') === 'asc' ? '&#9650;' : '&#9660;' !!}</small>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('SafColaboradores.index', array_merge(request()->query(), ['sort' => 'faixa', 'dir' => ($sort ?? 'nome') === 'faixa' ? $nextDir : 'asc'])) }}">Faixa
                                        @if(($sort ?? 'nome') === 'faixa')
                                            <small>{!! ($dir ?? 'asc') === 'asc' ? '&#9650;' : '&#9660;' !!}</small>
                                        @endif
                                    </a>
                                </th>
                                <th>PIX</th>
                                <th>Documento</th>
                                <th>CPF</th>
                                <th>Cidade</th>
                                <th>UF</th>
                                <th>País</th>
                                <th>Ativo</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($model as $item)
                                <tr>
                                    <td>{{ $item->nome }}</td>
                                    <td>{{ optional($item->representante)->nome }}</td>
                                    <td>{{ optional($item->funcaoProfissional)->nome }}</td>
                                    <td>{{ optional($item->tipoPrestador)->nome }}</td>
                                    <td>{{ optional($item->faixaSalarial)->nome }}</td>
                                    <td>{{ optional($item->pix)->nome }}</td>
                                    <td>{{ $item->documento }}</td>
                                    <td>{{ $item->cpf }}</td>
                                    <td>{{ $item->cidade }}</td>
                                    <td>{{ $item->uf }}</td>
                                    <td>{{ $item->pais }}</td>
                                    <td>{{ $item->ativo ? 'SIM' : 'NÃO' }}</td>
                                    <td class="text-end">
                                        @can('SAF_COLABORADORES - VER')
                                        <a href="{{ route('SafColaboradores.show', $item->id) }}" class="btn btn-secondary btn-sm">Ver</a>
                                        @endcan
                                        @can('SAF_COLABORADORES - EDITAR')
                                        <a href="{{ route('SafColaboradores.edit', $item->id) }}" class="btn btn-success btn-sm">Editar</a>
                                        @endcan
                                        @can('SAF_COLABORADORES - EXCLUIR')
                                        <form action="{{ route('SafColaboradores.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirma exclusão?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm">Excluir</button>
                                        </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="13" class="text-center text-muted">Nenhum colaborador encontrado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center gap-3">
                    <div class="text-muted">
                        Exibindo {{ $model->firstItem() }}–{{ $model->lastItem() }} de {{ $model->total() }}
                    </div>
                    <form method="GET" class="d-flex align-items-center gap-2">
                        <input type="hidden" name="sort" value="{{ $sort ?? 'nome' }}">
                        <input type="hidden" name="dir" value="{{ $dir ?? 'asc' }}">
                        <input type="hidden" name="q" value="{{ $q ?? '' }}">
                        <input type="hidden" name="cpf" value="{{ request('cpf','') }}">
                        <input type="hidden" name="cpf_exact" value="{{ request('cpf_exact','') }}">
                        <input type="hidden" name="cpf" value="{{ request('cpf','') }}">
                        <input type="hidden" name="representante_id" value="{{ $representanteId ?? '' }}">
                        <input type="hidden" name="funcao_profissional_id" value="{{ $funcaoId ?? '' }}">
                        <input type="hidden" name="saf_tipo_prestador_id" value="{{ $tipoId ?? '' }}">
                        <input type="hidden" name="saf_faixa_salarial_id" value="{{ $faixaId ?? '' }}">
                        <label for="per_page" class="form-label m-0">por página</label>
                        <select id="per_page" name="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                            @foreach ([10,20,50,100] as $n)
                                <option value="{{ $n }}" {{ (int)request('per_page', $model->perPage()) === $n ? 'selected' : '' }}>{{ $n }}</option>
                            @endforeach
                        </select>
                    </form>
                    <div>
                        {{ $model->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@can('SAF_COLABORADORES - EXPORTAR')
<!-- Modal: Exportar PDF (avançado) -->
<div class="modal fade" id="exportPdfAdvancedModal" tabindex="-1" aria-labelledby="exportPdfAdvancedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportPdfAdvancedModalLabel">Exportar PDF (opções avançadas)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="GET" action="{{ route('SafColaboradores.exportPdf') }}" target="_blank">
                <div class="modal-body">
                    <!-- Preservar filtros atuais -->
                    <input type="hidden" name="q" value="{{ $q ?? '' }}">
                    <input type="hidden" name="cpf" value="{{ request('cpf','') }}">
                    <input type="hidden" name="cpf_exact" value="{{ request('cpf_exact','') }}">
                    <input type="hidden" name="representante_id" value="{{ $representanteId ?? '' }}">
                    <input type="hidden" name="funcao_profissional_id" value="{{ $funcaoId ?? '' }}">
                    <input type="hidden" name="saf_tipo_prestador_id" value="{{ $tipoId ?? '' }}">
                    <input type="hidden" name="saf_faixa_salarial_id" value="{{ $faixaId ?? '' }}">
                    <input type="hidden" name="sort" value="{{ $sort ?? 'nome' }}">
                    <input type="hidden" name="dir" value="{{ $dir ?? 'asc' }}">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Título do cabeçalho</label>
                            <input type="text" name="header_title" class="form-control" value="SAF - Colaboradores">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function maskCPF(value){
        const v = (value||'').replace(/\D/g,'').slice(0,11);
        const p1 = v.slice(0,3);
        const p2 = v.slice(3,6);
        const p3 = v.slice(6,9);
        const p4 = v.slice(9,11);
        let out = '';
        if (p1) out = p1;
        if (p2) out += (out?'.':'') + p2;
        if (p3) out += (out?'.':'') + p3;
        if (p4) out += '-' + p4;
        return out;
    }
    document.querySelectorAll('input.cpf-mask').forEach(function(el){
        el.addEventListener('input', function(){ this.value = maskCPF(this.value); });
        el.value = maskCPF(el.value);
    });
});
</script>
@endpush
