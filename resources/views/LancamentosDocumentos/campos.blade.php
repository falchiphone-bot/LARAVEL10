@csrf
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-1211">
                <label for="Rotulo">Nome do documento</label>
                <input required  class="form-control @error('nome') is-invalid @else is-valid @enderror" name="Rotulo"
                    type="text" id="Rotulo" value="{{$documento->Rotulo??null}}">
                @error('nome')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <div class="badge bg-info text-wrap" style="width: 100%; height: 50%; font-size: 24px;">
                    TIPO DE ARQUIVO
            </div>

                <select required class="form-control select2" id="TipoArquivo" name="TipoArquivo">
                    <option value="">Selecionar tipo de arquivo</option>
                    @foreach ($tipoarquivo as $Tipoarquivo)
                    <option @if ($retorno['TipoArquivo'] == $Tipoarquivo->id) selected @endif
                        value="{{ $Tipoarquivo->id }}">
                        {{ $Tipoarquivo->nome }}
                    </option>
                    @endforeach
                </select>
        </div>
               <div class="form-group">
                    <label for="ArquivoFisico">ARQUIVO FISICO ONDE SE ENCONTRA O DOCUMENTO</label>
                    <input required  class="form-control @error('nome') is-invalid @else is-valid @enderror" name="ArquivoFisico"
                        type="text" id="ArquivoFisico" value="{{$documento->ArquivoFisico??null}}">
                    @error('nome')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>



            {{-- <label for="nome">Observação</label>
            <input class="form-control @error('observacao') is-invalid @else is-valid @enderror" name="observacao"
                type="text" id="observacao" value="{{$LancamentosDocumentos->observacao??null}}">
            @error('observacao')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror --}}

            


        </div>

        <div class="row mt-2">
            <div class="col-6">
                <button class="btn btn-primary">Salvar</button>
                <a href="{{route('LancamentosDocumentos.index')}}" class="btn btn-warning">Retornar para lista</a>
            </div>
        </div>
    </div>
</div>
