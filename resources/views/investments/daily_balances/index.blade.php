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
        <a href="{{ route('investments.daily-balances.exportCsv', request()->only('with_deleted')) }}" class="btn btn-outline-secondary" title="Exportar CSV">Exportar CSV</a>
        @endcan
        <form method="GET" action="{{ route('investments.daily-balances.index') }}" class="ms-3">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="with_deleted" value="1" id="withDeletedSwitch" onchange="this.form.submit()" {{ $withDeleted ? 'checked' : '' }}>
            <label class="form-check-label" for="withDeletedSwitch">Mostrar excluídos</label>
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
          <th class="text-end" style="width:16%">Dif (vs anterior)</th>
          <th class="text-end" style="width:16%">Var %</th>
          <th style="width:16%" class="text-end">Anterior</th>
          <th style="width:14%" class="text-center">Ações</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $r)
          @php
            $m = $r['model'];
            $diff = $r['diff'];
            $var = $r['var'];
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
              <td class="text-end {{ $diff === null ? 'text-muted' : ($diff >= 0 ? 'text-success' : 'text-danger') }}">
                @if($diff===null) — @else {{ number_format($diff, 2, ',', '.') }} @endif
              </td>
              <td class="text-end {{ $var === null ? 'text-muted' : ($var >= 0 ? 'text-success' : 'text-danger') }}">
                @if($var===null) — @else {{ number_format($var, 2, ',', '.') }}% @endif
              </td>
              <td>{{ optional($m->created_at)->format('d/m/Y H:i') }}</td>
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
