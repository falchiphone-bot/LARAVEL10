@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">
            {{-- <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Permissions</a></li>
              <li class="breadcrumb-item active" aria-current="page">edit</li>
            </ol>
          </nav> --}}



            <div class="card">

                <h1 class="text-center">Plano de contas padrão para contabilidade</h1>
                <hr>
                {{-- @cannot('PLANO DE CONTAS - LISTAR')
                    <li>
                        <a href="/dashboard" data-bs-toggle="tooltip" data-bs-placement="center"
                            data-bs-custom-class="custom-tooltip" data-bs-title="Clique e vá para o início do sistema"
                            class="botton-link text-black">
                            <i class="fa-solid fa-house"></i>
                            <a href="{{ route('dashboard') }}" class="btn btn-danger btn-lg enabled" tabindex="-1"
                                role="button" aria-disabled="true">SEM PERMISSÃO PARA ESTE SERVIÇO. CONSULTE O ADMINISTRADOR.
                                Clique e vá para o início do sistema</a>
                        </a>
                    </li>
                @endcan --}}

                <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                    <a class="btn btn-warning" href="/Contabilidade">Retornar e ou ir para Contabilidade</a>
                </nav>
                @can('AGRUPAMENTOS CONTAS - LISTAR')
                    <a href="{{ route('AgrupamentosContas.index') }}" class="btn btn-success btn-lg enabled" tabindex="-1" role="button"
                        aria-disabled="true">Agrupamento de contas</a>
           @endcan

                @can('PLANO DE CONTAS - INCLUIR')
                    <a href="{{ route('PlanoContas.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1"
                        role="button" aria-disabled="true">Incluir contas no plano de contas padrão</a>
                @endcan

                <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                    <a class="btn btn-success" href="/PlanoContas">Plano de Contas - mostrar todas contas</a>
                </nav>



                <p>Total de contas: {{ $linhas }}</p>


                <form method="POST" action="{{ route('planocontas.FiltroAgrupamento') }}">
                    @csrf
                    <input type="hidden" name="_method" value="">
                        @csrf
                            <div class="col-sm-6">
                                <label for="nomeagrupamento" style="color: black;">Agrupamento a ser filtrado</label>
                                <select  class="form-control select2" id="nomeagrupamento" name="nomeagrupamento">
                                    <option value="">
                                        Selecionar agrupamento
                                    </option>

                                    @foreach ($Agrupamento as $Agrupamentos)
                                        <option @if ($Agrupamento ?? null)
                                        {{-- @if ($moedasvalores->idmoeda == $moeda->id) selected @endif --}}
                                            @endif
                                            value="{{ $Agrupamentos->id }}">
                                            {{ $Agrupamentos->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">

                                <input type="radio" name="Selecao" id="semagrupamento" value="semagrupamento" >
                                <label for="visualizarCheckbox">Visualizar na tela sem agrupamentos com grau 5</label>


                                <input type="radio" name="Selecao" id="semagrupamento" value="semagrupamentocalcular" >
                                <label for="visualizarCheckbox">Visualizar na tela sem agrupamentos com grau 5 - CALCULAR</label>

                            </div>




                            <div class="row mt-2">
                                <div class="col-6">
                                    <button class="btn btn-primary">Filtrar pela seleção</button>
                                </div>
                            </div>
                </form>




                <table class="table table-bordered">

                    <tr>
                        <th>Descrição</th>
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Código</th>
                        <th>Grau</th>
                        <th>Bloqueio</th>
                        <th>Bloqueia datas anteriores a</th>
                        <th>Código Skala</th>
                        <th>Agrupamento</th>
                        <th>Nome do Agrupamento</th>
                    </tr>
                    @foreach ($cadastros as $cadastro)
                        <tr>

                            @if ($cadastro->Grau == '1')
                                <td style="padding-left: 10px; Color:red; font-size: 30px;">
                                    {{ $cadastro->Descricao }}
                                </td>
                            @endif


                            @if ($cadastro->Grau == '2')
                                <td style="padding-left: 60px;">
                                    {{ $cadastro->Descricao }}
                                </td>
                            @endif
                            @if ($cadastro->Grau == '3')
                                <td style="padding-left: 90px;">
                                    {{ $cadastro->Descricao }}
                                </td>
                            @endif
                            @if ($cadastro->Grau == '4')
                                <td style="padding-left: 120px;">
                                    {{ $cadastro->Descricao }}
                                </td>
                            @endif
                            @if ($cadastro->Grau == '5')
                                <td style="padding-left: 150px; color:Blue;; font-size: 20px;">
                                    {{ $cadastro->Descricao }}
                                </td>
                            @endif


                            <td>
                                {{ $cadastro->ID }}
                            </td>
                            <td>
                                {{ $cadastro->Tipo }}
                            </td>
                            <td>
                                {{ $cadastro->Codigo }}
                            </td>
                            <td>
                                {{ $cadastro->Grau }}
                            </td>
                            <td>
                                {{ $cadastro->Bloqueio }}
                            </td>


                            <td>
                                @php
                                    $Altera = DateTime::createFromFormat('Y-m-d', $cadastro->Bloqueiodataanterior);
                                    if ($Altera instanceof DateTime) {
                                        echo $Altera->format('d-m-Y');
                                    } else {
                                        echo ' ';
                                    }
                                @endphp
                            </td>
                            <td>
                                {{ $cadastro->CodigoSkala }}
                            </td>
                            <td>
                                {{   $cadastro->Agrupamento ?? null

                                 }}
                            </td>
                            <td>
                                {{   $cadastro->MostraNome->nome ?? null

                                 }}
                            </td>

                            <td>
                                <div class="row mt-2">
                                    <div class="col-6">
                                        @can('PLANO DE CONTAS - EDITAR')
                                            <a href="{{ route('PlanoContas.edit', $cadastro->ID) }}"
                                                class="btn btn-success btn-sm enabled" tabindex="-1" role="button"
                                                aria-disabled="true">Editar</a>
                                        @endcan

                                        @can('PLANO DE CONTAS - EXCLUIR')
                                            <form method="POST" action="{{ route('PlanoContas.destroy', $cadastro->ID) }}">
                                                @csrf
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button class="btn btn-danger btn-sm enabled" tabindex="-1" role="button"
                                                    aria-disabled="true">Excluir</button>
                                            </form>
                                        @endcan

                                        @can('PLANO DE CONTAS - VER')
                                            <a href="{{ route('PlanoContas.show', $cadastro->ID) }}"
                                                class="btn btn-info btn-sm enabled" tabindex="-1" role="button"
                                                aria-disabled="true">Ver</a>
                                        @endcan

                                        @can('PLANO DE CONTAS - INCLUIR')
                                        <a href="{{ route('PlanoContas.edit', $cadastro->ID) }}"
                                            class="btn btn-success btn-sm enabled" tabindex="-1" role="button"
                                            aria-disabled="true">Incluir conta</a>
                                    @endcan


                                    </div>
                                </div>
                            </td>

                        </tr>
                    @endforeach
                </table>
            @endsection

            @push('scripts')

            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


                <link rel="stylesheet"
                    href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
                {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script> --}}

                <script>

                    $(document).ready(function() {
                                $('.select2').select2();
                            });


                    $('form').submit(function(e) {
                        e.preventDefault();
                        $.confirm({
                            title: 'Confirmar!',
                            content: 'Confirma a exclusão? Não terá retorno.',
                            buttons: {
                                confirmar: function() {
                                    // $.alert('Confirmar!');
                                    $.confirm({
                                        title: 'Confirmar!',
                                        content: 'Deseja realmente continuar com a exclusão? Não terá retorno.',
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
