<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeria de fotos do VEC</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">

    @include('vec.header')
@section('content')
    <div class="container mx-auto">
        <h1 class="text-2xl font-bold mb-4">Galeria de Imagens VEC</h1>
        @php
    // Extrai o nome do arquivo e remove duplicatas
    $imagensUnicas = collect($imagens)->unique(function($item) {
        return pathinfo($item, PATHINFO_BASENAME);
    });
@endphp

<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
    @foreach ($imagensUnicas as $imagem)
        @php
            $nomeArquivo = pathinfo($imagem, PATHINFO_BASENAME);
        @endphp
        <div class="text-center group">
            <a href="{{ $imagem }}" target="_blank" class="block">
                <img src="{{ $imagem }}" alt="{{ $nomeArquivo }}"
                     class="w-full h-32 object-cover rounded-lg shadow-sm transition-transform duration-300 group-hover:scale-105 group-hover:shadow-lg">
            </a>
            <p class="mt-2 text-sm text-gray-700 truncate">{{ $nomeArquivo }}</p>
        </div>
    @endforeach
</div>

    </div>
</body>
</body>
</html>
