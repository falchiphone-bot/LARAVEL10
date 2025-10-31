@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
  <h1 class="h5 mb-3">Pré-visualização • Reclassificação em massa</h1>
  <div class="alert alert-info">
    <div><strong>De:</strong> <code>{{ $from }}</code> → <strong>Para:</strong> <code>{{ $to }}</code></div>
    <div class="small text-muted">Nada foi alterado ainda. Revise as mudanças abaixo e confirme para aplicar.</div>
  </div>

  @if(!empty($conflitos))
    <div class="alert alert-warning">
      <strong>Atenção:</strong> já existem contas com os códigos de destino abaixo. Ajuste o prefixo alvo para evitar conflitos.
      <div class="mt-2"><code>{{ implode(', ', $conflitos) }}</code></div>
    </div>
  @endif

  <div class="card mb-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm table-striped mb-0 align-middle">
          <thead>
            <tr>
              <th class="text-nowrap">ID</th>
              <th class="text-nowrap">Descrição</th>
              <th class="text-nowrap">Código atual</th>
              <th class="text-nowrap">Novo código</th>
              <th class="text-nowrap">Grau</th>
            </tr>
          </thead>
          <tbody>
            @foreach($mudancas as $m)
              <tr>
                <td>{{ $m['id'] }}</td>
                <td>{{ $m['descricao'] }}</td>
                <td><code>{{ $m['codigo_atual'] }}</code></td>
                <td><code class="text-primary">{{ $m['codigo_novo'] }}</code></td>
                <td>{{ $m['grau'] }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <form method="POST" action="{{ route('planocontas.reclass.apply') }}">
    @csrf
    <input type="hidden" name="from_prefix" value="{{ $from }}">
    <input type="hidden" name="to_prefix" value="{{ $to }}">

    <div class="d-flex gap-2">
      <a href="{{ route('PlanoContas.index') }}" class="btn btn-outline-secondary">Voltar</a>
      <button type="submit" class="btn btn-danger" @if(!empty($conflitos)) disabled @endif data-busy="1">
        Aplicar reclassificação ({{ count($mudancas) }})
      </button>
    </div>
  </form>
</div>
@endsection
