
<div class="card-body" style="background-color: rgb(47, 241, 96);">

    @can('FORMANDOBASERECEBIMENTOS - LISTAR')

    <nav class="navbar navbar-info" style="background-color: hsla(234, 92%, 47%, 0.096);">
        <a class="btn btn-info" href="/Recebimentos" style="display: inline-block;" target="_blank">Cadastro de recebimentos e representantes</a>
    </nav>
    @endcan

    {{-- ////////////////////////////////////  RECEBIMENTOS --}}
    <form method="POST" action="/FormandoBase/CreateRecebimentoFormandoBase" accept-charset="UTF-8">
        @csrf

        <input required
            class="form-control @error('formandobase_id') is-invalid @else is-valid @enderror d-none"
            name="formandobase_id" type="text" id="formandobase_id" value="{{ $model->id ?? null }}">


        <div class="col-6">
            <label for="Limite" style="color: black;">Incluir recebimento e representante</label>
            <select required class="form-control select2" id="representante_id" name="representante_id">
                <option value="">
                    Selecionar representante
                </option>
                @foreach ($representantes as $representante)
                    <option @required(true)
                        value="{{ $representante->id }}">
                        {{ $representante->nome }}

                    </option>
                @endforeach
            </select>

        </div>

        <div class="row mt-2">
            <div class="col-2">
                <button class="btn btn-success">Salvar recebimentos e representantes</button>

            </div>
        </div>
    </form>

    <hr>

    <table>
        @if ($recebimentoExiste)
            <tr>
                <th>Recebimento(s) / representante(s)</th>
                <th></th>
            </tr>



            @foreach ($FormandoBaseRecebimento as $item)
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
                    <td>{{ $item->MostraRepresentante->nome ?? null }}</td>


                    @can('FORMANDOBASERECEBIMENTOS - EXCLUIR')
                        <td>
                            <form method="POST" action="{{ route('FormandoBaseRecebimentos.destroy', $item->id) }}">
                                @csrf
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-danger">
                                    Excluir recebimento/representante
                                </button>
                            </form>
                        </td>
                    @endcan
                </tr>
            @endforeach

        @endif
    </table>


    {{-- //////////////////////////////////// FIM RECEBIMENTOS --}}
</div>

