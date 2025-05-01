<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Galeria VEC por Subpastas</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white px-6 py-8">
    <h1 class="text-3xl font-bold mb-6">Galeria de Imagens VEC</h1>

    @foreach ($galerias as $pasta => $imagens)
        <div class="mb-10">
            <h2 class="text-xl font-semibold mb-3">{{ $pasta ? trim($pasta, '/') : 'Raiz' }}</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach ($imagens as $imagem)
                    @php $nomeArquivo = pathinfo($imagem, PATHINFO_BASENAME); @endphp
                    <div class="text-center group">
                        <a href="{{ $imagem }}" target="_blank">
                            <img src="{{ $imagem }}" alt="{{ $nomeArquivo }}"
                                 class="w-full h-32 object-cover rounded-lg shadow-sm transition-transform duration-300 group-hover:scale-105 group-hover:shadow-lg">
                        </a>
                        <p class="mt-2 text-sm text-gray-300 truncate">{{ $nomeArquivo }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</body>
</html>
