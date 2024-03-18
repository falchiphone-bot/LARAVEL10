<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Visualização do Array</title>
</head>
<body>


<table border="1">
    <tr>
        <th>Código cidade</th>
        <th>Nome cidade</th>
        <th>Total</th>
        <th>Quantidade clientes</th>
    </tr>
   @foreach ($receberperiodo as $item)
        <tr>
            <td>{{ $item['Cidade'] }}</td>
            <td>{{ $item['NomeCidade'] }}</td>
            <td style="text-align: right;">{{ number_format($item['Sum'], 2, ',', '.') }} </td>


            <td style="text-align: center;">{{ $item['Count'] }}</td>

        </tr>
    @endforeach
</table>

</body>
</html>

