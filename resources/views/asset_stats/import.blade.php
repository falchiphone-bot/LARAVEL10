@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h5 mb-0">Importar Tabela/CSV</h1>
    <a href="{{ route('asset-stats.index') }}" class="btn btn-outline-secondary">Voltar</a>
  </div>
  <div class="alert alert-info small">
    Cole abaixo a tabela correspondente ao ativo, com as colunas: Data, Média, Mediana, P5, P95. A primeira linha de cabeçalhos é opcional. Datas em dd/mm/yyyy ou yyyy-mm-dd.
  </div>
  <form method="POST" action="{{ route('asset-stats.importStore') }}">
    @csrf
    <div class="mb-3">
      <label class="form-label">Símbolo</label>
      <input type="text" class="form-control" name="symbol" value="{{ old('symbol', request('symbol','OKLO')) }}" required>
      @error('symbol')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="mb-3">
      <label class="form-label">Tabela/CSV</label>
      <textarea name="payload" rows="12" class="form-control" placeholder="Data;Média;Mediana;P5;P95\n2025-10-01;10,1;10,0;8,5;12,3">{{ old('payload') }}</textarea>
      @error('payload')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <button class="btn btn-primary">Importar</button>
  </form>
</div>
@endsection
