
<div class="card-body" style="background-color: #000080; color: white;">


    {{-- ////////////////////////////////////  AVALIAÇÃO --}}
    <form method="POST" action="/FormandoBase/CreateAvaliacaoFormandoBase" accept-charset="UTF-8">
        @csrf

        <input required
            class="form-control @error('formandobase_id') is-invalid @else is-valid @enderror d-none"
            name="formandobase_id" type="text" id="formandobase_id" value="{{ $model->id ?? null }}">

            <div class="form-group">
            <label for="avaliacao">Avaliação em número de 1,00 a 10,00. Quanto mais alto melhor.</label>
            <!-- <input required class="form-control @error('avaliacao') is-invalid @else is-valid @enderror" name="avaliacao" -->
            <!-- type="number" step="0.01" id="avaliacao" min="1" max="10" value="{{ number_format($model->avaliacao ?? null, 2, '.', '') }}"> -->

            <input required class="form-control money @error('avaliacao') is-invalid @else is-valid @enderror" name="avaliacao"
                    type="decimal" step="0.01" id="avaliacao" min="1" max="10" value="{{ $mmodel->avaliacao ?? null }}">


            @error('avaliacao')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>


        <div class="row mt-2">
            <div class="col-2">
                <button class="btn btn-warning">Salvar avaliação</button>

            </div>
        </div>
    </form>

    <hr>

    <table style="background-color: #FFDDDD; color: black;">
        @if ($avaliacaoExiste)
            <tr>
                <th>Avaliação(ões) data</th>
                <th>Nota</th>
            </tr>



            @foreach ($FormandoBaseAvaliacao as $item)
                <style>
                    table {
                        border-collapse: collapse;
                        width: 100%;
                    }

                    th,
                    td {
                        border: 1px solid black;
                        padding: 8px;
                    }

                    th {
                        background-color: #f2f2f2;
                    }
                </style>


                <tr>


                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i:s') }}</td>

                <td> {{  number_format($item->avaliacao,2) }} </td>

                    @can('FORMANDOBASEAVALIACAO - EXCLUIR')
                        <td>
                            <form method="POST" action="{{ route('FormandoBaseAvaliacao.destroy', $item->id) }}">
                                @csrf
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-danger">
                                    Excluir avaliacao
                                </button>
                            </form>
                        </td>
                    @endcan
                </tr>
            @endforeach

        @endif
    </table>


    {{-- //////////////////////////////////// FIM AVALIAÇÃO --}}
</div>

