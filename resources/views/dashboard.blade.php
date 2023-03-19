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
                            <nav class="navbar navbar-red" style="background-color: hsla(235, 86%, 89%, 0.226);">
                                <a class="navbar-brand" href="/Usuarios">Usuários</a>
                            </nav>

                            <nav class="navbar navbar-red" style="background-color: hsla(235, 86%, 89%, 0.226);">
                                <a class="navbar-brand" href="/Permissoes">Permissões</a>
                            </nav>
                            <nav class="navbar navbar-red" style="background-color: hsla(235, 86%, 89%, 0.226);">
                                <a class="navbar-brand" href="/TemPermissoes">Tabelas que tem permissões</a>
                            </nav>
                            <nav class="navbar navbar-red" style="background-color: hsla(235, 86%, 89%, 0.226);">
                                <a class="navbar-brand" href="/ModelodeFuncoes">Modelos de funcões</a>
                            </nav>
                            <nav class="navbar navbar-red" style="background-color: hsla(235, 86%, 89%, 0.226);">
                                <a class="navbar-brand" href="/TemFuncoes">Funções que tem permissões</a>
                            </nav>
                          <nav class="navbar navbar-light" style="background-color: hsla(235, 86%, 79%, 0.226);">
                            <a class="navbar-brand" href="/Empresas">Empresas</a>
                          </nav>
                        </div>
                    </nav>
            </div>
        </div>
    </div>
    </h1>

</x-app-layout>
