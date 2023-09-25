@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;font-size: 24px;lign=˜Center˜">
                    DOCUMENTOS DO SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>
            </div>


            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @elseif (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                    <a class="btn btn-warning" href="/LancamentosDocumentos/dashboard">Retornar a lista de opções</a> </nav> --}}

                @can('LANCAMENTOS DOCUMENTOS - INCLUIR')
                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                        <a class="btn btn-primary" href="/TipoArquivo" target="about_blank">Tipos de arquivos</a>
                    @endcan
                    @can('LANCAMENTOS DOCUMENTOS - INCLUIR')
                        @if (session('googleUserDrive'))
                            <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                <a class="btn btn-primary" href="/drive/UploadArquivo">Upload de arquivo para
                                    Google Drive</a>
                            </nav>
                        @else
                            <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                <a class="btn btn-success" href="/drive/google/login/">Autenticar no Google
                                    Drive</a>
                            </nav>
                        @endif
                    @endcan
                    @can('LANCAMENTOS DOCUMENTOS - LISTAR')
                        <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                            <a class="btn btn-primary" href="/LancamentosDocumentos">Últimos 100 documentos enviados</a>
                        </nav>
                    @endcan


                    <form method="POST" action="{{ route('lancamentosdocumentos.pesquisaavancada') }}"
                        accept-charset="UTF-8">
                        @csrf

                        <div class="card">
                            <div class="card-body" style="background-color: rgb(33, 244, 33)">
                                <div class="row">
                                    <div class="col-6">

                                        <label for="Texto" style="color: black;">Texto a pesquisar</label>
                                        <input class="form-control @error('Descricao') is-invalid @else is-valid @enderror"
                                            name="Texto" size="70" type="text" id="Texto"
                                            value="{{ $retorno['Texto'] ?? null }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="badge bg-info text-wrap" style="width: 100%; height: 50%; font-size: 24px;">
                                        TIPO DE ARQUIVO
                                    </div>
                                    <select class="form-control select2" id="SelecionarTipoArquivo"
                                        name="SelecionarTipoArquivo">
                                        <option value="">Selecionar tipo de arquivo</option>
                                        @foreach ($tipoarquivo as $Tipoarquivo)
                                            <option @if ($retorno['TipoArquivo'] == $Tipoarquivo->id) selected @endif
                                                value="{{ $Tipoarquivo->id }}">
                                                {{ $Tipoarquivo->nome }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <div class="row">
                                        <div class="col-6">
                                            <input type="checkbox" name="SelecionarSemContabilidade" value="1">
                                            <label for="checkbox_enviar">Documento sem vínculo contábil</label>
                                            <br>
                                            <input type="checkbox" name="SelecionarComContabilidade" value="1">
                                            <label for="checkbox_enviar">Documento com vínculo contábil</label>
                                            <br>
                                            <input type="checkbox" name="SelecionarClubeComContabilidade" value="1">
                                            <label for="checkbox_enviar">Documento com vínculo contábil via CLUBE</label>
                                            <br>
                                            <br>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <label for="ordem">Ordem:</label>
                                            <select name="ordem" id="ordem">
                                                <option value="decrescente">Ordem decrescente</option>
                                                <option value="crescente">Ordem crescente</option>
                                            </select>
                                        </div>
                                    </div>

                                    <br>
                                    <div class="row">
                                        <div class="col-3">
                                            <label for="Limite" style="color: black;">Limite de registros para
                                                retorno</label>
                                            <input class="form-control @error('limite') is-invalid @else is-valid @enderror"
                                                name="Limite" size="30" type="number" step="1" id="Limite"
                                                value="{{ $retorno['Limite'] ?? null }}">
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-6">
                                            <button class="btn btn-primary">Pesquisar conforme informações constantes do
                                                formulário</button>

                                        </div>
                                    </div>
                                </div>

                            </div>



                    </form>

                    <div class="card-header">
                        <div class="badge bg-info text-wrap" style="width: 100%;font-size: 24px">
                            <p>Total de documentos listados no sistema de gerenciamento administrativo e contábil:
                                {{ $documentos->count() ?? 0 }}</p>
                        </div>
                    </div>



            </div>

            @if (session('ContaPagarID'))
                @can('CONTASPAGAR - EDITAR')
                                            <a href="{{ route('ContasPagar.edit', session('ContaPagarID')) }}" class="btn btn-success"
                                                tabindex="-1" role="button" aria-disabled="true">Ir para a edição do registro de contas a pagar oriundo deste documento</a>
                 @endcan
             @endif


            <tbody>
                <table class="table" style="background-color: rgb(247, 247, 213);">
                    <thead>
                        <tr>
                            <th scope="col" class="px-6 py-4">Rótulo do documento</th>
                            <th scope="col" class="px-6 py-4">Identificação</th>
                            <th scope="col" class="px-6 py-4"></th>
                            <th scope="col" class="px-6 py-4"></th>
                            <th scope="col" class="px-6 py-4"></th>
                            <th scope="col" class="px-6 py-4"></th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($documentos as $documento)
                            <tr>
                                <td class="">
                                    {{ $documento->Rotulo }}
                                    @if ($documento->TipoArquivoNome)
                                        <p>
                                            Tipo do arquivo:
                                            <span style="color: red;">{{ $documento->TipoArquivoNome->nome }}</span>
                                        </p>




                                        @if (isset($documento->TipoArquivoNome) && Str::contains($documento->TipoArquivoNome->nome, 'FORMANDO'))
                                            @can('FORMANDOBASE - LISTAR')

                                        <a href="{{ route('FormandoBase.index') }}" class="btn btn-secondary" tabindex="-1"
                                            role="button" aria-disabled="true">Formando</a>
                                          @endcan
                                      @endif
                               @endif



                              </td>

                        <td class="">
                            {{ $documento->LancamentoID }}
                        </td>





                        @can('LANCAMENTOS DOCUMENTOS - EDITAR')
                            <td>
                                <a href="{{ route('LancamentosDocumentos.edit', $documento->ID) }}" class="btn btn-success"
                                    tabindex="-1" role="button" aria-disabled="true">Editar</a>
                            </td>
                        @endcan

                        @can('LANCAMENTOS DOCUMENTOS - VER')
                            @if (session('googleUserDrive'))
                                <td>
                                    <a href="{{ route('google.drive.file.consultardocumento', ['id' => $documento->Nome]) }}"
                                        class="btn btn-info" tabindex="-1" role="button" aria-disabled="true"
                                        target="_blank">Ver</a>
                                </td>
                            @endif
                        @endcan

                        @can('LANCAMENTOS DOCUMENTOS - EXCLUIR')
                            <td>
                                <form method="POST" action="{{ route('LancamentosDocumentos.destroy', $documento->ID) }}">
                                    @csrf
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn btn-danger">
                                        Excluir
                                    </button>
                                </form>
                            </td>
                        @endcan
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="badge bg-primary text-wrap" style="width: 100%;">
                </div>
        </div>

    </div>
    <div class="b-example-divider"></div>
    </div>
@endsection

@push('scripts')
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
@endpush
