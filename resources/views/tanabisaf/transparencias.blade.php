
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transparências do TANABI SAF</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-green-900 text-white">

    @include('tanabisaf.header')

    <section class="max-w-4xl mx-auto py-12 px-6">
        <h2 class="text-4xl font-bold text-center mb-6">TRANSPARÊNCIAS</h2>
        <p class="text-green-300 text-lg leading-relaxed text-center">
            <strong>DO TANABI SAF</strong>
        </p>
        <p class="text-green-300 text-lg leading-relaxed mt-4">
            Apresentamos todas
        </p>
        <p class="text-green-300 text-lg leading-relaxed mt-4">
            as documentações oficiais
        </p>
    </section>




    <section class="bg-green-800 py-12 px-6">
        <div class="max-w-4xl mx-auto">
            <h3 class="text-3xl font-bold text-center text-red-500">ESTATUTO E DIRETORIA EM 16 DE DEZEMBRO DE 2024</h3>
            <p class="text-green-300 text-lg text-center mt-4">
                <a href="{{ route('download', ['id_arquivo' => 40491]) }}">Baixar Arquivo PDF</a>
            </p>
        </div>
    </section>

    <section class="bg-green-800 py-12 px-6">
        <div class="max-w-4xl mx-auto">
            <h3 class="text-3xl font-bold text-center text-red-500">BOLETIM DE SUBSCRIÇÃO EM 16 DE DEZEMBRO DE 2024</h3>
            <p class="text-green-300 text-lg text-center mt-4">
                <a href="{{ route('download', ['id_arquivo' => 40492]) }}">Baixar Arquivo PDF</a>
            </p>
        </div>
    </section>



    <section class="max-w-4xl mx-auto py-12 px-6">
        <h3 class="text-3xl font-bold text-center mb-6"> </h3>
        <ul class="text-gray-300 text-lg space-y-4">
            <li><strong class="text-red-500">CARTÃO CNPJ</strong>  </li>
                 <a href="{{ asset('tanabisaf/imagem/cnpj-tanabisaf-31032025.png') }}" target="_blank">
                <img src="{{ asset('tanabisaf/imagem/cnpj-tanabisaf-31032025.png') }}" alt="CNPJ" class="h-36">

            </a>

        </ul>
    </section>

    {{-- <<section class="bg-gray-800 py-12 px-6">
        <div class="max-w-4xl mx-auto">
            <h3 class="text-3xl font-bold text-center text-red-500">PLANO DE TRABALHO DO VEC PARA 2025</h3>
            <p class="text-gray-300 text-lg text-center mt-4">
                <a href="{{ route('download', ['id_arquivo' => 40662]) }}">Baixar Arquivo PDF</a>
            </p>
        </div>
    </section> --}}


    <section class="bg-gray-900 py-12 px-6">
        <div class="max-w-4xl mx-auto">
            <h3 class="text-3xl font-bold text-center text-red-500"> </h3>
            <p class="text-gray-300 text-lg text-center mt-4">


            </p>
        </div>
    </section>

    <section class="bg-gray-800 py-12 px-6">
        <div class="max-w-4xl mx-auto">
            <h3 class="text-3xl font-bold text-center text-red-500"> </h3>
            <p class="text-gray-300 text-lg text-center mt-4">


            </p>
                <p class="text-gray-300 text-lg text-center mt-4">

            </p>
                <p class="text-gray-300 text-lg text-center mt-4">

            </p>
                <p class="text-gray-300 text-lg text-center mt-4">


            </p>
        </div>
    </section>
</body>
</html>
