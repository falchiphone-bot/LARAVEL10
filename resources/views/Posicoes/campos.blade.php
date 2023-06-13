@csrf
<div class="card">
    <div class="card-body">
        <div class="row">

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

                        <option @required(true) @if ($retorno['TipoEsporte'] == $Esporte->id) selected @endif
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
