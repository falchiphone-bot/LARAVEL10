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
                    <div class="badge bg-primary text-wrap" style="width: 100%;font-size: 24px;lign=˜Center˜">
                        FORMANDOS PARA SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                    </div>
                </div
                <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                    <a class="btn btn-warning" href="Cadastros">Retornar a lista de opções</a> </nav>



                <div class="card-header">
                    <div class="badge bg-info text-wrap" style="width: 100%;font-size: 24px">
                        <p>Total de formandos pesquisados no cadastro  do sistema de gerenciamento administrativo e contábil:
                            {{ $model->count() ?? 0 }}</p>
                        <p>Esta tela está limitada a 100 registros. Pesquise pelas opções abaixo caso possua para você.</p>
                    </div>
                </div>

                @can('FORMANDOBASE - INCLUIR')
                    <a href="{{ route('FormandoBase.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
                        aria-disabled="true">Incluir formandos</a>
                @endcan

                @can('FORMANDOBASEAVALIACAO - LISTAR')
                <a href="{{ route('FormandoBaseAvaliacao.index', ['sort' => 'datenew']) }}" class="btn btn-primary mt-2">Listagem de notas em geral</a>
                @endcan


                <hr>
                <style>
                    .card {
                        background-color: rgb(240, 162, 162); /* Substitua "red" pela cor desejada */
                    }
                </style>

                <div class="card">

                 <form method="GET" action="{{ route('formandobase.consultaempresa') }}" accept-charset="UTF-8" class="text-center">
                        @csrf
                    <div class="form-group">
                        <div class="badge bg-info text-wrap" style="width: 100%; height: 50%; font-size: 24px;">
                            CLUBES
                        </div>
                        <select required class="form-control select2" id="EmpresaSelecionada" name="EmpresaSelecionada">
                            <option value="">Selecionar clube</option>
                            @foreach ($Empresas as $Empresa)
                            <option
                                value="{{ $Empresa->ID }}">
                                {{ $Empresa->Descricao }}
                            </option>
                            @endforeach
                        </select>

                            <div class="row mt-2">
                                <div class="col-4">
                                    <button class="btn btn-success mx-auto">Filtrar por clube selecionado</button>
                                </div>
                            </div>
                    </form>
                </div>

                <div class="card">

<form method="POST" action="{{ route('FormandoBase.indexBusca') }}" accept-charset="UTF-8" class="text-center">
       @csrf
   <div class="form-group">
       <div class="badge bg-info text-wrap" style="width: 100%; height: 50%; font-size: 24px;">
           BUSCAR POR NOME EM TODOS CLUBES PERMITIDOS AO USUÁRIO
       </div>

       <div class="col-6">

            <label for="Texto" style="color: black;">sequência de texto a pesquisar</label>
            <input class="form-control @error('Descricao') is-invalid @else is-valid @enderror"
                name="BuscarNome" size="70" type="text" id="BuscarNome"
                value="{{ $retorno['BuscaNome'] ?? null }}">
            </div>


           <div class="row mt-2">
               <div class="col-4">
                   <button class="btn btn-success mx-auto">Filtrar por texto no nome</button>
               </div>
           </div>
   </form>
</div>


            </div>

                @can('FORMANDOBASE - VERIFICA FORMANDOS EXCLUIDOS')
                <form method="GET" action="{{ route('formandobase.excluidos') }}" accept-charset="UTF-8" class="text-center">

                    @csrf

                        {{-- <label for="opcao1">
                          <input type="radio" id="Ativado" name="opcao" value="Ativados" checked>
                          Formandos ativados
                        </label><br> --}}

                        <label for="opcao2">
                          <input type="radio" id="Excluido" name="opcao" value="Excluidos" checked style="display: none;">
                         {{-- Ver os formandos excluídos --}}
                        </label><br>


                    <br>
                    <div class="row mt-12">
                        <div class="col-12">
                            <button class="btn btn-danger mx-auto">Filtrar os excluídos</button>
                        </div>
                    </div>
                </form>
                @endcan

            </div>

            <tbody>
                <table class="table" style="background-color: rgb(247, 247, 255);">
                    <thead>
                        <tr>
                            <th scope="col" class="px-6 py-4">NOME</th>
                            <th scope="col" class="px-6 py-4">TELEFONE</th>
                            <th scope="col" class="px-6 py-4">EMAIL</th>
                            <th scope="col" class="px-6 py-4">CPF</th>
                            <th scope="col" class="px-6 py-4">RG</th>
                            <th scope="col" class="px-6 py-4">NASCIMENTO</th>
                            <th scope="col" class="px-6 py-4">REPRESENTANTE PRINCIPAL</th>

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
                                    {{ $Model->cpf }}
                                </td>
                                <td class="">
                                    {{ $Model->rg }}
                                </td>
                                <td class="">
                                    {{ $Model->nascimento->format('d/m/Y') }}
                                </td>
                                <td class="">
                                    {{ $Model->MostraRepresentante->nome ?? null }}
                                </td>
                                @can('FORMANDOBASE - EDITAR')
                                    <td>
                                        <a href="{{ route('FormandoBase.edit', $Model->id) }}" class="btn btn-success" tabindex="-1"
                                            role="button" aria-disabled="true">Editar</a>
                                    </td>
                                @endcan

                                @can('FORMANDOBASE - VER')
                                    <td>
                                        <a href="{{ route('FormandoBase.show', $Model->id) }}" class="btn btn-info" tabindex="-1"
                                            role="button" aria-disabled="true">Ver</a>
                                    </td>
                                @endcan

                                @can('FORMANDOBASE - EXCLUIR')
                                    <td>
                                        <form method="POST" action="{{ route('FormandoBase.destroy', $Model->id) }}">
                                            @csrf
                                            <input type="hidden" name="_method" value="DELETE">


                                            <button type="submit" class="btn btn-danger">

                                                @if ($Model->deleted_at == null)
                                                    Excluir
                                                @else
                                                    Ativar
                                                @endif

                                            </button>

                                        </form>
                                    </td>
                                @endcan
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
