@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">
            <div class="card-body">

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    {{ session(['success' => null]) }}
                @elseif(session('cpf'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    {{ session(['cpf' => null]) }}
                @elseif(session('cnpj'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    {{ session(['cnpj' => null]) }}
                @elseif (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                    {{ session(['error' => null]) }}
                @endif

                <div class="card">
                    <div class="badge bg-primary text-wrap" style="width: 100%;font-size: 24px;text-align: center;">
                        EMPRESAS PARA PAC E PIE SP - SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                    </div>
                </div>

                <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                    @can('PACPIE - AJUSTACAMPOS')
                        <form action="{{ route('Pacpie.AjustaCampos') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-lg enabled" tabindex="-1" role="button"
                                aria-disabled="true">Ajustar Campos</button>
                        </form>
                    @endcan
                </nav>


                <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                    <a class="btn btn-warning" href="/Cadastros">Retornar a lista de opções</a>
                    @can('ORIGEMPACPIE - LISTAR')
                        <a href="{{ route('OrigemPacpie.index') }}" class="btn btn-success btn-lg enabled" tabindex="-1"
                            role="button" aria-disabled="true">Origem PAC e PIE</a>
                    @endcan
                </nav>

                <div class="card-header">
                    <div class="badge bg-info text-wrap" style="width: 100%;font-size: 24px;text-align: center;">
                        <p>Total de empresas PAC e PIE - cadastrados no sistema de gerenciamento administrativo e contábil:
                            {{ $model->count() ?? 0 }}</p>
                    </div>
                </div>

                @can('PACPIE - INCLUIR')
                    <a href="{{ route('Pacpie.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
                        aria-disabled="true">Incluir empresa</a>
                @endcan
            </div>

            <div class="container mt-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Selecionar Filtro</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('Pacpie.indexSelecao') }}">
                            @csrf
                            <style>
                                .table-bg-yellowgreen {
                                    background-color: yellowgreen;
                                }

                                .table-bg-blue {
                                    background-color: rgb(204, 204, 237);
                                }

                                .table-bg-whitesmoke {
                                    background-color: whitesmoke);
                                }

                                .form-check-input:checked#todos~.form-check-label {
                                    color: red;
                                }

                                .form-check-input:checked#semNome~.form-check-label {
                                    color: green;
                                }

                                .form-check-input:checked#PromessaAporte~.form-check-label {
                                    color: red;
                                }

                                .form-check-input:checked#Aportou~.form-check-label {
                                    color: green;
                                }

                                .form-check-input:checked#PromessaAporteValor~.form-check-label {
                                    color: rgb(140, 0, 255);
                                }

                                .form-check-input:checked#AportouValor~.form-check-label {
                                    color: rgb(0, 98, 128);
                                }

                                .form-check-input:checked#RetornoPrimeiroContatoEmail~.form-check-label {
                                    color: blue;
                                }

                                .form-check-input:checked#semPrimeiroContatoEmail~.form-check-label {
                                    color: purple;
                                }

                                .form-check-input:checked#emailComFalha~.form-check-label {
                                    color: orange;
                                }

                                .form-check-input:checked#semEmail~.form-check-label {
                                    color: brown;
                                }
                            </style>

                            <table class="table-bg-whitesmoke">
                                <tr>
                                    <td>
                                        <div class="form-check mb-2">
                                            <input type="radio" class="form-check-input" name="Selecao" id="Todos"
                                                value="Todos">
                                            <label class="form-check-label" for="todos">Todos registros</label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check mb-2">
                                            <input type="radio" class="form-check-input" name="Selecao" id="semNome"
                                                value="SemNome">
                                            <label class="form-check-label" for="semNome">Filtrar sem nome
                                                preenchido</label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check mb-2">
                                            <input type="radio" class="form-check-input" name="Selecao"
                                                id="RetornoPrimeiroContatoEmail" value="RetornoPrimeiroContatoEmail">
                                            <label class="form-check-label" for="RetornoPrimeiroContatoEmail">Retornado pelo
                                                primeiro contato</label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check mb-2">
                                            <input type="radio" class="form-check-input" name="Selecao"
                                                id="semPrimeiroContatoEmail" value="SemPrimeiroContatoEmail">
                                            <label class="form-check-label" for="semPrimeiroContatoEmail">Filtrar sem
                                                primeiro contato por email</label>
                                        </div>
                                    </td>


                                    <td>
                                        <div class="form-check mb-2">
                                            <input type="radio" class="form-check-input" name="Selecao" id="emailComFalha"
                                                value="emailComFalha">
                                            <label class="form-check-label" for="emailComFalha">Email com falha</label>
                                        </div>
                                    </td>
                                <tr>
                                    <td>
                                        <div class="form-check mb-2">
                                            <input type="radio" class="form-check-input" name="Selecao" id="semEmail"
                                                value="SemEmail">
                                            <label class="form-check-label" for="semEmail">Filtrar sem email</label>
                                        </div>
                                    </td>


                                    <td>
                                        <div class="form-check mb-2">
                                            <input type="radio" class="form-check-input" name="Selecao"
                                                id="PromessaAporte" value="PromessaAporte">
                                            <label class="form-check-label" for="PromessaAporte">Promessa de aporte</label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check mb-2">
                                            <input type="radio" class="form-check-input" name="Selecao"
                                                id="PromessaAporteValor" value="PromessaAporteValor">
                                            <label class="form-check-label" for="PromessaAporte">Promessa de aporte com
                                                valor</label>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="form-check mb-2">
                                            <input type="radio" class="form-check-input" name="Selecao" id="Aportou"
                                                value="Aportou">
                                            <label class="form-check-label" for="Aportou">Aportou</label>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="form-check mb-2">
                                            <input type="radio" class="form-check-input" name="Selecao"
                                                id="AportouValor" value="AportouValor">
                                            <label class="form-check-label" for="AportouValor">Aportou com valor</label>
                                        </div>
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                            </table>

                            <button type="submit" class="btn btn-danger mt-3 w-100">Selecionar filtro</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="container mt-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Pesquisar</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('Pacpie.BuscarTexto') }}" accept-charset="UTF-8">
                            @csrf
                            <div class="card">
                                <div class="card-body" style="background-color: rgb(33, 244, 33)">
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <label for="Texto" style="color: black;">Texto a pesquisar</label>
                                            <input
                                                class="form-control @error('Descricao') is-invalid @else is-valid @enderror"
                                                name="Texto" size="70" type="text" id="Texto"
                                                value="{{ session('textoBusca') }}">
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-12 col-md-6">
                                            <button class="btn btn-primary">Pesquisar conforme informações constantes do
                                                formulário</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="container mt-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">



                        @if (isset($selecaoFiltro) && $selecaoFiltro == 'SemPrimeiroContatoEmail')
                            <h5 class="card-title mb-0">Registros: Filtrar sem primeiro contato por email</h5>
                        @elseif (isset($selecaoFiltro) && $selecaoFiltro == 'RetornoPrimeiroContatoEmail')
                            <h5 class="card-title mb-0">Registros: Filtrar retornado pelo primeiro contato</h5>
                        @elseif (isset($selecaoFiltro) && $selecaoFiltro == 'SemEmail')
                        <h5 class="card-title mb-0">Registros: Filtrar sem email informado</h5>
                        @elseif (isset($selecaoFiltro) && $selecaoFiltro == 'SemNome')
                        <h5 class="card-title mb-0">Registros: Filtrar sem nome preenchido</h5>
                        @elseif (isset($selecaoFiltro) && $selecaoFiltro == 'Todos')
                        <h5 class="card-title mb-0">Registros: Filtrar todos registros</h5>
                        @elseif (isset($selecaoFiltro) && $selecaoFiltro == 'emailComFalha')
                        <h5 class="card-title mb-0">Registros: Filtrar registrados os email com falha</h5>

                        @elseif (isset($selecaoFiltro) && $selecaoFiltro == 'Aportou')
                        <h5 class="card-title mb-0">Registros: Filtrar aportou</h5>

                        @elseif (isset($selecaoFiltro) && $selecaoFiltro == 'PromessaAporte')
                        <h5 class="card-title mb-0">Registros: Filtrar promessas de aporte</h5>

                        @elseif (isset($selecaoFiltro) && $selecaoFiltro == 'PromessaAporteValor')
                        <h5 class="card-title mb-0">Registros: Filtrar promessas de aporte de valor</h5>


                        @elseif (isset($selecaoFiltro) && $selecaoFiltro == 'AportouValor')
                        <h5 class="card-title mb-0">Registros: Filtrar aportou valor</h5>



                            @else
                            <h5 class="card-title mb-0">Registros: Nada filtrado {{ Pesquisa: $emailComFalha}}</h5>
                        @endif



                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" style="background-color: rgb(247, 247, 255);">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" class="px-6 py-4">FICHAS DE EMPRESAS PARA BUSCA DE INCENTIVOS A
                                            PROJETOS</th>
                                    </tr>
                                </thead>
                                <style>
                                    .highlight-row {
                                        background-color: #f2f2f2;
                                        /* Escolha a cor que preferir */
                                        text-align: center;
                                    }
                                </style>
                                <tbody>
                                    @foreach ($model as $Model)
                                        <tr class="highlight-row">
                                            <td colspan="11">ORIGINADO DE: {{ $Model->MostraOrigem->nome ?? null }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Nome:</strong> {{ $Model->nome }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Telefone:</strong> {{ $Model->telefone }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong> {{ $Model->email }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>CNPJ:</strong> {{ $Model->cnpj }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Promessa de aporte:</strong>
                                                {{ $Model->promessa_aporte == 1 ? 'SIM' : '' }}</td>

                                            <td><strong>Promessa de aporte no ano:</strong>
                                                {{ $Model->promessa_aporte_ano }}</td>
                                            <td><strong>Promessa de aporte no valor:</strong>
                                                {{ $Model->promessa_aporte_valor }}</td>
                                        </tr>

                                        <tr>
                                            <td><strong>Aportou :</strong> {{ $Model->aportou == 1 ? 'SIM' : '' }}</td>
                                            <td><strong>Aportou no ano:</strong> {{ $Model->aportou_ano }}</td>
                                            <td><strong>Aportou valor:</strong> {{ $Model->aportou_valor }}</td>

                                        </tr>


                                        <tr>
                                            <td><strong>PROPRIETÁRIO DOS DADOS:</strong>
                                                {{ $Model->MostraEmpresa->Descricao }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Primeiro contato:</strong>
                                                {{ $Model->emailprimeirocontato == 1 ? 'SIM' : '' }}
                                                @if ($Model->emailprimeirocontato == null)
                                                    <form method="GET"
                                                        action="{{ route('Pacpie.MarcaEnviadoemailparaprimeirocontato', $Model->id) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-danger">Marcar primeiro
                                                            contato por email</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>

                                        <tr>
                                            <td><strong>Retorno do primeiro contato:</strong>
                                                {{ $Model->retornoemailprimeirocontato == 1 ? 'SIM' : '' }}
                                                @if ($Model->retornoemailprimeirocontato == null)
                                                    <form method="GET"
                                                        action="{{ route('Pacpie.MarcaRetornoEnviadoemailparaprimeirocontato', $Model->id) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-danger">Marcar retorno do
                                                            primeiro contato por email</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>

                                        <tr>
                                            <td><strong>Email com falhas:</strong>
                                                {{ $Model->emailcomfalhas == 1 ? 'SIM' : '' }}
                                                @if ($Model->emailprimeirocontato == true)
                                                    <form method="GET"
                                                        action="{{ route('Pacpie.Marcaemailcomfalhas', $Model->id) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-warning">Marcar email com
                                                            falhas</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Data do registro:</strong>
                                                {{ $Model->created_at->format('d/m/Y H:i:s') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Usuário que atualizou a ficha:</strong> {{ $Model->user_updated }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <div class="btn-group" role="group" aria-label="Ações"></div>
                                                <div class="card"
                                                    style="float: center; background-color: #E3F2FD; padding: 50px;">

                                                    @can('PACPIE - EDITAR')
                                                        <a href="{{ route('Pacpie.edit', $Model->id) }}"
                                                            class="btn btn-primary">Editar</a>
                                                    @endcan

                                                    @can('PACPIE - VISUALIZAR')
                                                        <a href="{{ route('Pacpie.show', $Model->id) }}"
                                                            class="btn btn-secondary">Visualizar</a>
                                                    @endcan


                                                    @can('PACPIE - EXCLUIR')
                                                        <form method="POST"
                                                            action="{{ route('Pacpie.destroy', $Model->id) }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger"
                                                                onclick="return confirm('Tem certeza que deseja excluir este registro?')">Excluir</button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div>
                                <div>
                                    <div style="background-color: #FFEB3B; padding: 10px;">

                                        ==========================================================================================================================
                                    </div>

                                </div>
                            </div>
                            <style>
                                .pagination {
                                    display: flex;
                                    justify-content: center;
                                }
                            </style>
                        </div>
                    </div>
                </div>
            </div>
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
