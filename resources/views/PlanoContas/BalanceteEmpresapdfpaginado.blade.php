<!DOCTYPE html>
<html>

<head>
    <style>
        /* Estilo para a tabela */
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 35px;
        }

        /* Estilo para as células do cabeçalho */
        th {
            background-color: blue;
            /* Cor de fundo do cabeçalho */
            color: white;
            /* Cor do texto do cabeçalho */
            border: 2px solid #333;
            /* Borda do cabeçalho */
            padding: 10px;
            /* Espaçamento interno do cabeçalho */
        }

        /* Estilo para as células dos dados */
        td {
            background-color: #F2F2F2;
            /* Cor de fundo das células de dados */
            border: 1px solid #333;
            /* Borda das células de dados */
            padding: 8px;
            /* Espaçamento interno das células de dados */
        }

        /* Estilo para o cabeçalho repetido em cada página */
        .header {
            position: fixed;
            top: 5;
            left: 5;
            right: 5;
            background-color: blue;
            color: white;
            text-align: center;
            padding: 10px;
        }

        /* Estilo para o rodapé repetido em cada página */
        .footer {
            position: fixed;
            bottom: 5;
            left: 5;
            right: 5;
            background-color: blue;
            color: white;
            text-align: center;
            padding: 10px;
        }

        /* Define os cabeçalhos e rodapés nas páginas impressas */
        @page {
            margin: 100px 25px 100px 25px;
            counter-increment: page;
            /* Incrementa o número da página */
        }
        @page :first {
        margin-top: 5px;
        }


        .page-number::before {
            content: "Página " counter(page);
            /* Insere o número da página */
        }

    </style>
</head>
<div style="text-align: center;">
    <h3>BALANCETE DE TODAS EMPRESAS</h3>
</div>

<body>
    <!-- Cabeçalho -->
    <!-- <div class="header">
    </div> -->

    <!-- Tabela de dados -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th><h4>Período de
                     {{ \Carbon\Carbon::parse($retorno['DataInicial'])
                        ->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($retorno['DataFinal'])
                            ->format('d/m/Y') }}</h4></td>
                <th>
                    <h5>VALOR TOTAL RECEBIDO NO PERÍODO:

                </th>
                <th>
                      {{ number_format($ValorRecebido , 2, ',', '.') }}</h5>
                </th>
            </tr>
            <tr>
                <th>Descrição</th>
                <th>% S/Recebimentos</th>
                <th>Saldo atual</th>
            </tr>
        </thead>
        @php
        $CodigoAtivo = null;
        $CodigoPassivo = null;
        $Codigoatual = null;
        @endphp
        <tbody>
            <!-- Seu conteúdo da tabela aqui -->

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
                        {{ number_format(abs($somaSaldoAtualPassivo), 2, ',', '.') }}
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
                        {{ $conta['Descricao'] }}
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
            </tbody>
    </table>

    <table class="table table-bordered">
    <tbody>
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
                </td>
                <td>
                    <div class="badge bg-success text-wrap" style="width: 100%; text-align: right;">
                        {{ number_format($ResultadoReceitasDespesas, 2, ',', '.') }}
                    </div>
                </td>

            </tr>
        </tbody>
    </table>
</body>

</html>
