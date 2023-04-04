@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;">
                    FATURAMENTOS DO SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
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
                    @can('FATURAMENTOS- INCLUIR')
                        <a href="{{ route('Faturamentos.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1"
                            role="button" aria-disabled="true">Incluir faturamento</a>
                    @endcan
                    <div class="card-header">
                        <div class="badge bg-success text-wrap" style="width: 100%;">
                        <p>Total de faturamentos no sistema de gerenciamento administrativo e contábil:
                            {{ $faturamentos->count() ?? 0 }}</p>
                        </div>
                    </div>



                </div>

                <tbody>
                    <table class="table" style="background-color: rgb(247, 247, 213);">
                        <thead>
                            <tr>
                                <th scope="col" class="px-6 py-4">EMPRESA</th>
                                <th scope="col" class="px-6 py-4">DATA</th>
                                <th scope="col" class="px-6 py-4">VALOR FATURAMENTO</th>
                                <th scope="col" class="px-6 py-4">VALOR DO IMPOSTO</th>
                                <th scope="col" class="px-6 py-4">VALOR BASE LUCRO LIQUIDO</th>
                                <th scope="col" class="px-6 py-4">PERCENTUAL LUCRO LIQUIDO</th>
                                <th scope="col" class="px-6 py-4">LUCRO LIQUIDO</th>
                                <th scope="col" class="px-6 py-4">LANÇADO POR</th>
                                <th scope="col" class="px-6 py-4"></th>
                                <th scope="col" class="px-6 py-4"></th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($faturamentos as $Fatura)
                                <tr>
                                    <td class="">
                                         
                                        {{$Fatura->empresarelacionada->Descricao}}
                                        </a>
                                    </td>
                                    <td class="">
                                        {{ $Fatura->data->format('d/m/Y')  }}
                                    </td>
                                    <td class="">
                                        {{ $Fatura->ValorFaturamento}}
                                    </td>
                                    <td class="">
                                        {{ $Fatura->ValorImposto}}
                                    </td>
                                    <td class="">
                                        {{ $Fatura->ValorBaseLucroLiquido}}
                                    </td>

                                    <td class="">
                                        {{ $Fatura->PercentualLucroLiquido}}
                                    </td>
                                    <td class="">
                                        {{ $Fatura->LucroLiquido}}
                                    </td>
                                    <td class="">
                                        {{ $Fatura->LancadoPor}}
                                    </td>
                                    @can('FATURAMENTOS EDITAR')
                                        <td>
                                            <a href="{{ route('Faturamentos.edit', $Fatura->id) }}" class="btn btn-success"
                                                tabindex="-1" role="button" aria-disabled="true">Editar</a>
                                        </td>
                                    @endcan

                                    @can('FATURAMENTOS - VER')
                                    <td>
                                        <a href="{{ route('Faturamentos.show', $Fatura->id) }}" class="btn btn-info"
                                            tabindex="-1" role="button" aria-disabled="true">Ver</a>
                                    </td>
                                    @endcan

                                    @can('FATURAMENTOS - EXCLUIR')
                                    <td>
                                        <form method="POST" action="{{ route('Faturamentos.destroy', $Fatura->id) }}">
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
