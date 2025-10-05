@extends('layouts.bootstrap5')
@section('content')
<div class="container" style="max-width:780px">
  <h1 class="h5 mb-3">Importar Holdings (CSV)</h1>
  <div class="card shadow-sm">
    <form method="post" action="{{ route('holdings.import.store') }}" enctype="multipart/form-data">
      @csrf
      <div class="card-body">
        @if(session('success'))
          <div class="alert alert-success py-2 small">{{ session('success') }}</div>
        @endif
        @if($errors->any())
          <div class="alert alert-danger py-2 small">
            <ul class="mb-0">
              @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
          </div>
        @endif
        <p class="small text-muted mb-2">Formato esperado (detecção automática de cabeçalho):<br><code>Codigo;Quantidade;Preço Médio;Investido;Moeda</code> ou variantes (&ldquo;Ticker&rdquo;, &ldquo;Qty&rdquo;, &ldquo;Avg Price&rdquo;, &ldquo;Investido&rdquo;, &ldquo;Currency&rdquo;). Delimitador ; ou ,. Agora também é possível colar o conteúdo direto.</p>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label small">Arquivo CSV</label>
            <input type="file" name="csv" class="form-control form-control-sm" accept=".csv,text/csv" />
            <div class="form-text">Opcional se usar campo de colar conteúdo.</div>
          </div>
          <div class="col-md-6">
            <label class="form-label small">Conta destino</label>
            <select name="account_id" class="form-select form-select-sm">
              <option value="">— Selecionar existente ou criar nova —</option>
              @foreach($accounts as $acc)
                <option value="{{ $acc->id }}">{{ $acc->account_name }} @if($acc->broker) ({{ $acc->broker }}) @endif</option>
              @endforeach
            </select>
          </div>
          <div class="col-12">
            <label class="form-label small">Colar conteúdo CSV (opcional)</label>
            <textarea name="csv_raw" class="form-control form-control-sm" rows="6" placeholder="Codigo;Quantidade;Preço Médio;Investido;Moeda\nAAPL;10;150,25;1502,50;USD"></textarea>
            <div class="form-text">Se preenchido, substitui o arquivo. Aceita ; ou , como separador.</div>
          </div>
          <div class="col-md-6">
            <label class="form-label small">Criar nova conta (opcional)</label>
            <input type="text" name="create_account_name" class="form-control form-control-sm" placeholder="Nome da Conta" />
          </div>
          <div class="col-md-6">
            <label class="form-label small">Corretora (nova conta)</label>
            <input type="text" name="create_account_broker" class="form-control form-control-sm" placeholder="Avenue" />
          </div>
          <div class="col-md-6">
            <label class="form-label small">Modo de mescla</label>
            <select name="mode_merge" class="form-select form-select-sm">
              <option value="replace">Substituir (override)</option>
              <option value="sum">Somar (ajustar quantidade e novo PM)</option>
            </select>
          </div>
        </div>
      </div>
      <div class="card-footer d-flex justify-content-between">
        <a href="{{ route('openai.portfolio.index') }}" class="btn btn-sm btn-secondary">Voltar</a>
        <button class="btn btn-sm btn-primary">Importar</button>
      </div>
    </form>
  </div>
  <div class="mt-3 small">
    <strong>Notas:</strong>
    <ul class="mb-2">
      <li>Ignora linhas sem código ou com quantidade zero.</li>
      <li>Se modo "Somar": recalcula preço médio ponderado pelas quantidades e agrega Investido.</li>
      <li>Se Investido vazio: usa Quantidade * Preço Médio.</li>
      <li>Moeda opcional; caso não venha, mantenha vazia ou ajuste depois.</li>
    </ul>
  </div>
</div>
@endsection
