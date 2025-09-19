@csrf
<div class="mb-3">
  <label class="form-label">Nome</label>
  <input type="text" name="nome" value="{{ old('nome', $model->nome ?? '') }}" class="form-control" required maxlength="255">
  @error('nome')<div class="text-danger small">{{ $message }}</div>@enderror
</div>
<div class="d-flex gap-2">
  <a href="{{ route('Pix.index') }}" class="btn btn-outline-secondary">Cancelar</a>
  <button class="btn btn-primary" type="submit">Salvar</button>
</div>
