<header class="flex justify-between items-center p-6 bg-green-800">
    <h1 class="text-xl font-bold">
        <a href="{{ url('/') }}" class="flex items-center">
            <div class="bg-white text-green-800 px-4 py-2 rounded-lg font-bold text-lg">
                SAF
            </div>
        </a>
    </h1>

    <button id="menu-toggle" class="md:hidden text-white focus:outline-none">
        &#9776;
    </button>

    <nav id="menu" class="hidden md:flex space-x-6">
        <ul class="flex flex-col md:flex-row md:space-x-6 bg-green-800 md:bg-transparent absolute md:static top-16 left-0 w-full md:w-auto p-4 md:p-0">
            <li><a href="{{ url('/') }}" class="block p-2 hover:text-yellow-300">Início</a></li>
            <li class="relative group">
                <a href="#" class="block p-2 hover:text-yellow-300">Clubes & Competições</a>
                <ul class="absolute left-0 top-full hidden group-hover:block bg-green-700 rounded mt-1 min-w-max">
                    <li><a href="{{ route('SafClubes.index') }}" class="block px-4 py-2 hover:bg-green-600">Clubes</a></li>
                    <li><a href="{{ route('SafFederacoes.index') }}" class="block px-4 py-2 hover:bg-green-600">Federações</a></li>
                    <li><a href="{{ route('SafCampeonatos.index') }}" class="block px-4 py-2 hover:bg-green-600">Campeonatos</a></li>
                    <li><a href="{{ route('SafAnos.index') }}" class="block px-4 py-2 hover:bg-green-600">Temporadas</a></li>
                </ul>
            </li>
            <li class="relative group">
                <a href="#" class="block p-2 hover:text-yellow-300">Administração</a>
                <ul class="absolute left-0 top-full hidden group-hover:block bg-green-700 rounded mt-1 min-w-max">
                    <li><a href="{{ route('SafColaboradores.index') }}" class="block px-4 py-2 hover:bg-green-600">Colaboradores</a></li>
                    <li><a href="{{ route('SafTiposPrestadores.index') }}" class="block px-4 py-2 hover:bg-green-600">Tipos de Prestadores</a></li>
                    <li><a href="{{ route('SafFaixasSalariais.index') }}" class="block px-4 py-2 hover:bg-green-600">Faixas Salariais</a></li>
                </ul>
            </li>
            @auth
                <li><a href="{{ route('dashboard') }}" class="block p-2 hover:text-yellow-300">Dashboard</a></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="block p-2 hover:text-yellow-300">Sair</button>
                    </form>
                </li>
            @else
                <li><a href="{{ route('login') }}" class="block p-2 hover:text-yellow-300">Login</a></li>
            @endauth
        </ul>
    </nav>
</header>

<script>
    document.getElementById('menu-toggle').addEventListener('click', function () {
        document.getElementById('menu').classList.toggle('hidden');
    });
</script>
