<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football Life</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">

    <header class="flex justify-between items-center p-6 bg-gray-800">
        <h1 class="text-xl font-bold">
            <img src="{{ asset('vec/logo/logovec1.jpeg') }}" alt="Logo" class="h-50">
        </h1>

        <nav>
            <ul class="flex space-x-6">
                <li><a href="#" class="hover:text-red-500">Home</a></li>
                <li><a href="#" class="hover:text-red-500">Sobre</a></li>
                <li><a href="#" class="hover:text-red-500">Serviços</a></li>
                <li><a href="#" class="hover:text-red-500">Contato</a></li>
            </ul>
        </nav>
    </header>

    <section class="flex justify-between items-center px-12 py-20 bg-gray-900">
        <div class="w-1/2">
            <h2 class="text-4xl font-bold mb-4">Nossa história</h2>
            <p class="text-gray-300 mb-6">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris convallis, lacus ac tincidunt viverra.
            </p>
            <a href="#" class="bg-red-500 px-6 py-2 rounded-lg text-white font-bold hover:bg-red-600">LEIA MAIS</a>
        </div>
        <div class="w-1/2 flex justify-center">
            <img src="{{ asset('images/football-image.png') }}" alt="Futebol" class="w-96">
        </div>
    </section>

</body>
</html>
