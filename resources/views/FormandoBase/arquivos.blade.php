
<div class="card-body" style="background-color: #f7e7d0;">

    {{-- @can('FORMANDOBASEARQUIVOS - INCLUIR')

    <nav class="navbar navbar-info" style="background-color: hsla(234, 92%, 47%, 0.096);">
        <a class="btn btn-info" href="/LANCAMENTOSDOCUMENTOS" style="display: inline-block;" target="_blank">Cadastro de arquivos</a>
    </nav>
   @endcan --}}

    {{-- ////////////////////////////////////  ARQUIVOS--}}
    <form method="POST" action="/FormandoBase/CreateArquivoFormandoBase" accept-charset="UTF-8">
        @csrf

        <input required
            class="form-control @error('formandobase_id') is-invalid @else is-valid @enderror d-none"
            name="formandobase_id" type="text" id="formandobase_id" value="{{ $model->id ?? null }}">


        <div class="col-6">
            <label for="Limite" style="color: black;">Incluir arquivos</label>
            <select required class="form-control select2" id="arquivo_id" name="arquivo_id">
                <option value="">
                    Selecionar arquivo
                </option>
                @foreach ($documento as $documentos)
                    <option @required(true)
                        value="{{ $documentos->id }}">
                        {{ $documentos->Rotulo }}

                    </option>
                @endforeach
            </select>

        </div>

        <div class="row mt-2">
            <div class="col-2">
                <button class="btn btn-success">Salvar arquivo</button>

            </div>
        </div>
    </form>

    <hr>

    <table>
        @if ($arquivoExiste)
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
                    <td>{{ $item->MostraArquivoNome->nome ?? null }}</td>


                    @can('FORMANDOBASEARQUIVOS - EXCLUIR')
                        <td>
                            <form method="POST" action="{{ route('FormandoBaseArquivos.destroy', $item->id) }}">
                                @csrf
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-danger">
                                    Excluir arquivo
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

