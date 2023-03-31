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

                <div class="badge bg-warning text-wrap" style="width: 100%; color: blue;">
                    PESQUISA AVANÇADA EM LANÇAMENTOS CONTÁBEIS
                </div>

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @elseif (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    @elseif (session('entrada'))
                    <div class="alert alert-danger">
                        {{ session('entrada') }}
                    </div>
                @endif

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


                {{-- @can('PESQUISA AVANCADA')
                    <a href="{{ route('PlanoContas.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
                        aria-disabled="true">Incluir contas no plano de contas padrão</a>
                @endcan --}}

                <form method="POST" action="{{ route('planocontas.pesquisaavancada.post') }}" accept-charset="UTF-8">
                    @csrf

                    <div class="card">
                        <div class="card-body" style="background-color: rgb(33, 244, 33)">
                            <div class="row">
                                <div class="col-6">

                                    <label for="Texto" style="color: black;">Texto a pesquisar</label>
                                    <input class="form-control @error('Descricao') is-invalid @else is-valid @enderror"
                                        name="Texto" size="70" type="text" id="Texto"
                                        value="{{ $retorno['Texto'] ?? null }}">
                                </div>
                                <div class="col-6">

                                    <label for="Valor" style="color: black;">Valor a pesquisar</label>
                                    <input class="form-control @error('Valor') is-invalid @else is-valid @enderror"
                                        name="Valor" size="30" type="number" step="0.01" id="Valor"
                                        value="{{ $retorno['Valor'] ?? null }}">
                                </div>

                                <div class="col-6">

                                    <label for="DataInicial" style="color: black;">Consulta após a data inicial</label>
                                    <input class="form-control @error('DataInicial') is-invalid @else is-valid @enderror"
                                        name="DataInicial" size="30" type="date" step="1" id="DataInicial"
                                        value="{{ $retorno['DataInicial'] ?? null }}">
                                </div>

                                <div class="col-6">

                                    <label for="DataFinal" style="color: black;">Consulta antes da data final</label>
                                    <input class="form-control @error('DataFinal') is-invalid @else is-valid @enderror"
                                        name="DataFinal" size="30" type="date" step="1" id="DataFinal"
                                        value="{{ $retorno['DataFinal'] ?? null }}">
                                </div>

                                <div class="col-6">

                                    <label for="Limite" style="color: black;">Limite de registros para retorno</label>
                                    <input class="form-control @error('limite') is-invalid @else is-valid @enderror"
                                        name="Limite" size="30" type="number" step="1" id="Limite"
                                        value="{{ $retorno['Limite'] ?? null }}">
                                </div>


                                <div class="col-6">
                                    <label for="Limite" style="color: black;">Empresas permitidas para o usuário</label>
                                    <select class="form-control select2" id="selEmpresa" name="EmpresaSelecionada">
                                        <option value="">
                                            Selecionar empresa
                                        </option>
                                        @foreach ($Empresas as $Empresa)
                                            <option @if ($retorno['EmpresaSelecionada'] == $Empresa->ID) selected @endif
                                                value="{{ $Empresa->ID }}">

                                                {{ $Empresa->Descricao }}
                                            </option>
                                        @endforeach


                                    </select>
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



                </form>


                <p>Total de lançamentos encontrados: {{ $pesquisa->count() }}</p>

                <table class="table" style="background-color: rgb(247, 247, 213);">

                    <tr>

                        <th>DATA</th>
                        <th>DÉBITO</th>
                        <th>CRÉDITO</th>
                        <th>VALOR</th>
                        <th>LANCAMENTO</th>
                        <th>EMPRESA</th>


                    </tr>
                    @foreach ($pesquisa as $lancamentos)
                        <tr>
                            <td>
                                {{ $lancamentos->DataContabilidade->format('d/m/Y') }}
                            </td>
                            <td align="left">

                                <a style="padding-left: 10px; Color:rgb(255, 0, 13); font-size: 18px;"
                                    href="/Contas/Extrato/{{ $lancamentos->ContaDebito->ID }}">
                                    {{ $lancamentos->ContaDebito->PlanoConta->Descricao }}
                                </a>
                            </td>
                            <td align="left">

                                <a style="padding-left: 10px; Color:blue; font-size: 18px;"
                                    href="/Contas/Extrato/{{ $lancamentos->ContaCredito->ID }}">
                                    {{ $lancamentos->ContaCredito->PlanoConta->Descricao }}
                                </a>

                            </td>

                            <td style="padding-left: 10px; Color:green; font-size: 18px;" align="right">

                                {{ number_format($lancamentos->Valor, 2, ',', '.') }}

                            </td>
                            <td style="padding-left: 10px; Color:black; font-size: 18px;">
                                {{ $lancamentos->DescricaoHistorico . '  ' . $lancamentos->Descricao }}
                            </td>

                            <td style="padding-left: 10px; Color:black; font-size: 18px;"
                            href="/Contas/Extrato/{{ $lancamentos->Empresa->ID }}">
                                {{ $lancamentos->Empresa->Descricao}}
                            </td>



                        </tr>
                    @endforeach
                </table>

                <table class="table" style="background-color: rgb(213, 247, 224);">
                    <tr>
                        <td colspan="2" style="padding-right: 10px; Color:rgb(255, 0, 0); font-size: 20px;"
                            align="right">
                            TOTAL GERAL
                        </td>
                        <td colspan="3" style="padding-right: 10px; Color:rgb(255, 0, 0); font-size: 20px;"
                            align="right">
                            {{ number_format($pesquisa->sum('Valor'), 2, ',', '.') }}
                        </td>
                    </tr>
                </table>
            @endsection

            @push('scripts')
                <link rel="stylesheet"
                    href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
                <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>

                <script>
                    $('form').submit(function(e) {
                        e.preventDefault();
                        $.confirm({
                            title: 'Confirmar!',
                            content: 'Confirma a consulta?',
                            buttons: {
                                confirmar: function() {
                                    // $.alert('Confirmar!');
                                    // $.confirm({
                                    //     title: 'Confirmar!',
                                    //     content: 'Deseja realmente continuar com a exclusão? Não terá retorno.',
                                    //     buttons: {
                                    //         confirmar: function() {
                                    //             // $.alert('Confirmar!');
                                    //             e.currentTarget.submit()
                                    //         },
                                    //         cancelar: function() {
                                    //             // $.alert('Cancelar!');
                                    //         },

                                    //     }
                                    // });
                                    e.currentTarget.submit()

                                },
                                cancelar: function() {
                                    // $.alert('Cancelar!');
                                },

                            }
                        });
                    });
                </script>
            @endpush
