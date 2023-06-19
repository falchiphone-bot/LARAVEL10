<div class="card-body">

    {{-- ////////////////////////////////////  POSICOES --}}
    <form method="POST" action="/FormandoBase/CreatePosicaoFormandoBase" accept-charset="UTF-8">
        @csrf

        <input required
            class="form-control @error('formandobase_id') is-invalid @else is-valid @enderror d-none"
            name="formandobase_id" type="text" id="formandobase_id" value="{{ $model->id ?? null }}">


        <div class="col-6">
            <label for="Limite" style="color: black;">Incluir posição</label>
            <select class="form-control select2" id="posicao_id" name="posicao_id">
                <option value="">
                    Selecionar posição
                </option>
                @foreach ($Posicao as $posicoes)
                    <option @required(true)
                        value="{{ $posicoes->id }}">
                        {{ $posicoes->nome }}

                    </option>
                @endforeach
            </select>

        </div>

        <div class="row mt-2">
            <div class="col-2">
                <button class="btn btn-success">Salvar posição</button>

            </div>
        </div>
    </form>

    <hr>

    <table>
        @if ($posicaoExiste)
            <tr>
                <th>Posição(ões)</th>
                <th></th>
            </tr>



            @foreach ($FormandoBasePosicao as $item)
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
                    <td>{{ $item->MostraPosicao->nome?? null }}</td>


                    @can('FORMANDOBASEPOSICOES - EXCLUIR')
                        <td>
                            <form method="POST" action="{{ route('FormandoBasePosicoes.destroy', $item->id) }}">
                                @csrf
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-danger">
                                    Excluir posição
                                </button>
                            </form>
                        </td>
                    @endcan
                </tr>
            @endforeach

        @endif
    </table>


    {{-- //////////////////////////////////// FIM POSIÇÕES --}}
</div>
