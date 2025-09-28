<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IRMÃOS EMAUS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-blue-900 text-white">

    @include('emaus.header')

    <section class="flex justify-between items-center px-12 py-20 bg-blue-900">

        <div class="w-1/2">
            <h2 class="text-4xl font-bold mb-4">INÍCIO</h2>
            <p class="text-gray-300 mb-6">
                Bem-vindo ao Sistema Irmãos Emaus
                Somos uma organização dedicada ao cuidado e assistência social. Nosso objetivo é oferecer suporte estruturado, promovendo o desenvolvimento social e espiritual de nossos assistidos.
            </p>

            <div class="space-y-4">
                <a href="{{ route('Irmaos_EmausPia.index') }}" class="block bg-green-600 px-6 py-3 rounded-lg text-white font-semibold hover:bg-green-700 transition text-center">
                    CONTROLE DE PIA
                </a>
                <a href="{{ route('Irmaos_EmausServicos.index') }}" class="block bg-yellow-600 px-6 py-3 rounded-lg text-white font-semibold hover:bg-yellow-700 transition text-center">
                    SERVIÇOS
                </a>
                <a href="{{ route('Irmaos_Emaus_FichaControle.index') }}" class="block bg-purple-600 px-6 py-3 rounded-lg text-white font-semibold hover:bg-purple-700 transition text-center">
                    FICHA DE CONTROLE
                </a>
            </div>
        </div>

        <div class="w-1/2">
            <h2 class="text-4xl font-bold mb-4">Nossa Missão</h2>
            <p class="text-gray-300 mb-6">
                Desde nossa fundação, o foco está no desenvolvimento integral de pessoas em situação de vulnerabilidade, através de atividades de assistência social, formação espiritual e capacitação para o exercício da cidadania.
            </p>
            
            <div class="mt-8">
                <h3 class="text-2xl font-bold mb-4">Acesso Rápido</h3>
                <div class="grid grid-cols-1 gap-3">
                    <a href="{{ route('Irmaos_EmausServicos.EntradaSaida', ['id' => 1]) }}" class="bg-indigo-600 px-4 py-2 rounded text-center hover:bg-indigo-700 transition">
                        Entrada/Saída
                    </a>
                </div>
            </div>
        </div>

    </section>

</body>
</html>
