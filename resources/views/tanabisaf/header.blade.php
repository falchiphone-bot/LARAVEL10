<header class="flex justify-between items-center p-6 bg-green-800">
    <h1 class="text-xl font-bold">
        <a href="{{ url('/') }}">
            <img src="{{ asset('tanabisaf/logo/logo-tec.jpeg') }}" alt="Logo" class="h-36">
        </a>

    </h1>

    <button id="menu-toggle" class="md:hidden text-white focus:outline-none">
        &#9776;
    </button>

    <nav id="menu" class="hidden md:flex space-x-6">
        <ul class="flex flex-col md:flex-row md:space-x-6 bg-green-800 md:bg-transparent absolute md:static top-16 left-0 w-full md:w-auto p-4 md:p-0">
            <li><a href="{{ url('/') }}" class="block p-2 hover:text-red-500">Início</a></li>
            <li><a href="{{ url('/tanabisaf.certidoes') }}" class="block p-2 hover:text-red-500">Certidões oficiais</a></li>
            <li><a href="{{ url('/sites') }}" class="block p-2 hover:text-red-500">Documentos/Videos</a></li>
            {{-- <li><a href="{{ url('/vec.historia1') }}" class="block p-2 hover:text-red-500">Sobre</a></li> --}}
            <li><a href="{{ url('/tanabisaf.transparencias') }}" class="block p-2 hover:text-red-500">Transparências</a></li>
            <li><a href="{{ url('/tanabisaf.categoria') }}" class="block p-2 hover:text-red-500">Categorias</a></li>
            <li><a href="{{ url('/tanabisaf.localtreino') }}" class="block p-2 hover:text-red-500">Local treinos</a></li>
            {{-- <li><a href="{{ url('/vec.comoparticipar') }}" class="block p-2 hover:text-red-500">Participar</a></li> --}}
            <li><a href="{{ url('/tanabisaf.contato') }}" class="block p-2 hover:text-red-500">Contato</a></li>
        </ul>
    </nav>
</header>

<script>
    document.getElementById('menu-toggle').addEventListener('click', function () {
        document.getElementById('menu').classList.toggle('hidden');
    });
</script>
