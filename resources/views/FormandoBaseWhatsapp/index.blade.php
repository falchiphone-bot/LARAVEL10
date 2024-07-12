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
                        FORMANDOS WHATSAPP PARA SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                    </div>
                </div
                <div class="card">
                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                    <a class="btn btn-warning" href="/Cadastros">Retornar a lista de opções</a> </nav>
                    @can('FORMANDOBASEWHATSAPP - LISTAR')

                    <th>
                        <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                            <a class="btn btn-success" href="/FormandoBaseWhatsapp">Formandos - atletas - cadastros via flow whatsapp </a>
                        </nav>
                    </th>
                @endcan
                    </div>



                <div class="card-header">
                    <div class="badge bg-info text-wrap" style="width: 100%;font-size: 24px">
                        <p>Total de cadastros pesquisados no cadastro  do sistema de gerenciamento administrativo e contábil:
                            {{ $model->count() ?? 0 }}</p>

                        {{-- @if($retorno['Limite'] == null)
                                 <p>Esta tela está limitada a 1000 registros, caso não foi efetuada pesquisa. Pesquise pelas opções abaixo caso possua para você.</p>
                        @endif --}}
                    </div>
                </div>

                {{-- @can('FORMANDOBASEWHATSAPP - INCLUIR')
                    <a href="{{ route('FormandoBase.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
                        aria-disabled="true">Incluir formandos</a>
                @endcan --}}

                {{-- @can('FORMANDOBASEWHATSAPP - LISTAR')
                <a href="{{ route('FormandoBaseAvaliacao.index', ['sort' => 'datenew']) }}" class="btn btn-primary mt-2">Listagem de notas em geral</a>
                @endcan --}}


                <hr>
                <style>
                    .card {
                        background-color: rgb(162, 240, 206); /* Substitua "red" pela cor desejada */
                    }
                </style>

                {{-- <div class="card">
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
                </div> --}}

                <div class="card">

<form method="POST" action="{{ route('FormandoBaseWhatsapp.indexBusca') }}" accept-charset="UTF-8" class="text-center">
       @csrf
   <div class="form-group">
       <div class="badge bg-info text-wrap" style="width: 100%; height: 50%; font-size: 24px;">
           BUSCAR POR NOME EM TODOS CLUBES PERMITIDOS AO USUÁRIO
       </div>

       <div class="col-12">

            <label for="Texto" style="color: black;">sequência de texto a pesquisar</label>
            <input class="form-control @error('Descricao') is-invalid @else is-valid @enderror"
                name="BuscarNome" size="70" type="text" id="BuscarNome"
                value="{{ $retorno['BuscarNome'] ?? null }}">
            </div>
        <div>

            <div class="col-12">
                <div class="col-3">
                <label for="Limite" style="color: black;">Limite de registros para retorno</label>
                <input class="form-control @error('limite') is-invalid @else is-valid @enderror"
                    name="Limite" size="10" type="number" step="1" id="Limite"
                    value="{{ $retorno['Limite'] ?? null }}">
               </div>
            <div>


                <div class="col-4">
                    <input type="radio" name="Avaliacao" value="1" id="periodoTodos">
                    <label for="periodoTodos">Avaliações no período de 29.01.2024 a 01.03.2024 (TODOS PERIODO)</label>
                </div>

                <div class="col-4">
                    <input type="radio" name="Avaliacao" value="AVALIACAO_FUTEBOL_29012024_02022024" id="periodo0202">
                    <label for="periodo0202">Avaliações no período de 29.01.2024 a 02.02.2024</label>
                </div>

                <div class="col-4">
                    <input type="radio" name="Avaliacao" value="AVALIACAO_FUTEBOL_05022024_09022024" id="periodo0902">
                    <label for="periodo0902">Avaliações no período de 05.02.2024 a 09.02.2024</label>
                </div>

                <div class="col-4">
                    <input type="radio" name="Avaliacao" value="AVALIACAO_FUTEBOL_19022024_23022024" id="periodo2302">
                    <label for="periodo2302">Avaliações no período de 19.02.2024 a 23.02.2024</label>
                </div>

                <div class="col-4">
                    <input type="radio" name="Avaliacao" value="AVALIACAO_FUTEBOL 26022024_01032024" id="periodo0103">
                    <label for="periodo0103">Avaliações no período de 26.02.2024 a 01.03.2024</label>
                </div>


           <div class="row mt-2">
               <div class="col-4">
                   <button class="btn btn-success mx-auto">Filtrar/Pesquisar</button>
               </div>
           </div>
   </form>

   <form method="POST" action="{{ route('FormandoBaseWhatsapp.AtualizaIdade') }}" accept-charset="UTF-8" class="text-center">
         @csrf
         <div class="form-group">
        <div class="badge bg-danger text-wrap" style="width: 100%; height: 50%; font-size: 24px;">
            ATUALIZAR IDADE NO BANCO DE DADOS DE TODOS REGISTROS
        </div>
        <div class="row mt-12">
            <div class="col-8">
                <button class="btn btn-warning mx-auto"> CLIQUE PARA ATUALIZAR IDADE NO BANCO DE DADOS DE TODOS REGISTROS</button>
            </div>
        </div>
    </form>

    <form method="POST" action="{{ route('FormandoBaseWhatsapp.AtualizaWatsapp') }}" accept-charset="UTF-8" class="text-center">
        @csrf
        <div class="form-group">
       <div class="badge bg-danger text-wrap" style="width: 100%; height: 50%; font-size: 24px;">
           ATUALIZAR WHATSAPP NO BANCO DE DADOS DE TODOS REGISTROS
       </div>
       <div class="row mt-12">
           <div class="col-8">
               <button class="btn btn-warning mx-auto"> CLIQUE PARA ATUALIZAR WHATSAPP NO BANCO DE DADOS DE TODOS REGISTROS</button>
           </div>
       </div>
   </form>
