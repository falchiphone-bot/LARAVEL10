
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contatos</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">

    @include('vec.header')

    <section class="max-w-4xl mx-auto py-12 px-6">
        <h2 class="text-4xl font-bold text-center mb-6">Sobre o VEC</h2>
        <p class="text-gray-300 text-lg leading-relaxed text-center">
         Email:
        </p>
        <p class="text-gray-300 text-lg leading-relaxed mt-4">
        contato@vec.org.br
        </p>

    </section>

    <section class="bg-gray-800 py-12 px-6">
        <div class="max-w-4xl mx-auto">
            <h3 class="text-3xl font-bold text-center text-red-500">Telefone:</h3>
            <p class="text-gray-300 text-lg text-center mt-4">
               +55 17 9 9766 2949, também estamos no WhatsApp.
            </p>
        </div>
    </section>

    <section class="max-w-4xl mx-auto py-12 px-6">
        <h3 class="text-3xl font-bold text-center mb-6">site</h3>
        <ul class="text-gray-300 text-lg space-y-4">
            <li><strong class="text-red-500">vec.org.br:</strong> Um dos sites</li>
           </ul>
    </section>
</body>
</html>
