@csrf
<div class="card">
    <div class="card-body">
        <div class="row">



            <div class="col-6">
                <label for="Descricao">Nome</label>
                <input class="form-control @error('Descricao') is-invalid @else is-valid @enderror" name="Descricao"
                    type="text" id="Descricao" value="{{$CentroCustos->Descricao??null}}">
                @error('Descricao')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>


        </div>
        </div>

        <div class="row mt-2">
            <div class="col-6">
                <button class="btn btn-primary">Salvar</button>
                <a href="{{route('CentroCustos.index')}}" class="btn btn-warning">Retornar para lista</a>
            </div>
        </div>
    </div>
</div>
