

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
        <a href="https://www.futebolpaulista.com.br/Competicoes/Tabela.aspx"
        class="text-blue-900 hover:underline" target="_blank" rel="noopener noreferrer">
          Para ver a tabela do Campeonato Paulista selecione a competição
        </a>
      </caption>

    <caption class="text-xl font-bold text-blue-900 bg-blue-100 py-4">
      Paulista SUB15 - 2025 | Rodada 05
    </caption>
    <tr class="border-b">
      <th class="bg-gray-500 text-left px-6 py-4 w-1/3">Jogo</th>
      <td class="px-6 py-4">Tanabi SAF 1 X 4 Mirassol</td>
    </tr>
    <tr class="border-b">
      <th class="bg-gray-500 text-left px-6 py-4">Data</th>
      <td class="px-6 py-4">17/05/2025</td>
    </tr>
    <tr class="border-b">
      <th class="bg-gray-500 text-left px-6 py-4">Horário</th>
      <td class="px-6 py-4">9:00</td>
    </tr>
    <tr class="border-b">
        <th class="bg-gray-500 text-left px-6 py-4">Estádio</th>
        <td class="px-6 py-4">Estádio Municipal Prefeito Alberto Victolo / Tanabi-SP</td>
     </tr>
     <tr class="border-b">
        <th class="bg-gray-500 text-left px-6 py-4">Súmula</th>

        <td class="px-6 py-4">
        <p class="text-white-900 text-lg text-center mt-4">
            <a href="{{ route('download', ['id_arquivo' => 40799]) }}"target="_blank" rel="noopener noreferrer" >Baixar Arquivo PDF do jogo</a>
        </p>

        <tr>
            <th class="bg-gray-600 text-left px-6 py-4">Jogo completo</th>
            <td class="px-6 py-4 text-center">

              <div class="mt-4">
                <iframe width="480" height="320" src="https://www.youtube.com/embed/iO2gijJe-qM" title="AO VIVO COM IMAGENS - TANABI X MIRASSOL - PAULISTA SUB-15" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
              </div>
            </td>
          </tr>


          <tr>
            <th class="bg-gray-600 text-left px-6 py-4">Ùnico gol do Tanabi por Fernando Gasparini Lemes - (Fernando)o</th>
            <td class="px-6 py-4 text-center">

              <div class="mt-4">
                <iframe width="480" height="315"
                        src=" https://www.youtube.com/embed/iO2gijJe-qM?start=1700"
                         title="YouTube video player"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
              </div>
            </td>
          </tr>


          {{-- <tr>
            <th class="bg-gray-600 text-left px-6 py-4">BASTIDORES DO AMÉRICA - RIO PRETO</th>
                <td class="px-6 py-4 text-center">

                <div class="mt-4">
                    <iframe width="742" height="417"
                        src="https://www.youtube.com/embed/N7bceU5lCLM"
                        title="BASTIDORES DO AMÉRICA - RIO PRETO"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allowfullscreen>
                        </iframe>
                </div>
                </td>
         </tr> --}}





