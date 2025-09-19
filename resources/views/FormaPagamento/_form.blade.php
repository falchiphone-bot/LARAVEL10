@csrf
<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">Nome</label>
        <input type="text" name="nome" class="form-control" value="{{ old('nome', $model->nome ?? '') }}" required>
        <div class="form-text">O nome deve ser único.</div>
    </div>
    <div class="col-12">
        <button class="btn btn-primary">Salvar</button>
        <a href="{{ route('FormaPagamento.index') }}" class="btn btn-secondary">Cancelar</a>
    </div>
    @if ($errors->any())
        <div class="col-12">
            <div class="alert alert-danger mt-2">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
</div>
