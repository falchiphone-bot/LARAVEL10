@csrf
<div class="card">
    <div class="card-body">
        @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        {{ session(['success' =>  null ]) }}
        @elseif (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
        {{ session(['error' => NULL])}}
        @endif
        <div class="form-group">
            <label for="nome">Nome</label>
            <input required class="form-control @error('nome') is-invalid @else is-valid @enderror" name="nome"
                type="text" id="nome" value="{{$model->nome??null}}">
            @error('nome')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="1">Observação</label>
            <input required class="form-control @error('observacao') is-invalid @else is-valid @enderror" name="observacao"
                type="text" id="observacao" value="{{$model->observacao??null}}">
            @error('nome')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="row mt-2">
            <div class="col-6">
                <button class="btn btn-primary">Salvar</button>
                <a href="{{route('AgrupamentosContas.index')}}" class="btn btn-warning">Retornar para lista</a>
            </div>
        </div>
    </div>
</div>

