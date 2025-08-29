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
      Paulista SUB23 - 2025 | Rodada 10
    </div>

    <table class="w-full text-sm md:text-base">
      <tbody>
        <tr class="border-b border-gray-800">
          <th class="bg-gray-600 text-left px-6 py-4 w-1/3">Jogo</th>
          <td class="px-6 py-4">Tanabi SAF 5 x 0 Olimpia</td>
        </tr>
        <tr class="border-b border-gray-700">
          <th class="bg-gray-600 text-left px-6 py-4">Data</th>
          <td class="px-6 py-4">21/06/2025</td>
        </tr>
        <tr class="border-b border-gray-700">
          <th class="bg-gray-600 text-left px-6 py-4">Horário</th>
          <td class="px-6 py-4">15:00</td>
        </tr>
        <tr class="border-b border-gray-700">
          <th class="bg-gray-600 text-left px-6 py-4">Estádio</th>
          <td class="px-6 py-4">Estádio Municipal Prefeito Alberto Victolo / Tanabi</td>
        </tr>



        <tr>
          <th class="bg-gray-600 text-left px-6 py-4">Súmula</th>
          <td class="px-6 py-4 text-center">
            <a href="{{ route('download', ['id_arquivo' => 40891]) }}"
               class="text-blue-300 font-semibold hover:text-blue-400 hover:underline transition duration-200"
               target="_blank" rel="noopener noreferrer">
              Baixar Arquivo PDF do jogo
            </a>
          </td>
        </tr>

        <tr>
            <th class="bg-gray-600 text-left px-6 py-4">Boletim financeiro</th>
            <td class="px-6 py-4 text-center">
              <a href="{{ route('download', ['id_arquivo' => 40892]) }}"
                 class="text-blue-300 font-semibold hover:text-blue-400 hover:underline transition duration-200"
                 target="_blank" rel="noopener noreferrer">
                Baixar Arquivo PDF do boletim financeiro do jogo
              </a>
            </td>
          </tr>


          <tr>
                <th class="bg-gray-600 text-left px-6 py-4">
                    LEI 2987/2019 - CESSÃO DE USO DO ESTÁDIO
                </th>
                <td class="px-6 py-4 text-center">
                    <a href="{{ route('download', ['id_arquivo' => 40989]) }}"
                    class="flex items-center justify-center gap-2 px-4 py-2 bg-gray-800 rounded-lg shadow-md text-blue-300 font-semibold hover:text-blue-400 hover:underline hover:bg-gray-700 hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 w-full"
                    target="_blank" rel="noopener noreferrer">
                        <!-- Ícone PDF com hover -->
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"
                            class="h-5 w-5 text-red-500 transition-colors duration-200 hover:text-red-400"
                            fill="currentColor">
                            <path d="M181.9 256.2c-5.4 0-9.7 4.3-9.7 9.7 0 5.4 4.3 9.7 9.7 9.7 5.4 0 9.7-4.3 9.7-9.7 0-5.4-4.3-9.7-9.7-9.7zm-48.5 73.1c-13.4 0-24.2 10.8-24.2 24.2 0 13.4 10.8 24.2 24.2 24.2 13.4 0 24.2-10.8 24.2-24.2 0-13.4-10.8-24.2-24.2zm162.6-170.5c-9.8-9.8-25.6-9.8-35.4 0l-23.4 23.4-23.4-23.4c-9.8-9.8-25.6-9.8-35.4 0-9.8 9.8-9.8 25.6 0 35.4l23.4 23.4-23.4 23.4c-9.8 9.8-9.8 25.6 0 35.4 9.8 9.8 25.6 9.8 35.4 0l23.4-23.4 23.4 23.4c9.8 9.8 25.6 9.8 35.4 0 9.8-9.8 9.8-25.6 0-35.4l-23.4-23.4 23.4-23.4c9.8-9.8 9.8-25.6 0-35.4z"/>
                        </svg>
                        Baixar Arquivo PDF do comprovante de transferência de uso do Estádio Municipal Prefeito Alberto Victolo
                    </a>

                    <a href="{{ route('download', ['id_arquivo' => 40990]) }}"
                    class="flex items-center justify-center gap-2 px-4 py-2 bg-gray-800 rounded-lg shadow-md text-blue-300 font-semibold hover:text-blue-400 hover:underline hover:bg-gray-700 hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 w-full"
                    target="_blank" rel="noopener noreferrer">
                        <!-- Ícone PDF com hover -->
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"
                            class="h-5 w-5 text-red-500 transition-colors duration-200 hover:text-red-400"
                            fill="currentColor">
                            <path d="M181.9 256.2c-5.4 0-9.7 4.3-9.7 9.7 0 5.4 4.3 9.7 9.7 9.7 5.4 0 9.7-4.3 9.7-9.7 0-5.4-4.3-9.7-9.7-9.7zm-48.5 73.1c-13.4 0-24.2 10.8-24.2 24.2 0 13.4 10.8 24.2 24.2 24.2 13.4 0 24.2-10.8 24.2-24.2 0-13.4-10.8-24.2-24.2zm162.6-170.5c-9.8-9.8-25.6-9.8-35.4 0l-23.4 23.4-23.4-23.4c-9.8-9.8-25.6-9.8-35.4 0-9.8 9.8-9.8 25.6 0 35.4l23.4 23.4-23.4 23.4c-9.8 9.8-9.8 25.6 0 35.4 9.8 9.8 25.6 9.8 35.4 0l23.4-23.4 23.4 23.4c9.8 9.8 25.6 9.8 35.4 0 9.8-9.8 9.8-25.6 0-35.4l-23.4-23.4 23.4-23.4c9.8-9.8 9.8-25.6 0-35.4z"/>
                        </svg>
                        Baixar Arquivo PDF do RELATÓRIO do comprovante de transferência de uso do Estádio Municipal Prefeito Alberto Victolo
                    </a>
                </td>
            </tr>

          <tr>
            <th class="bg-gray-600 text-left px-6 py-4">Jogo completo</th>
            <td class="px-6 py-4 text-center">

                 <div class="mt-4">
                    <iframe width="480" height="320" src="https://www.youtube.com/embed/gHXu44la0eA" title="JOGO COMPLETO: TANABI X OLÍMPIA | RODADA 10 | PAULISTA SUB-23 2ª DIVISÃO SICREDI 2025" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                </div>
            </td>
          </tr>


        <tr>
            <th class="bg-gray-600 text-left px-6 py-4">Primeiro gol de Marcelo Enrique Maçola Filho - Marcelo</th>
            <td class="px-6 py-4 text-center">

              <div class="mt-4">
                <iframe width="480" height="315"
                        src="https://www.youtube.com/embed/gHXu44la0eA?start=1585"
                        title="YouTube video player"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
              </div>
            </td>
        </tr>


             <tr>
                <th class="bg-gray-600 text-left px-6 py-4">Segundo gol de Diogo Vieira Santos Fonseca</th>
                <td class="px-6 py-4 text-center">

                    <div class="mt-4">
                        <iframe width="480" height="315"
                                src="https://www.youtube.com/embed/gHXu44la0eA?start=5057"
                                title="YouTube video player"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen></iframe>
                    </div>
                </td>
             </tr>



          <th class="bg-gray-600 text-left px-6 py-4">Terceiro gol de Maicon Henrique Silva de Carvalho - (Maicon)</th>
            <td class="px-6 py-4 text-center">

              <div class="mt-4">
                <iframe width="480" height="315"
                        src="https://www.youtube.com/embed/gHXu44la0eA?start=5638"
                        title="YouTube video player"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
              </div>
            </td>
          </tr>

          <th class="bg-gray-600 text-left px-6 py-4">Quarto gol de João Lucas Paim Addor (- ROMARINHO) </th>
            <td class="px-6 py-4 text-center">

              <div class="mt-4">
                <iframe width="480" height="315"
                        src="https://www.youtube.com/embed/gHXu44la0eA?start=6634"
                        title="YouTube video player"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
              </div>
            </td>
          </tr>

          <tr>
                <th class="bg-gray-600 text-left px-6 py-4">Quinto gol de Diogo Vieira Santos Fonseca</th>
                <td class="px-6 py-4 text-center">

                    <div class="mt-4">
                        <iframe width="480" height="315"
                                src="https://www.youtube.com/embed/gHXu44la0eA?start=6925"
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

