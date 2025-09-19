@csrf
<div class="mb-3">
  <label class="form-label">Nome</label>
  <input type="text" name="nome" class="form-control" value="{{ old('nome', $envio->nome ?? '') }}" required>
  @error('nome')<div class="text-danger small">{{ $message }}</div>@enderror
  <div class="form-text">Dê um nome para identificar este envio.</div>
  </div>
<div class="mb-3">
  <label class="form-label">Descrição</label>
  <textarea name="descricao" class="form-control" rows="3">{{ old('descricao', $envio->descricao ?? '') }}</textarea>
  @error('descricao')<div class="text-danger small">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
  <label class="form-label">Arquivos</label>
  <input type="file" name="files[]" class="form-control" multiple>
  @error('files')<div class="text-danger small">{{ $message }}</div>@enderror
  @error('files.*')<div class="text-danger small">{{ $message }}</div>@enderror
  <div class="form-text">Você pode selecionar vários arquivos (qualquer tipo) de até 100 MB cada.</div>
</div>
<div>
  <button type="submit" class="btn btn-primary">Salvar</button>
  <a href="{{ route('Envios.index') }}" class="btn btn-secondary">Cancelar</a>
</div>
