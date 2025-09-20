@csrf
<div class="card">
    <div class="card-body">
        <div class="row">

            <div class="col-6">
                <label for="data">Data</label>
                <input class="form-control @error('data') is-invalid @else is-valid @enderror" name="data"
                    type="date" id="data" value="{{ old('data', optional(data_get($feriados ?? null, 'data'))->format('Y-m-d')) }}">
                @error('data')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-6">
                <label for="nome">Nome</label>
                <input class="form-control @error('nome') is-invalid @else is-valid @enderror" name="nome"
                    type="text" id="nome" value="{{ old('nome', data_get($feriados ?? null, 'nome')) }}">
                @error('nome')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>


        </div>
        </div>

        <div class="row mt-2">
            <div class="col-6">
                <button class="btn btn-primary">Salvar</button>
                <a href="{{route('Feriados.index')}}" class="btn btn-warning">Retornar para lista</a>
            </div>
        </div>
    </div>
</div>
