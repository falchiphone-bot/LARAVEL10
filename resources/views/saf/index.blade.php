<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAF - SOCIEDADE ANÔNIMA DO FUTEBOL</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-green-900 text-white">

    @include('saf.header')

    <section class="flex justify-between items-center px-12 py-20 bg-green-900">

        <div class="w-1/2">
            <h2 class="text-4xl font-bold mb-4">INÍCIO</h2>
            <p class="text-gray-300 mb-6">
                Bem-vindo ao Sistema SAF - Sociedade Anônima do Futebol
                Sistema completo para gestão de clubes, federações, campeonatos e toda estrutura organizacional do futebol brasileiro conforme a Lei 14.193/2021.
            </p>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('SafClubes.index') }}" class="block bg-blue-600 px-4 py-3 rounded-lg text-white font-semibold hover:bg-blue-700 transition text-center">
                        CLUBES
                    </a>
                    <a href="{{ route('SafFederacoes.index') }}" class="block bg-purple-600 px-4 py-3 rounded-lg text-white font-semibold hover:bg-purple-700 transition text-center">
                        FEDERAÇÕES
                    </a>
                    <a href="{{ route('SafCampeonatos.index') }}" class="block bg-yellow-600 px-4 py-3 rounded-lg text-white font-semibold hover:bg-yellow-700 transition text-center">
                        CAMPEONATOS
                    </a>
                    <a href="{{ route('SafAnos.index') }}" class="block bg-red-600 px-4 py-3 rounded-lg text-white font-semibold hover:bg-red-700 transition text-center">
                        TEMPORADAS
                    </a>
                </div>
            </div>
        </div>

        <div class="w-1/2">
            <h2 class="text-4xl font-bold mb-4">Gestão Administrativa</h2>
            <p class="text-gray-300 mb-6">
                Módulos para gestão completa da estrutura administrativa e financeira das SAFs, incluindo colaboradores, tipos de prestadores e faixas salariais.
            </p>
            
            <div class="space-y-4">
                <div class="grid grid-cols-1 gap-3">
                    <a href="{{ route('SafColaboradores.index') }}" class="bg-indigo-600 px-4 py-2 rounded text-center hover:bg-indigo-700 transition">
                        Colaboradores
                    </a>
                    <a href="{{ route('SafTiposPrestadores.index') }}" class="bg-teal-600 px-4 py-2 rounded text-center hover:bg-teal-700 transition">
                        Tipos de Prestadores
                    </a>
                    <a href="{{ route('SafFaixasSalariais.index') }}" class="bg-orange-600 px-4 py-2 rounded text-center hover:bg-orange-700 transition">
                        Faixas Salariais
                    </a>
                </div>
            </div>

            <div class="mt-8">
                <h3 class="text-2xl font-bold mb-4">Lei da SAF</h3>
                <a href="https://www.planalto.gov.br/ccivil_03/_ato2019-2022/2021/lei/l14193.htm" 
                   target="_blank" 
                   class="text-yellow-300 hover:text-yellow-400 underline">
                    Lei nº 14.193/2021 - Regime Jurídico da SAF
                </a>
            </div>
        </div>

    </section>

</body>
</html>
