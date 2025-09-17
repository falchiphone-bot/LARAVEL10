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
        <input type="text" name="pais" class="form-control" value="{{ old('pais', $model->pais ?? 'BRASIL') }}">
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary" type="submit">Salvar</button>
    <a href="{{ route('SafFederacoes.index') }}" class="btn btn-warning">Retornar para lista</a>
    <a href="{{ route('Cadastros') }}" class="btn btn-secondary">Retornar ao menu de cadastros</a>
</div>
