<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste OpenAI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <h1 class="text-2xl font-bold mb-4">Consultar API da OpenAI</h1>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Erro!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="bg-white p-6 rounded-lg shadow-lg" x-data="{ submitting: false }">
            <form action="{{ route('openai.chat') }}" method="POST" x-on:submit="submitting = true">
                @csrf
                <div class="mb-4">
                    <label for="prompt" class="block text-gray-700 text-sm font-bold mb-2">
                        Digite seu prompt:
                    </label>
                    <textarea
                        id="prompt"
                        name="prompt"
                        rows="4"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="Ex: Qual a capital do Brasil?"
                    >{{ old('prompt') }}</textarea>
                    @error('prompt')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex items-center justify-between">
                    <button
                        type="submit"
                        class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline transition-colors duration-200 disabled:opacity-75 disabled:cursor-wait"
                        :disabled="submitting"
                    >
                        <span x-show="!submitting">Enviar</span>
                        <span x-show="submitting">Enviando...</span>
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-6 flex justify-end">
            <form action="{{ route('openai.chat.clear') }}" method="POST" onsubmit="return confirm('Tem certeza que deseja limpar o histórico?');">
                @csrf
                <button
                    type="submit"
                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline @if(!isset($messages) || count($messages) <= 1) opacity-50 cursor-not-allowed @endif"
                    @if(!isset($messages) || count($messages) <= 1) disabled @endif
                >
                    Limpar Histórico
                </button>
            </form>
        </div>

        @if(isset($messages) && count($messages) > 1)
            <div class="mt-2 bg-white p-6 rounded-lg shadow-lg space-y-4">
                <h2 class="text-xl font-bold mb-2">Histórico da Conversa:</h2>
                @foreach($messages as $message)
                    @if($message['role'] === 'system')
                        @continue
                    @endif
                    <div class="p-4 rounded-lg {{ $message['role'] === 'user' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                        <p class="font-bold capitalize">{{ $message['role'] === 'user' ? 'Você' : 'Assistente' }}:</p>
                        <div class="whitespace-pre-wrap">{{ $message['content'] }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</body>
</html>
