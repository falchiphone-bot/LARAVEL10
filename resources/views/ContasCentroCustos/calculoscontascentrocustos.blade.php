@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;font-size: 24px;lign=˜Center˜">
                    CONTAS PARA CENTRO DE CUSTOS PARA SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>
            </div>


            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    {{ session(['success' => null]) }}
                @elseif (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    {{ session(['error' => null]) }}
                @endif

                <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                    <a class="btn btn-warning" href="/CentroCustos/dashboard">Retornar a lista de opções</a> </nav>


                {{-- @can('CONTASCENTROCUSTOS- INCLUIR')
                    <a href="{{ route('ContasCentroCustos.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
                        aria-disabled="true">Incluir contas no centro de custos</a>
                @endcan
                <div class="card-header">
                    <div class="badge bg-info text-wrap" style="width: 100%;font-size: 24px">
                        <p>Total de contas no centro de custos cadastrados no sistema de gerenciamento administrativo e contábil:
                            {{ $ContasCentroCustos->count() ?? 0 }}</p>
                    </div>
                </div> --}}



            </div>

            <table class="table" style="background-color: rgb(247, 213, 213);">
                <thead>
                    <tr>
                        <th scope="col" class="px-6 py-4">SALDO ANTERIOR</th>
                        <th scope="col" class="px-6 py-4">SALDO DO DIA</th>
                        <th scope="col" class="px-6 py-4">SALDO ATUAL</th>
                        <th scope="col" class="px-6 py-4">CENTRO DE CUSTOS</th>
                        <th scope="col" class="px-6 py-4">CONTAS</th>
                         <th scope="col" class="px-6 py-4">EMPRESA</th>
                        <th scope="col" class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($Resultado as $ContasCentroCusto)
                    <tr>
                        <td class="">{{ number_format($ContasCentroCusto['saldoAnterior'], 2, ',', '.') ?? null}}</td>
                        <td class="">{{ number_format($ContasCentroCusto['SaldoDia'], 2, ',', '.') ?? null}}</td>
                        <td class="">{{ number_format($ContasCentroCusto['SaldoAtual'], 2, ',', '.') ?? null}}</td>
                        <td class="">{{ $ContasCentroCusto['NomeCentroCustos'] ?? null}}</td>
                        <td class="">{{ $ContasCentroCusto['NomeConta'] ?? null }}</td>
                        <td class="">{{ $ContasCentroCusto['Empresa'] ?? null }}</td>
                        <td class=""></td>
                    </tr>
                    @endforeach
                    <tr class="table" style="background-color: rgb(19, 211, 83);">
                        <td class="">{{ number_format($somaSaldoAnterior, 2, ',', '.') }}</td>
                        <td class="">{{ number_format($somaSaldoDia, 2, ',', '.') }}</td>
                        <td class="">{{ number_format($somaSaldoAtual, 2, ',', '.') ?? null}}</td>
                    </tr>
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
