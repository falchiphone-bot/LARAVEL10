<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Categorias do TANABI SAF</title>
  <script src="https://cdn.tailwindcss.com"></script>

  @include('tanabisaf.header')
</head>
<body class="bg-green-900  text-white min-h-screen flex flex-col items-center pt-32 px-4">

  <table class="w-full max-w-md bg-green shadow-lg rounded-xl overflow-hidden">


    <caption class="text-xl font-bold text-blue-900 bg-blue-100 py-4">
        <a href="https://www.futebolpaulista.com.br/Competicoes/Tabela.aspx" class="text-blue-900 hover:underline" target="_blank" rel="noopener noreferrer">
          Para ver a tabela do Campeonato Paulista selecione a competição
        </a>
      </caption>


      @include('tanabisaf.jogos.2025.sub17.rodada01-12042025')

