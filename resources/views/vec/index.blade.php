<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VOTUPORANGA ESPORTE CLUBE</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">

    @include('vec.header')

    <section class="flex justify-between items-center px-12 py-20 bg-gray-900">

        <div class="w-1/2">
            <h2 class="text-4xl font-bold mb-4">INÍCIO</h2>
            <p class="text-gray-300 mb-6">
                Bem-vindo ao Votuporanga Esporte Clube
                Somos um clube comprometido com a formação de atletas e o desenvolvimento do futsal em Votuporanga no Estado de São Paulo. Nosso objetivo é oferecer um ambiente estruturado, promovendo o crescimento esportivo e social de nossos beneficiários.

            </p>




        <div class="w-1/2">
            <h2 class="text-4xl font-bold mb-4">Nossa história</h2>
            <p class="text-gray-300 mb-6">
                Em 2007, nasceu o VOTUPORANGA ESPORTE CLUBE – VEC. Desde seu nascimento, o foco está no desenvolvimento integral de crianças, adolescentes e jovens, através de atividades esportivas e na formação para o exercício da cidadania.
            </p>
            <a href="vec.historia1" class="bg-red-500 px-6 py-2 rounded-lg text-white font-bold hover:bg-red-600">LEIA MAIS</a>
        </div>

    </div>
        <div class="w-1/2 flex justify-center">
            <img src="{{ asset('vec/imagem/bola1.png') }}" alt="Logo" class="h-50">
        </div>
    </section>

</body>
</html>
