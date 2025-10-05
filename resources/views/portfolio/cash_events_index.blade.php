@extends('layouts.bootstrap5')
@section('content')
<div class="container-fluid">
  <h1 class="h5 mb-3">Eventos de Caixa</h1>
  <div class="card mb-3 shadow-sm">
    <div class="card-body">
      <form method="get" class="row g-2 align-items-end">
        <div class="col-md-2">
          <label class="form-label small mb-1">Conta</label>
          <select name="account_id" class="form-select form-select-sm">
            <option value="">Todas</option>
            @foreach($accounts as $acc)
              <option value="{{ $acc->id }}" @selected($filter_account_id===$acc->id)>{{ $acc->account_name }} @if($acc->broker) ({{ $acc->broker }}) @endif</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">Categoria</label>
          <select name="category" class="form-select form-select-sm">
            <option value="">Todas</option>
            @foreach($categories as $cat)
              <option value="{{ $cat }}" @selected($filter_category===$cat)>{{ $cat }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">Status (contém)</label>
          <input type="text" name="status" value="{{ $filter_status }}" class="form-control form-control-sm" />
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">De</label>
          <input type="date" name="from" value="{{ $filter_from }}" class="form-control form-control-sm" />
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">Até</label>
          <input type="date" name="to" value="{{ $filter_to }}" class="form-control form-control-sm" />
        </div>
        <div class="col-md-2 d-flex gap-2">
          <button class="btn btn-sm btn-outline-primary flex-grow-1" type="submit">Filtrar</button>
          <a href="{{ route('cash.events.index') }}" class="btn btn-sm btn-outline-secondary" title="Limpar filtros">Limpar</a>
        </div>
      </form>
    </div>
    <div class="card-footer small text-muted">
      <div class="d-flex flex-wrap gap-3 align-items-center">
        <div>Entradas: <span class="text-success">{{ number_format($sumIn,2,',','.') }}</span> | Saídas: <span class="text-danger">{{ number_format($sumOut,2,',','.') }}</span> | Saldo (∑ filtrado): <strong>{{ number_format($sumTotal,2,',','.') }}</strong></div>
        <div class="ms-auto d-flex gap-2">
          <a href="{{ route('cash.events.export.csv', request()->query()) }}" class="btn btn-sm btn-outline-info" title="Exportar eventos filtrados em CSV">Exportar CSV</a>
          @can('CASH EVENTS - IMPORTAR')
          <a href="{{ route('cash.import.form') }}#gsc.tab=0" class="btn btn-sm btn-outline-primary" title="Importar novo bloco de caixa">Importar Caixa</a>
          @endcan
        </div>
      </div>
    </div>
  </div>
  <div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>Lista</strong>
      <small class="text-muted">Total: {{ $events->total() }} | Página {{ $events->currentPage() }}/{{ $events->lastPage() }}</small>
    </div>
    <div class="table-responsive">
      <table class="table table-sm table-striped align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Data</th>
            <th>Liquidação</th>
            <th>Conta</th>
            <th>Categoria</th>
            <th>Título</th>
            <th>Detalhe</th>
            <th class="text-end">Valor (USD)</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
        @forelse($events as $e)
          @php $cls = $e->amount>0?'text-success':($e->amount<0?'text-danger':'text-secondary'); @endphp
          <tr>
            <td>{{ $e->event_date? $e->event_date->format('d/m/Y'):'—' }}</td>
            <td>{{ $e->settlement_date? $e->settlement_date->format('d/m/Y'):'—' }}</td>
            <td>{{ $e->account? $e->account->account_name:'—' }}</td>
            <td>{{ $e->category }}</td>
            <td>{{ $e->title }}</td>
            <td>{{ $e->detail ?: '—' }}</td>
            <td class="text-end {{ $cls }}">{{ number_format($e->amount,2,',','.') }}</td>
            <td>{{ $e->status ?: '—' }}</td>
          </tr>
        @empty
          <tr><td colspan="8" class="text-center text-muted">Nenhum evento encontrado.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">
      {{ $events->links() }}
    </div>
  </div>
</div>
@endsection
