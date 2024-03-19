@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Visualização</title>
</head>
<body>



    @php
    $inicio = DateTime::createFromFormat('Y-m-d', $data_vencimento_inicial);
    $fim = DateTime::createFromFormat('Y-m-d', $data_vencimento_final);
    @endphp

    <h1>
        Início em: {{ $inicio->format('d/m/Y') }}
        até {{ $fim->format('d/m/Y') }}
    </h1>


    {{-- <table border="4" responsive> --}}
        <table class="table">
        <tr style="background-color: lightblue; color: white;">
            <th>Código cidade</th>
            <th>Nome cidade</th>
            <th>Total</th>
            <th>Quantidade clientes</th>
        </tr>

        @php
            $totalSum = 0;
            $totalCount = 0;
        @endphp

        @foreach ($receberperiodo as $item)

        @php
            $totalSum += $item['Sum'];
            $totalCount += $item['Count'];
        @endphp

            <tr>
                <td>{{ $item['Cidade'] }}</td>
                <td>{{ $item['NomeCidade'] }}</td>
                <td style="text-align: right;">{{ number_format($item['Sum'], 2, ',', '.') }} </td>
                <td style="text-align: center;">{{ $item['Count'] }}</td>
            </tr>
        @endforeach
        <tr style="background-color: lightblue; color: white;">
            <td> </td>
            <td> TOTAL </td>
            <td style="text-align: right;">{{ number_format($totalSum, 2, ',', '.') }} </td>
            <td style="text-align: center;">{{ $totalCount }}</td>
        </tr>
    </table>


</div>
</div>
@endsection
</body>
</html>

