@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h5 mb-0">Ativos (sem repetição)</h1>
    <div class="d-flex gap-2">
      <a href="{{ route('openai.records.index') }}" class="btn btn-outline-secondary">← Registros</a>
    </div>
  </div>

  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <form method="GET" action="{{ route('openai.records.assets') }}" class="row g-2 align-items-end">
        <div class="col-sm-3 col-md-2">
          <label class="form-label small mb-1">De</label>
          <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
        </div>
        <div class="col-sm-3 col-md-2">
          <label class="form-label small mb-1">Até</label>
          <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
        </div>
        <div class="col-sm-3 col-md-3">
          <label class="form-label small mb-1">Conta Investimento</label>
          <input type="text" name="investment_account_id" value="{{ request('investment_account_id') }}" class="form-control form-control-sm" placeholder="(opcional) id ou 0=sem">
        </div>
        <div class="col-sm-3 col-md-2 d-grid">
          <button class="btn btn-sm btn-outline-primary">Filtrar</button>
        </div>
      </form>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-sm table-bordered align-middle">
      <thead class="table-dark">
        <tr>
          <th style="width:18%">Código</th>
          <th style="width:30%">Conversa</th>
          <th style="width:16%">Data/Hora</th>
          <th style="width:14%" class="text-end">Valor</th>
          <th style="width:14%">Conta</th>
          <th style="width:8%" class="text-center">Qtd</th>
        </tr>
      </thead>
      <tbody>
        @forelse($records as $r)
          @php $code = trim($r->chat?->code ?? '') ?: trim($r->chat?->title ?? ''); @endphp
          <tr>
            <td>
              <strong>{{ $r->chat?->code ?? '—' }}</strong>
            </td>
            <td>
              <a href="{{ route('openai.records.index', ['chat_id' => $r->chat_id]) }}" class="text-decoration-none">
                {{ $r->chat?->title ?? '—' }}
              </a>
            </td>
            <td>{{ $r->occurred_at?->format('d/m/Y H:i:s') ?? '—' }}</td>
            <td class="text-end">{{ number_format((float)$r->amount, 2, ',', '.') }}</td>
            <td>{{ $r->investmentAccount?->account_name ?? '—' }} @if($r->investmentAccount?->broker) <small class="text-muted">({{ $r->investmentAccount?->broker }})</small> @endif</td>
            <td class="text-center">{{ $counts[ $code ] ?? 1 }}</td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted">Nenhum ativo encontrado.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="alert alert-info mt-3">
    Esta é uma visualização agregada. Nenhum dado é salvo nesta tela. Em breve será possível habilitar a gravação em lote via uma tag.
  </div>
</div>
@endsection
