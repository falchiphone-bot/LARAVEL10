@extends('layouts.bootstrap5')
@section('content')
<div class="container" style="max-width:760px">
  <h1 class="h5 mb-3">Atualizar via Avenue Screen</h1>
  <div class="card shadow-sm">
    <form method="post" action="{{ route('holdings.screen.quick.store') }}">
      @csrf
      <div class="card-body">
        @if(session('success'))
          <div class="alert alert-success py-2 small mb-2">{{ session('success') }}</div>
        @endif
        @if($errors->any())
          <div class="alert alert-danger py-2 small mb-2">
            <ul class="mb-0">
              @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
          </div>
        @endif
  <p class="small text-muted mb-2">Cole exatamente o bloco copiado da tela da Avenue (incluindo linhas como <code>Logo de ...</code>). O parser extrai ticker, quantidade, preço médio, investido e preço atual. Se a <strong>primeira linha não vazia</strong> for um e-mail (<code>sem@falchi.com.br</code> ou <code>falchiphone@gmail.com.br</code>) ele deve corresponder ao nome ou corretora da conta selecionada.</p>
        <div class="mb-3">
          <label class="form-label small">Conta destino</label>
          <select name="account_id" class="form-select form-select-sm" required>
            <option value="">— selecione —</option>
            @foreach($accounts as $acc)
              <option value="{{ $acc->id }}">{{ $acc->account_name }} @if($acc->broker) ({{ $acc->broker }}) @endif</option>
            @endforeach
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label small">Bloco Avenue Screen</label>
          <textarea name="screen_raw" class="form-control form-control-sm" rows="12" placeholder="Cole aqui..." required></textarea>
        </div>
        <div class="row g-2 mb-2">
          <div class="col-md-4">
            <label class="form-label small">Modo</label>
            <input type="text" readonly class="form-control form-control-sm" value="replace" />
          </div>
          <div class="col-md-8 small text-muted d-flex align-items-end">
            Sempre substitui quantidade / preço médio / investido / preço atual para os tickers da conta.
          </div>
        </div>
      </div>
      <div class="card-footer d-flex justify-content-between">
        <a href="{{ route('openai.portfolio.index') }}" class="btn btn-sm btn-secondary">Voltar</a>
        <button class="btn btn-sm btn-primary">Processar</button>
      </div>
    </form>
  </div>
  <div class="mt-3 small">
    <strong>Notas:</strong>
    <ul class="mb-0">
      <li>Ignora blocos incompletos (sem quantidade ou preço médio).</li>
      <li>Se posição existir (inclusive soft-deletada) ela é atualizada/restaurada.</li>
      <li>Moeda fixada em USD neste parser.</li>
    </ul>
  </div>
</div>
@endsection
