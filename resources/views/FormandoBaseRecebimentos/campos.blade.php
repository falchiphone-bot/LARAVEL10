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

        @can('FORMANDOBASE - EDITAR')
        <td>
            <a href="{{ route('FormandoBase.edit', $model->formandobase_id) }}" class="btn btn-success" tabindex="-1"
                role="button" aria-disabled="true">Editar ficha do formando</a>
        </td>
        @endcan

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

        <div class="form-group">
            <label for="created_at" style="color: rgb(227, 15, 15);">Registrado em:</label>
            <input type="text" id="created_at" name="created_at" value="<?php echo date('d/m/Y H:i:s'); ?>">

            <label for="created_at" style="color: rgb(227, 15, 15);">Criado por:</label>
            <input type="text" id="user_created" name="created_at" value=" {{ $model->user_created }}">



            @if ($model->user_updated )
                <label for="created_at" style="color: rgb(227, 15, 15);">Alterado por:</label>
                 <input type="text" id="user_updated" name="user_updated" value=" {{ $model->user_updated }}">
                <div class="form-group">
                    <label for="updated_at" style="color: rgb(227, 15, 15);">Alterado o registro em:</label>
                    <input type="text" id="created_at" name="updated_at" value="<?php echo date('d/m/Y H:i:s'); ?>">
                </div>

            @endif
        </div>




        <div class="col-sm-2">
            <label for="data">Data do patrocínio em:</label>
            <input class="form-control @error('data') is-invalid @else is-valid @enderror" name="data"
                type="date" id="data"


                @if ($model->data)
                    value="@if ($model??null){{ $model->data->format('Y-m-d') }}@endif">
                @else
                    value="@if ($model??null){{ $model->data }}@endif">
                @endif

            @error('data')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>



        <div class="col-sm-2">
            <label for="patrocinio">Valor do patrocinio</label>
            <input required class="form-control money @error('valor') is-invalid @else is-valid @enderror" name="patrocinio"
                type="decimal" step="0.01" id="valor" value="{{ $model->patrocinio ?? null }}">
            @error('patrocinio')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>

        @if ($lancamento)
            <div class="form-group">
            <label for="lancamento_id" style="color: black;">Contabilidade</label>
            <select class="form-control select2" id="lancamento_id" name="lancamento_id">
                <option value="">Selecionar </option>
                @foreach ($lancamento as $lancamentos)
                <option @required(true) @if ($retorno['lancamentos'] == $lancamentos->ID) selected @endif
                    value="{{ $lancamentos->ID }}">
                    {{ 'Descrição do lançamento: '.$lancamentos->Descricao . ' Valor lançado: '. $lancamentos->Valor . ' em  '. $lancamentos->DataContabilidade->format('d/m/Y') }}
                </option>
                @endforeach
            </select>
        </div>
        @endif




        <div class="row mt-2">
            <div class="col-6">
                <button class="btn btn-primary">Salvar</button>
                <a href="{{route('FormandoBaseRecebimentos.index')}}" class="btn btn-warning">Retornar para lista</a>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.js"></script>
    <script>

        $(document).ready(function() {
            $('.money').mask('000.000.000.000.000,00', {reverse: true});
        });
    </script>
@endpush

