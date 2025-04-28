<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TANABI SAF</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-green-900 text-white">

    @include('tanabisaf.header')

    <section class="flex justify-between items-center px-12 py-20 bg-green-900">

        <div class="w-1/2">
            <h2 class="text-4xl font-bold mb-4">TANABI FORMANDO</h2>
            <p class="text-gray-300 mb-6">
                Bem-vindo ao TANABI SAF
                Somos um clube comprometido com a formação de atletas e o desenvolvimento do futebol em TANABI no Estado de São Paulo. Nosso objetivo é oferecer um ambiente estruturado, promovendo o crescimento esportivo e social de nossos beneficiários.
            </p>




  <!-- Nossa História -->
  <section class="flex flex-col md:flex-row md:items-start gap-8">
    <div class="md:w-1/2">
      <h2 class="text-4xl font-bold text-white mb-4">Nossa História</h2>
      <p class="text-gray-300 text-lg leading-relaxed mb-6">
        Em 1942, nasceu o TANABI ESPORTE CLUBE, e em 18.12.2024, foi transformado em TANABI SAF.
        Desde seu nascimento, o foco está no desenvolvimento integral de crianças, adolescentes e jovens,
        através de atividades esportivas e na formação para o exercício da cidadania.
      </p>


      <a href="tanabisaf.historia1"
         class="inline-block bg-red-600 px-6 py-3 rounded-lg text-white font-semibold hover:bg-red-700 transition">
        LEIA MAIS
      </a>

    </div>
  </section>

<hr>


    </div>
        <div class="w-1/2 flex justify-center">
            <img src="{{ asset('tanabisaf/imagem/bola1.png') }}" alt="Logo" class="h-50">
        </div>
    </section>





        <div class="max-w-4xl mx-auto">
            {{-- <h3 class="text-3xl font-bold text-center text-red-500">Nossos Valores</h3> --}}

            <h2 class="text-4xl font-bold text-white mb-4">TANABI FORMANDO E SUAS BASES DE FORMAÇÃO</h2>

            <p class="text-green-300 text-lg text-center mt-4">
                Atendemos diversas categorias do futebol, desde a iniciação até a equipe adulta, proporcionando uma formação completa para os atletas.
            </p>
            <p class="text-green-300 text-lg text-center mt-4">
                Participação nos campeonatos paulistas, com o objetivo de desenvolver habilidades técnicas, táticas e sociais.
            </p>
            <p class="text-green-300 text-lg text-center mt-4">
                Clique no nome das base de treinamentos e captação para mais informações.
            </p>


            <ul class="text-gray-300 text-lg text-center mt-4">
                <li>
                    <a href="{{ url('/tanabisaf.tanabiformando.alvaresflorence') }}"
                       class="block p-2 text-blue-400 underline hover:text-red-500">
                        Álvares Florence - SP
                    </a>
                </li>
            </ul>

            <ul class="text-gray-300 text-lg text-center mt-4">
                <li>
                    {{-- <a href="{{ url('/tanabisaf.categoriasub15') }}" --}}
                       {{-- class="block p-2 text-blue-400 underline hover:text-red-500"> --}}

                    </a>
                </li>
            </ul>



            <ul class="text-gray-300 text-lg text-center mt-4">
                <li>
                    {{-- <a href="{{ url('/tanabisaf.categoriasub15') }}" --}}
                       {{-- class="block p-2 text-blue-400 underline hover:text-red-500"> --}}

                    </a>
                </li>
            </ul>


        </div>
    </section>



</body>
</html>
