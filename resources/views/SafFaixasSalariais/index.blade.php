@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
    @if (session('success'))
        <div class="alert alert-success">{!! session('success') !!}</div>
    @endif
    @if (session('warning'))
        <div class="alert alert-warning">{!! session('warning') !!}</div>
    @endif
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>SAF - Faixas Salariais</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('SafFaixasSalariais.index') }}" class="btn btn-outline-secondary">Limpar</a>
            <a href="{{ route('SafFaixasSalariais.export', array_merge(request()->query(), ['fmt'=>'csv'])) }}" class="btn btn-outline-success">Exportar CSV</a>
            <a href="{{ route('SafFaixasSalariais.export', array_merge(request()->query(), ['fmt'=>'xlsx'])) }}" class="btn btn-outline-success">Exportar Excel</a>
            @can('SAF_FAIXASSALARIAIS - INCLUIR')
            <a href="{{ route('SafFaixasSalariais.create') }}" class="btn btn-primary">Nova Faixa</a>
            @endcan
        </div>
    </div>

    <form method="get" class="card card-body mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Buscar</label>
                <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Nome/observações">
            </div>
            <div class="col-md-3">
                <label class="form-label">Função Profissional</label>
                <select name="funcao_profissional_id" class="form-select">
                    <option value="">-- Todas --</option>
                    @foreach($funcoes as $id=>$nome)
                        <option value="{{ $id }}" @selected($id==($funcaoId??null))>{{ $nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipo de Prestador</label>
                <select name="saf_tipo_prestador_id" class="form-select">
                    <option value="">-- Todos --</option>
                    @foreach($tipos as $id=>$nome)
                        <option value="{{ $id }}" @selected($id==($tipoPrestadorId??null))>{{ $nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Senioridade</label>
                <input type="text" name="senioridade" value="{{ $senioridade }}" class="form-control" placeholder="JUNIOR/PLENO/SENIOR">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipo Contrato</label>
                <select name="tipo_contrato" class="form-select">
                    <option value="">-- Todos --</option>
                    @foreach(['CLT','PJ','ESTAGIO'] as $opt)
                        <option value="{{ $opt }}" @selected($opt==($tipoContrato??null))>{{ $opt }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Moeda</label>
                <input type="text" name="moeda" value="{{ $moeda }}" class="form-control" placeholder="BRL">
            </div>
            <div class="col-md-2">
                <label class="form-label">Somente vigentes</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="somente_vigentes" value="1" @checked($vigentes)>
                    <label class="form-check-label">Ativar filtro</label>
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label">Data de corte</label>
                <input type="date" name="data_corte" value="{{ request('data_corte') ?? ($vigentes ? now()->format('Y-m-d') : '') }}" class="form-control" @if(!$vigentes) disabled @endif>
            </div>
            <div class="col-md-2">
                <label class="form-label">Ordenar por</label>
                <select name="sort" class="form-select">
                    @foreach(['vigencia_inicio'=>'Vigência início','vigencia_fim'=>'Vigência fim','nome'=>'Nome','valor_minimo'=>'Valor mín.','valor_maximo'=>'Valor máx.','funcao'=>'Função'] as $k=>$v)
                        <option value="{{ $k }}" @selected($k==$sort)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">Dir</label>
                <select name="dir" class="form-select">
                    @foreach(['asc'=>'Asc','desc'=>'Desc'] as $k=>$v)
                        <option value="{{ $k }}" @selected($k==$dir)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Por página</label>
                <input type="number" min="5" max="100" name="per_page" value="{{ $perPage }}" class="form-control">
            </div>
            <div class="col-md-2">
                <button class="btn btn-secondary w-100">Filtrar</button>
            </div>
        </div>
    </form>

    @can('SAF_FAIXASSALARIAIS - INCLUIR')
    <form method="post" action="{{ route('SafFaixasSalariais.import') }}" class="card card-body mb-3" enctype="multipart/form-data">
        @csrf
        <div class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Importar CSV/Excel</label>
                <input type="file" name="arquivo" class="form-control" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" required>
            </div>
            <div class="col-md-3">
                <button class="btn btn-outline-primary w-100">Enviar</button>
            </div>
            <div class="col-md-3 d-grid gap-2">
                <a class="btn btn-outline-secondary" href="{{ route('SafFaixasSalariais.importTemplate', ['fmt'=>'xlsx']) }}">Baixar modelo (Excel)</a>
                <a class="btn btn-outline-secondary" href="{{ route('SafFaixasSalariais.importTemplate', ['fmt'=>'csv']) }}">Baixar modelo (CSV)</a>
            </div>
        </div>
    </form>
    @endcan

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Função</th>
                    <th>Tipo Prestador</th>
                    <th>Senioridade</th>
                    <th>Contrato</th>
                    <th>Per.</th>
                    <th class="text-end">Mín.</th>
                    <th class="text-end">Máx.</th>
                    <th>Moeda</th>
                    <th>Vigência</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($model as $row)
                    <tr>
                        <td>{{ $row->nome }}</td>
                        <td>{{ optional($row->funcaoProfissional)->nome }}</td>
                        <td>{{ optional($row->tipoPrestador)->nome }}</td>
                        <td>{{ $row->senioridade }}</td>
                        <td>{{ $row->tipo_contrato }}</td>
                        <td>{{ $row->periodicidade }}</td>
                        <td class="text-end">{{ $row->moeda==='BRL' ? 'R$ ' : '' }}{{ number_format($row->valor_minimo, 2, ',', '.') }}</td>
                        <td class="text-end">{{ $row->moeda==='BRL' ? 'R$ ' : '' }}{{ number_format($row->valor_maximo, 2, ',', '.') }}</td>
                        <td>{{ $row->moeda }}</td>
                        <td>
                            {{ \Illuminate\Support\Carbon::parse($row->vigencia_inicio)->format('d/m/Y') }}
                            -
                            {{ $row->vigencia_fim ? \Illuminate\Support\Carbon::parse($row->vigencia_fim)->format('d/m/Y') : 'aberta' }}
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                @can('SAF_FAIXASSALARIAIS - VER')
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('SafFaixasSalariais.show',$row->id) }}">Ver</a>
                                @endcan
                                @can('SAF_FAIXASSALARIAIS - EDITAR')
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('SafFaixasSalariais.edit',$row->id) }}">Editar</a>
                                @endcan
                                @can('SAF_FAIXASSALARIAIS - INCLUIR')
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('SafFaixasSalariais.duplicate',$row->id) }}">Duplicar</a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="text-center">Nenhum registro encontrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $model->appends(request()->query())->links() }}
    </div>
</div>
@endsection
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function(){
        const chk = document.querySelector('input[name="somente_vigentes"]');
        const dt = document.querySelector('input[name="data_corte"]');
        function sync(){ if (!chk || !dt) return; dt.disabled = !chk.checked; if (chk.checked && !dt.value) { dt.value = new Date().toISOString().slice(0,10);} }
        if (chk && dt) { chk.addEventListener('change', sync); }
    });
</script>
@endpush
