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
            <h2 class="text-4xl font-bold mb-4">TANABI FORMANDO - ÁLVARES FLORENCE - SP</h2>
            <p class="text-gray-300 mb-6">
                Bem-vindo ao TANABI SAF
                Somos um clube comprometido com a formação de atletas e o desenvolvimento do futebol em TANABI no Estado de São Paulo. Nosso objetivo é oferecer um ambiente estruturado, promovendo o crescimento esportivo e social de nossos beneficiários.
            </p>

 <hr>


    </div>
        <div class="w-1/2 flex justify-center">
            <img src="{{ asset('tanabisaf/imagem/bola1.png') }}" alt="Logo" class="h-50">
        </div>
    </section>





        <div class="max-w-4xl mx-auto">

            <ul class="text-gray-300 text-lg text-center mt-4">
                <li>
                    <a href="{{ route('download', ['id_arquivo' => 40754]) }}" target="_blank"
                        class="block p-2 text-blue-400 underline hover:text-red-500">
                        OFÍCIO DE SOLICITAÇÃO DE MATERIAL ESPORTIVO À SECRETARIA DE ESPORTES DO ESTADO DE SÃO PAULO PELO PREFEITO ADILSON BASTISTA LEITE
                     </a>

                </li>
            </ul>

            <ul class="text-gray-300 text-lg text-center mt-4">
                <li>
                    <a href="{{ route('download', ['id_arquivo' => 40755]) }}" target="_blank"
                        class="block p-2 text-blue-400 underline hover:text-red-500">
                         Uniforme para viagem dos atletas aos jogos
                     </a>

                </li>
            </ul>

            <ul class="text-gray-300 text-lg text-center mt-4">
                <li><a href="{{ url('/alvaresflorence') }}" class="block p-2 hover:text-red-500">Documentos/Videos</a></li>
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
