 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dados - Gabriel Magossi Falchi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h1 class="mb-4">RETIRADAS AGRUPADAS POR MES</h1>


                @php
                           $totalgeral = 0;
                @endphp
            @foreach ($DadosMes as $mes => $lancamentos)
        <h2>Mês: {{ $mes }}</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>Data</th>

                    <th>Valor</th>
                    <th>NomeEmpresa</th>


                </tr>
            </thead>
            <tbody>
                @php
                    $total = 0;

                @endphp
                @foreach ($lancamentos as $lancamento)
                    <tr>
                        <td>{{ $lancamento->DataContabilidade->format('d/m/Y') }}</td>

                        <td>{{ $lancamento->Valor }}</td>

                        <td>{{ $lancamento->NomeEmpresa }}</td>
                         
                    </tr>
                         @php
                            $total += $lancamento->Valor;
                        @endphp
                 @endforeach
                 @php
                            $totalgeral += $total;
                        @endphp
            <tr>
                <td><strong>Total</strong></td>
                <td><strong>{{ number_format($total, 2, ',', '.') }}</strong></td>
            </tr>
            </tbody>

        </table>
        <tr>
            <td><strong>Total geral</strong></td>
            <td><strong>{{ number_format($totalgeral, 2, ',', '.') }}</strong></td>
        </tr>
    @endforeach


</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