</div>


            {{-- </div>
                @can('FORMANDOBASE - VERIFICA FORMANDOS EXCLUIDOS')
                <form method="GET" action="{{ route('formandobase.excluidos') }}" accept-charset="UTF-8" class="text-center">
                    @csrf
                        <label for="opcao2">
                          <input type="radio" id="Excluido" name="opcao" value="Excluidos" checked style="display: none;">
                        </label><br>
                    <br>
                    <div class="row mt-12">
                        <div class="col-12">
                            <button class="btn btn-danger mx-auto">Filtrar os excluídos</button>
                        </div>
                    </div>
                </form>
                @endcan
            </div> --}}

            <tbody>
                <table class="table" style="background-color: rgb(247, 247, 255);">
                    <thead>
                        <tr>
                            <th scope="col" class="px-6 py-4">NOME</th>
                            <th scope="col" class="px-6 py-4">TELEFONE</th>
                            <th scope="col" class="px-6 py-4">WHATSAPP</th>
                            {{-- <th scope="col" class="px-6 py-4">EMAIL</th> --}}
                            <th scope="col" class="px-6 py-4">CPF</th>
                            <th scope="col" class="px-6 py-4">MOTIVO CADASTRO</th>
                            <th scope="col" class="px-6 py-4">NASCIMENTO</th>
                            <th scope="col" class="px-6 py-4">ANOTAÇÃO</th>
                            {{-- <th scope="col" class="px-6 py-4">REPRESENTANTE PRINCIPAL</th> --}}

                            <th scope="col" class="px-6 py-4"></th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($model as $Model)
                            <?php
                                    // Data de nascimento no formato AAAA-MM-DD
                                    $data_nascimento =  $Model->nascimento;

                                    // Converte a string da data de nascimento para um objeto DateTime
                                    $data_nascimento = new DateTime($data_nascimento);

                                    // Obtém a data atual
                                    $data_atual = new DateTime();

                                    // Calcula a diferença entre a data atual e a data de nascimento
                                    $intervalo = $data_atual->diff($data_nascimento);

                                    // Obtém a idade em anos
                                    $idade = $intervalo->y;


                            ?>




                            <tr>

                                <td class="">
                                    {{ $Model->nome }}
                                </td>
                                <td class="">
                                    {{ $Model->telefone }}
                                </td>
                                <td class="">
                                    {{ $Model->whatsapp }}
                                </td>
                                {{-- <td class="">
                                    {{ $Model->email }}
                                </td> --}}
                                <td class="">
                                    {{ $Model->cpf }}
                                </td>
                                <td class="">
                                    {{ $Model->motivo_cadastro }}
                                </td>
                                <td class="">
                                    {{ $Model->nascimento->format('d/m/Y') . " Idade:" . $idade }}

                                        @if ($Model->idade != null)
                                          {{ "Idade: $Model->idade | "}}
                                        @endif
                                        @if ($Model->idade == $idade)
                                         CORRETO NO BD
                                        @endif
                                </td>


                                {{-- <td class="">
                                    {{ $Model->MostraRepresentante->nome ?? null }}
                                </td> --}}

                                <td class="">
                                    {{ $Model->flow_description }}
                                    {{-- {{ $Model->nascimento->format('d/m/Y') }} --}}
                                </td>

                                @can('FORMANDOBASE - EDITAR')
                                    <td>
                                        <a href="{{ route('FormandoBaseWhatsapp.edit', $Model->id) }}" class="btn btn-success" tabindex="-1"
                                            role="button" aria-disabled="true">Editar</a>
                                    </td>
                                @endcan

                                @can('FORMANDOBASE - VER')
                                    <td>
                                        <a href="{{ route('FormandoBaseWhatsapp.show', $Model->id) }}" class="btn btn-info" tabindex="-1"
                                            role="button" aria-disabled="true">Ver</a>
                                    </td>
                                @endcan

                                @can('FormandoBaseWhatsapp - EXCLUIR')
                                    <td>
                                        <form method="POST" action="{{ route('FormandoBaseWhatsapp.destroy', $Model->id) }}">
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
