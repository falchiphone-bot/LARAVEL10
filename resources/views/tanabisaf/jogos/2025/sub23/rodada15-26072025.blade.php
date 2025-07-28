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
      Paulista SUB23 - 2025 | Rodada 15
    </div>

    <table class="w-full text-sm md:text-base">
      <tbody>
        <tr class="border-b border-gray-800">
          <th class="bg-gray-600 text-left px-6 py-4 w-1/3">Jogo</th>
          <td class="px-6 py-4">Independente de Limeira x Tanabi SAF</td>
        </tr>
        <tr class="border-b border-gray-700">
          <th class="bg-gray-600 text-left px-6 py-4">Data</th>
          <td class="px-6 py-4">26/07/2025</td>
        </tr>
        <tr class="border-b border-gray-700">
          <th class="bg-gray-600 text-left px-6 py-4">Horário</th>
          <td class="px-6 py-4">16:00</td>
        </tr>
        <tr class="border-b border-gray-700">
          <th class="bg-gray-600 text-left px-6 py-4">Estádio</th>
          <td class="px-6 py-4">Estádio Municipal Comendador Agostinho Prada /Limeira-SP</td>
        </tr>



        <tr>
            <th class="bg-gray-600 text-left px-6 py-4">Boletim financeiro</th>
            <td class="px-6 py-4 text-center">
              <a href="{{ route('download', ['id_arquivo' => 40954]) }}"
                 class="text-blue-300 font-semibold hover:text-blue-400 hover:underline transition duration-200"
                 target="_blank" rel="noopener noreferrer">
                Baixar Arquivo PDF do boletim financeiro do jogo
              </a>
            </td>
          </tr>


          <tr>
            <th class="bg-gray-600 text-left px-6 py-4">Jogo completo</th>
            <td class="px-6 py-4 text-center">

                 <div class="mt-4">
                    <iframe width="480" height="320" src="https://www.youtube.com/embed/sT0_R5Y5BfI" title="AO VIVO E COM IMAGENS: INDEPENDENTE X TANABI | RODADA 15 | PAULISTA SUB-23 2ª DIVISÃO SICREDI 2025" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                </div>
            </td>
          </tr>


        <tr>
            <th class="bg-gray-600 text-left px-6 py-4">Primeiro gol de Marcelo Enrique Maçola Filho - Marcelo</th>
            <td class="px-6 py-4 text-center">

              <div class="mt-4">
                <iframe width="480" height="315"
                        src="https://www.youtube.com/embed/sT0_R5Y5BfI?start=3781"
                        title="YouTube video player"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
              </div>
            </td>
          </tr>


             <tr>
                <th class="bg-gray-600 text-left px-6 py-4">Segundo gol de João Lucas Paim Addor  - ROMARINHO </th>
                <td class="px-6 py-4 text-center">

                <div class="mt-4">
                    <iframe width="480" height="315"
                             src="https://www.youtube.com/embed/sT0_R5Y5BfI?start=5839"
                            title="YouTube video player"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen></iframe>
                </div>
            </td>
          </tr>


          <th class="bg-gray-600 text-left px-6 py-4">Terceiro gol de Thales Gabriel Duarte Santiago</th>
            <td class="px-6 py-4 text-center">

              <div class="mt-4">
                <iframe width="480" height="315"
                        src="https://www.youtube.com/embed/sT0_R5Y5BfI?start=6354"
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

