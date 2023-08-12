
<div class="card-body" style="background-color: #f7e7d0;">
    {{-- ////////////////////////////////////  ARQUIVOS--}}
    <form method="POST" action="/Preparadores/CreateArquivoPreparadores" accept-charset="UTF-8">
        @csrf

        <input required
            class="form-control @error('preparadores_id') is-invalid @else is-valid @enderror d-none"
            name="preparadores_id" type="text" id="preparadores_id" value="{{ $model->id ?? null }}">


        <div class="col-6">
            <label for="arquivo_id" style="color: black;">Incluir arquivos</label>
            <select required class="form-control select2" id="arquivo_id" name="arquivo_id">
                <option value="">
                    Selecionar arquivo
                </option>
                @foreach ($documento as $documentos)
                    <option @required(true)
                        value="{{ $documentos->ID }}">
                        @if ($documentos->TipoArquivoNome)
                          {{ $documentos->Rotulo  ." ->> ". $documentos->TipoArquivoNome->nome }}
                        @else
                             {{ $documentos->Rotulo  }}
                        @endif
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
                <th>Arquivo(s)</th>
                <th></th>
                <th>Tipo de arquivo</th>
                <th></th>
            </tr>



            @foreach ($PreparadoresArquivo as $item)
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
                    <td>
                        <a href="https://drive.google.com/file/d/{{ $item->MostraLancamentoDocumento->Nome ?? null }}/view??usp=sharing" target="_blank">{{ $item->MostraLancamentoDocumento->Rotulo ?? null }}</a>

                    </td>
                    {{-- <td>
                        <div>
                            <?php if ($item->MostraLancamentoDocumento->Ext == 'jpeg'): ?>
                                    @if(isset($item) && isset($item->MostraLancamentoDocumento->Nome))
                                    <img src="https://drive.google.com/file/d/{{ $item->MostraLancamentoDocumento->Nome }}/?usp=sharing" alt="{{ $item->MostraLancamentoDocumento->Rotulo }}">
                                    @endif

                            <?php elseif ($item->MostraLancamentoDocumento->Ext == 'pdf'): ?>
                            <iframe src="https://drive.google.com/file/d/{{ $item->MostraLancamentoDocumento->Nome ?? null }}/?usp=sharing" width="100%" height="250"></iframe>
                            <?php endif; ?>
                        </div> --}}


                      <td>{{ $item->MostraLancamentoDocumento->TipoArquivoNome->nome ?? null  }}</td>

                    @can('PREPARADORESARQUIVOS - EXCLUIR')
                        <td>
                            <form method="POST" action="{{ route('PreparadoresArquivos.destroy', $item->id) }}">
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


    {{-- //////////////////////////////////// FIM ARQUIVOS --}}
</div>


