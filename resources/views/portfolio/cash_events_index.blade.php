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
        <div class="col-md-3">
          <label class="form-label small mb-1">Título (contém)</label>
          <input type="text" name="title" value="{{ $filter_title ?? '' }}" class="form-control form-control-sm" />
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">De</label>
          <input type="date" name="from" value="{{ $filter_from }}" class="form-control form-control-sm" />
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">Até</label>
          <input type="date" name="to" value="{{ $filter_to }}" class="form-control form-control-sm" />
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">Liq. De</label>
          <input type="date" name="settle_from" value="{{ $filter_settle_from }}" class="form-control form-control-sm" />
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">Liq. Até</label>
          <input type="date" name="settle_to" value="{{ $filter_settle_to }}" class="form-control form-control-sm" />
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">Direção</label>
          <select name="direction" class="form-select form-select-sm">
            <option value="">Todas</option>
            <option value="in" @selected($filter_direction==='in')>Entradas</option>
            <option value="out" @selected($filter_direction==='out')>Saídas</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">Valor >=</label>
          <input type="number" step="0.01" name="val_min" value="{{ $filter_val_min }}" class="form-control form-control-sm" />
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">Valor <=</label>
          <input type="number" step="0.01" name="val_max" value="{{ $filter_val_max }}" class="form-control form-control-sm" />
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">Fonte</label>
          <select name="source" class="form-select form-select-sm">
            <option value="">Todas</option>
            @foreach($sources as $src)
              <option value="{{ $src }}" @selected($filter_source===$src)>{{ $src }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">Ordenar por</label>
          <select name="sort" class="form-select form-select-sm">
            <option value="event_date" @selected($sort==='event_date')>Data</option>
            <option value="settlement_date" @selected($sort==='settlement_date')>Liquidação</option>
            <option value="category" @selected($sort==='category')>Categoria</option>
            <option value="title" @selected($sort==='title')>Título</option>
            <option value="amount" @selected($sort==='amount')>Valor</option>
            <option value="status" @selected($sort==='status')>Status</option>
          </select>
        </div>
        <div class="col-md-1">
          <label class="form-label small mb-1">Dir</label>
          <select name="dir" class="form-select form-select-sm">
            <option value="desc" @selected($dir==='desc')>DESC</option>
            <option value="asc" @selected($dir==='asc')>ASC</option>
          </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
          <button class="btn btn-sm btn-outline-primary flex-grow-1" type="submit">Filtrar</button>
          <a href="{{ route('cash.events.index') }}" class="btn btn-sm btn-outline-secondary" title="Limpar filtros">Limpar</a>
        </div>
        <div class="col-md-2">
          <div class="form-check mt-4" data-bs-toggle="tooltip" data-bs-title="Exibe a coluna 'Saldo Após' calculada a partir do snapshot mais recente da conta. Disponível apenas na página 1, com conta filtrada e ordenação por Data ou Liquidação.">
            <input class="form-check-input" type="checkbox" value="1" id="chkRunning" name="show_running" @checked($showRunning) />
            <label class="form-check-label small" for="chkRunning">Exibir Saldo Após</label>
          </div>
        </div>
      </form>
    </div>
    <div class="card-footer small text-muted">
      <div class="d-flex flex-wrap gap-3 align-items-center">
        <div>Entradas: <span class="text-success">{{ number_format($sumIn,2,',','.') }}</span> | Saídas: <span class="text-danger">{{ number_format($sumOut,2,',','.') }}</span> | Saldo (∑ filtrado): <strong>{{ number_format($sumTotal,2,',','.') }}</strong></div>
        <div class="ms-auto d-flex gap-2">
          <a href="{{ route('cash.positions.summary', request()->query()) }}#gsc.tab=0" class="btn btn-sm btn-outline-success" title="Ver resumo por ativo (saldo e preço médio)">Resumo por Ativo</a>
          <a href="{{ route('openai.portfolio.index') }}#gsc.tab=0" class="btn btn-sm btn-outline-secondary" title="Ver carteira (posições)">Carteira</a>
          <a href="{{ route('cash.events.export.csv', request()->query()) }}" class="btn btn-sm btn-outline-info" title="Exportar eventos filtrados em CSV">Exportar CSV</a>
          @can('CASH EVENTS - IMPORTAR')
          <a href="{{ route('cash.import.form') }}#gsc.tab=0" class="btn btn-sm btn-outline-primary" title="Importar novo bloco de caixa">Importar Caixa</a>
          <a href="{{ route('cash.import.csv.form') }}#gsc.tab=0" class="btn btn-sm btn-outline-primary" title="Importar CSV avenue-report-statement">Importar Caixa CSV</a>
          @endcan
          <button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#modalTruncateCash" title="Apagar todos os eventos e snapshots de caixa deste usuário">Limpar Tudo</button>
        </div>
      </div>
    </div>
  </div>
  @if(!empty($periodSummary) || !empty($byAccountSummary))
  <div class="row g-3 mb-3">
    <div class="col-lg-7">
      <div class="card h-100 shadow-sm">
        <div class="card-header"><strong>Resumo por Período (mensal)</strong></div>
        <div class="table-responsive">
          <table class="table table-sm table-striped mb-0">
            <thead class="table-light">
              <tr>
                <th>Período</th>
                <th class="text-end">Compras</th>
                <th class="text-end">Vendas</th>
                <th class="text-end">Taxas (fee)</th>
                <th class="text-end">Saldo (Vnd - Cmp)</th>
              </tr>
            </thead>
            <tbody>
              @forelse($periodSummary ?? [] as $per => $s)
                <tr>
                  <td>{{ $per }}</td>
                  <td class="text-end text-danger">{{ number_format($s['buy'] ?? 0, 2, ',', '.') }}</td>
                  <td class="text-end text-success">{{ number_format($s['sell'] ?? 0, 2, ',', '.') }}</td>
                  <td class="text-end text-secondary">{{ number_format($s['fee'] ?? 0, 2, ',', '.') }}</td>
                  @php $net = ($s['sell'] ?? 0) - ($s['buy'] ?? 0); @endphp
                  <td class="text-end {{ $net>0 ? 'text-success' : ($net<0 ? 'text-danger' : 'text-secondary') }}">{{ number_format($net, 2, ',', '.') }}</td>
                </tr>
              @empty
                <tr><td colspan="5" class="text-center text-muted">Sem dados no período.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="card h-100 shadow-sm">
        <div class="card-header"><strong>Resumo por Conta</strong></div>
        <div class="table-responsive">
          <table class="table table-sm table-striped mb-0">
            <thead class="table-light">
              <tr>
                <th>Conta</th>
                <th class="text-end">Compras</th>
                <th class="text-end">Vendas</th>
                <th class="text-end">Taxas (fee)</th>
              </tr>
            </thead>
            <tbody>
              @php
                // Map para nome da conta
                $accNames = collect($accounts ?? [])->keyBy('id');
              @endphp
              @forelse(($byAccountSummary ?? []) as $accId => $s)
                <tr>
                  <td>{{ $accNames[$accId]->account_name ?? ('Conta #'.$accId) }}</td>
                  <td class="text-end text-danger">{{ number_format($s['buy'] ?? 0, 2, ',', '.') }}</td>
                  <td class="text-end text-success">{{ number_format($s['sell'] ?? 0, 2, ',', '.') }}</td>
                  <td class="text-end text-secondary">{{ number_format($s['fee'] ?? 0, 2, ',', '.') }}</td>
                </tr>
              @empty
                <tr><td colspan="4" class="text-center text-muted">Sem dados por conta.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  @endif
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
            <th>Fonte</th>
            @if($canComputeRunning)
            <th class="text-end">Saldo Após</th>
            @endif
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
            <td><span class="badge bg-secondary-subtle text-secondary-emphasis border">{{ $e->source }}</span></td>
            @if($canComputeRunning)
            <td class="text-end">{{ $e->running_balance_after!==null ? number_format($e->running_balance_after,2,',','.') : '—' }}</td>
            @endif
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

@push('scripts')
<div class="modal fade" id="modalTruncateCash" tabindex="-1" aria-labelledby="modalTruncateCashLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTruncateCashLabel">Confirmar limpeza total</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <form method="POST" action="{{ route('cash.events.truncate.user') }}" id="formTruncateCash">
        @csrf
        <input type="hidden" name="confirm" value="yes" />
        <div class="modal-body">
          <p class="small mb-2">Esta ação irá <strong>APAGAR TODOS</strong> os eventos e snapshots de caixa <strong>somente do seu usuário</strong>. Não pode ser desfeita.</p>
          <p class="small mb-2">Para confirmar, digite <code>APAGAR</code> abaixo:</p>
          <input type="text" name="confirm_token" class="form-control form-control-sm" placeholder="Digite APAGAR" required />
        </div>
        <div class="modal-footer d-flex justify-content-between">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-sm btn-danger" id="btnTruncateSubmit" disabled>Apagar Tudo</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
  const modal = document.getElementById('modalTruncateCash');
  if(!modal) return;
  const input = modal.querySelector('input[name="confirm_token"]');
  const btn = modal.querySelector('#btnTruncateSubmit');
  if(input && btn){
    input.addEventListener('input', function(){
      btn.disabled = (input.value.trim().toUpperCase() !== 'APAGAR');
    });
  }
});
</script>
@endpush
