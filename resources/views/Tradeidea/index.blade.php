@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;font-size: 24px;lign=˜Center˜">
                    TRADE IDEA
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

                <nav class="navbar navbar-red" style="background-color: hsla(244, 92%, 27%, 0.096);">
                    <a class="btn btn-warning" href="Cadastros">Retornar a lista de opções</a> </nav>


                @can('TRADEIDEA - IMPORTAR ARQUIVO EXCEL TRADE IDEA')
                    <a href="{{ route('Tradeidea.importarexceltradeidea') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
                        aria-disabled="true">Importar arquivo excel trade idea</a>
                @endcan

                {{-- @can('PREPARADORES - INCLUIR')
                    <a href="{{ route('Preparadores.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
                        aria-disabled="true">Incluir preparadores/professores/treinadores</a>
                @endcan --}}
                {{-- <div class="card-header">
                    <div class="badge bg-info text-wrap" style="width: 100%;font-size: 24px">
                        <p>Total de tipos de preparadores/professores/treinadores cadastrados no sistema de gerenciamento administrativo e contábil:
                            {{ $model->count() ?? 0 }}</p>
                    </div>
                </div> --}}



            </div>

            <tbody>
                <table class="table" style="background-color: rgb(185, 215, 240);">
                    <thead>
                        <tr>
                            <th scope="col" class="px-6 py-4">CLIENTE</th>
                            <th scope="col" class="px-6 py-4">ASSESSOR</th>
                            <th scope="col" class="px-6 py-4">Id</th>
                            <th scope="col" class="px-6 py-4">TRADEIDEA</th>
                            <th scope="col" class="px-6 py-4">ANALISTA</th>
                            <th scope="col" class="px-6 py-4">VALOR APORTADO</th>
                            <th scope="col" class="px-6 py-4">VALOR LIQUIDADO</th>
                            <th scope="col" class="px-6 py-4">LUCRO/PREJUIZO</th>
                            <th scope="col" class="px-6 py-4">QUANTIDADE</th>
                            <th scope="col" class="px-6 py-4">PRECO ENTRADA</th>
                            <th scope="col" class="px-6 py-4">ENTRADA</th>
                            <th scope="col" class="px-6 py-4">PRECO ENCERRAMENTO</th>
                            <th scope="col" class="px-6 py-4">ENCERRAMENTO</th>


                            <th scope="col" class="px-6 py-4">MOTIVO</th>
                            <th scope="col" class="px-6 py-4"></th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($model as $Model)
                            <tr>

                                <td class="">
                                    {{ $Model['Cliente']}}
                                </td>
                                <td class="">
                                    {{ $Model['Assessor']}}
                                </td>
                                <td class="">
                                    {{ $Model['Id_Tradeidea']}}
                                </td>

                                <td class="">
                                    {{ $Model['Tradeidea']}}
                                </td>

                                <td class="">
                                    {{ $Model['Analista']}}
                                </td>

                                <td class="">
                                    {{ $Model['Valor_aportado']}}
                                </td>

                                <td class="">
                                    {{ $Model['Valor_liquidado']}}
                                </td>

                                <td class="">
                                    {{ $Model['Lucro_prejuizo']}}
                                </td>

                                <td class="">
                                    {{ $Model['Quantidade']}}
                                </td>

                                <td class="">
                                    {{ $Model['Preco_entrada']}}
                                </td>

                                <td class="">
                                    {{ $Model['Entrada']}}
                                </td>

                                <td class="">
                                    {{ $Model['Preco_encerramento']}}
                                </td>

                                <td class="">
                                    {{ $Model['Encerramento']}}
                                </td>


                                <td class="">
                                    {{ $Model['Motivo']}}
                                </td>

                                {{-- @can('TRADEIDEA - EDITAR')
                                    <td>
                                        <a href="{{ route('Tradeidea.edit', $Model->id) }}" class="btn btn-success" tabindex="-1"
                                            role="button" aria-disabled="true">Editar</a>
                                    </td>
                                @endcan

                                @can('TRADEIDEA - VER')
                                    <td>
                                        <a href="{{ route('Tradeidea.show', $Model->id) }}" class="btn btn-info" tabindex="-1"
                                            role="button" aria-disabled="true">Ver</a>
                                    </td>
                                @endcan

                                @can('TRADEIDEA - EXCLUIR')
                                    <td>
                                        <form method="POST" action="{{ route('Tradeidea.destroy', $Model->id)->with($Model->cliente) }}">
                                            @csrf
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="btn btn-danger">
                                                Excluir
                                            </button>
                                        </form>
                                    </td>
                                @endcan --}}
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
