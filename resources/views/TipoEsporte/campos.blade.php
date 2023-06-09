@csrf
<div class="card">
    <div class="card-body">
        <div class="row">

            {{-- <div class="col-6">
                <label for="data">Data</label>
                <input class="form-control @error('data') is-invalid @else is-valid @enderror" name="data"
                    type="Date" id="data" value="{{$model->data??null}}">
                @error('data')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div> --}}

            <div class="col-6">
                <label for="nome">Nome</label>
                <input required class="form-control @error('nome') is-invalid @else is-valid @enderror" name="nome"
                    type="text" id="nome" value="{{$model->nome??null}}">
                @error('nome')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>




        </div>
        </div>

        <div class="row mt-2">
            <div class="col-6">
                <button class="btn btn-primary">Salvar</button>
                <a href="{{route('TipoEsporte.index')}}" class="btn btn-warning">Retornar para lista</a>
            </div>
        </div>
    </div>
</div>
