@extends('layouts.bootstrap5')
@section('content')
<div class="container-fluid">
  <h1 class="h4 mb-3">Variações Mensais Salvas</h1>
  <form method="get" class="row g-2 mb-3">
    <div class="col-auto">
      <label class="form-label mb-0 small">Ano</label>
      <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">Todos</option>
        @foreach($years as $y)
          <option value="{{ $y }}" @selected((string)$y === (string)$year)>{{ $y }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label mb-0 small">Código</label>
      <input type="text" name="code" value="{{ $code }}" class="form-control form-control-sm" placeholder="TSLA" />
    </div>
    <div class="col-auto align-self-end">
      <button class="btn btn-sm btn-primary">Filtrar</button>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-sm table-striped align-middle">
      <thead>
        <tr>
          <th>ID</th>
          <th>Código</th>
          <th>Ano</th>
          <th>Mês</th>
          <th>Variação (%)</th>
          <th>Criado</th>
          <th>Atualizado</th>
        </tr>
      </thead>
      <tbody>
        @forelse($variations as $v)
          <tr>
            <td>{{ $v->id }}</td>
            <td>{{ $v->asset_code }}</td>
            <td>{{ $v->year }}</td>
            <td>{{ str_pad($v->month,2,'0',STR_PAD_LEFT) }}</td>
            <td>{{ number_format($v->variation, 4, ',', '.') }}</td>
            <td>{{ $v->created_at?->format('Y-m-d H:i') }}</td>
            <td>{{ $v->updated_at?->format('Y-m-d H:i') }}</td>
          </tr>
        @empty
          <tr><td colspan="7" class="text-center text-muted">Nenhuma variação encontrada.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div>
    {{ $variations->links() }}
  </div>
</div>
@endsection