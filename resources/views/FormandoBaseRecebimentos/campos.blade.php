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
            <label for="formandobase_id" style="color: black;">Nome </label>
            <select required class="form-control select2" id="formandobase_id" name="formandobase_id">
                <option value="">Selecionar </option>
                @foreach ($formandosbase as $formandobase)
                <option @required(true) @if ($retorno['formandobase'] == $formandobase->id) selected @endif
                    value="{{ $formandobase->id }}">
                    {{ $formandobase->nome }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="representante_id" style="color: black;">Representante </label>
            <select required class="form-control select2" id="representante_id" name="representante_id">
                <option value="">Selecionar </option>
                @foreach ($representante as $representantes)
                <option @required(true) @if ($retorno['representante'] == $representantes->id) selected @endif
                    value="{{ $representantes->id }}">
                    {{ $representantes->nome }}
                </option>
                @endforeach
            </select>
        </div>



        <div class="row mt-2">
            <div class="col-6">
                <button class="btn btn-primary">Salvar</button>
                <a href="{{route('Posicoes.index')}}" class="btn btn-warning">Retornar para lista</a>
            </div>
        </div>
    </div>
</div>

