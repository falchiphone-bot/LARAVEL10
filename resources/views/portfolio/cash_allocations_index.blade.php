@extends('layouts.bootstrap5')
@section('content')
<div class="container-fluid">
  <h1 class="h5 mb-3">Alocações LIFO por Venda</h1>
  <div class="card mb-3 shadow-sm">
    <div class="card-body">
      <form method="get" class="row g-2 align-items-end">
        <div class="col-md-2">
          <label class="form-label small mb-1">De (data da venda)</label>
          <input type="date" name="from" value="{{ $filter_from }}" class="form-control form-control-sm" />
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">Até</label>
          <input type="date" name="to" value="{{ $filter_to }}" class="form-control form-control-sm" />
        </div>
        <div class="col-md-3">
          <label class="form-label small mb-1">Símbolo (contém)</label>
          <input type="text" name="symbol" value="{{ $filter_symbol }}" class="form-control form-control-sm" placeholder="Ex: AAPL, LTBR" />
        </div>
        <div class="col-md-2 d-flex gap-2">
          <button class="btn btn-sm btn-outline-primary flex-grow-1" type="submit">Filtrar</button>
          <a href="{{ route('cash.allocations.index') }}" class="btn btn-sm btn-outline-secondary">Limpar</a>
        </div>
      </form>
      <div class="row mt-2">
        <div class="col d-flex gap-2 justify-content-end">
          <form method="post" action="{{ route('cash.allocations.clear') }}" onsubmit="return confirm('Confirma limpar alocações para o período/símbolo filtrados?');" class="m-0">
            @csrf
            <input type="hidden" name="from" value="{{ $filter_from }}" />
            <input type="hidden" name="to" value="{{ $filter_to }}" />
            <input type="hidden" name="symbol" value="{{ $filter_symbol }}" />
            <button class="btn btn-sm btn-outline-danger" type="submit" title="Remove alocações que correspondem aos filtros atuais">Limpar alocações (por filtro)</button>
          </form>
          <a href="{{ route('cash.events.index') }}#gsc.tab=0" class="btn btn-sm btn-outline-secondary">Eventos de Caixa</a>
        </div>
      </div>
    </div>
  </div>

  @if(empty($groups))
    <div class="alert alert-info">Nenhuma alocação encontrada para os filtros informados.</div>
  @endif

  @foreach($groups as $g)
    @php
      $sellDate = optional($g['sell_event_date'])->format('d/m/Y');
      $sellSettle = optional($g['sell_settlement_date'])->format('d/m/Y');
      $accName = $accNames[$g['sell_account_id']] ?? null;
    @endphp
    <div class="card mb-3 shadow-sm">
      <div class="card-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
          <div>
            <strong>Venda #{{ $g['sell_event_id'] }}</strong>
            <span class="ms-2">Data: {{ $sellDate ?: '—' }}</span>
            <span class="ms-2 text-muted">(Liq: {{ $sellSettle ?: '—' }})</span>
            @if(!empty($g['symbol']))
              <span class="badge bg-secondary-subtle text-secondary-emphasis border ms-2">{{ $g['symbol'] }}</span>
            @endif
            @if($accName)
              <span class="ms-2 text-muted">Conta: {{ $accName }}</span>
            @endif
          </div>
          <div class="d-flex align-items-center gap-2">
            @php
              $sellQty = $g['sell_qty_parsed'];
              $allocQty = $g['sum_alloc_qty'];
              $statusBadge = '';
              $sellId = (int)$g['sell_event_id'];
              if ($sellQty === null) {
                $statusBadge = '<span class="badge bg-secondary">Sem qtd parseada</span>';
              } else {
                $diff = (float)$allocQty - (float)$sellQty;
                $absDiff = abs($diff);
                if ($absDiff < 1e-6) {
                  $statusBadge = '<span class="badge bg-success">Alocado = Vendido (parseado)</span>';
                } elseif ($diff < 0) {
                  $faltam = number_format(0 - $diff, 6, ',', '.');
                  $statusBadge = '<span class="badge bg-warning text-dark" title="Faltam alocações">Pendente: faltam '.$faltam.'</span>';
                } else { // diff > 0
                  $exced = number_format($diff, 6, ',', '.');
                  $statusBadge = '<span class="badge bg-danger" title="Alocação excedente">Excedente: +'.$exced.'</span>';
                }
              }
            @endphp
            <div class="small text-muted">Qtd vendida (parse): {{ $sellQty!==null ? number_format($sellQty,6,',','.') : '—' }} | Alocada: <strong>{{ number_format($allocQty,6,',','.') }}</strong></div>
            <div>{!! $statusBadge !!}</div>
            <form method="post" action="{{ route('cash.allocations.clearSell', ['sell'=>$sellId]) }}" onsubmit="return confirm('Confirma remover TODAS as alocações desta venda (#{{ $sellId }})?');">
              @csrf
              <button class="btn btn-sm btn-outline-danger" type="submit">Limpar desta venda</button>
            </form>
          </div>
        </div>
        <div class="small text-muted mt-1">{{ $g['sell_title'] }} @if(!empty($g['sell_detail'])) — {{ $g['sell_detail'] }} @endif</div>
      </div>
      <div class="table-responsive">
        <table class="table table-sm table-striped mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 120px;">Data Compra</th>
              <th>Compra</th>
              <th class="text-end" style="width: 180px;">Quantidade Alocada</th>
            </tr>
          </thead>
          <tbody>
            @foreach($g['allocations'] as $al)
              @php
                $bDate = optional($al['buy_event_date'])->format('d/m/Y');
                $bSettle = optional($al['buy_settlement_date'])->format('d/m/Y');
                $accB = $accNames[$al['buy_account_id']] ?? null;
              @endphp
              <tr>
                <td>
                  {{ $bDate ?: '—' }}
                  <div class="small text-muted">Liq: {{ $bSettle ?: '—' }}</div>
                </td>
                <td>
                  <span class="text-muted">Compra #{{ $al['buy_event_id'] }}</span>
                  @if($accB)
                    <span class="ms-2 text-muted">Conta: {{ $accB }}</span>
                  @endif
                  <div class="small">{{ $al['buy_title'] }} @if(!empty($al['buy_detail'])) — <span class="text-muted">{{ $al['buy_detail'] }}</span> @endif</div>
                </td>
                <td class="text-end"><strong>{{ number_format($al['qty'],6,',','.') }}</strong></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endforeach
</div>
@endsection
