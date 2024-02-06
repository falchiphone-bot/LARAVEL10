@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;font-size: 24px;lign=˜Center˜">
                    CLIENTES IXC DO SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>
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
                    <a class="btn btn-warning" href="/Ixc">Retornar a lista de opções</a> </nav>




            </div>


            <table class="table" style="background-color: rgb(195, 245, 130);">
                <thead>
                    <tr>
                        <th scope="col" class="px-6 py-4">Ativos no cadastro de clientes:{{$CadastroClientesAtivo}}</th>

                        <th scope="col" class="px-6 py-4">NÃO ativos no cadastro de clientes</th>
                        <th scope="col" class="px-2 py-4">{{$CadastroClientesNaoAtivo}}</th>

                       <th scope="col" class="px-6 py-4">Cadastro de clientes</th>
                       <th scope="col" class="px-2 py-4">{{$CadastroClientes}}</th>
                   </tr>

                   <tr>
                        <th scope="col" class="px-6 py-4">Internet ativa no cadastro de clientes:{{$status_internet_Ativo}}</th>
                        <th scope="col" class="px-6 py-4">Internet não ativa no cadastro de clientes:{{$status_internet_NAOAtivo}}</th>
                   </tr>
                   <tr>
                        <th scope="col" class="px-6 py-4">Quantos registros com número de Whatsapp:{{$tem_whatsapp}}</th>
                   </tr>

            </thead>
            </table>


            <tbody>
                <div class="card-header">
                    <div class="badge bg-info text-wrap" style="width: 100%;font-size: 24px">
                        <p>Total de clientes selecionados no sistema de gerenciamento administrativo e contábil:
                            {{ $clientes->count() ?? 0 }}</p>
                    </div>
                </div>
                <div class="badge bg-info text-wrap" style="width: 100%;font-size: 24px">
                    <p>Últimos cadastrados</p>
                </div>
                <table class="table" style="background-color: rgb(247, 247, 213);">
                    <thead>
                        <tr>
                            <th scope="col" class="px-6 py-4">NOME</th>
                            <th scope="col" class="px-6 py-4">ATIVO</th>
                            <th scope="col" class="px-6 py-4">CIDADE</th>
                            <th scope="col" class="px-6 py-4">DATA CADASTRO</th>
                            <th scope="col" class="px-6 py-4"></th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($clientes as $cliente)
                            <tr>
                                <td class="">
                                    {{ $cliente->razao }}
                                    </a>
                                </td>
                                <td class="">
                                    {{ $cliente->ativo }}
                                </td>
                                <td class="">
                                    {{ $cliente->City->nome }}
                                </td>
                                <td class="">
                                    <?php
                                        $dataCadastroObjeto = new DateTime($cliente->data_cadastro);
                                        $dataCadastroFormatada = $dataCadastroObjeto->format('d/m/Y');
                                    ?>

                                    {{ $dataCadastroFormatada }}
                                </td>
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
