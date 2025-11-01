@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 mb-0">IBKR • Importar Contas (JSON)</h1>
    <div class="d-flex gap-2">
      <a href="{{ route('ibkr.api-web') }}" class="btn btn-outline-secondary btn-sm">Api Web IBKR</a>
      <a href="{{ route('ibkr.accounts') }}" class="btn btn-outline-primary btn-sm">Ver Contas (app)</a>
    </div>
  </div>

  <div class="alert alert-info small">
    Cole o JSON obtido em <code>/v1/api/portfolio/accounts</code> (gateway) ou envie um arquivo <code>.json</code>. Opcionalmente, salve uma cópia em <code>storage/app/tmp/ibkr</code>.
  </div>

  <form method="POST" action="{{ route('ibkr.import.accounts.process') }}" enctype="multipart/form-data" class="card">
    @csrf
    <div class="card-body">
      @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      @endif
      <div class="mb-3">
        <label for="json" class="form-label">JSON (colar)</label>
        <textarea name="json" id="json" rows="8" class="form-control" placeholder='[{"accountId":"..."}]'>{{ old('json') }}</textarea>
      </div>
      <div class="mb-3">
        <label for="json_file" class="form-label">ou Arquivo .json</label>
        <input type="file" accept="application/json,.json" name="json_file" id="json_file" class="form-control" />
      </div>
      <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" id="save" name="save" value="1">
        <label for="save" class="form-check-label">Salvar cópia em storage (tmp/ibkr)</label>
      </div>
      <button type="submit" class="btn btn-primary">Importar e visualizar</button>
    </div>
  </form>
</div>
@endsection
