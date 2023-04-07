@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">
            {{-- <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Conta</a></li>
              <li class="breadcrumb-item active" aria-current="page">Index</li>
            </ol>
          </nav> --}}

            <div class="card">
                <div class="card-header">
                    Contas
                </div>

                {{-- @can('MOEDAS- INCLUIR') --}}
                    <a href="{{ route('ContasCobranca.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
                        aria-disabled="true">Incluir Conta de Cobrança</a>
                {{-- @endcan --}}

                <div class="card-body">
                    <a href="/Cobranca" class="btn btn-secondary">Voltar</a>
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @elseif (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <p>Total de Contas: {{ $contasCobrancas->count() }}</p>

                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col" class="px-6 py-4">EmpresaID</th>
                                <th scope="col" class="px-6 py-4">Conta</th>
                                <th scope="col" class="px-6 py-4">Agencia</th>
                                <th scope="col" class="px-6 py-4">Posto</th>
                                <th scope="col" class="px-6 py-4">Associado Beneficiário</th>
                                <th scope="col" class="px-6 py-4">#</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($contasCobrancas as $conta)
                                <tr>
                                    <td class="">
                                        {{ $conta->Empresa->Descricao }}
                                    </td>
                                    <td class="">
                                        {{ $conta->conta }}
                                    </td>
                                    <td class="">
                                        {{ $conta->agencia }}
                                    </td>
                                    <td class="">
                                        {{ $conta->posto }}
                                    </td>
                                    <td class="">
                                        {{ $conta->associadobeneficiario }}
                                    </td>
                                    <td>
                                        @can('CONTAS - EDITAR')
                                            <a href="{{ route('ContasCobranca.edit', $conta->id) }}" class="btn btn-success"
                                                tabindex="-1" role="button" aria-disabled="true">Editar</a>
                                        @endcan
                                    </td>
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
