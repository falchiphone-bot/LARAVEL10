
<div class="card-body" style="background-color: #f7e7d0;">


    {{-- ////////////////////////////////////  ARQUIVOS--}}
    {{-- <form method="POST" action="/Lancamentos/createArquivoDocumentos" accept-charset="UTF-8"> --}}
    <form method="POST" action="{{ route('lancamentos.ArquivoLancamentoDocumentos') }}" accept-charset="UTF-8">

           @csrf

            @can('LANCAMENTOS DOCUMENTOS - LISTAR')
                            <tr>
                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-danger" href="/LancamentosDocumentos">Enviar documentos</a>
                                    </nav>

                                </th>
                            </tr>
             @endcan

             <input required
             class="form-control @error('documento_id') is-invalid @else is-valid @enderror d-none"
             name="documento_id" type="text" id="documento_id" value="{{ $documento->ID }}">


        <div class="col-6">
            {{-- <label for="arquivo_id" style="color: black;">Incluir arquivos</label>
            <input type="text" id="buscaArquivo" placeholder="Buscar arquivo..." class="form-control mb-2">
            <button type="button" onclick="document.getElementById('buscaArquivo').value=''; document.getElementById('arquivo_id').options.style.display='';">Limpar busca</button> --}}


            <select required class="form-control select2" id="arquivo_id_vinculo" name="arquivo_id_vinculo">
                <option value="">Selecionar arquivo</option>
                @foreach ($documentoListado as $documentoListados)
                    <option value="{{ $documentoListados->ID }}">
                        @if ($documentoListados->TipoArquivoNome)
                            {{ $documentoListados->Rotulo . " ->> " . $documentoListados->TipoArquivoNome->nome }}
                        @else
                            {{ $documentoListados->Rotulo }}
                        @endif
                    </option>
                @endforeach
            </select>
        </div>


            <div class="col-2">
                <button class="btn btn-success">Salvar arquivo associando a este registro de ID =   {{ $documento->ID }} .</button>
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



            @foreach ($DocumentoArquivo as $item)
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


                    {{-- <img src="https://lh3.googleusercontent.com/drive-viewer/AFGJ81pZa4Oj2S1PHjJSwO3uKmakwnnwzqpyipCoW9fQ_HdiN5fFkamKBl_FMUEJBV4scPgVVhLmEFtYRdJXtO8QXyWf5PtETw=w1292-h636" alt="{{ $item->MostraLancamentoDocumento->Rotulo }}"> --}}
                    <td>{{ $item->MostraLancamentoDocumento->TipoArquivoNome->nome ?? null  }}</td>

                    @can('CONTASPAGAR - EXCLUIR')
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


    {{-- //////////////////////////////////// FIM ARQUIVOS DE LANCAMENTOS DOCUMENTOS --}}
</div>

{{-- <script>
    document.getElementById('buscaArquivo').addEventListener('keyup', function() {
        var filter = this.value.toLowerCase();
        var select = document.getElementById('arquivo_id');
        var options = select.options;

        for (var i = 0; i < options.length; i++) {
            var optionText = options[i].text.toLowerCase();
            options[i].style.display = optionText.includes(filter) ? '' : 'none';
        }
    });
</script> --}}
