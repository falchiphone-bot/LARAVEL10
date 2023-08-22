@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;font-size: 24px;lign=˜Center˜">
                    TIPO DE ARQUIVO PARA SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
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

                <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                    <a class="btn btn-warning" href="Cadastros">Retornar a lista de opções</a> </nav>


                @can('TIPOARQUIVO - INCLUIR')
                    <a href="{{ route('TipoArquivo.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
                        aria-disabled="true">Incluir tipo de arquivo</a>
                @endcan
                <div class="card-header">
                    <div class="badge bg-info text-wrap" style="width: 100%;font-size: 24px">
                        <p>Total de tipos de arquivo cadastrados no sistema de gerenciamento administrativo e contábil:
                            {{ $model->count() ?? 0 }}</p>
                    </div>
                </div>
                @can('LANCAMENTOS DOCUMENTOS - LISTAR')
                        <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                            <a class="btn btn-primary" href="/LancamentosDocumentos">Últimos 100 documentos enviados</a>
                        </nav>
                    @endcan
            </div>

            <tbody>
                <table class="table" style="background-color: rgb(185, 240, 193);">
                    <thead>
                        <tr>
                            <th scope="col" class="px-6 py-4">NOME</th>

                            <th scope="col" class="px-6 py-4"></th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($model as $Model)
                            <tr>

                                <td class="">
                                    {{ $Model->nome }}
                                </td>


                                @can('TIPOARQUIVO - EDITAR')
                                    <td>
                                        <a href="{{ route('TipoArquivo.edit', $Model->id) }}" class="btn btn-success" tabindex="-1"
                                            role="button" aria-disabled="true">Editar</a>
                                    </td>
                                @endcan

                                @can('TIPOARQUIVO - VER')
                                    <td>
                                        <a href="{{ route('TipoArquivo.show', $Model->id) }}" class="btn btn-info" tabindex="-1"
                                            role="button" aria-disabled="true">Ver</a>
                                    </td>
                                @endcan

                                @can('TIPOARQUIVO - EXCLUIR')
                                    <td>
                                        {{-- <form method="POST" action="{{ route('TipoArquivo.destroy', $Model->id)->with($Model->nome) }}">
                                            @csrf
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="btn btn-danger">
                                                Excluir
                                            </button>
                                        </form> --}}


                                        <form method="POST" action="{{ route('TipoArquivo.destroy', [$Model->id]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="nome" value="{{ $Model->nome }}">
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
