<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dados - AVENUE / POUPANCA </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h1 class="mb-4">COMPARATIVO AVENUE COM POUPANÇA BRASILEIRA</h1>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>Nome</th>
                <th>Débito</th>
                <th>Crédito</th>
                <th>Saldo</th>
                <th>Dolar</th>
                <th>Selecionados</th>
                <th>Empresa</th>
                <th>De</th>
                <th>Até</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dados as $dado)
                <tr>
                    <td>{{ $dado['Nome'] }}</td>
                    <td>{{ number_format($dado['Débito'], 2, ',', '.') }}</td>
                    <td>{{ number_format($dado['Crédito'], 2, ',', '.') }}</td>
                    <td>{{ number_format($dado['Saldo'], 2, ',', '.') }}</td>
                    <td>{{ number_format($dado['Dolar'], 2, ',', '.') }}</td>
                    <td>{{ $dado['Selecionados'] }}</td>
                    <td>{{ $dado['Empresa'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($dado['De'])->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($dado['Até'])->format('d/m/Y') }}</td>

                </tr>
            @endforeach
            <tr>
                <td>TOTAL</td>
                <td>{{ number_format($Débito, 2, ',', '.') }}</td>
                <td>{{ number_format($Crédito, 2, ',', '.') }}</td>
                <td>{{ number_format($Saldo, 2, ',', '.') }}</td>
                <td>
                    Dolares : {{ number_format($somaDolar, 2, ',', '.') }}
                    Real : {{ number_format($somaDolarReal, 2, ',', '.') }}
                    <div>
                         Valor dolar hoje: US$ {{ number_format($valordolarhoje, 4, ',', '.') }}
                    </div>
                </td>
                <td></td>
                <td></td>
                <td></td>
            </tr>

        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
