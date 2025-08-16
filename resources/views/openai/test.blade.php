<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste OpenAI</title>
    <script src="https://cdn.tailwindcss.com"></script>
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

        <div class="bg-white p-6 rounded-lg shadow-lg">
            <form action="{{ route('openai.test.text') }}" method="GET">
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
                    >{{ old('prompt', $prompt ?? '') }}</textarea>
                    @error('prompt')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex items-center justify-between">
                    <button
                        type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                    >
                        Enviar
                    </button>
                </div>
            </form>
        </div>

        @if(isset($response))
            <div class="mt-8 bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-xl font-bold mb-2">Resposta da OpenAI:</h2>
                <div class="text-gray-700 bg-gray-100 p-4 rounded whitespace-pre-wrap">{{ $response['choices'][0]['message']['content'] ?? json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</div>
            </div>
        @endif
    </div>
</body>
</html>
