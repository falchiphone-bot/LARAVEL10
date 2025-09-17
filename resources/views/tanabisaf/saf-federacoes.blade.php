@include('tanabisaf.header')

<div class="bg-white py-16 sm:py-24">
  <div class="mx-auto max-w-7xl px-6 lg:px-8">
    <div class="mx-auto max-w-2xl lg:mx-0">
      <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Federações</h2>
      <p class="mt-2 text-lg leading-8 text-gray-600">Lista de federações vinculadas à TANABI SAF.</p>
    </div>

    <form method="GET" class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-5">
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
        <a href="{{ route('tanabisaf.federacoes') }}" class="rounded-md border px-4 py-2 text-gray-700 hover:bg-gray-50">Limpar</a>
      </div>
    </form>

    <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
      @forelse($federacoes as $fed)
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
          <h3 class="text-xl font-semibold text-gray-900">{{ $fed->nome }}</h3>
          <p class="mt-2 text-sm text-gray-600">
            {{ $fed->cidade ? $fed->cidade . ' - ' : '' }}{{ $fed->uf }}
          </p>
          <p class="mt-1 text-sm text-gray-600">{{ $fed->pais }}</p>
        </div>
      @empty
        <p class="text-gray-500">Nenhuma federação cadastrada.</p>
      @endforelse
    </div>

    @if(method_exists($federacoes, 'links'))
      <div class="mt-6">
        {{ $federacoes->onEachSide(1)->links() }}
        <p class="mt-2 text-sm text-gray-500">
          Exibindo {{ $federacoes->firstItem() ?? 0 }}–{{ $federacoes->lastItem() ?? 0 }} de {{ $federacoes->total() }}
        </p>
      </div>
    @endif
  </div>
</div>

@include('tanabisaf.pde')
