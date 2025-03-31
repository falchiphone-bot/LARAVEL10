
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensagem do VEC</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">

    @include('tanabisaf.header')

    <section class="max-w-4xl mx-auto py-12 px-6">
        <h2 class="text-4xl font-bold text-center mb-6">MENSAGEM</h2>


    <section class="bg-green-800 py-12 px-6">
        <div class="max-w-4xl mx-auto">
            {{-- <h3 class="text-3xl font-bold text-center text-red-500">Nossos Valores</h3> --}}

            <p class="text-graen-300 text-lg text-center mt-4">
                {{ $mensagem->mensagem ?? 'Nenhuma mensagem disponível' }}
            </p>
        </div>
    </section>
</body>
</html>
