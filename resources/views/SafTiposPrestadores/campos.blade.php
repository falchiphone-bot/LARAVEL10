@csrf
<div class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Nome</label>
    <input type="text" name="nome" class="form-control" value="{{ old('nome', $model->nome ?? '') }}" required>
  </div>
  <div class="col-md-4">
    <label class="form-label">Cidade</label>
    <input type="text" name="cidade" class="form-control" value="{{ old('cidade', $model->cidade ?? '') }}">
  </div>
  <div class="col-md-2">
    <label class="form-label">UF</label>
    <input type="text" name="uf" class="form-control" maxlength="2" value="{{ old('uf', $model->uf ?? '') }}">
  </div>
  <div class="col-md-4">
    <label class="form-label">País</label>
    <input type="text" name="pais" class="form-control" value="{{ old('pais', $model->pais ?? '') }}">
  </div>
    <div class="col-md-6">
      <label class="form-label">Função Profissional</label>
      <select name="funcao_profissional_id" class="form-select">
        <option value="">-- selecione --</option>
        @foreach(($funcoes ?? []) as $id => $nome)
          <option value="{{ $id }}" {{ (string)old('funcao_profissional_id', $model->funcao_profissional_id ?? '') === (string)$id ? 'selected' : '' }}>{{ $nome }}</option>
        @endforeach
      </select>
    </div>
</div>
<div class="mt-3 d-flex gap-2">
  <button class="btn btn-primary" type="submit">Salvar</button>
  <a class="btn btn-warning" href="{{ route('SafTiposPrestadores.index') }}">Voltar</a>
  @if ($errors->any())
    <div class="text-danger small ms-2">{{ $errors->first() }}</div>
  @endif
</div>
