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
            <h2 class="text-4xl font-bold mb-4">INÍCIO</h2>
            <p class="text-gray-300 mb-6">
                Bem-vindo ao TANABI SAF
                Somos um clube comprometido com a formação de atletas e o desenvolvimento do futebol em TANABI no Estado de São Paulo. Nosso objetivo é oferecer um ambiente estruturado, promovendo o crescimento esportivo e social de nossos beneficiários.
            </p>

           <!-- Lei da SAF -->
<section class="mb-12">
    <h2 class="text-4xl font-bold text-white mb-4">Lei da SAF</h2>
    <a href="https://www.planalto.gov.br/ccivil_03/_ato2019-2022/2021/lei/l14193.htm"
       target="_blank"
       class="text-blue-400 hover:text-blue-300 transition duration-200 underline">
      Acessar Lei nº 14.193/2021
    </a>
  </section>

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


    </div>
        <div class="w-1/2 flex justify-center">
            <img src="{{ asset('tanabisaf/imagem/bola1.png') }}" alt="Logo" class="h-50">
        </div>
    </section>

</body>
</html>
