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
                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                        <a class="btn btn-warning" href="/Contabilidade">Retornar a lista de opções</a> </nav>




                    @can('FATURAMENTOS - INCLUIR')
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


                <nav class="navbar navbar-secondary" style="background-color: hsla(244, 92%, 27%, 0.096);">
                    <form action="{{ route('Faturamentos.selecaoperiodoempresa') }}" method="get">
                            <label for="data_vencimento_inicial">Data inicial:</label>
                            <input required type="date" id="data_vencimento_inicial" name="data_vencimento_inicial">

                            <label for="data_vencimento_final">Data final:</label>
                            <input required type="date" id="data_vencimento_final" name="data_vencimento_final">

                            <div class="col-12">
                                <label for="EmpresaID" style="color: black;">Empresas disponíveis</label>
                                <select required class="form-control select2" id="EmpresaID" name="EmpresaID"> --}}
                                    <option value="">
                                        Selecionar empresa
                                    </option>
                                    @foreach ($empresas as $EmpresasSelecionar)
                                        <option

                                            value="{{ $EmpresasSelecionar->ID }}">
                                            {{ $EmpresasSelecionar->Descricao }}
                                        </option>
                                    @endforeach


                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">Pesquisar/selecionar por data</button>
                    </form>
            </nav>



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


                            @php
                                $totalFaturamento = 0;
                                $totalImposto = 0;
                                $totalBaseLucroLiquido = 0;
                                $totalLucroLiquido = 0;
                           @endphp
                            @foreach ($faturamentos as $Fatura)
                                <tr>
                                    <td style='text-align:right'>

                                        {{$Fatura->empresarelacionada->Descricao}}
                                        </a>
                                    </td>
                                    <td style='text-align:right'>
                                        {{ $Fatura->data->format('d/m/Y')  }}
                                    </td>
                                    <td style='text-align:right'>
                                        {{ number_format($Fatura->ValorFaturamento,2,",", ".")}}
                                    </td>
                                    <td style='text-align:right'>
                                        {{    number_format( $Fatura->ValorImposto,2,",", ".")}}
                                    </td>
                                    <td style='text-align:right'>
                                        {{  number_format($Fatura->ValorBaseLucroLiquido,2,",", ".")}}
                                    </td>

                                    <td style='text-align:right'>
                                        {{  number_format($Fatura->PercentualLucroLiquido,2,",", ".")}}

                                    </td>
                                    <td style='text-align:right'>
                                        {{  number_format($Fatura->LucroLiquido,2,",", ".")}}
                                    </td>
                                    <td style='text-align:right'>
                                        {{ $Fatura->LancadoPor}}
                                    </td>


                                    @can('FATURAMENTOS - EDITAR')
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


                                @php
                                    $totalFaturamento += $Fatura['ValorFaturamento'];
                                    $totalImposto += $Fatura['ValorImposto'];
                                    $totalBaseLucroLiquido += $Fatura['ValorBaseLucroLiquido'];
                                    $totalLucroLiquido += $Fatura['LucroLiquido'];
                                @endphp

                            @endforeach
                        </tbody>



                    <tr>
                        <td style='text-align:right'>

                        </td>
                        <td style='text-align:right'>
                                TOTAL
                        </td>
                        <td style='text-align:right'>
                            {{  number_format($totalFaturamento,2,",", ".")}}
                        </td>
                        <td style='text-align:right'>
                            {{  number_format($totalImposto,2,",", ".")}}
                        </td>
                        <td style='text-align:right'>
                            {{  number_format($totalBaseLucroLiquido,2,",", ".")}}
                        </td>
                        <td style='text-align:right'>

                        </td>
                        <td style='text-align:right'>
                            {{  number_format($totalLucroLiquido,2,",", ".")}}
                        </td>
                    </tr>

                    <tr>
                        <td style='text-align:right'>

                        </td>
                        <td style='text-align:right'>

                        </td>
                        <td style='text-align:right'>

                        </td>
                        <td style='text-align:right'>
                            {{  number_format(($totalImposto/$totalFaturamento)*100,2,",", ".")}}%
                        </td>
                        <td style='text-align:right'>

                        </td>
                        <td style='text-align:right'>

                        </td>
                        <td style='text-align:right'>
                            {{  number_format(($totalLucroLiquido/$totalFaturamento)*100,2,",", ".")}}%
                        </td>
                    </tr>

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
