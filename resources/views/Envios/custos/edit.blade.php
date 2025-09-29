@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
  <div class="card">
    <div class="card-body">
      <h5 class="mb-3">Editar Custo</h5>
      <form method="POST" action="{{ route('Envios.custos.update', ['Envio'=>$envio->getKey(),'custo'=>$custo->getKey()]) }}">
        @csrf
        @method('PUT')
        <div class="mb-3">
          <label class="form-label">Data</label>
          <input type="date" name="data" class="form-control" value="{{ old('data', optional($custo->data)->format('Y-m-d')) }}" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Nome do Custo</label>
          <input type="text" name="nome" class="form-control" maxlength="150" required value="{{ old('nome', $custo->nome) }}">
        </div>
        <div class="mb-3">
          <label class="form-label">Valor (R$)</label>
          <input type="number" step="0.01" min="0" name="valor" class="form-control" required value="{{ old('valor', $custo->valor) }}">
        </div>
        <button class="btn btn-primary">Salvar</button>
        <a href="{{ route('Envios.edit', ['Envio'=>$envio->getKey()]) }}" class="btn btn-secondary ms-2">Voltar</a>
      </form>
      <form method="POST" action="{{ route('Envios.custos.destroy', ['Envio'=>$envio->getKey(),'custo'=>$custo->getKey()]) }}" class="mt-3" onsubmit="return confirm('Tem certeza que deseja excluir este custo?');">
        @csrf
        @method('DELETE')
        <button class="btn btn-danger">Excluir</button>
      </form>
    </div>
  </div>
</div>
@endsection
