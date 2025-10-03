@extends('layouts.bootstrap5')
@section('content')
<div class="container-fluid">
  <h1 class="h4 mb-3">Variações Mensais Salvas</h1>
  <form method="get" class="row g-2 mb-3">
    <div class="col-auto">
      <label class="form-label mb-0 small">Ano</label>
      <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">Todos</option>
        @foreach($years as $y)
          <option value="{{ $y }}" @selected((string)$y === (string)$year)>{{ $y }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label mb-0 small">Código</label>
      <input type="text" name="code" value="{{ $code }}" class="form-control form-control-sm" placeholder="TSLA" />
    </div>
    <div class="col-auto">
      <label class="form-label mb-0 small">Sinal</label>
      <select name="polarity" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="" @selected(($polarity ?? '')==='')>Todos</option>
        <option value="positive" @selected(($polarity ?? '')==='positive')>Positivos</option>
        <option value="negative" @selected(($polarity ?? '')==='negative')>Negativos</option>
      </select>
    </div>
    <div class="col-auto align-self-end">
      <button class="btn btn-sm btn-primary">Filtrar</button>
    </div>
    <div class="col-auto align-self-end">
      @php
        $quickBase = array_filter([
          'year' => request('year') ?: null,
          'code' => $code ?: null,
          'sort' => ($sort ?? 'year_desc') !== 'year_desc' ? $sort : null,
        ]);
      @endphp
      <div class="btn-group btn-group-sm" role="group" aria-label="Atalhos de sinal">
        <a href="{{ route('openai.variations.index', $quickBase) }}"
           class="btn btn-outline-secondary {{ (($polarity ?? '')==='' ) ? 'active' : '' }}"
           title="Mostrar todos (positivos e negativos)">Todos</a>
        <a href="{{ route('openai.variations.index', array_merge($quickBase, ['polarity'=>'positive'])) }}"
           class="btn btn-outline-success {{ (($polarity ?? '')==='positive') ? 'active' : '' }}"
           title="Mostrar apenas variações positivas">Somente positivos</a>
        <a href="{{ route('openai.variations.index', array_merge($quickBase, ['polarity'=>'negative'])) }}"
           class="btn btn-outline-danger {{ (($polarity ?? '')==='negative') ? 'active' : '' }}"
           title="Mostrar apenas variações negativas">Somente negativos</a>
      </div>
    </div>
  </form>

  @php
    $exportParams = array_filter([
      'year'=>request('year')?:null,
      'code'=>$code?:null,
      'polarity'=> ($polarity ?? null) ?: null,
      'sort' => ($sort ?? 'year_desc') !== 'year_desc' ? $sort : null,
    ]);
  @endphp
  <div class="mb-2 d-flex gap-2">
    <a href="{{ route('openai.variations.exportCsv', $exportParams) }}" class="btn btn-sm btn-outline-secondary" title="Exportar visão atual em CSV">Exportar CSV</a>
    <a href="{{ route('openai.variations.exportXlsx', $exportParams) }}" class="btn btn-sm btn-outline-success" title="Exportar visão atual em XLSX">Exportar XLSX</a>
  </div>

  <div class="table-responsive">
    <table class="table table-sm table-striped align-middle">
      <thead>
        <tr>
          <th>ID</th>
          @php
            // Parâmetros base preservados para os links de ordenação
            $baseParams = array_filter([
              'year'=>request('year')?:null,
              'code'=>$code?:null,
              'polarity'=> ($polarity ?? null) ?: null,
            ]);
          @endphp
          @php
            $isCodeAsc = ($sort ?? '') === 'code_asc';
            $isCodeDesc = ($sort ?? '') === 'code_desc';
            $codeNext = $isCodeAsc ? 'code_desc' : ($isCodeDesc ? 'year_desc' : 'code_asc');
            $codeIcon = $isCodeAsc ? '↑' : ($isCodeDesc ? '↓' : '↕');
          @endphp
          <th>
            <a class="text-decoration-none" href="{{ route('openai.variations.index', array_merge($baseParams, ['sort'=>$codeNext])) }}" title="Ordenar / alternar ordenação por código">
              Código {{ $codeIcon }}
            </a>
          </th>
          @php
            $isYearAsc = ($sort ?? '') === 'year_asc';
            $isYearDesc = ($sort ?? '') === 'year_desc';
            $yearNext = $isYearAsc ? 'year_desc' : ($isYearDesc ? 'month_desc' : 'year_asc');
            // Nota: ciclo diferente pode confundir; manter padrão 3 estados como demais: asc->desc->padrão(year_desc). Mas year_desc é também o padrão, então: asc->desc->asc? Melhor replicar padrão: (none)->asc->desc->none. Como default já é year_desc, faremos: if default e user clica: year_asc.
            // Ajuste: se default (year_desc) e não explicitamente setado, mostrar ícone ↕ sem link extra.
            $yearIcon = $isYearAsc ? '↑' : ($isYearDesc ? '↓' : '↕');
            if($isYearDesc && request('sort') !== 'year_desc'){ /* year_desc vindo explicitamente */ }
            $yearNext = $isYearAsc ? 'year_desc' : ($isYearDesc ? 'year_asc' : 'year_asc');

            $isMonthAsc = ($sort ?? '') === 'month_asc';
            $isMonthDesc = ($sort ?? '') === 'month_desc';
            $monthNext = $isMonthAsc ? 'month_desc' : ($isMonthDesc ? 'year_desc' : 'month_asc');
            $monthIcon = $isMonthAsc ? '↑' : ($isMonthDesc ? '↓' : '↕');
          @endphp
          <th>
            <a class="text-decoration-none" href="{{ route('openai.variations.index', array_merge($baseParams, ['sort'=>$yearNext])) }}" title="Ordenar / alternar por ano">
              Ano {{ $yearIcon }}
            </a>
          </th>
          <th>
            <a class="text-decoration-none" href="{{ route('openai.variations.index', array_merge($baseParams, ['sort'=>$monthNext])) }}" title="Ordenar / alternar por mês">
              Mês {{ $monthIcon }}
            </a>
          </th>
          @php
            $isVarAsc = ($sort ?? '') === 'variation_asc';
            $isVarDesc = ($sort ?? '') === 'variation_desc';
            $nextSort = $isVarAsc ? 'variation_desc' : ($isVarDesc ? 'year_desc' : 'variation_asc');
            $icon = $isVarAsc ? '↑' : ($isVarDesc ? '↓' : '↕');
          @endphp
          <th>
            <a class="text-decoration-none" href="{{ route('openai.variations.index', array_merge($baseParams, ['sort'=>$nextSort])) }}" title="Ordenar / alternar ordenação pela variação">
              Variação (%) {{ $icon }}
            </a>
          </th>
          @php
            $isCreatedAsc = ($sort ?? '') === 'created_asc';
            $isCreatedDesc = ($sort ?? '') === 'created_desc';
            $createdNext = $isCreatedAsc ? 'created_desc' : ($isCreatedDesc ? 'year_desc' : 'created_asc');
            $createdIcon = $isCreatedAsc ? '↑' : ($isCreatedDesc ? '↓' : '↕');
            $isUpdatedAsc = ($sort ?? '') === 'updated_asc';
            $isUpdatedDesc = ($sort ?? '') === 'updated_desc';
            $updatedNext = $isUpdatedAsc ? 'updated_desc' : ($isUpdatedDesc ? 'year_desc' : 'updated_asc');
            $updatedIcon = $isUpdatedAsc ? '↑' : ($isUpdatedDesc ? '↓' : '↕');
          @endphp
          <th>
            <a class="text-decoration-none" href="{{ route('openai.variations.index', array_merge($baseParams, ['sort'=>$createdNext])) }}" title="Ordenar / alternar por data de criação">
              Criado {{ $createdIcon }}
            </a>
          </th>
          <th>
            <a class="text-decoration-none" href="{{ route('openai.variations.index', array_merge($baseParams, ['sort'=>$updatedNext])) }}" title="Ordenar / alternar por data de atualização">
              Atualizado {{ $updatedIcon }}
            </a>
          </th>
        </tr>
      </thead>
      <tbody>
        @forelse($variations as $v)
          <tr>
            <td>{{ $v->id }}</td>
            <td>{{ $v->asset_code }}</td>
            <td>{{ $v->year }}</td>
            <td>{{ str_pad($v->month,2,'0',STR_PAD_LEFT) }}</td>
            <td>{{ number_format($v->variation, 4, ',', '.') }}</td>
            <td>{{ $v->created_at?->format('Y-m-d H:i') }}</td>
            <td>{{ $v->updated_at?->format('Y-m-d H:i') }}</td>
          </tr>
        @empty
          <tr><td colspan="7" class="text-center text-muted">Nenhuma variação encontrada.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div>
    {{ $variations->links() }}
  </div>
</div>
@endsection
