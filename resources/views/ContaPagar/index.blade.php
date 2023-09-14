@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">



            <div class="card">
                <div class="card-header">
                    <div class="badge bg-success text-wrap" style="width: 100%;">
                        CONTAS A PAGAR PARA O SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                    </div>
                </div>

                @can('CONTASPAGAR - INCLUIR')
                    <a href="{{ route('ContasPagar.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
                        aria-disabled="true">Incluir Contas a pagar  </a>
                @endcan

                <div class="card-body">
                    <a href="/dashboard" class="btn btn-warning">Retornar para opções anteriores</a>

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @elseif (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    <div class="card-header mix-blend-color-burn">
                    <p>Total de Contas a pagar: {{ $contasPagar->count() }}</p>
                </div>
                    <div class="card-header">
                    <div class="badge bg-warning text-wrap" style="width: 100%;">

                    </div>
                </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col" class="px-6 py-4">EmpresaID</th>
                                <th scope="col" class="px-6 py-4">Data</th>
                                <th scope="col" class="px-6 py-4">Conta Pagar</th>
                                <th scope="col" class="px-6 py-4">Conta de pagamento</th>
                                <th scope="col" class="px-6 py-4">Vencimento</th>
                                <th scope="col" class="px-6 py-4">Programado</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($contasPagar as $conta)

                            <!-- @DD($conta) -->
                                <tr>
                                    <td class="">
                                        {{ $conta->EmpresaID }}
                                    </td>
                                    <td class="">
                                        {{ $conta->DataDocumento }}
                                    </td>
                                    <td class="">
                                        {{ $conta->ContaFornecedorID }}
                                    </td>
                                    <td class="">
                                        {{ $conta->ContaPagamentoID }}
                                    </td>
                                    <td class="">
                                        {{ $conta->DataProgramacao }}
                                    </td>
                                    <td>
                                        @can('CONTASPAGAR - EDITAR')
                                            <a href="{{ route('ContasPagar.edit', $conta->ID) }}" class="btn btn-success"
                                                tabindex="-1" role="button" aria-disabled="true">Editar</a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-header">
                    <div class="badge bg-success text-wrap" style="width: 100%;">

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
