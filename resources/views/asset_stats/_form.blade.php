<div class="row g-3">
  <div class="col-md-3">
    <label class="form-label">Símbolo</label>
  <input type="text" name="symbol" value="{{ old('symbol', $model->symbol ?? request('symbol')) }}" class="form-control" placeholder="Ex: PETR4" maxlength="16" style="max-width:140px;" required>
    @error('symbol')<div class="text-danger small">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-3">
    <label class="form-label">Data</label>
    <input type="date" name="date" value="{{ old('date', optional($model->date ?? null)->format('Y-m-d')) }}" class="form-control" required>
    @error('date')<div class="text-danger small">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-2">
    <label class="form-label">Média</label>
    <input type="text" name="mean" value="{{ old('mean', $model->mean ?? null) }}" class="form-control">
    @error('mean')<div class="text-danger small">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-2">
    <label class="form-label">Mediana</label>
    <input type="text" name="median" value="{{ old('median', $model->median ?? null) }}" class="form-control">
    @error('median')<div class="text-danger small">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-1">
    <label class="form-label">P5</label>
    <input type="text" name="p5" value="{{ old('p5', $model->p5 ?? null) }}" class="form-control">
    @error('p5')<div class="text-danger small">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-1">
    <label class="form-label">P95</label>
    <input type="text" name="p95" value="{{ old('p95', $model->p95 ?? null) }}" class="form-control">
    @error('p95')<div class="text-danger small">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-2">
    <label class="form-label">Fechado (opcional)</label>
    <input type="text" name="close_value" value="{{ old('close_value', $model->close_value ?? null) }}" class="form-control">
    @error('close_value')<div class="text-danger small">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-2 d-flex align-items-end">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="1" id="isAccurate" name="is_accurate"
             {{ old('is_accurate', isset($model) ? (int)($model->is_accurate ?? 0) : 0) ? 'checked' : '' }}>
      <label class="form-check-label" for="isAccurate" title="Marca se o fechamento ficou entre P5 e P95">Acurácia OK</label>
    </div>
  </div>
</div>
