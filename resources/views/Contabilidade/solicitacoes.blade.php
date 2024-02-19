@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">


            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;">
                    SOLICITAÇÕES PARA EXCLUSÃO - SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
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

                    <div class="card-header">
                        <p>Total de pedidos cadastrados no sistema de gerenciamento administrativo e contábil:
                            {{ $solicitacoes->count() }}</p>
                    </div>


                    <table class="table" style="background-color: yellow;">

                        {{-- <thead>

                            <tr>
                                @can('EMPRESAS - DESBLOQUEAR TODAS')
                                    <th>
                                        <form method="POST" action="{{ route('Empresas.DesbloquearEmpresas') }}"
                                            accept-charset="UTF-8">
                                            <input type="hidden" name="_method" value="PUT">
                                            @include('Empresas.desbloquearempresas')
                                        </form>
                                    </th>
                                @endcan

                                @can('EMPRESAS - BLOQUEAR TODAS')
                                    <th>
                                        <form method="POST" action="{{ route('Empresas.BloquearEmpresas') }}"
                                            accept-charset="UTF-8">
                                            <input type="hidden" name="_method" value="PUT">
                                            @include('Empresas.BloquearEmpresas')
                                        </form>
                                    </th>
                                @endcan
                            </tr>
                        </thead> --}}

            </div>

            <tbody>




                <table class="table" style="background-color: rgb(247, 247, 213);">
                    <thead>
                        <tr>
                            <th scope="col" class="px-6 py-4">DESCRIÇÃO</th>
                            <th scope="col" class="px-6 py-4">SOLICITADO EM</th>
                            <th scope="col" class="px-6 py-4">SOLICITADO POR</th>
                            <th scope="col" class="px-6 py-4">LANCAMENTO</th>
                            <th scope="col" class="px-6 py-4">VALOR</th>
                            <th scope="col" class="px-6 py-4">DÉBITO</th>
                            <th scope="col" class="px-6 py-4">CRÉDITO</th>
                            <th scope="col" class="px-6 py-4">VALOR</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($solicitacoes as $cadastro)
                            <tr>
                                <td class="">

                                        {{ $cadastro->Descricao }}
                                    </a>
                                </td>
                                <td class="">

                                    {{ \Carbon\Carbon::parse($cadastro->Created)->format('d/m/Y H:i:s') }}

                                </td>

                                <td class="">
                                    {{ $cadastro->usuario->name }}
                                </td>

                                <td class="">
                                    {{ $cadastro->TableID }}
                                </td>

                                <td class="">
                                    {{ $cadastro->lancamento->Valor }}
                                </td>
                                <td class="">
                                    {{ $cadastro->ContaDebitoID  }}
                                </td>

                                <td class="">
                                    {{ $cadastro->contaDebito  }}
                                </td>
                                <td class="">
                                    {{ $cadastro->ContaCreditoID }}
                                </td>

                                {{-- <td class="">
                                    @if ($cadastro->Bloqueio)
                                        Sim
                                    @else
                                        Não
                                    @endif
                                </td>
                                <td class="">
                                    {{ $cadastro->Bloqueiodataanterior?->format('d/m/Y') }}
                                </td>

                                @can('EMPRESAS - EDITAR')
                                    <td>
                                        <a href="{{ route('Empresas.edit', $cadastro->EmpresaID) }}" class="btn btn-success"
                                            tabindex="-1" role="button" aria-disabled="true">Editar</a>
                                    </td>
                                @endcan --}}


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
