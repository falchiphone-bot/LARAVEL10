@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;">
                    HISTÓRICOS DO SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
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
                    @can('HISTORICO - INCLUIR')
                        <a href="{{ route('Historicos.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1"
                            role="button" aria-disabled="true">Incluir nome de históricos</a>
                    @endcan


                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                        <a class="btn btn-primary" href="/Contabilidade">Contabilidade</a>
                    </nav>

                    {{-- <div class="card-header">
                        <div class="badge bg-secondary text-wrap" style="width: 100%;">
                            <p>Total de historicos cadastradas no sistema de gerenciamento administrativo e contábil:
                                {{ $Historicos->count() ?? 0 }}</p>
                        </div>
                    </div> --}}
                </div>



                <form method="POST" action="{{ route('pesquisapost') }}" accept-charset="UTF-8">
                    @csrf
                    <div class="card">
                        <div class="card-body" style="background-color: rgb(33, 244, 33)">
                            <div class="row">
                                <div class="col-6">
                                    <label for="Limite" style="color: black;">Empresas permitidas para o usuário</label>
                                    <select class="form-control select2" id="EmpresaSelecionada" name="EmpresaSelecionada">
                                        <option value="">
                                            Selecionar empresa
                                        </option>
                                        @foreach ($Empresas as $Empresa)
                                            <option  @selected($retorno["EmpresaSelecionada"] == $Empresa->ID)
                                                value="{{ $Empresa->ID }}">

                                                {{ $Empresa->Descricao }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-3">
                                    <label for="Pesquisa" style="color: black;">Pesquisar texto no histórico</label>
                                   <input value= "{{$retorno["PesquisaTexto"]}}" type="text" name='PesquisaTexto' class="form-control">
                                </div>

                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-6">
                                <button class="btn btn-primary">Pesquisar</button>

                            </div>
                        </div>
                    </div>

                </form>





                <tbody>
                    <table class="table" style="background-color: rgb(247, 247, 213);">
                        <thead>
                            <tr>
                                <th scope="col" class="px-6 py-4">EMPRESA</th>
                                <th scope="col" class="px-6 py-4">NOME</th>
                                <th scope="col" class="px-6 py-4">CONTA DÉBITO</th>
                                <th scope="col" class="px-6 py-4">CONTA CRÉDITO</th>
                                <th scope="col" class="px-6 py-4"></th>
                                <th scope="col" class="px-6 py-4"></th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($pesquisa as $Historico)
                                <tr>
                                    <td class="">
                                        {{ $Historico->empresa->Descricao }}
                                        </a>
                                    </td>
                                    <td class="">
                                        {{ $Historico->Descricao }}
                                        </a>
                                    </td>
                                    <td class="">
                                        {{ $Historico->ContaDebito->PlanoConta->Descricao }}
                                    </td>
                                    <td class="">
                                        {{ $Historico->ContaCredito->PlanoConta->Descricao }}
                                    </td>


                                    @can('HISTORICOS - EDITAR')
                                        <td>
                                            <a href="{{ route('Historicos.edit', $Historico->ID) }}" class="btn btn-success"
                                                tabindex="-1" role="button" aria-disabled="true">Editar</a>
                                        </td>
                                    @endcan

                                    @can('HISTORICOS - VER')
                                        <td>
                                            <a href="{{ route('Historicos.show', $Historico->ID) }}" class="btn btn-info"
                                                tabindex="-1" role="button" aria-disabled="true">Ver</a>
                                        </td>
                                    @endcan

                                    @can('HISTORICOS - EXCLUIR')
                                        <td>
                                            <form method="POST" action="{{ route('Historicos.destroy', $Historico->ID) }}">
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
