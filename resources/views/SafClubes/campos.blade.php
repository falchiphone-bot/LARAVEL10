@csrf
<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @elseif (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label for="nome" class="form-label">Nome do clube</label>
                <input required class="form-control @error('nome') is-invalid @else is-valid @enderror" name="nome" id="nome" type="text" value="{{ $model->nome ?? null }}">
                @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label for="cidade" class="form-label">Cidade</label>
                <input class="form-control @error('cidade') is-invalid @else is-valid @enderror" name="cidade" id="cidade" type="text" value="{{ $model->cidade ?? null }}">
                @error('cidade')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
                <label for="uf" class="form-label">UF</label>
                <input class="form-control text-uppercase @error('uf') is-invalid @else is-valid @enderror" name="uf" id="uf" type="text" maxlength="2" value="{{ $model->uf ?? null }}">
                @error('uf')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label for="pais" class="form-label">País</label>
                <input class="form-control @error('pais') is-invalid @else is-valid @enderror" name="pais" id="pais" type="text" value="{{ $model->pais ?? 'BRASIL' }}">
                @error('pais')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
    <div class="card-footer">
        <button class="btn btn-primary">Salvar</button>
        <a href="{{ route('SafClubes.index') }}" class="btn btn-warning">Retornar para lista</a>
    </div>
</div>
