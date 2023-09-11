
<head>
    <style>
        /* Estilo para a tabela */
        table {
            border-collapse: collapse;
            width: 100%;
        }

        /* Estilo para as células do cabeçalho */
        th {
            background-color: #007BFF;
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
    </style>
</head>

<body>
Gerado em {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
    <table class="table" style="background-color: rgb(247, 247, 213);">
        <thead>

            <tr>
                <th>
                    AVALIAÇÃO DOS FORMANDOS/ATLETAS
                </th>
                <th>
                    Total de avaliações cadastrados

                </th>
                <th>
                    {{ $model->count() ?? 0 }}
                </th>
            </tr>
            <tr>
                <th scope="col" class="px-6 py-4">DATA</th>
                <th scope="col" class="px-6 py-4">NOTA</th>
                <th scope="col" class="px-6 py-4">NOME</th>

            </tr>
        </thead>

        <tbody>
            @foreach ($model as $Model)
            <tr>


                <td>{{ \Carbon\Carbon::parse($Model->created_at)->format('d/m/Y') }}</td>

                <td> {{ number_format($Model->avaliacao,2) }} </td>
                <td>
                    {{ $Model->MostraFormando->nome }}
                </td>

                @endforeach
        </tbody>
    </table>
    Gerado em {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}

</body>
