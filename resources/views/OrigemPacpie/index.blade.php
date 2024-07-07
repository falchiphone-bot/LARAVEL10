@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                     {{ session(['success' => NULL])}}
                 @elseif(session('cpf'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    {{ session(['cpf' => NULL])}}
                    @elseif(session('cnpj'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    {{ session(['cnpj' => NULL])}}
                @elseif (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    {{ session(['error' => NULL])}}
                @endif

                <div class="card">
                    <div class="badge bg-secondary text-wrap" style="width: 100%;font-size: 24px;text-align: center;">
                        EMPRESAS PARA PAC E PIE SP - SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                    </div>
                </div>
                <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                    <a class="btn btn-warning" href="/Cadastros">Retornar a lista de opções</a>
                    @can('PACPIE - LISTAR')
                        <a href="{{ route('Pacpie.index') }}" class="btn btn-success btn-lg enabled" tabindex="-1"
                            role="button" aria-disabled="true">Cadastro de PAC e PIE</a>

                    @endcan
                </nav>

                <div class="card-header">
                    <div class="badge bg-info text-wrap" style="width: 100%;font-size: 24px;text-align: center;">
                        <p>Total de cadastro de origem de empresas PAC e PIE - cadastrados no sistema de gerenciamento administrativo e contábil:
                            {{ $model->count() ?? 0 }}</p>
                    </div>
                </div>

                @can('ORIGEMPACPIE - INCLUIR')
                    <a href="{{ route('OrigemPacpie.create') }}" class="btn btn-danger btn-lg enabled" tabindex="-1" role="button"
                        aria-disabled="true">Incluir cadastro de origem de empresas</a>
                @endcan

            </div>

            {{-- <div class="col-12">
                <form method="GET" action="{{ route('Pacpie.indexSelecao') }}">
                    @csrf

                    <input type="radio" name="Selecao" id="semagrupamento" value="Todos" >
                    <label for="visualizarCheckbox">Todos registros</label>

                    <input type="radio" name="Selecao" id="semagrupamento" value="SemNome" >
                    <label for="visualizarCheckbox">Filtrar sem nome preenchido</label>

                    <input type="radio" name="Selecao" id="semagrupamento" value="SemPrimeiroContatoEmail" >
                    <label for="visualizarCheckbox">Filtrar sem primeiro contato por email</label>

                    <input type="radio" name="Selecao" id="semagrupamento" value="Emailcomfalha" >
                    <label for="visualizarCheckbox">Email com falha</label>

                    <input type="radio" name="Selecao" id="semagrupamento" value="SemEmail" >
                    <label for="visualizarCheckbox">Filtrar sem email</label>
                    <button type="submit" class="btn btn-danger">
                        Selecionar filtro
                    </button>
                </form>
            </div>

            <form method="POST" action="{{ route('Pacpie.BuscarTexto') }}" accept-charset="UTF-8">
                @csrf

                <div class="card">
                    <div class="card-body" style="background-color: rgb(33, 244, 33)">
                        <div class="row">
                            <div class="col-6">

                                <label for="Texto" style="color: black;">Texto a pesquisar</label>
                                <input class="form-control @error('Descricao') is-invalid @else is-valid @enderror" name="Texto" size="70" type="text" id="Texto" value="{{ $retorno['Texto'] ?? null }}">
                            </div>
                        </div>


                        <div class="row mt-2">
                            <div class="col-6">
                                <button class="btn btn-primary">Pesquisar conforme informações constantes do
                                    formulário</button>
                            </div>
                        </div>
                    </div>

                </div>



            </form> --}}

            <table class="table" style="background-color: rgb(247, 247, 255);">
                <thead>
                    <tr>
                        <th scope="col" class="px-6 py-4">NOME</th>
                        <th scope="col" class="px-6 py-4">TELEFONE</th>
                        <th scope="col" class="px-6 py-4">EMAIL</th>


                        <th scope="col" class="px-6 py-4">EMPRESA</th>

                        <th scope="col" class="px-6 py-4">Data cadastro</th>
                        <th scope="col" class="px-6 py-4">Usuário</th>
                        <th scope="col" class="px-6 py-4"></th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($model as $Model)
                        <tr>
                            <td class="">
                                {{ $Model->nome }}
                            </td>
                            <td class="">
                                {{ $Model->telefone }}
                            </td>
                            <td class="">
                                {{ $Model->email }}
                            </td>


                            <td class="">
                                {{ $Model->MostraEmpresa->Descricao ?? null}}
                            </td>



                             <td class="">
                                {{ $Model->created_at->format('d/m/Y H:m:s') }}

                            </td>
                            <td class="">
                                {{ $Model->user_created }}

                            </td>

                            @can('PACPIE - EDITAR')
                                <td>
                                    <a href="{{ route('Pacpie.edit', $Model->id) }}" class="btn btn-success" tabindex="-1"
                                        role="button" aria-disabled="true">Editar</a>
                                </td>
                            @endcan

                            @can('PACPIE - VER')
                                <td>
                                    <a href="{{ route('Pacpie.show', $Model->id) }}" class="btn btn-info" tabindex="-1"
                                        role="button" aria-disabled="true">Ver</a>
                                </td>
                            @endcan

                            @can('PACPIE - EXCLUIR')
                                <td>
                                    <form method="POST" action="{{ route('Pacpie.destroy', $Model->id) }}">
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
