@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h4 mb-0 d-flex align-items-center gap-2">
      Ordens
      <x-market.badge storageKey="orders.localBadge.visible" idPrefix="orders" />
    </h1>
    <div class="d-flex gap-2">
      <a href="{{ route('openai.records.index') }}" class="btn btn-outline-secondary">← Registros</a>
      <a href="{{ route('openai.chat') }}" class="btn btn-outline-dark">Chat</a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success py-2">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger py-2">{{ session('error') }}</div>
  @endif

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form class="row g-2 align-items-end" method="GET" action="{{ route('openai.orders.index') }}">
        <div class="col-sm-4 col-md-3">
          <label class="form-label small mb-1">Conversa</label>
          <select name="chat_id" class="form-select form-select-sm">
            <option value="">Todas</option>
            @foreach($chats as $c)
              <option value="{{ $c->id }}" {{ ($chatId ?? 0) == $c->id ? 'selected' : '' }}>{{ $c->title }} @if($c->code) ({{ $c->code }}) @endif</option>
            @endforeach
          </select>
        </div>
        <div class="col-sm-3 col-md-3">
          <label class="form-label small mb-1">Código</label>
          <input type="text" name="code" value="{{ $code ?? '' }}" class="form-control form-control-sm" placeholder="Ticker/código">
        </div>
        <div class="col-sm-3 col-md-2">
          <label class="form-label small mb-1">Tipo</label>
          <select name="type" class="form-select form-select-sm">
            <option value="">Todos</option>
            <option value="compra" {{ ($type ?? '')==='compra' ? 'selected' : '' }}>Compra</option>
            <option value="venda"  {{ ($type ?? '')==='venda'  ? 'selected' : '' }}>Venda</option>
          </select>
        </div>
        <div class="col-sm-2 col-md-2 d-grid">
          <button class="btn btn-sm btn-outline-primary">Filtrar</button>
        </div>
        @if(request()->hasAny(['chat_id','code','type']) && (request('chat_id')||request('code')||request('type')))
          <div class="col-sm-2 col-md-2">
            <a href="{{ route('openai.orders.index') }}" class="btn btn-sm btn-outline-dark w-100">Limpar</a>
          </div>
        @endif
      </form>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-sm table-bordered align-middle">
      <thead class="table-dark">
        <tr>
          <th style="width:18%">Código</th>
          <th style="width:12%">Tipo</th>
          <th style="width:14%" class="text-end">Quantidade</th>
          <th style="width:14%" class="text-end">Valor</th>
          <th style="width:22%">Conta</th>
          <th>Conversa</th>
          <th style="width:18%">Criado em</th>
          <th style="width:16%" class="text-center">Ações</th>
        </tr>
      </thead>
      <tbody>
        @forelse($orders as $o)
          <tr>
            <td><span class="badge bg-dark">{{ $o->code }}</span></td>
            <td>
              @php $cls = $o->type === 'compra' ? 'success' : 'danger'; @endphp
              <span class="badge bg-{{ $cls }}">{{ ucfirst($o->type) }}</span>
            </td>
            <td class="text-end">{{ rtrim(rtrim(number_format((float)$o->quantity, 6, ',', '.'), '0'), ',') }}</td>
            <td class="text-end">@if(!is_null($o->value)) {{ number_format((float)$o->value, 2, ',', '.') }} @else — @endif</td>
            <td>
              @php
                $accName = $o->derived_account_name ?? null;
                $accBroker = $o->derived_account_broker ?? null;
                if(!$accName && isset($firstUserAccount)){
                  $accName = $firstUserAccount->account_name;
                  $accBroker = $firstUserAccount->broker;
                }
              @endphp
              @if($accName)
                <span class="text-primary" title="Conta de investimento">{{ $accName }}</span>
                @if($accBroker)
                  <small class="text-danger">— {{ $accBroker }}</small>
                @endif
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
            <td>{{ $o->chat?->title ?? '—' }}</td>
            <td>
              @php $cdt = $o->created_at ? $o->created_at->timezone(config('app.timezone')) : null; @endphp
              @if($cdt)
                <span title="{{ $cdt->toIso8601String() }}">{{ $cdt->format('d/m/Y H:i:s') }}</span>
              @else — @endif
            </td>
            <td class="text-center">
              <a href="{{ route('openai.records.index', ['chat_id' => $o->chat_id]) }}" class="btn btn-sm btn-outline-secondary">Registros</a>
              <a href="{{ route('openai.records.codeOrder.edit', $o->id) }}" class="btn btn-sm btn-outline-primary">Editar</a>
              <form action="{{ route('openai.records.codeOrder.destroy', $o->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir esta ordem?');">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">Excluir</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="text-center text-muted">Nenhuma ordem.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-3">
    {{ $orders->links() }}
  </div>
</div>
@endsection

@push('scripts')
<!-- Componente x-market.badge já inclui script de status/toggle -->
@endpush
