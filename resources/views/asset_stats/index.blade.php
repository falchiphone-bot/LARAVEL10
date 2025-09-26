@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h5 mb-0">Estatísticas Diárias de Ativos</h1>
    <div class="d-flex gap-2">
      <a href="{{ route('asset-stats.create') }}" class="btn btn-outline-primary">Novo</a>
      <a href="{{ route('asset-stats.importForm') }}" class="btn btn-outline-secondary">Importar Tabela/CSV</a>
    </div>
  </div>

  <form method="GET" class="mb-3" id="asset-stats-filter-form">
    <div class="row g-2 align-items-end">
      <div class="col-auto">
        <label class="form-label">Símbolo</label>
        <input type="text" class="form-control" name="symbol" value="{{ $symbol }}" placeholder="Ex: OKLO">
      </div>
      <div class="col-auto">
        <label class="form-label">De</label>
        <input type="date" class="form-control" name="date_start" value="{{ $dateStart ?? '' }}">
      </div>
      <div class="col-auto">
        <label class="form-label">Até</label>
        <input type="date" class="form-control" name="date_end" value="{{ $dateEnd ?? '' }}">
      </div>
      <div class="col-auto form-check mt-4">
        <input class="form-check-input" type="checkbox" value="1" id="hasClose" name="has_close" {{ !empty($hasClose) ? 'checked' : '' }}>
        <label class="form-check-label" for="hasClose">Somente com Fechado</label>
      </div>
      <div class="col-auto">
        <label class="form-label">Acurácia</label>
        <select name="acc" class="form-select">
          <option value="" {{ ($acc ?? '')==='' ? 'selected' : '' }}>Todos</option>
          <option value="ok" {{ ($acc ?? '')==='ok' ? 'selected' : '' }}>OK</option>
          <option value="out" {{ ($acc ?? '')==='out' ? 'selected' : '' }}>Fora</option>
          <option value="na" {{ ($acc ?? '')==='na' ? 'selected' : '' }}>Indeterminado</option>
        </select>
      </div>
      <div class="col-auto">
        <button class="btn btn-primary">Filtrar</button>
      </div>
    </div>
  </form>

  <div class="mb-2">
    @php
      $hasChips = ($symbol ?? '') !== '' || ($dateStart ?? '') !== '' || ($dateEnd ?? '') !== '' || ($acc ?? '') !== '' || !empty($hasClose);
      $accLabel = [
        'ok' => 'Acurácia: OK',
        'out' => 'Acurácia: Fora',
        'na' => 'Acurácia: Indef.'
      ][$acc ?? ''] ?? null;
      $fmtDate = function($d){ try { return \Carbon\Carbon::parse($d)->format('d/m/Y'); } catch (\Throwable $e) { return $d; } };
    @endphp
    @if($hasChips)
      <span class="text-muted small me-2">Filtros ativos:</span>
      @if(($symbol ?? '') !== '')
        <a class="badge rounded-pill bg-primary text-decoration-none me-1"
           title="Remover filtro Símbolo"
           href="{{ route('asset-stats.index', array_filter(['symbol'=>null,'date_start'=>$dateStart ?? null,'date_end'=>$dateEnd ?? null,'acc'=>$acc ?? null,'has_close'=>!empty($hasClose)?1:null,'sort'=>$sort ?? null,'dir'=>$dir ?? null])) }}">
          Símbolo: {{ $symbol }} <i class="fa-solid fa-xmark ms-1"></i>
        </a>
      @endif
      @if(($dateStart ?? '') !== '' && ($dateEnd ?? '') !== '')
        <a class="badge rounded-pill bg-secondary text-decoration-none me-1"
           title="Remover filtro Período"
           href="{{ route('asset-stats.index', array_filter(['symbol'=>$symbol ?? null,'date_start'=>null,'date_end'=>null,'acc'=>$acc ?? null,'has_close'=>!empty($hasClose)?1:null,'sort'=>$sort ?? null,'dir'=>$dir ?? null])) }}">
          Período: {{ $fmtDate($dateStart) }} – {{ $fmtDate($dateEnd) }} <i class="fa-solid fa-xmark ms-1"></i>
        </a>
      @elseif(($dateStart ?? '') !== '')
        <a class="badge rounded-pill bg-secondary text-decoration-none me-1"
           title="Remover filtro De"
           href="{{ route('asset-stats.index', array_filter(['symbol'=>$symbol ?? null,'date_start'=>null,'date_end'=>$dateEnd ?? null,'acc'=>$acc ?? null,'has_close'=>!empty($hasClose)?1:null,'sort'=>$sort ?? null,'dir'=>$dir ?? null])) }}">
          De: {{ $fmtDate($dateStart) }} <i class="fa-solid fa-xmark ms-1"></i>
        </a>
      @elseif(($dateEnd ?? '') !== '')
        <a class="badge rounded-pill bg-secondary text-decoration-none me-1"
           title="Remover filtro Até"
           href="{{ route('asset-stats.index', array_filter(['symbol'=>$symbol ?? null,'date_start'=>$dateStart ?? null,'date_end'=>null,'acc'=>$acc ?? null,'has_close'=>!empty($hasClose)?1:null,'sort'=>$sort ?? null,'dir'=>$dir ?? null])) }}">
          Até: {{ $fmtDate($dateEnd) }} <i class="fa-solid fa-xmark ms-1"></i>
        </a>
      @endif
      @if($accLabel)
        <a class="badge rounded-pill bg-info text-dark text-decoration-none me-1"
           title="Remover filtro Acurácia"
           href="{{ route('asset-stats.index', array_filter(['symbol'=>$symbol ?? null,'date_start'=>$dateStart ?? null,'date_end'=>$dateEnd ?? null,'acc'=>null,'has_close'=>!empty($hasClose)?1:null,'sort'=>$sort ?? null,'dir'=>$dir ?? null])) }}">
          {{ $accLabel }} <i class="fa-solid fa-xmark ms-1"></i>
        </a>
      @endif
      @if(!empty($hasClose))
        <a class="badge rounded-pill bg-warning text-dark text-decoration-none me-1"
           title="Remover filtro Somente com Fechado"
           href="{{ route('asset-stats.index', array_filter(['symbol'=>$symbol ?? null,'date_start'=>$dateStart ?? null,'date_end'=>$dateEnd ?? null,'acc'=>$acc ?? null,'has_close'=>null,'sort'=>$sort ?? null,'dir'=>$dir ?? null])) }}">
          Somente com Fechado <i class="fa-solid fa-xmark ms-1"></i>
        </a>
      @endif
      <a class="badge rounded-pill bg-light text-dark border text-decoration-none me-1"
         title="Limpar todos os filtros"
         href="{{ route('asset-stats.index') }}">
        Limpar tudo <i class="fa-solid fa-xmark ms-1"></i>
      </a>
    @endif
  </div>

  <div class="mb-3">
    <form method="POST" action="{{ route('asset-stats.recomputeAccuracy') }}" class="d-inline">
      @csrf
      <input type="hidden" name="symbol" value="{{ $symbol }}">
      <input type="hidden" name="date_start" value="{{ $dateStart ?? '' }}">
      <input type="hidden" name="date_end" value="{{ $dateEnd ?? '' }}">
      <input type="hidden" name="acc" value="{{ $acc ?? '' }}">
      <input type="hidden" name="has_close" value="{{ !empty($hasClose) ? 1 : '' }}">
      <button class="btn btn-outline-secondary" title="Recalcula a acurácia para os registros filtrados">Recalcular Acurácia</button>
    </form>
    <form method="POST" action="{{ route('asset-stats.fillCloseFromRecordsBulk') }}" class="d-inline ms-2" title="Preenche Fechado (em branco) a partir dos Registros de Conversas para o filtro atual">
      @csrf
      <input type="hidden" name="symbol" value="{{ $symbol }}">
      <input type="hidden" name="date_start" value="{{ $dateStart ?? '' }}">
      <input type="hidden" name="date_end" value="{{ $dateEnd ?? '' }}">
      <input type="hidden" name="acc" value="{{ $acc ?? '' }}">
      <input type="hidden" name="has_close" value="{{ !empty($hasClose) ? 1 : '' }}">
      <button class="btn btn-outline-primary">Preencher Fechado (Filtro)</button>
    </form>
  </div>

  @if(session('success'))
    <div class="alert alert-success py-2">{{ session('success') }}</div>
  @endif

  <div class="mb-2 text-muted small">
    Filtrados: <span class="badge bg-secondary">{{ $totalFiltered ?? 0 }}</span>
    &nbsp;|&nbsp; Com Fechado: <span class="badge bg-info text-dark">{{ $withCloseFiltered ?? 0 }}</span>
  </div>

  <div class="table-responsive">
    <table class="table table-sm table-bordered align-middle">
      <thead class="table-dark">
        <tr>
          <th>
            @php
              $isDate = ($sort ?? 'date') === 'date';
              $currentDir = $dir ?? 'asc';
              $nextDir = ($isDate && $currentDir === 'asc') ? 'desc' : 'asc';
            @endphp
            <a class="link-light text-decoration-none" href="{{ route('asset-stats.index', array_filter(['symbol'=>$symbol,'date_start'=>$dateStart ?? null,'date_end'=>$dateEnd ?? null,'has_close'=>!empty($hasClose)?1:null, 'sort'=>'date','dir'=>$isDate ? $nextDir : 'asc'])) }}">
              Data
              @if($isDate)
                {!! ($currentDir) === 'asc' ? '&#9650;' : '&#9660;' !!}
              @endif
            </a>
          </th>
          <th>
            @php
              $isSym = ($sort ?? 'date') === 'symbol';
              $currentDirS = $dir ?? 'asc';
              $nextDirS = ($isSym && $currentDirS === 'asc') ? 'desc' : 'asc';
            @endphp
            <a class="link-light text-decoration-none" href="{{ route('asset-stats.index', array_filter(['symbol'=>$symbol,'date_start'=>$dateStart ?? null,'date_end'=>$dateEnd ?? null,'has_close'=>!empty($hasClose)?1:null, 'sort'=>'symbol','dir'=>$isSym ? $nextDirS : 'asc'])) }}">
              Símbolo
              @if($isSym)
                {!! ($currentDirS) === 'asc' ? '&#9650;' : '&#9660;' !!}
              @endif
            </a>
          </th>
          <th class="text-end">Média</th>
          <th class="text-end">Mediana</th>
          <th class="text-end">
            P5
            <i class="fa-regular fa-circle-question ms-1 text-light"
               data-bs-toggle="tooltip" data-bs-placement="top"
               data-bs-title="5º percentil: 5% dos valores ficam abaixo deste preço (limite inferior)."></i>
          </th>
          <th class="text-end">
            P95
            <i class="fa-regular fa-circle-question ms-1 text-light"
               data-bs-toggle="tooltip" data-bs-placement="top"
               data-bs-title="95º percentil: 95% dos valores ficam abaixo deste preço (limite superior)."></i>
          </th>
          <th class="text-end" title="Preço de fechamento do ativo, se já existir na base.">Fechado</th>
          <th class="text-center" title="Fechamento dentro do intervalo P5-P95?">
            @php
              $isAccSort = ($sort ?? 'date') === 'acc';
              $currentDirA = $dir ?? 'asc';
              $nextDirA = ($isAccSort && $currentDirA === 'asc') ? 'desc' : 'asc';
            @endphp
            <a class="link-light text-decoration-none" href="{{ route('asset-stats.index', array_filter(['symbol'=>$symbol,'date_start'=>$dateStart ?? null,'date_end'=>$dateEnd ?? null,'acc'=>$acc ?? null,'has_close'=>!empty($hasClose)?1:null,'sort'=>'acc','dir'=>$isAccSort ? $nextDirA : 'asc'])) }}">
              Acurácia
              @if($isAccSort)
                {!! ($currentDirA) === 'asc' ? '&#9650;' : '&#9660;' !!}
              @endif
            </a>
          </th>
          <th class="text-center">Ações</th>
        </tr>
      </thead>
      <tbody>
        @forelse($stats as $s)
          <tr>
            <td>
              {{ optional($s->date)->format('d/m/Y') }}
              @php
                $dCell = $s->date;
                $isUpdatable = $dCell && $dCell->copy()->timezone('UTC')->format('Y-m-d') >= \Carbon\Carbon::now('UTC')->format('Y-m-d');
              @endphp
              @if($isUpdatable)
                <span class="ms-1" title="Atualizável (hoje ou futuro)">⏳</span>
              @endif
            </td>
            <td>{{ $s->symbol }}</td>
            <td class="text-end">{{ number_format((float)$s->mean, 6, ',', '.') }}</td>
            <td class="text-end">{{ number_format((float)$s->median, 6, ',', '.') }}</td>
            <td class="text-end">{{ number_format((float)$s->p5, 6, ',', '.') }}</td>
            <td class="text-end">{{ number_format((float)$s->p95, 6, ',', '.') }}</td>
            <td class="text-end">{{ isset($s->close_value) ? number_format((float)$s->close_value, 6, ',', '.') : '' }}</td>
            <td class="text-center">
              @if($s->is_accurate === null)
                <span class="text-muted">—</span>
              @elseif($s->is_accurate)
                <span class="text-success" title="Dentro do intervalo"><i class="fa-solid fa-check"></i></span>
              @else
                <span class="text-danger" title="Fora do intervalo"><i class="fa-solid fa-xmark"></i></span>
              @endif
            </td>
            <td class="text-center">
              <a href="{{ route('asset-stats.edit', $s) }}" class="btn btn-sm btn-outline-primary">Editar</a>
              @php
                $d = $s->date;
                $todayIso = \Carbon\Carbon::now('UTC')->format('Y-m-d');
                $canRefresh = $d && $d->copy()->timezone('UTC')->format('Y-m-d') >= $todayIso;
              @endphp
              @if($canRefresh)
                <form action="{{ route('asset-stats.refreshClose', $s) }}" method="POST" class="d-inline">
                  @csrf
                  <button class="btn btn-sm btn-outline-success" title="Consultar e atualizar o preço de fechamento">Atualizar Fechado</button>
                </form>
              @endif
              <form action="{{ route('asset-stats.recomputeAccuracyOne', $s) }}" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-sm btn-outline-secondary" title="Recalcular acurácia deste registro">Rec. Acurácia</button>
              </form>
              @if(is_null($s->close_value))
                <form action="{{ route('asset-stats.fillCloseFromRecords', $s) }}" method="POST" class="d-inline">
                  @csrf
                  <button class="btn btn-sm btn-outline-info" title="Preencher Fechado a partir do registro do dia (OpenAI Records)">Preencher do Registro</button>
                </form>
              @endif
              <form action="{{ route('asset-stats.destroy', $s) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir registro?');">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">Excluir</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="text-center text-muted">Nenhum registro.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{ $stats->links() }}
</div>
@endsection
