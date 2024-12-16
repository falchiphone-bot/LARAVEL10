<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exemplo de Variáveis</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .variavel {
            margin-bottom: 10px;
        }
        .variavel span {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Dados Exibidos</h1>

        <div class="variavel">
            <span>Saldo:</span> {{ $saldo }}
        </div>

        <div class="variavel">
            <span>Data para Calcular:</span> {{ $dataCalcular }}
        </div>

        <div class="variavel">
            <span>Descrição:</span> {{ $descricao }}
        </div>

        <div class="variavel">
            <span>Próxima Data:</span> {{ $proximaData }}
        </div>

        <div class="variavel">
            <span>Débito:</span> {{ $debito }}
        </div>

        <div class="variavel">
            <span>Crédito:</span> {{ $credito }}
        </div>

        <div class="variavel">
            <span>Nova Descrição:</span> {{ $novaDescricao }}
        </div>

        <div class="variavel">
            <span>Juros Arredondado:</span> {{ $jurosArredondado }}
        </div>
    </div>
</body>
</html>

