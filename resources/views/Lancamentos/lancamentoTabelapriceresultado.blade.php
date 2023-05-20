@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                @if (session('Lancamento'))
                    <div class="alert alert-danger">
                        {{ session('Lancamento') }}
                    </div>
                    {{ session(['Lancamento' => null]) }}
                @endif
                <div class="badge bg-secondary text-wrap" style="width: 100%;
                ;font-size: 24px; lign=˜Center˜">
                    CÁLCULO PELA TABELA PRICE E EFETUAR LANÇAMENTOS NO SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>


                <div class="card-body">

                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                        <a class="btn btn-warning" href="\Lancamentos\lancamentoinformaprice">Retornar a lista de opções</a>
                    </nav>
                </div>

                <div class="badge bg-secondary text-wrap"
                    style="width: 100%; font-size: 24px; color: black; text-align: center;">
                    <div class="card">
                        <nav class="navbar navbar-success" style="background-color: hsla(234, 92%, 47%, 0.096);">
                            ABAIXO A TABELA CALCULADA
                        </nav>
                    </div>

                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Parcela</th>
                            <th>Amortização</th>
                            <th>Juros</th>
                            <th>Valor da parcela</th>
                            <th>Taxa de juros</th>

                            <th>Valor financiado</th>

                    </thead>
                    <tbody>
                        @foreach ($tabelaParcelas as $parcela)
                            <tr>
                                <td> {{ $parcela['Parcela'] }} </td>
                                <td> {{ number_format($parcela['Amortização'], 2, ',', '.') }} </td>
                                <td> {{ number_format($parcela['Juros'], 2, ',', '.') }} </td>
                                <td> {{ number_format($parcela['Total'], 2, ',', '.') }}</td>
                                <td> {{ number_format($parcela['taxaJuros'], 4, ',', '.') }}</td>
                                <td> {{  $parcela['valorTotalFinanciado'] }}</td>
                            </tr>
                        @endforeach

                    </tbody>
                    <tfoot>

                        <tr>
                            <td colspan="6">
                                <div class="badge bg-secondary text-wrap"
                                    style="width: 100%; font-size: 24px; color: black; text-align: center;">


                                </div>
                            </td>

                        </tr>
                        <tr>
                            <td>Total:</td>
                            <td>{{ number_format(array_sum(array_column($tabelaParcelas, 'Amortização')), 2, ',', '.') }}
                            </td>
                            <td>{{ number_format(array_sum(array_column($tabelaParcelas, 'Juros')), 2, ',', '.') }}</td>
                            <td>{{ number_format(array_sum(array_column($tabelaParcelas, 'Total')), 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>


            </div>

            <div class="row">

            </div>

            <div class="row">

            </div>

        </div>
    </div>



    </div>




    </div>
    </div>

    </div>
    <div class="b-example-divider"></div>
    </div>
@endsection
