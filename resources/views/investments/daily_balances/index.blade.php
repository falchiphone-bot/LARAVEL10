@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h5 mb-0">Evolução do Saldo (Snapshots)</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('openai.investments.index') }}" class="btn btn-outline-secondary">← Investimentos</a>
        @can('INVESTIMENTOS SNAPSHOTS - CRIAR')
        <form method="POST" action="{{ route('investments.daily-balances.store') }}" id="new-snapshot-form">
          @csrf
          <button class="btn btn-outline-primary" title="Gerar novo snapshot agora">Novo Snapshot</button>
        </form>
        @endcan
        @can('INVESTIMENTOS SNAPSHOTS - EXPORTAR')
        <a href="{{ route('investments.daily-balances.exportCsv', request()->only('with_deleted','latest_per_day')) }}" class="btn btn-outline-secondary" title="Exportar CSV">Exportar CSV</a>
        @endcan
        <form method="GET" action="{{ route('investments.daily-balances.index') }}" class="d-flex align-items-center ms-3 gap-3">
          <div class="form-check form-switch m-0">
            <input class="form-check-input" type="checkbox" name="with_deleted" value="1" id="withDeletedSwitch" onchange="this.form.submit()" {{ ($withDeleted ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="withDeletedSwitch" title="Mostrar também snapshots excluídos">Excluídos</label>
          </div>
          <div class="form-check form-switch m-0">
            <input class="form-check-input" type="checkbox" name="latest_per_day" value="1" id="latestPerDaySwitch" onchange="this.form.submit()" {{ ($latestPerDay ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="latestPerDaySwitch" title="Mantém apenas o snapshot mais recente de cada dia">Mais novo/dia</label>
          </div>
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
          <th style="width:20%">Data/Hora</th>
          <th class="text-end" style="width:18%">Total</th>
          <th class="text-end" style="width:14%" title="Diferença = valor desta linha (mais recente) - valor da próxima linha (mais antiga); positivo = crescimento">Dif (vs próximo)</th>
          <th class="text-end" style="width:12%" title="Var % = (Dif / valor desta linha) * 100">Var %</th>
          <th class="text-end" style="width:14%" title="Acumulado Dif = valor atual - valor do 1º snapshot listado">Acum Dif</th>
          <th class="text-end" style="width:12%" title="Acum % = (Acum Dif / valor do 1º snapshot) * 100">Acum %</th>
          <th style="width:12%" class="text-end">Anterior</th>
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
          @endphp
            <tr @if($m->trashed()) class="table-warning" @endif>
              <td>
                {{ optional($m->snapshot_at)->format('d/m/Y H:i:s') }}
                @if($m->trashed())<span class="badge bg-warning text-dark ms-1">Excluído</span>@endif
              </td>
              <td class="text-end">{{ number_format($m->total_amount, 2, ',', '.') }}</td>
              <td class="text-end {{ $diff === null ? 'text-muted' : ($diff > 0 ? 'text-success' : ($diff < 0 ? 'text-danger' : 'text-muted')) }}">
                @if($diff===null) — @else
                  @if($diff>0) ↑ @elseif($diff<0) ↓ @else → @endif
                  {{ number_format($diff, 2, ',', '.') }}
                @endif
              </td>
              <td class="text-end {{ $var === null ? 'text-muted' : ($var > 0 ? 'text-success' : ($var < 0 ? 'text-danger' : 'text-muted')) }}">
                @if($var===null) — @else {{ number_format($var, 2, ',', '.') }}% @endif
              </td>
              <td class="text-end {{ $accDiff === null ? 'text-muted' : ($accDiff > 0 ? 'text-success' : ($accDiff < 0 ? 'text-danger' : 'text-muted')) }}">
                @if($accDiff===null) — @else
                  @if($accDiff>0) ↑ @elseif($accDiff<0) ↓ @else → @endif
                  {{ number_format($accDiff, 2, ',', '.') }}
                @endif
              </td>
              <td class="text-end {{ $accPerc === null ? 'text-muted' : ($accPerc > 0 ? 'text-success' : ($accPerc < 0 ? 'text-danger' : 'text-muted')) }}">
                @if($accPerc===null) — @else {{ number_format($accPerc, 2, ',', '.') }}% @endif
              </td>
              <td class="text-end">{{ optional($m->created_at)->format('d/m/Y H:i') }}</td>
              <td class="d-flex gap-1">
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
              </td>
            </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted">Nenhum snapshot ainda.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="alert alert-info mt-3 small">
    Cada snapshot registra a soma do último valor de cada ativo monitorado nos registros OpenAI. Gere manualmente clicando em "Novo Snapshot" ou pelo botão "Snapshot Saldo" na página de investimentos.
  </div>
</div>
@endsection
