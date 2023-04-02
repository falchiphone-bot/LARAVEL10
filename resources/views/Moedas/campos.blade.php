@csrf
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-6">
                <label for="nome">Nome</label>
                <input class="form-control @error('nome') is-invalid @else is-valid @enderror" name="nome"
                    type="text" id="nome" value="{{$moedas->nome??null}}">
                @error('nome')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
             
            <label for="nome">Observação</label>
            <input class="form-control @error('observacao') is-invalid @else is-valid @enderror" name="observacao"
                type="text" id="observacao" value="{{$moedas->observacao??null}}">
            @error('observacao')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>
        </div>

        <div class="row mt-2">
            <div class="col-6">
                <button class="btn btn-primary">Salvar</button>
                <a href="{{route('Moedas.index')}}" class="btn btn-warning">Retornar para lista</a>
            </div>
        </div>
    </div>
</div>
