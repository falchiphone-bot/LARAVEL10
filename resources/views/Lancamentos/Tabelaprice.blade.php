@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                @if (session('Lancamento'))
                    <div class="alert alert-success">
                        {{ session('Lancamento') }}
                    </div>
                    {{ session(['Lancamento' => null]) }}
                @endif
                <div class="badge bg-primary text-wrap" style="width: 100%;
                ;font-size: 24px; lign=˜Center˜">
                    CÁLCULO PELA TABELA PRICE NO SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>


                <div class="card-body">

                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                        <a class="btn btn-warning" href="\Contabilidade">Retornar a lista de opções</a>
                    </nav>
                </div>

                <div class="badge bg-warning text-wrap"
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
                                <td> {{ number_format($parcela['valorTotalFinanciado'], 2, ',', '.') }}</td>
                            </tr>
                        @endforeach

                    </tbody>
                    <tfoot>

                        <tr>
                            <td colspan="6">
                                <div class="badge bg-warning text-wrap"
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
