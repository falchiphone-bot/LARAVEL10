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
                    <div class="badge bg-primary text-wrap" style="width: 100%;font-size: 24px;text-align: center;">
                        EMPRESAS PARA PAC E PIE SP - SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                    </div>
                </div>
                <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                    <a class="btn btn-warning" href="/Cadastros">Retornar a lista de opções</a>

                    @can('ORIGEMPACPIE - LISTAR')
                        <a href="{{ route('OrigemPacpie.index') }}" class="btn btn-success btn-lg enabled" tabindex="-1" role="button" aria-disabled="true">Origem PAC e PIE</a>
                    @endcan
                </nav>

                <div class="card-header">
                    <div class="badge bg-info text-wrap" style="width: 100%;font-size: 24px;text-align: center;">
                        <p>Total de empresas PAC e PIE - cadastrados no sistema de gerenciamento administrativo e contábil: {{ $model->count() ?? 0 }}</p>
                    </div>
                </div>

                @can('PACPIE - INCLUIR')
                    <a href="{{ route('Pacpie.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button" aria-disabled="true">Incluir empresa</a>
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

                <div class="form-check mb-2">
                    <input type="radio" class="form-check-input" name="Selecao" id="todos" value="Todos">
                    <label class="form-check-label" for="todos">Todos registros</label>
                </div>

                <div class="form-check mb-2">
                    <input type="radio" class="form-check-input" name="Selecao" id="semNome" value="SemNome">
                    <label class="form-check-label" for="semNome">Filtrar sem nome preenchido</label>
                </div>

                <div class="form-check mb-2">
                    <input type="radio" class="form-check-input" name="Selecao" id="semPrimeiroContatoEmail" value="SemPrimeiroContatoEmail">
                    <label class="form-check-label" for="semPrimeiroContatoEmail">Filtrar sem primeiro contato por email</label>
                </div>

                <div class="form-check mb-2">
                    <input type="radio" class="form-check-input" name="Selecao" id="emailComFalha" value="Emailcomfalha">
                    <label class="form-check-label" for="emailComFalha">Email com falha</label>
                </div>

                <div class="form-check mb-2">
                    <input type="radio" class="form-check-input" name="Selecao" id="semEmail" value="SemEmail">
                    <label class="form-check-label" for="semEmail">Filtrar sem email</label>
                </div>

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
                                <input class="form-control @error('Descricao') is-invalid @else is-valid @enderror" name="Texto" size="70" type="text" id="Texto" value="{{ $retorno['Texto'] ?? null }}">
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-12 col-md-6">
                                <button class="btn btn-primary">Pesquisar conforme informações constantes do formulário</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

            {{-- <table class="table table-responsive" style="background-color: rgb(247, 247, 255);">
                <thead>
                    <tr>
                        <th scope="col" class="px-6 py-4">NOME</th>
                        <th scope="col" class="px-6 py-4">TELEFONE</th>
                        <th scope="col" class="px-6 py-4">EMAIL</th>
                        <th scope="col" class="px-6 py-4">CNPJ</th>
                        <th scope="col" class="px-6 py-4">EMPRESA</th>
                        <th scope="col" class="px-6 py-4">Primeiro contato via email</th>
                        <th scope="col" class="px-6 py-4"></th>
                        <th scope="col" class="px-6 py-4">Email com falha</th>
                        <th scope="col" class="px-6 py-4">Data cadastro</th>
                        <th scope="col" class="px-6 py-4">Usuário</th>
                        <th scope="col" class="px-6 py-4"></th>
                    </tr>
                </thead>
                <style>
                    .highlight-row {
                        background-color: #f2f2f2; /* Escolha a cor que preferir */
                        text-align: center;
                    }
                </style>
                <tbody>
                    @foreach ($model as $Model)
                    <tr class="highlight-row">
                        <td colspan="10">ORIGINADO DE: {{ $Model->MostraOrigem->nome ?? null }}</td>
                    </tr>
                    <tr>
                        <td>{{ $Model->nome }}</td>
                        <td>{{ $Model->telefone }}</td>
                        <td>{{ $Model->email }}</td>
                        <td>{{ $Model->cnpj }}</td>
                        <td>{{ $Model->MostraEmpresa->Descricao }}</td>
                        <td>{{ $Model->emailprimeirocontato == 1 ? 'SIM' : '' }}</td>
                        <td>
                            @if ($Model->emailprimeirocontato == null)
                            <form method="GET" action="{{ route('Pacpie.MarcaEnviadoemailparaprimeirocontato', $Model->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-danger">Marcar primeiro contato por email</button>
                            </form>
                            @endif
                        </td>
                        <td>
                            {{ $Model->emailcomfalhas == 1 ? 'SIM' : '' }}
                            @if ($Model->emailprimeirocontato == true)
                            <form method="GET" action="{{  route('Pacpie.Marcaemailcomfalhas', $Model->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-warning">Marcar email com falhas</button>
                                <button onclick="location.reload(true);">Recarregar Página</button>
                            </form>
                            @endif
                        </td>
                        <td>{{ $Model->created_at->format('d/m/Y H:i:s') }}</td>
                        <td>{{ $Model->user_updated }}</td>
                        @can('PACPIE - EDITAR')
                        <td>
                            <a href="{{ route('Pacpie.edit', $Model->id) }}" class="btn btn-success" tabindex="-1" role="button" aria-disabled="true">Editar</a>
                        </td>
                        @endcan
                        @can('PACPIE - VER')
                        <td>
                            <a href="{{ route('Pacpie.show', $Model->id) }}" class="btn btn-info" tabindex="-1" role="button" aria-disabled="true">Ver</a>
                        </td>
                        @endcan
                        @can('PACPIE - EXCLUIR')
                        <td>
                            <form method="POST" action="{{ route('Pacpie.destroy', $Model->id) }}">
                                @csrf
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-danger">Excluir</button>
                            </form>
                        </td>
                        @endcan
                    </tr>
                    @endforeach
                </tbody>
            </table> --}}

            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Tabela em Card</title>
                <!-- Bootstrap CSS -->
                <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
            </head>
            <body>

            <div class="container mt-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Registros</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" style="background-color: rgb(247, 247, 255);">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" class="px-6 py-4">NOME</th>
                                        <th scope="col" class="px-6 py-4">TELEFONE</th>
                                        <th scope="col" class="px-6 py-4">EMAIL</th>
                                        <th scope="col" class="px-6 py-4">CNPJ</th>
                                        <th scope="col" class="px-6 py-4">EMPRESA</th>
                                        {{-- <th scope="col" class="px-6 py-4">Primeiro contato via email</th>
                                        <th scope="col" class="px-6 py-4"></th>
                                        <th scope="col" class="px-6 py-4">Email com falha</th>
                                        <th scope="col" class="px-6 py-4">Data cadastro</th>
                                        <th scope="col" class="px-6 py-4">Usuário</th>
                                        <th scope="col" class="px-6 py-4"></th> --}}
                                    </tr>
                                </thead>
                                <style>
                                    .highlight-row {
                                        background-color: #f2f2f2; /* Escolha a cor que preferir */
                                        text-align: center;
                                    }
                                </style>
                                <tbody>
                                    @foreach ($model as $Model)
                                    <tr class="highlight-row">
                                        <td colspan="11">ORIGINADO DE: {{ $Model->MostraOrigem->nome ?? null }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ $Model->nome }}</td>
                                        <td>{{ $Model->telefone }}</td>
                                        <td>{{ $Model->email }}</td>
                                        <td>{{ $Model->cnpj }}</td>
                                        <td>{{ $Model->MostraEmpresa->Descricao }}</td>
                                    </tr>

                                    <tr>
                                       <td>Primeiro contato: {{ $Model->emailprimeirocontato == 1 ? 'SIM' : '' }}


                                            @if ($Model->emailprimeirocontato == null)
                                            <form method="GET" action="{{ route('Pacpie.MarcaEnviadoemailparaprimeirocontato', $Model->id) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-danger">Marcar primeiro contato por email</button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>Email com falhas:
                                            {{ $Model->emailcomfalhas == 1 ? 'SIM' : '' }}

                                            @if ($Model->emailprimeirocontato == true)
                                            <form method="GET" action="{{  route('Pacpie.Marcaemailcomfalhas', $Model->id) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-warning">Marcar email com falhas</button>
                                                {{-- <button onclick="location.reload(true);" class="btn btn-secondary">Recarregar Página</button> --}}
                                            </form>
                                            @endif
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>Data do registro: {{ $Model->created_at->format('d/m/Y H:i:s') }}</td>
                                    </tr>

                                    <tr>
                                        <td>Usuário que atualizou a ficha: {{ $Model->user_updated }}</td>
                                    </tr>


                                    <td>
                                        <div class="btn-group" role="group" aria-label="Ações">
                                            @can('PACPIE - EDITAR')
                                            <a href="{{ route('Pacpie.edit', $Model->id) }}" class="btn btn-success" tabindex="-1" role="button" aria-disabled="true">Editar</a>
                                            @endcan

                                            @can('PACPIE - VER')
                                            <a href="{{ route('Pacpie.show', $Model->id) }}" class="btn btn-info" tabindex="-1" role="button" aria-disabled="true">Ver</a>
                                            @endcan

                                            @can('PACPIE - EXCLUIR')
                                            <form method="POST" action="{{ route('Pacpie.destroy', $Model->id) }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="submit" class="btn btn-danger">Excluir</button>
                                            </form>
                                            @endcan
                                        </div>
                                      </td>

                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>


            </body>
            </html>




@endsection




@push('scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.min.js"></script>
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
                        $.confirm({
                            title: 'Confirmar!',
                            content: 'Deseja realmente continuar?',
                            buttons: {
                                confirmar: function() {
                                    e.currentTarget.submit();
                                },
                                cancelar: function() {
                                    // Cancel action
                                },
                            }
                        });
                    },
                    cancelar: function() {
                        // Cancel action
                    },
                }
            });
        });
    </script>
@endpush
