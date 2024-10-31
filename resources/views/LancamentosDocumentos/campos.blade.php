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
                    <input required  class="form-control @error('ArquivoFisico') is-invalid @else is-valid @enderror" name="ArquivoFisico"
                        type="text" id="ArquivoFisico" value="{{$documento->ArquivoFisico??null}}">
                    @error('ArquivoFisico')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="Email1Vinculado">LINK DO EMAIL VINCULADO AO DOCUMENTO</label>
                    <input  class="form-control @error('Email1Vinculado') is-invalid @else is-valid @enderror" name="Email1Vinculado"
                        type="text" id="Email1Vinculado" value="{{$documento->Email1Vinculado??null}}">
                    @error('Email1Vinculado')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>


                <div class="form-group">
                    <label for="AnotacoesGerais">ANOTAÇÕES GERAIS</label>
                    <textarea class="form-control @error('AnotacoesGerais') is-invalid @else is-valid @enderror" name="AnotacoesGerais" id="AnotacoesGerais" rows="5">{{ $documento->AnotacoesGerais ?? '' }}</textarea>
                    @error('AnotacoesGerais')
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




@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2();
    });

    $('form').submit(function(e) {
        e.preventDefault();
        $.confirm({
            title: 'Confirmar!',
            content: 'Confirma?',
            buttons: {
                confirmar: function() {
                    // $.alert('Confirmar!');
                    $.confirm({
                        title: 'Confirmar!',
                        content: 'Deseja realmente continuar?',
                        buttons: {
                            confirmar: function() {
                                // $.alert('Confirmar!');
                                e.currentTarget.submit()
                            },
                            cancelar: function() {
                                // $.alert('Cancelar!');
                            },

                        }
                    });

                },
                cancelar: function() {
                    // $.alert('Cancelar!');
                },

            }
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#cpf').inputmask('999.999.999-99', {
            clearMaskOnLostFocus: false
        });
        $('#cnpj').inputmask('99.999.999/9999-99', {
            clearMaskOnLostFocus: false
        });
    });
</script>
@endpush
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.js"></script>
<script>
    $(document).ready(function() {
        $('.money').mask('000.000.000.000.000,00', {
            reverse: true
        });
    });
</script>
@endpush
