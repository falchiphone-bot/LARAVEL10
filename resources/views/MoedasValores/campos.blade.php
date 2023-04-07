@csrf
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-2">
                <label for="data">Data</label>
                <input required class="form-control @error('data') is-invalid @else is-valid @enderror" name="data"
                    type="date" id="data"
                    value="@if ($moedasvalores ?? null) {{ $moedasvalores->data->format('Y-m-d') }} @endif">
                @error('data')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-sm-6">
                <label for="idmoeda" style="color: black;">Moedas disponíveis</label>
                <select required class="form-control select2" id="idmoeda" name="idmoeda">
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

            <div class="col-sm-2">
                <label for="valor">Valor</label>
                <input required class="form-control @error('valor') is-invalid @else is-valid @enderror" name="valor"
                    type="number" step="0.01" id="valor" value="{{ $moedasvalores->valor ?? null }}">
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

@push('scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
    <script>
        .$('.btn-selecionar-empresa').click(function() {
            ..$("#EmpresaSelecionada").val($(this).attr('data-empresaID'));
        });....$('form').submit(function(e) {
            e.preventDefault();.....$.confirm({
                title: 'Confirmar!',
                content: 'Confirma a consulta?',
                buttons: {
                    confirmar: function() {
                        . // $.alert('Confirmar!');                               .     . // $.confirm({                                    //     title: 'Confirmar!',         .              . .  .    //   content: 'Deseja realmente continuar com a exclusão? Não terá retorno.',                     .          //     buttons: {          .      .       //    confirmar: function() {                                     //             // $.alert('Confirmar!');                                     //          .   e.currentTarget.submit()  .                                   //         },                                     //         cancelar: function() {                . .     //     // $.alert('Cancelar!'); .           . . . . . .  .           //         },                                      //     }                                     // });                                   . e.currentTarget.submit()    . . . . . . .            . . . . . . .   },        cancelar: function() {   . . . . . . .   . . . . .      // $.alert('Cancelar!'); . .  . . .
                    },
                }
            });
        });
    </script>
@endpush
