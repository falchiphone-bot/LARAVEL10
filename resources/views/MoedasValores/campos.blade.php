@csrf
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-2">
                <label for="data">Data</label>
                <input class="form-control @error('data') is-invalid @else is-valid @enderror" name="data"
                    type="date" id="data" value="@if($moedasvalores??null){{ $moedasvalores->data->format('Y-m-d') }}@endif">
                @error('data')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-6">
                <label for="idmoeda" style="color: black;">Moedas disponíveis</label>
                <select class="form-control select2" id="idmoeda" name="idmoeda">
                    <option value="">
                        Selecionar moeda
                    </option>
                    @foreach ($Moedas as $moeda)
                        <option @if ($moedasvalores ?? null) @if ($moedasvalores->idmoeda == $moeda->id) selected @endif
                            @endif
                            value="{{ $moeda->id }}">
                            {{ $moeda->nome }}
                        </option>
                    @endforeach


                </select>
            </div>

            <div class="col-2">
                <label for="valor">Valor</label>
                <input class="form-control @error('valor') is-invalid @else is-valid @enderror" name="valor"
                    type="decimal" id="valor" value="{{ $moedasvalores->valor ?? null }}">
                @error('valor')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-6">
            <button class="btn btn-primary">Salvar</button>
            <a href="{{ route('MoedasValores.index') }}" class="btn btn-warning">Retornar para lista</a>
        </div>
    </div>
</div>
</div>
