<header class="flex justify-between items-center p-6 bg-blue-800">
    <h1 class="text-xl font-bold">
        <a href="{{ url('/') }}" class="flex items-center">
            <div class="bg-white text-blue-800 px-4 py-2 rounded-lg font-bold text-lg">
                IRMÃOS EMAUS
            </div>
        </a>
    </h1>

    <button id="menu-toggle" class="md:hidden text-white focus:outline-none">
        &#9776;
    </button>

    <nav id="menu" class="hidden md:flex space-x-6">
        <ul class="flex flex-col md:flex-row md:space-x-6 bg-blue-800 md:bg-transparent absolute md:static top-16 left-0 w-full md:w-auto p-4 md:p-0">
            <li><a href="{{ url('/') }}" class="block p-2 hover:text-yellow-300">Início</a></li>
            <li><a href="{{ route('Irmaos_EmausPia.index') }}" class="block p-2 hover:text-yellow-300">Controle de Pia</a></li>
            <li><a href="{{ route('Irmaos_EmausServicos.index') }}" class="block p-2 hover:text-yellow-300">Serviços</a></li>
            <li><a href="{{ route('Irmaos_Emaus_FichaControle.index') }}" class="block p-2 hover:text-yellow-300">Ficha de Controle</a></li>
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
