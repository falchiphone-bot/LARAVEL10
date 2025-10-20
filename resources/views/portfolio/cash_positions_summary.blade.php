@extends('layouts.bootstrap5')
@section('content')
<div class="container-fluid">
  <h1 class="h5 mb-3">Posições por Ativo (Saldo e Preço Médio)</h1>
  <div class="card mb-3 shadow-sm">
    <div class="card-body">
      <form method="get" class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label small mb-1">Conta</label>
          <select name="account_id" class="form-select form-select-sm">
            <option value="">Todas</option>
            @foreach($accounts as $acc)
              <option value="{{ $acc->id }}" @selected($filter_account_id===$acc->id)>{{ $acc->account_name }} @if($acc->broker) ({{ $acc->broker }}) @endif</option>
            @endforeach
          </select>
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
          <label class="form-label small mb-1">Fonte</label>
          <select name="source" class="form-select form-select-sm">
            <option value="">Todas</option>
            @foreach($sources as $src)
              <option value="{{ $src }}" @selected($filter_source===$src)>{{ $src }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
          <button class="btn btn-sm btn-outline-primary flex-grow-1" type="submit">Filtrar</button>
          <a href="{{ route('cash.positions.summary') }}" class="btn btn-sm btn-outline-secondary">Limpar</a>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>Resumo</strong>
      <div class="d-flex align-items-center gap-2">
        <small class="text-muted">Ativos: {{ count($positions) }}</small>
        <a href="{{ route('cash.events.index', request()->query()) }}#gsc.tab=0" class="btn btn-sm btn-outline-secondary" title="Voltar para Eventos de Caixa">Voltar</a>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-sm table-striped align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Ativo</th>
            <th class="text-end">Saldo (Qtd)</th>
            <th class="text-end">Saldo Médio (Preço)</th>
            <th class="text-end">Custo Total</th>
            <th class="text-end">Valor Atual</th>
            <th class="text-end">Novo Total</th>
            <th class="text-end">Variação</th>
          </tr>
        </thead>
        <tbody>
          @forelse($positions as $p)
            <tr>
              <td>{{ $p['symbol'] }}</td>
              <td class="text-end">{{ number_format($p['qty'], 4, ',', '.') }}</td>
              <td class="text-end">{{ number_format($p['avg'], 4, ',', '.') }}</td>
              <td class="text-end">{{ number_format($p['cost'], 2, ',', '.') }}</td>
              <td class="text-end">
                @if(isset($p['current_price']) && $p['current_price'] !== null)
                  {{ $p['currency'] ?? '' }} {{ number_format($p['current_price'], 4, ',', '.') }}
                  @if(!empty($p['quote_source']) || !empty($p['updated_at']))
                    <span class="badge bg-light text-muted border" title="{{ $p['quote_source'] ?? 'cotação' }} @if(!empty($p['updated_at'])) • {{ $p['updated_at'] }} @endif">{{ Str::upper($p['quote_source'] ?? 'cot') }}</span>
                  @endif
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
              <td class="text-end">
                @if(isset($p['new_total']) && $p['new_total'] !== null)
                  {{ $p['currency'] ?? '' }} {{ number_format($p['new_total'], 2, ',', '.') }}
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
              <td class="text-end">
                @php
                  $var = $p['variation'] ?? null; $pct = $p['variation_pct'] ?? null;
                  $cls = $var === null ? '' : ($var > 0 ? 'text-success' : ($var < 0 ? 'text-danger' : ''));
                @endphp
                @if($var === null)
                  <span class="text-muted">—</span>
                @else
                  <span class="{{ $cls }}">{{ $p['currency'] ?? '' }} {{ number_format($var, 2, ',', '.') }}</span>
                  @if($pct !== null)
                    <small class="ms-1 {{ $cls }}">({{ number_format($pct*100, 2, ',', '.') }}%)</small>
                  @endif
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted">Sem dados no período.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer small text-muted">
      Observação: o cálculo considera apenas eventos com textos reconhecíveis de compra/venda e usa média móvel simples.
    </div>
  </div>
</div>
@endsection
