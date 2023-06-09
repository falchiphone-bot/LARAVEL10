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

            <div class="col-3">
                <label for="Limite" style="color: black;">Tipo de esportes</label>
                <select class="form-control select2" id="tipo_esporte" name="tipo_esporte">
                    <option value="">
                        Selecionar esporte
                    </option>
                    @foreach ($TipoEsporte as $Esporte)
                        <option @if ($retorno['TipoEsporte'] == $Esporte->id) selected @endif
                            value="{{ $Esporte->id }}">

                            {{ $Esporte->nome }}
                        </option>
                    @endforeach
                </select>
            </div>




        </div>
        </div>

        <div class="row mt-2">
            <div class="col-6">
                <button class="btn btn-primary">Salvar</button>
                <a href="{{route('Posicoes.index')}}" class="btn btn-warning">Retornar para lista</a>
            </div>
        </div>
    </div>
</div>
