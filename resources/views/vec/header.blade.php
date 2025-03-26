<header class="flex justify-between items-center p-6 bg-gray-800">
    <h1 class="text-xl font-bold">
        <a href="{{ url('/') }}">
            <img src="{{ asset('vec/logo/logovec1.jpeg') }}" alt="Logo" class="h-50">
        </a>
    </h1>

    <nav>
        <ul class="flex space-x-6">
            <li><a href="{{ url('/') }}" class="hover:text-red-500">Início</a></li>
            <li><a href="{{ url('/vec.historia1') }}" class="hover:text-red-500">Sobre</a></li>
            <li><a href="{{ url('/vec.categoria') }}" class="hover:text-red-500">Categorias</a></li>
            <li><a href="{{ url('/vec.localtreino') }}" class="hover:text-red-500">Local treinos</a></li>
            <li><a href="{{ url('/vec.comoparticipar') }}" class="hover:text-red-500">Participar</a></li>
            <li><a href="{{ url('/vec.contato') }}" class="hover:text-red-500">Contato</a></li>
        </ul>
    </nav>
</header>
