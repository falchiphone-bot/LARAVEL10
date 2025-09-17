@csrf
<div class="row g-3">
  <div class="col-md-4">
    <label class="form-label">Ano</label>
    <input type="number" name="ano" class="form-control" min="1900" max="9999" value="{{ old('ano', $model->ano ?? '') }}" required>
  </div>
</div>
<div class="mt-3 d-flex gap-2">
  <button class="btn btn-primary" type="submit">Salvar</button>
  <a class="btn btn-warning" href="{{ route('SafAnos.index') }}">Voltar</a>
</div>
