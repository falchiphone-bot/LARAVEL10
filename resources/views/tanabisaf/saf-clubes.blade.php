<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TANABI SAF - Clubes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="description" content="Lista de clubes vinculados à TANABI SAF.">
</head>
<body class="bg-green-900 text-white">

    @include('tanabisaf.header')

    <main class="px-6 md:px-12 py-8">
        <h1 class="text-3xl font-bold mb-6">Clubes</h1>

        <form method="GET" class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-5">
            <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Buscar por nome ou cidade"
                class="w-full rounded-md border-gray-300 text-black font-bold shadow-sm focus:border-green-600 focus:ring-green-600" />
            <input type="text" name="uf" value="{{ $uf ?? '' }}" placeholder="UF"
                class="w-full rounded-md border-gray-300 text-black font-bold shadow-sm focus:border-green-600 focus:ring-green-600" />
            <input type="text" name="pais" value="{{ $pais ?? '' }}" placeholder="País"
                class="w-full rounded-md border-gray-300 text-black font-bold shadow-sm focus:border-green-600 focus:ring-green-600" />
            <select name="per_page" class="w-full rounded-md border-gray-300 text-black font-bold shadow-sm focus:border-green-600 focus:ring-green-600">
                @foreach([10,15,25,50,100] as $n)
                    <option value="{{ $n }}" {{ (isset($perPage) ? (int)$perPage : 15) === $n ? 'selected' : '' }}>{{ $n }} por página</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button class="rounded-md bg-green-700 px-4 py-2 text-white hover:bg-green-800" type="submit">Filtrar</button>
                <a href="{{ route('tanabisaf.clubes') }}" class="rounded-md border px-4 py-2 text-white hover:bg-green-800/40">Limpar</a>
            </div>
        </form>

        @if(($clubes->total() ?? 0) === 0)
            <p class="text-gray-300">Nenhum clube cadastrado.</p>
        @else
            <div class="overflow-x-auto bg-green-800/40 rounded-lg">
                <table class="min-w-full text-left">
                    <thead class="bg-green-800/70">
                        <tr>
                            <th class="px-4 py-3">Nome</th>
                            <th class="px-4 py-3">Cidade</th>
                            <th class="px-4 py-3">UF</th>
                            <th class="px-4 py-3">País</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clubes as $clube)
                            <tr class="border-b border-green-700 hover:bg-green-800/30">
                                <td class="px-4 py-2">{{ $clube->nome }}</td>
                                <td class="px-4 py-2">{{ $clube->cidade }}</td>
                                <td class="px-4 py-2">{{ $clube->uf }}</td>
                                <td class="px-4 py-2">{{ $clube->pais }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $clubes->onEachSide(1)->links() }}
            </div>
        @endif
    </main>

    <footer class="px-6 md:px-12 py-6 text-sm text-gray-300">
        <p>
            Exibindo {{ $clubes->firstItem() ?? 0 }}–{{ $clubes->lastItem() ?? 0 }} de {{ $clubes->total() }}
        </p>
    </footer>

</body>
</html>
