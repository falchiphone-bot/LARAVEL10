@extends('layouts.bootstrap5')
@section('content')
<div class="container py-3">
  <h5 class="mb-3">Importar Caixa (CSV Avenue)</h5>
  @if(session('success'))
    <div class="alert alert-success small py-2 px-3">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger small py-2 px-3">
      <ul class="mb-0 ps-3">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif
  <div class="mb-3">
    <a href="{{ route('cash.events.index') }}#gsc.tab=0" class="btn btn-sm btn-outline-secondary">Voltar Eventos</a>
    <a href="{{ route('cash.import.form') }}#gsc.tab=0" class="btn btn-sm btn-outline-secondary">Tela Texto</a>
  </div>
  <form method="POST" action="{{ route('cash.import.csv.store') }}" enctype="multipart/form-data" class="border rounded p-3 bg-light">
    @csrf
    <div class="row g-3 align-items-end">
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Conta</label>
        <select name="account_id" class="form-select form-select-sm" required>
          <option value="">-- selecione --</option>
          @foreach($accounts as $acc)
            <option value="{{ $acc->id }}">{{ $acc->account_name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-5">
        <label class="form-label small fw-semibold">Arquivo CSV (avenue-report-statement.csv)</label>
        <input type="file" name="csv_file" accept=".csv,text/csv" class="form-control form-control-sm" required />
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-sm btn-primary mt-3 w-100">Importar CSV</button>
      </div>
    </div>
    <hr />
    <p class="small text-muted mb-1">Formato esperado das colunas (ordem):</p>
    <code class="d-block small mb-2">Data transação,Data liquidação,Descrição,Valor,Saldo</code>
    <ul class="small text-muted ps-3 mb-0">
      <li>Valores: usar ponto como separador decimal (ex.: 24.34, -7.30)</li>
      <li>Saldo: usado apenas para gerar snapshot (primeira linha = saldo atual)</li>
      <li>Atualiza categoria/status de eventos existentes</li>
    </ul>
  </form>
</div>
@endsection
