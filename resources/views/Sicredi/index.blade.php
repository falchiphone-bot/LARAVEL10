@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;">
                    LISTA DE LIQUIDAÇÃO DE BOLETOS NO DIA {{ $dia }}
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
                    @can('MOEDAS- INCLUIR')
                        <a href="{{ route('Moedas.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1"
                            role="button" aria-disabled="true">Incluir nome de moedas</a>
                    @endcan
                    <div class="card-header">
                        <div class="badge bg-secondary text-wrap" style="width: 100%;">
                            <p>Total de moedas cadastradas no sistema de gerenciamento administrativo e contábil:
                                {{ count($consulta ?? null) }}</p>
                        </div>
                    </div>



                </div>

                <tbody>
                    <div class="table-responsive">
                        <table class="table" style="background-color: rgb(247, 247, 213);">
                            <thead>
                                <tr>
                                    <th scope="col" class="px-6 py-4">cooperativa</th>
                                    <th scope="col" class="px-6 py-4">codigoBeneficiario</th>
                                    <th scope="col" class="px-6 py-4">cooperativaPostoBeneficiario</th>
                                    <th scope="col" class="px-6 py-4">nossoNumero</th>
                                    <th scope="col" class="px-6 py-4">seuNumero</th>
                                    <th scope="col" class="px-6 py-4">tipoCarteira</th>
                                    <th scope="col" class="px-6 py-4">dataPagamento</th>
                                    <th scope="col" class="px-6 py-4">valor</th>
                                    <th scope="col" class="px-6 py-4">valorLiquidado</th>
                                    <th scope="col" class="px-6 py-4">jurosLiquido</th>
                                    <th scope="col" class="px-6 py-4">descontoLiquido</th>
                                    <th scope="col" class="px-6 py-4">multaLiquida</th>
                                    <th scope="col" class="px-6 py-4">abatimentoLiquido</th>
                                    <th scope="col" class="px-6 py-4">tipoLiquidacao</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($consulta['items'] as $item)
                                    <tr>
                                        <td>{{ $item['cooperativa'] }}</td>
                                        <td>{{ $item['codigoBeneficiario'] }}</td>
                                        <td>{{ $item['cooperativaPostoBeneficiario'] }}</td>
                                        <td>{{ $item['nossoNumero'] }}</td>
                                        <td>{{ $item['seuNumero'] }}</td>
                                        <td>{{ $item['tipoCarteira'] }}</td>
                                        <td>{{ $item['dataPagamento'] }}</td>
                                        <td>{{ $item['valor'] }}</td>
                                        <td>{{ $item['valorLiquidado'] }}</td>
                                        <td>{{ $item['jurosLiquido'] }}</td>
                                        <td>{{ $item['descontoLiquido'] }}</td>
                                        <td>{{ $item['multaLiquida'] }}</td>
                                        <td>{{ $item['abatimentoLiquido'] }}</td>
                                        <td>{{ $item['tipoLiquidacao'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
