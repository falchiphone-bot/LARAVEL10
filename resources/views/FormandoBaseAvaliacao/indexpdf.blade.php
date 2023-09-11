

        <div class="card">
            <div class="badge bg-primary text-wrap" style="width: 100%;font-size: 24px;lign=˜Center˜">
                AVALIAÇÃO DOS FORMANDOS/ATLETAS
            </div>
        </div>


        <div class="card-body">


            <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">


                <div class="card-header">
                    <div class="badge bg-info text-wrap" style="width: 100%;font-size: 24px">
                        <p>Total de avaliações cadastrados:
                            {{ $model->count() ?? 0 }}
                        </p>
                    </div>
                </div>

        </div>
 
<head>
    <style>
        /* Estilo para a tabela */
        table {
            border-collapse: collapse;
            width: 100%;
        }

        /* Estilo para as células do cabeçalho */
        th {
            background-color: #007BFF; /* Cor de fundo do cabeçalho */
            color: white; /* Cor do texto do cabeçalho */
            border: 2px solid #333; /* Borda do cabeçalho */
            padding: 10px; /* Espaçamento interno do cabeçalho */
        }

        /* Estilo para as células dos dados */
        td {
            background-color: #F2F2F2; /* Cor de fundo das células de dados */
            border: 1px solid #333; /* Borda das células de dados */
            padding: 8px; /* Espaçamento interno das células de dados */
        }
    </style>
</head>
<body>

<table class="table" style="background-color: rgb(247, 247, 213);">
                <thead>
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

</body>

