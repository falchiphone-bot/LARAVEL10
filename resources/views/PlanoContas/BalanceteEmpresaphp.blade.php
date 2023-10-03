
{{-- <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Permissions</a></li>
              <li class="breadcrumb-item active" aria-current="page">edit</li>
            </ol>
          </nav> --}}

<div class="card">


    <!DOCTYPE html>
    <html>

    <head>
        <style>
            table {
                background-color: #e0f2e9;
                /* Fundo verde claro */
                border-collapse: collapse;
                width: 100%;
            }

            th,
            td {
                border: 3px solid #dddddd;
                text-align: left;
                padding: 3px;
            }

            th {
                background-color: #e6f7ff;
                /* Fundo azul claro para cabeçalho */
                color: #003366;
                /* Texto em azul escuro para cabeçalho */
            }

            td {
                background-color: #ffffff;
                /* Fundo branco para células de dados */
                color: #003366;
                /* Texto em azul escuro para células de dados */
            }
        </style>
    </head>

    <body>

        <table>
            <tr>
                <th colspan="3">
                    <h1>BALANCETE DE TODAS EMPRESAS</h1>
                </th>
            </tr>
            <tr>
                <td colspan="3">
                    <h2>Período de {{ \Carbon\Carbon::parse($retorno['DataInicial'])->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($retorno['DataFinal'])->format('d/m/Y') }}</h2>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <h3>VALOR TOTAL RECEBIDO NO PERÍODO: {{ number_format($ValorRecebido , 2, ',', '.') }}</h3>
                </td>
            </tr>

        </table>

    </body>

    </html>


    <hr>

    <head>
        <style>
            /* Estilo para adicionar bordas */
            table {
                border-collapse: collapse;
                width: 100%;
                border: 1px solid #ccc;
            }

            th,
            td {
                border: 1px solid #ccc;
                padding: 8px;
                text-align: left;
            }

            /* Estilo para realçar a primeira linha como cabeçalho */
            th {
                background-color: #e0eaf0;
            }
        </style>
    </head>

    <body>

        <table class="table table-bordered">

            <tr>
                <th>Descrição</th>
                <th>% S/Recebimentos</th>
                <th>Saldo atual</th>
                <hr>
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
                <div class="badge text-wrap" style="width: 100%; text-align: center; color: red;">
                        {{ number_format(abs($somaPercentual), 2, ',', '.') }}
                    </div>
                </td>


                <td>
                <div class="badge text-wrap" style="width: 100%; text-align: center; color: green;">
                        {{ number_format(abs($somaPercentual), 2, ',', '.') }}
                    </div>
                </td>



                @elseif($Codigo == 2)
                <div class="badge bg-secondary text-wrap" style="width: 100%; text-align: right;">
                    TOTAL DO PASSIVO
                </div>
                <td>
                <div class="badge text-wrap" style="width: 100%; text-align: center; color: red;">
                        {{ number_format(abs($somaPercentual), 2, ',', '.') }}
                    </div>
                </td>

                <td>
                <div class="badge text-wrap" style="width: 100%; text-align: center; color: green;">
                        {{ number_format(abs($SaldoAtualPassivo), 2, ',', '.') }}
                    </div>
                </td>
                @elseif($Codigo == 3)

                <div class="badge bg-secondary text-wrap" style="width: 100%; text-align: right;">
                    TOTAL DO DESPESAS
                </div>
                <td>
                <div class="badge text-wrap" style="width: 100%; text-align: center; color: red;">
                        {{ number_format(abs($somaPercentual), 2, ',', '.') }}
                    </div>
                </td>

                <td>
                <div class="badge text-wrap" style="width: 100%; text-align: center; color: green;">
                        {{ number_format($somaSaldoAtualDespesas, 2, ',', '.') }}
                    </div>
                </td>
                @elseif($Codigo == 4)
                <div class="badge bg-secondary text-wrap" style="width: 100%; text-align: right;">
                    TOTAL DA RECEITA
                </div>

                <td>
                     <div class="badge text-wrap" style="width: 100%; text-align: center; color: red;">
                        {{ number_format(abs($somaPercentual), 2, ',', '.') }}
                    </div>
                </td>
                <td>
                <div class="badge text-wrap" style="width: 100%; text-align: center; color: green;">
                        {{ number_format(abs($somaSaldoAtualReceitas), 2, ',', '.') }}
                    </div>
                </td>
                @endif
                </td>
            </tr>
            @endif

            <tr>
                <td style="text-align: left;">
                    @if ($conta['Grau'] == '5')
                        <div class="badge text-wrap" style="width: 100%; text-align: left; color: black;">
                            {{ $conta['NomeAgrupamento'] }}
                        </div>
                    @endif

                </td>

                <td style="text-align: center;">
                    <div class="badge text-wrap" style="width: 100%; text-align: center; color: red;">
                        {{ number_format(abs($conta['PercentualValorRecebido']), 2, ',', '.') }}
                    </div>
                </td>

                <td style="text-align: right;">
                     <div class="badge text-wrap" style="width: 100%; text-align: center; color: green;">
                       {{ number_format(abs($conta['SaldoAtual']), 2, ',', '.') }}
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
        <hr>

</div>
</body>
