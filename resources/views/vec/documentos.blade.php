
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DOCUMENTOS / VIDEOS do VEC</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">

    @include('vec.header')

    <section class="max-w-4xl mx-auto py-12 px-6">
        <h2 class="text-4xl font-bold text-center mb-6">DOCUMENTOS/VIDEOS</h2>


    <section class="bg-gray-800 py-12 px-6">
        <div class="max-w-4xl mx-auto">
            {{-- <h3 class="text-3xl font-bold text-center text-red-500">Nossos Valores</h3> --}}

            <p class="text-gray-300 text-lg text-center mt-4">
                {{-- {{ $mensagem->mensagem ?? 'Nenhuma mensagem disponível' }} --}}

                <div class="card-body">
                    <div class="card-header">
                        <div class="badge bg-info text-wrap" style="width: 100%;font-size: 24px">
                            <p>Total listados
                                {{ $documentos->count() ?? 0 }}</p>
                        </div>
                    </div>
            </div>



            <div class="card">
                <div class="table-responsive">
                    <table class="table table-bordered" style="background-color: rgb(220, 11, 11);">
                        <thead>
                            <tr>
                                <th scope="col" class="px-22 py-2">Identificação</th>
                                <th scope="col" class="px-6 py-2"></th>
                                <th scope="col" class="px-2 py-2"></th>
                                <th scope="col" class="px-2 py-2"></th>
                                <th scope="col" class="px-2 py-2"></th>

                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($documentos as $documento)
                                <tr>
                                    <td class="overflow-hidden" style="max-width: 200px;">
                                        {{ \Illuminate\Support\Str::limit($documento->Rotulo, 100) }}
                                        <!-- Truncate label and limit to 50 characters -->
                                        {{-- @if (isset($documento->ArquivoFisico))
                                            <p>
                                                <strong>Arquivo físico:</strong>
                                                {{ $documento->ArquivoFisico }}
                                            </p>
                                        @endif
                                        @if ($documento->TipoArquivoNome)
                                            <p>
                                                Tipo do arquivo:
                                                <span style="color: red;">{{ $documento->TipoArquivoNome->nome }}</span>
                                            </p>

                                            @if (isset($documento->TipoArquivoNome) && Str::contains($documento->TipoArquivoNome->nome, 'FORMANDO'))
                                                @can('FORMANDOBASE - LISTAR')
                                                    <a href="{{ route('FormandoBase.index') }}" class="btn btn-secondary"
                                                        tabindex="-1" role="button" aria-disabled="true">Formando</a>
                                                @endcan
                                            @endif
                                        @endif --}}

                                        <div class="card">
                                            <div class="card-body">

                                                @if(!empty($documento->Email1Vinculado))
                                                    <p class="card-text">
                                                        <strong>Email 1 vinculado:</strong>
                                                        <a href="{{ $documento->Email1Vinculado }}" target="_blank">{{ $documento->Email1Vinculado }}</a>
                                                    </p>
                                                @endif


                                            </div>
                                            @if(!empty($documento->Nome))
                                                <div class="form-group">
                                                    {{-- Visualizar o documento: <a href="https://drive.google.com/file/d/{{ $documento->MostraLancamentoDocumento->Nome ?? null }}/view??usp=sharing" target="_blank">{{ $documento->MostraLancamentoDocumento->Rotulo ?? null }}</a> --}}
                                                    <strong>Visualizar:</strong> <a href="https://drive.google.com/file/d/{{ $documento->Nome ?? null }}/view??usp=sharing"
                                                        target="_blank">{{ $documento->Rotulo ?? null }}</a>
                                                </div>
                                            @endif


                                                @if(!empty($documento->NomeLocalTimeStamps))
                                                    <div class="form-group">
                                                        {{-- Visualizar o documento: <a href="https://drive.google.com/file/d/{{ $documento->MostraLancamentoDocumento->Nome ?? null }}/view??usp=sharing" target="_blank">{{ $documento->MostraLancamentoDocumento->Rotulo ?? null }}</a> --}}
                                                        <strong>Visualizar o documento:</strong> <a href="/storage/arquivospublicos/{{ $documento->NomeLocalTimeStamps . '.'.$documento->Ext ?? null }}" target="_blank">{{ $documento->Rotulo ?? null }}</a>
                                                    </div>
                                                @endif

                                       </div>


                                    </td>

                                    <td class="">{{ $documento->LancamentoID }}</td>
                                </tr>

                                <tr>

                                    <td colspan="5">
                                        <p>
                                            <strong>____________________________________________________________________________________</strong>
                                        </p>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    </div>

            </p>
        </div>
    </section>
</body>
</html>
