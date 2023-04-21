<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">

                    <div class="mt-2 text-gray-600 dark:text-gray-400 text-sm">
                        <h3>Generate Token</h3>
                        <p>Teste de Geração de Token</p>
                    </div>
                    <div class="mt-2 text-gray-600 dark:text-gray-400 text-sm flex justify-between">
                        <form action="{{ route('generate.token') }}" method="post" class="mr-2">
                            @csrf
                            <button type="submit"
                                class="cursor p-2 px-6 bg-gray-900 text-gray-600 font-semibold">Gerar Token</button>
                        </form>
                        @if (session()->has('token'))
                            <form action="{{ route('send.email') }}" method="post">
                                @csrf
                                <input type="hidden" name="oauth_token" value="{{ session()->get('token') }}">
                                <button type="submit"
                                    class="cursor p-2 px-6 bg-gray-100 text-gray-700 font-semibold">
                                    Enviar Email Teste
                                </button>
                            </form>
                        @endif
                    </div>
                    @if (session()->has('error') || session()->has('token'))
                        <div class="mt-2 text-gray-600 dark:text-gray-400 text-sm">
                            <h4 style="margin-bottom: 5px;">Token</h4>
                            @if (session()->has('error'))
                                <p class="font-semibold">{{ session()->get('error') }}</p>
                            @endif

                            @if (session()->has('token'))
                                <p class="font-semibold" style="font-size: 10px; margin: 0px;">
                                    {{ session()->get('token') }}</p>
                            @endif
                        </div>
                    @endif

                    @if (session()->has('success'))
                        <div class="mt-2 text-gray-600 dark:text-gray-400 text-sm">
                            <p class="font-semibold">{{ session()->get('success') }}</p>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
