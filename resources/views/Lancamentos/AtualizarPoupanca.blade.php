<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar Dados</title>
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
        form {
            margin-top: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, button {
            padding: 8px;
            margin-bottom: 10px;
            width: 100%;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Dados Exibidos</h1>




        <div class="variavel">
            <span>Empresa:</span> {{ $EmpresaID }}
        </div>

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

        <!-- Formulário de Atualização -->
        <form action="AtualizarDadosPoupanca" method="POST">
            @csrf

            <label for="EmpresaID">Empresa</label>
            <input type="text" id="EmpresaID" name="EmpresaID" value="{{ $EmpresaID }}" required>

            <label for="saldo">Saldo</label>
            <input type="text" id="saldo" name="saldo" value="{{ $saldo }}" required>

            <label for="dataCalcular">Data para Calcular</label>
            <input type="date" id="dataCalcular" name="dataCalcular" value="{{ $dataCalcular }}" required>

            <label for="descricao">Descrição</label>
            <input type="text" id="descricao" name="descricao" value="{{ $descricao }}" required>

            <label for="proximaData">Próxima Data</label>
            <input type="date" id="proximaData" name="proximaData" value="{{ $proximaData }}" required>

            <label for="debito">Débito</label>
            <input type="number" step="0.01" id="debito" name="debito" value="{{ $debito }}" required>

            <label for="credito">Crédito</label>
            <input type="number" step="0.01" id="credito" name="credito" value="{{ $credito }}" required>

            <label for="novaDescricao">Nova Descrição</label>
            <input type="text" id="novaDescricao" name="novaDescricao" value="{{ $novaDescricao }}" required>

            <label for="jurosArredondado">Juros Arredondado</label>
            <input type="number" step="0.01" id="jurosArredondado" name="jurosArredondado" value="{{ $jurosArredondado }}" required>

            <button type="submit">Atualizar Dados/Gravar</button>
        </form>
    </div>
</body>
</html>
