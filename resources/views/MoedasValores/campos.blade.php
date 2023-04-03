@csrf
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-6">
                <label for="data">Data</label>
                <input class="form-control @error('data') is-invalid @else is-valid @enderror" name="data"
                    type="date" id="data" value="{{$moedasvalores->data??null}}">
                @error('data')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <label for="idmoeda">Moeda</label>
            <input class="form-control @error('idmoeda') is-invalid @else is-valid @enderror" name="idmoeda"
                type="number" id="idmoeda" value="{{$moedasvalores->idmoeda??null}}">
            @error('idmoeda')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror

            <label for="valor">Valor</label>
            <input class="form-control @error('valor') is-invalid @else is-valid @enderror" name="valor"
                type="decimal" id="valor" value="{{$moedasvalores->valor??null}}">
            @error('valor')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror

        </div>
        </div>

        <div class="row mt-2">
            <div class="col-6">
                <button class="btn btn-primary">Salvar</button>
                <a href="{{route('MoedasValores.index')}}" class="btn btn-warning">Retornar para lista</a>
            </div>
        </div>
    </div>
</div>
