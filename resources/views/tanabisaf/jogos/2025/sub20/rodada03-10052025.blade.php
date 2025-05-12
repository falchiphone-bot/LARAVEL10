<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Categorias do TANABI SAF</title>
  <script src="https://cdn.tailwindcss.com"></script>

 @include('tanabisaf.header')
</head>
<body class="bg-green-900 text-white min-h-screen flex flex-col items-center pt-20 px-4">

  <div class="w-full max-w-2xl bg-green-800 shadow-2xl rounded-2xl overflow-hidden">

    <div class="bg-blue-100 text-blue-900 text-center py-4 px-6">
      <a href="https://www.futebolpaulista.com.br/Competicoes/Tabela.aspx"
         class="font-bold hover:underline"
         target="_blank" rel="noopener noreferrer">
        Para ver a tabela do Campeonato Paulista selecione a competição
      </a>
    </div>

    <div class="bg-blue-200 text-blue-900 text-center text-xl font-semibold py-4 px-6">
      Paulista SUB20 - 2025 | Rodada 03
    </div>

    <table class="w-full text-sm md:text-base">
      <tbody>
        <tr class="border-b border-gray-800">
          <th class="bg-gray-600 text-left px-6 py-4 w-1/3">Jogo</th>
          <td class="px-6 py-4">São Paulo F C 0 x 2 Tanabi SAF</td>
        </tr>
        <tr class="border-b border-gray-700">
          <th class="bg-gray-600 text-left px-6 py-4">Data</th>
          <td class="px-6 py-4">10/05/2025</td>
        </tr>
        <tr class="border-b border-gray-700">
          <th class="bg-gray-600 text-left px-6 py-4">Horário</th>
          <td class="px-6 py-4">15:00</td>
        </tr>
        <tr class="border-b border-gray-700">
          <th class="bg-gray-600 text-left px-6 py-4">Estádio</th>
          <td class="px-6 py-4">Marcelo Portugal Gouvêa (Laudo Natel) / Cotia-SP</td>
        </tr>
        <tr>
          <th class="bg-gray-600 text-left px-6 py-4">Súmula</th>
          <td class="px-6 py-4 text-center">
            <a href="{{ route('download', ['id_arquivo' => 40770]) }}"
               class="text-blue-300 font-semibold hover:text-blue-400 hover:underline transition duration-200"
               target="_blank" rel="noopener noreferrer">
              Baixar Arquivo PDF do jogo
            </a>
          </td>
        </tr>

        {{-- <tr>
            <th class="bg-gray-600 text-left px-6 py-4">Boletim financeiro</th>
            <td class="px-6 py-4 text-center">
              <a href="{{ route('download', ['id_arquivo' => 40737]) }}"
                 class="text-blue-300 font-semibold hover:text-blue-400 hover:underline transition duration-200"
                 target="_blank" rel="noopener noreferrer">
                Baixar Arquivo PDF do boletim financeiro do jogo
              </a>
            </td>
          </tr> --}}


          <tr>
            <th class="bg-gray-600 text-left px-6 py-4">Jogo completo</th>
            <td class="px-6 py-4 text-center">

              <div class="mt-4">
                <iframe width="480" height="320" src="https://www.youtube.com/embed/DSaL2U7TVp0" title="JOGO COMPLETO: SÃO PAULO X TANABI | RODADA 3 | PAULISTA SUB-20 SICREDI 2025" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>            </td>
          </tr>


          <tr>
            <th class="bg-gray-600 text-left px-6 py-4">Primeiro gol de Nelson Miguel Rodrigues dos Santos Caetano - Nelson</th>
            <td class="px-6 py-4 text-center">

              <div class="mt-4">
                <iframe width="480" height="320"
                        src="https://www.youtube.com/embed/DSaL2U7TVp0?start=3953"
                        title="YouTube video player"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
              </div>
            </td>
          </tr>


          <tr>
            <th class="bg-gray-600 text-left px-6 py-4">Segundo gol de Giovanni da Cruz Pereira </th>
            <td class="px-6 py-4 text-center">

              <div class="mt-4">
                <iframe width="480" height="320"
                        src="https://www.youtube.com/embed/DSaL2U7TVp0?start=7550"
                        title="YouTube video player"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
              </div>
            </td>
          </tr>


      </tbody>
    </table>
  </div>

</body>
</html>

