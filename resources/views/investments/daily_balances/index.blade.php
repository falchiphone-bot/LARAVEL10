@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h5 mb-0">Evolução do Saldo (Snapshots)
      @if(isset($baseMode))
        <span class="badge bg-info ms-2" title="Base acumulada">base: {{ $baseMode==='recent'?'mais recente':'mais antigo' }}</span>
      @endif
      @if(!empty($sparkSeries))
        <span class="ms-2 align-middle" style="display:inline-block;width:120px;height:28px">
          <canvas id="sparklineBalance" width="120" height="28"></canvas>
        </span>
      @endif
    </h1>
    <div class="d-flex gap-2">
        <a href="{{ route('openai.investments.index') }}" class="btn btn-outline-secondary">← Investimentos</a>
        @can('INVESTIMENTOS SNAPSHOTS - CRIAR')
        <form method="POST" action="{{ route('investments.daily-balances.store') }}" id="new-snapshot-form">
          @csrf
          <button class="btn btn-outline-primary" title="Gerar novo snapshot agora">Novo Snapshot</button>
        </form>
        @endcan
        @can('INVESTIMENTOS SNAPSHOTS - EXPORTAR')
        <a href="{{ route('investments.daily-balances.exportCsv', array_filter([
          'with_deleted'=> request('with_deleted'),
          'latest_per_day'=> request('latest_per_day'),
          'base_mode'=> request('base_mode'),
          'compact'=> request('compact'),
          'spark'=> request('spark'),
          'from'=> request('from'),
          'to'=> request('to'),
        ])) }}" class="btn btn-outline-secondary" title="Exportar CSV">Exportar CSV</a>
        @endcan
        <form method="GET" action="{{ route('investments.daily-balances.index') }}" class="d-flex align-items-center ms-3 gap-3 flex-wrap">
          <div class="d-flex align-items-end gap-2 flex-wrap">
            <div>
              <label class="form-label form-label-sm small mb-0">De</label>
              <input type="date" name="from" value="{{ $from ?? '' }}" class="form-control form-control-sm" onchange="this.form.submit()">
            </div>
            <div>
              <label class="form-label form-label-sm small mb-0">Até</label>
              <input type="date" name="to" value="{{ $to ?? '' }}" class="form-control form-control-sm" onchange="this.form.submit()">
            </div>
            <div class="d-flex gap-1 align-items-end">
              <button name="range" value="7d" class="btn btn-sm btn-outline-secondary" @disabled(($range??'')==='7d')>7d</button>
              <button name="range" value="30d" class="btn btn-sm btn-outline-secondary" @disabled(($range??'')==='30d')>30d</button>
              <button name="range" value="ytd" class="btn btn-sm btn-outline-secondary" @disabled(($range??'')==='ytd')>YTD</button>
              <a href="{{ route('investments.daily-balances.index', array_filter([
                'with_deleted'=> request('with_deleted'),
                'latest_per_day'=> request('latest_per_day'),
                'base_mode'=> request('base_mode'),
                'compact'=> request('compact'),
                'spark'=> request('spark'),
              ])) }}" class="btn btn-sm btn-outline-danger" title="Limpar período">Limpar</a>
            </div>
          </div>
          <div class="form-check form-switch m-0">
            <input class="form-check-input" type="checkbox" name="with_deleted" value="1" id="withDeletedSwitch" onchange="this.form.submit()" {{ ($withDeleted ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="withDeletedSwitch" title="Mostrar também snapshots excluídos">Excluídos</label>
          </div>
          <div class="form-check form-switch m-0">
            <input class="form-check-input" type="checkbox" name="latest_per_day" value="1" id="latestPerDaySwitch" onchange="this.form.submit()" {{ ($latestPerDay ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="latestPerDaySwitch" title="Mantém apenas o snapshot mais recente de cada dia">Mais novo/dia</label>
          </div>
          <div class="form-check form-switch m-0">
            <input class="form-check-input" type="checkbox" name="group_by_date" value="1" id="groupByDateSwitch" onchange="this.form.submit()" {{ ($groupByDate ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="groupByDateSwitch" title="Agrupa todos snapshots de cada dia em uma linha">Agrupar dia</label>
          </div>
          <div class="form-check form-switch m-0">
            <input class="form-check-input" type="checkbox" name="compact" value="1" id="compactSwitch" onchange="this.form.submit()" {{ ($compact ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="compactSwitch" title="Modo compacto para dif & var">Compacto</label>
          </div>
          <div class="form-check form-switch m-0">
            <input class="form-check-input" type="checkbox" name="spark" value="1" id="sparkSwitch" onchange="this.form.submit()" {{ (!empty($sparkSeries)) ? 'checked' : '' }}>
            <label class="form-check-label" for="sparkSwitch" title="Mostrar sparkline">Spark</label>
          </div>
          <select name="base_mode" class="form-select form-select-sm w-auto" onchange="this.form.submit()" title="Base acumulada">
            <option value="oldest" {{ ($baseMode??'oldest')==='oldest'?'selected':'' }}>Base: mais antigo</option>
            <option value="recent" {{ ($baseMode??'oldest')==='recent'?'selected':'' }}>Base: mais recente</option>
          </select>
        </form>
    </div>
  </div>
  @if(session('success'))
    <div class="alert alert-success py-2">{{ session('success') }}</div>
  @endif

  <div class="table-responsive">
    <table class="table table-sm table-bordered align-middle">
      <thead class="table-dark">
        <tr>
          @if(!($groupByDate ?? false))
            <th style="width:20%">Data/Hora</th>
          @else
            <th style="width:14%" title="Data (agrupada)">Data</th>
            <th class="text-end" style="width:10%" title="Quantidade de snapshots no dia">Qtde</th>
          @endif
          <th class="text-end" style="width:18%">Total</th>
          @if(!($compact ?? false))
            <th class="text-end" style="width:14%" title="Diferença = valor desta linha (mais recente) - próximo (mais antigo); positivo = crescimento">Dif (vs próximo)</th>
            <th class="text-end" style="width:12%" title="Var % = Dif / valor mais antigo * 100">Var %</th>
          @else
            <th class="text-end" style="width:20%" title="Δ e Δ% (vs próximo)">Δ / Δ%</th>
          @endif
          <th class="text-end" style="width:14%" title="Acumulado Dif = valor atual - valor do 1º snapshot listado">Acum Dif</th>
          <th class="text-end" style="width:12%" title="Acum % = (Acum Dif / valor do 1º snapshot) * 100">Acum %</th>
          @if(!($groupByDate ?? false))
            <th style="width:12%" class="text-end">Anterior</th>
          @else
            <th class="text-end" style="width:14%" title="Δ intra-dia (fechamento - abertura) / %">Intra Δ / %</th>
          @endif
          <th style="width:12%" class="text-center">Ações</th>
        </tr>
      </thead>
      <tbody>
  @forelse($rows as $r)
          @php
            $m = $r['model'];
            $diff = $r['diff'];
            $var = $r['var'];
            $accDiff = $r['acc_diff'] ?? null;
            $accPerc = $r['acc_perc'] ?? null;
            $prevTotal = $r['prev_total'];
            $cls = '';
            if($diff !== null){ if($diff>0) $cls='text-success'; elseif($diff<0) $cls='text-danger'; else $cls='text-muted'; }
            $grouped = $r['grouped'] ?? false;
            $intraDiff = $r['intra_diff'] ?? null;
            $intraVar = $r['intra_var'] ?? null;
          @endphp
            <tr @if($m->trashed()) class="table-warning" @endif>
              @if(!$grouped)
                <td>
                  {{ optional($m->snapshot_at)->format('d/m/Y H:i:s') }}
                  @if($m->trashed())<span class="badge bg-warning text-dark ms-1">Excluído</span>@endif
                </td>
              @else
                <td>{{ $r['group_date'] ? \Carbon\Carbon::parse($r['group_date'])->format('d/m/Y') : '' }}</td>
                <td class="text-end">
                  <span class="badge bg-secondary" title="Snapshots no dia">{{ $r['count'] }}</span>
                </td>
              @endif
              <td class="text-end">{{ $grouped ? number_format(($r['sum_total'] ?? $m->total_amount), 2, ',', '.') : number_format($m->total_amount, 2, ',', '.') }}</td>
              @if(!($compact ?? false))
                <td class="text-end {{ $diff === null ? 'text-muted' : ($diff > 0 ? 'text-success' : ($diff < 0 ? 'text-danger' : 'text-muted')) }}">
                  @if($diff===null) — @else
                    @if($diff>0) ↑ @elseif($diff<0) ↓ @else → @endif
                    {{ number_format($diff, 2, ',', '.') }}
                  @endif
                </td>
                <td class="text-end {{ $var === null ? 'text-muted' : ($var > 0 ? 'text-success' : ($var < 0 ? 'text-danger' : 'text-muted')) }}">
                  @if($var===null) — @else {{ number_format($var, 2, ',', '.') }}% @endif
                </td>
              @else
                <td class="text-end {{ ($diff ?? 0) > 0 ? 'text-success' : (($diff ?? 0) < 0 ? 'text-danger' : 'text-muted') }}">
                  @if($diff===null) — @else
                    @if($diff>0) ↑ @elseif($diff<0) ↓ @else → @endif
                    {{ number_format($diff, 2, ',', '.') }}
                    <span class="text-muted">/</span>
                    <span class="{{ $var === null ? 'text-muted' : ($var > 0 ? 'text-success' : ($var < 0 ? 'text-danger' : 'text-muted')) }}">{{ $var===null ? '—' : number_format($var, 2, ',', '.') . '%' }}</span>
                  @endif
                </td>
              @endif
              <td class="text-end {{ $accDiff === null ? 'text-muted' : ($accDiff > 0 ? 'text-success' : ($accDiff < 0 ? 'text-danger' : 'text-muted')) }}">
                @if($accDiff===null) — @else
                  @if($accDiff>0) ↑ @elseif($accDiff<0) ↓ @else → @endif
                  {{ number_format($accDiff, 2, ',', '.') }}
                @endif
              </td>
              <td class="text-end {{ $accPerc === null ? 'text-muted' : ($accPerc > 0 ? 'text-success' : ($accPerc < 0 ? 'text-danger' : 'text-muted')) }}">
                @if($accPerc===null) — @else {{ number_format($accPerc, 2, ',', '.') }}% @endif
              </td>
              @if(!$grouped)
                <td class="text-end">{{ optional($m->created_at)->format('d/m/Y H:i') }}</td>
              @else
                <td class="text-end">
                  @if($intraDiff===null) — @else
                    <span class="{{ $intraDiff>0?'text-success':($intraDiff<0?'text-danger':'text-muted') }}">
                      @if($intraDiff>0) ↑ @elseif($intraDiff<0) ↓ @else → @endif
                      {{ number_format($intraDiff, 2, ',', '.') }}
                      <span class="text-muted">/</span>
                      <span class="{{ $intraVar === null ? 'text-muted' : ($intraVar > 0 ? 'text-success' : ($intraVar < 0 ? 'text-danger' : 'text-muted')) }}">{{ $intraVar===null ? '—' : number_format($intraVar, 2, ',', '.') . '%' }}</span>
                    </span>
                  @endif
                </td>
              @endif
              <td class="d-flex gap-1">
                @if(!$grouped)
                  @if(!$m->trashed())
                    @can('INVESTIMENTOS SNAPSHOTS - EXCLUIR')
                    <form method="POST" action="{{ route('investments.daily-balances.destroy', $m) }}" class="d-inline" onsubmit="return confirm('Excluir este snapshot?');">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-sm btn-outline-danger" title="Excluir snapshot">Excluir</button>
                    </form>
                    @endcan
                  @else
                    @can('INVESTIMENTOS SNAPSHOTS - RESTAURAR')
                    <form method="POST" action="{{ route('investments.daily-balances.restore', $m->id) }}" class="d-inline" onsubmit="return confirm('Restaurar este snapshot?');">
                      @csrf
                      @method('PATCH')
                      <button class="btn btn-sm btn-outline-success" title="Restaurar snapshot">Restaurar</button>
                    </form>
                    @endcan
                  @endif
                @else
                  <span class="text-muted small" title="Ações indisponíveis no modo agrupado">—</span>
                @endif
              </td>
            </tr>
        @empty
          <tr><td colspan="8" class="text-center text-muted">
            Nenhum snapshot encontrado
            @if(($from??'')!=='' || ($to??'')!=='' || ($range??'')!=='')
              para o período selecionado.
            @endif
          </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if(($totalPages ?? 1) > 1)
    <nav aria-label="Paginação snapshots" class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
      <div class="small text-muted">Página {{ $page }} / {{ $totalPages }} • {{ $totalItems }} registros</div>
      <ul class="pagination pagination-sm mb-0">
        @php $qs = request()->except('page'); @endphp
        <li class="page-item {{ $page==1?'disabled':'' }}">
          <a class="page-link" href="?{{ http_build_query(array_merge($qs,['page'=>max(1,$page-1)])) }}" aria-label="Anterior">«</a>
        </li>
        @for($p=max(1,$page-3); $p<=min($totalPages,$page+3); $p++)
          <li class="page-item {{ $p==$page?'active':'' }}"><a class="page-link" href="?{{ http_build_query(array_merge($qs,['page'=>$p])) }}">{{ $p }}</a></li>
        @endfor
        <li class="page-item {{ $page==$totalPages?'disabled':'' }}">
          <a class="page-link" href="?{{ http_build_query(array_merge($qs,['page'=>min($totalPages,$page+1)])) }}" aria-label="Próxima">»</a>
        </li>
      </ul>
    </nav>
  @endif
  <div class="alert alert-info mt-3 small">
    Cada snapshot registra a soma do último valor de cada ativo monitorado nos registros OpenAI. Gere manualmente clicando em "Novo Snapshot" ou pelo botão "Snapshot Saldo" na página de investimentos.
  </div>
</div>
@endsection

@if(!empty($sparkSeries))
@push('scripts')
@php
  $sparkJson = json_encode($sparkSeries);
@endphp
<script>(function(){
  var data = JSON.parse('{!! addslashes($sparkJson) !!}');
  var canvas = document.getElementById('sparklineBalance');
  if(!canvas || !data.length) return;
  var ctx = canvas.getContext('2d');
  var w = canvas.width, h = canvas.height;
  ctx.clearRect(0,0,w,h);
  var min = Math.min.apply(null,data);
  var max = Math.max.apply(null,data);
  var rng = (max-min)||1;
  ctx.strokeStyle = '#0d6efd';
  ctx.lineWidth = 1;
  ctx.beginPath();
  for(var i=0;i<data.length;i++){
    var v = data[i];
    var x = (i/(data.length-1)) * (w-2) + 1;
    var y = h - (((v-min)/rng) * (h-2) + 1);
    if(i===0) ctx.moveTo(x,y); else ctx.lineTo(x,y);
  }
  ctx.stroke();
})();</script>
@endpush
@endif
