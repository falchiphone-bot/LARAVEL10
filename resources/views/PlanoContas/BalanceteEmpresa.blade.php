@extends('layouts.bootstrap5')
@section('content')
<div class="py-2 bg-light">
    <div class="container">
        {{-- <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Permissions</a></li>
              <li class="breadcrumb-item active" aria-current="page">edit</li>
            </ol>
          </nav> --}}

        <div class="card">
            @can('CONTABILIDADE - LISTAR')
            <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                <a class="btn btn-success" href="/PlanoContas/Balancetes">Balancete por período e empresa
                    selecionada</a>
            </nav>
            @endcan
            @can('EMPRESAS - LISTAR')
            <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                <a class="btn btn-primary" href="/Empresas">Selecionar empresa</a>
            </nav>
            @endcan

            <div class="badge bg-success text-wrap" style="width: 100%;">
                Contas de {{ session('Empresa')->Descricao }}
            </div>
            <div class="badge bg-primary text-wrap" style="width: 100%;">
                Período de {{ \Carbon\Carbon::parse($retorno['DataInicial'])->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($retorno['DataFinal'])->format('d/m/Y') }}
            </div>

            <div class="badge bg-danger text-wrap" style="width: 100%;">
                <h3>Agrupamentos sem definição: {{ $Agrupamentovazio }}</h3>
                <h3>Selecão: {{ $Selecao }}</h3>
                <p>
                <h3>Agrupar: {{ $Agrupar }}</h3>
                </p>
                <p>
                <h2>Ordem de % S/Recebimentos</h2>
                </p>
            </div>



            <hr>
            <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                VALOR TOTAL RECEBIDO NO PERÍODO: {{ number_format($ValorRecebido , 2, ',', '.') }}
            </nav>


            <table class="table table-bordered">

                <tr>
                    <th>Descrição</th>
                    <th>% S/Recebimentos</th>
                    <th>Saldo atual</th>
                    <th>Classificação</th>
                    <th>Grau</th>
                </tr>
                @php
                $CodigoAtivo = null;
                $CodigoPassivo = null;
                $Codigoatual = null;
                @endphp

                @foreach ($contasEmpresa as $conta)

                @php
                $Codigo = substr($conta['Codigo'], 0, 1)
                @endphp

                @if ($Codigo != $Codigoatual)
                <tr>

                    <td>
                        @if($Codigo == 1)

                        <div class="badge bg-secondary text-wrap" style="width: 100%; text-align: right;">
                            TOTAL DO ATIVO
                        </div>
                    <td>
                        <div class="badge bg-secondary text-wrap" style="width: 100%; text-align: right;">
                            {{ number_format(abs($somaPercentual), 2, ',', '.') }}
                        </div>
                    </td>

                    <td>
                        <div class="badge bg-secondary text-wrap" style="width: 100%; text-align: right;">
                            {{ number_format($SaldoAtualAtivo, 2, ',', '.') }}
                        </div>
                    </td>


                    @elseif($Codigo == 2)
                    <div class="badge bg-secondary text-wrap" style="width: 100%; text-align: right;">
                        TOTAL DO PASSIVO
                    </div>
                    <td>
                        <div class="badge bg-secondary text-wrap" style="width: 100%; text-align: right;">
                            {{ number_format(abs($somaPercentual), 2, ',', '.') }}
                        </div>
                    </td>

                    <td>
                        <div class="badge bg-secondary text-wrap" style="width: 100%; text-align: right;">
                            {{ number_format(abs($SaldoAtualPassivo), 2, ',', '.') }}
                        </div>
                    </td>
                    @elseif($Codigo == 3)

                    <div class="badge bg-secondary text-wrap" style="width: 100%; text-align: right;">
                        TOTAL DO DESPESAS
                    </div>
                    <td>
                        <div class="badge bg-secondary text-wrap" style="width: 100%; text-align: right;">
                            {{ number_format(abs($somaPercentual), 2, ',', '.') }}
                        </div>
                    </td>

                    <td>
                        <div class="badge bg-secondary text-wrap" style="width: 100%; text-align: right;">
                            {{ number_format($somaSaldoAtualDespesas, 2, ',', '.') }}
                        </div>
                    </td>
                    @elseif($Codigo == 4)
                    <div class="badge bg-secondary text-wrap" style="width: 100%; text-align: right;">
                        TOTAL DA RECEITA
                    </div>

                    <td>
                        <div class="badge bg-secondary text-wrap" style="width: 100%; text-align: right;">
                            {{ number_format(abs($somaPercentual), 2, ',', '.') }}
                        </div>
                    </td>
                    <td>
                        <div class="badge bg-secondary text-wrap" style="width: 100%; text-align: right;">
                            {{ number_format(abs($somaSaldoAtualReceitas), 2, ',', '.') }}
                        </div>
                    </td>
                    @endif
                    </td>
                </tr>
                @endif
                <tr>
                    <td style="text-align: left;">


                        @if ($conta['Grau'] == '1')
                        <div class="badge bg-primary text-wrap" style="width: 100%;">
                            {{ $conta['Descricao'] }}
                        </div>
                        @endif
                        @if ($conta['Grau'] == '2')
                        {{ $conta['Descricao'] }}
                        @endif
                        @if ($conta['Grau'] == '3')
                        {{ $conta['Descricao'] }}
                        @endif
                        @if ($conta['Grau'] == '4')
                        {{ $conta['Descricao'] }}
                        @endif
                        @if ($conta['Grau'] == '5')
                        <a href="/Contas/Extrato/{{ $conta['ID'] }}" class="btn btn-link">
                            @if($Agrupamentovazio == 'Agrupadosvazio')
                            {{$conta['Descricao'] }}
                            @else
                            @if ($Agrupar == 'Descricao')
                            {{ $conta['Agrupamento'] }} - {{$conta['Descricao'] }}
                            @elseif ($Agrupar == 'Agrupamento')
                            {{ $conta['NomeAgrupamento'] }}
                            @endif
                            @endif
                        </a>
                        @endif

                    </td>

                    <td style="text-align: right;">
                        {{ number_format(abs($conta['PercentualValorRecebido']), 2, ',', '.') }}
                    </td>

                    <td style="text-align: right;">

                        @if ($Ativo)
                                 {{ number_format(abs($conta['SaldoAtualAtivo']), 2, ',', '.') }}
                        @elseif ($Passivo)
                                 {{ number_format(abs($conta['SaldoAtualPassivo']), 2, ',', '.') }}
                        @else
                             {{ number_format(abs($conta['SaldoAtual']), 2, ',', '.') }}
                        @endif

                    </td>

                    <td>
                        <div class="badge bg-success text-wrap" style="width: 100%;">
                            {{ $conta['Codigo'] }}
                        </div>
                    </td>

                    <td>
                        <div class="badge bg-warning text-wrap" style="width: 100%;">
                            {{ $conta['Grau'] }}
                        </div>
                    </td>
                </tr>

                @php
                $Codigoatual = substr($conta['Codigo'], 0, 1)
                @endphp

                @endforeach
                <tr>
                    <td>

                    </td>
                    <td>
                        <div class="badge bg-warning text-wrap" style="width: 100%; text-align: right;">
                            SALDO TOTAL IGUAL A 0,00. SIGNIFICA TUDO CORRETO!
                        </div>

                    <td>
                        <div class="badge bg-warning text-wrap" style="width: 100%; text-align: right;">
                            {{ number_format($somaSaldoAtual, 2, ',', '.') }}
                        </div>
                    </td>
                    </td>
                </tr>
                <tr>

                    <td>

                    </td>
                    <td>
                        <div class="badge bg-warning text-wrap" style="width: 100%; text-align: right;">
                            RECEITAS - DESPESAS
                        </div>

                    <td>
                        <div class="badge bg-success text-wrap" style="width: 100%; text-align: right;">
                            {{ number_format($ResultadoReceitasDespesas, 2, ',', '.') }}
                        </div>
                    </td>
                    </td>
                </tr>



            </table>
            @endsection

            @push('scripts')
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>

            <script>
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
