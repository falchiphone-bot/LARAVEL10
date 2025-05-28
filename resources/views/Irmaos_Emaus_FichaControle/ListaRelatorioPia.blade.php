@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;font-size: 24px;lign=˜Center˜">
                    RELATÓRIO PIA PARA SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>
            </div>


            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    {{ session(['success' =>  null ]) }}

                @elseif (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    {{ session(['error' => NULL])}}

                @endif

                {{-- <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                    <a class="btn btn-warning" href="Cadastros">Retornar a lista de opções</a> </nav> --}}


                {{-- @can('IRMAOS_EMAUS_NOME_SERVICO - RELATÓRIO ENTRADAS E SAIDAS')
                    <a href="{{ route('Irmaos_EmausServicos.create') }}" class="btn btn-danger btn-lg enabled"
                     tabindex="-1" role="button"
                        aria-disabled="true">Incluir NOME DO SERVIÇO</a>
                @endcan --}}

                <div class="card-header">
                    <div class="badge bg-info text-wrap" style="width: 100%;font-size: 24px">
                        <p>Total de RELATORIO PIA no sistema de gerenciamento administrativo e contábil:
                            {{ $model->count() ?? 0 }}</p>
                    </div>
                </div>
                    <div class="card-header">
                        <div class="badge bg-secondary text-wrap" style="width: 100%;font-size: 24px">
                            <p>Nome:
                             {{ $nomeFichaControle }}
                            </p>
                            @can('IRMAOS_EMAUS_FICHA_CONTROLE - VER')
                                    <td>
                                        <a href="{{ route('Irmaos_Emaus_FichaControle.show', $idFichaControle) }}" class="btn btn-info" tabindex="-1"
                                            role="button" aria-disabled="true">Retornar para a ficha</a>
                                    </td>
                                @endcan
                        </div>
                     </div>


            </div>

            <tbody>
                <table class="table" style="background-color: rgb(243, 236, 145);">
                    <thead>
                        <tr>
                            {{-- <th scope="col" class="px-6 py-4">NOME</th> --}}
                            <th scope="col" class="px-6 py-4">Tópico PIA</th>
                            <th scope="col" class="px-6 py-4">Data</th>
                            <th scope="col" class="px-6 py-4">CADASTRADO POR:</th>
                            <th scope="col" class="px-6 py-4">CADASTRADO EM:</th>
                            <th scope="col" class="px-6 py-4"></th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($model as $Model)
                            <tr>

                                {{-- <td class="">
                                    {{ $Model->FichaControle->Nome ? $Model->FichaControle->Nome : '' }}
                                </td> --}}

                                <td class="">
                                    {{ $Model->Irmaos_EmausPia->nomePia ? $Model->Irmaos_EmausPia->nomePia : 'Sem id' }}
                                </td>
                                 <td class="">
                                   {{ $Model->Data ? \Carbon\Carbon::parse($Model->Data)->format('d/m/Y') : 'Sem data' }}
                                </td>


                                <td class="">
                                    {{ $Model->user_created ? $Model->user_created : 'Sem usuário' }}
                                </td>
                                 <td class="">
                                   {{ $Model->created_at ? \Carbon\Carbon::parse($Model->created_at)->format('d/m/Y H:i:s') : 'Sem data' }}
                                </td>

                                {{-- @can('IRMAOS_EMAUS_NOME_SERVICO - EDITAR')
                                    <td>
                                        <a href="{{ route('Irmaos_EmausServicos.edit', $Model->id) }}" class="btn btn-success" tabindex="-1"
                                            role="button" aria-disabled="true">Editar</a>
                                    </td>
                                @endcan --}}

                                {{-- @can('IRMAOS_EMAUS_NOME_SERVICO - VER')
                                    <td>
                                        <a href="{{ route('Irmaos_EmausServicos.show', $Model->id) }}" class="btn btn-info" tabindex="-1"
                                            role="button" aria-disabled="true">Ver</a>
                                    </td>
                                @endcan --}}

                                {{-- @can('IRMAOS_EMAUS_NOME_SERVICO - EXCLUIR')
                                    <td>
                                        <form method="POST" action="{{ route('Irmaos_EmausServicos.destroy', $Model->id)  }}">
                                            @csrf
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="btn btn-danger">
                                                Excluir
                                            </button>
                                        </form>
                                    </td>
                                @endcan --}}
                            </tr>
                            <tr>
                                <td style="background-color: #fff9c4;">
                                    Anotações: {{ $Model->Anotacoes ? $Model->Anotacoes : '' }}
                                </td>
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
