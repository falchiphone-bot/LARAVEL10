<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>


    {{--
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        {{ __("Conectado!") }}
                    </div>
                </div>
            </div>
        </div> --}}
    <h1 style="background-color:hsla(120, 100%, 50%, 0.2);">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <nav class="navbar navbar-red bg-green">
                        <div class="container">
                            @can('USUARIOS - LISTAR')
                                <nav class="navbar navbar-red" style="background-color: hsla(235, 86%, 89%, 0.226);">
                                    <a class="navbar-brand" href="/Usuarios">Usuários</a>
                                </nav>
                            @endcan

                            @can('PERMISSOES - LISTAR')
                                <nav class="navbar navbar-red" style="background-color: hsla(235, 86%, 89%, 0.226);">
                                    <a class="navbar-brand" href="/Permissoes">Permissões</a>
                                </nav>
                            @endcan

                            @can('FUNCOES - LISTAR')
                                <nav class="navbar navbar-red" style="background-color: hsla(235, 86%, 89%, 0.226);">
                                    <a class="navbar-brand" href="/Funcoes">Funcões</a>
                                </nav>
                            @endcan

                            @can('PLANO DE CONTAS - LISTAR')
                                <nav class="navbar navbar-red" style="background-color: hsla(235, 86%, 89%, 0.226);">
                                    <a class="navbar-brand" href="/PlanoContas">Plano de contas padrão</a>
                                </nav>
                            @endcan
                            @can('EMPRESAS - LISTAR')
                                <nav class="navbar navbar-light" style="background-color: hsla(235, 86%, 79%, 0.226);">
                                    <a class="navbar-brand" href="/Empresas">Empresas</a>
                                </nav>
                            @endcan
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </h1>

</x-app-layout>
