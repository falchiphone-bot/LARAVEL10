@include('tanabisaf.header')

<div class="bg-white py-16 sm:py-24">
  <div class="mx-auto max-w-7xl px-6 lg:px-8">
    <div class="mx-auto max-w-2xl lg:mx-0">
      <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Campeonatos</h2>
      <p class="mt-2 text-lg leading-8 text-gray-600">Lista de campeonatos organizados/participados pela TANABI SAF.</p>
    </div>

  <form method="GET" class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-6">
      <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Buscar por nome ou cidade"
        class="w-full rounded-md border-gray-300 text-black font-bold shadow-sm focus:border-green-600 focus:ring-green-600" />
      <input type="text" name="uf" value="{{ $uf ?? '' }}" placeholder="UF"
        class="w-full rounded-md border-gray-300 text-black font-bold shadow-sm focus:border-green-600 focus:ring-green-600" />
      <input type="text" name="pais" value="{{ $pais ?? '' }}" placeholder="País"
        class="w-full rounded-md border-gray-300 text-black font-bold shadow-sm focus:border-green-600 focus:ring-green-600" />
      <select name="federacao_id" class="w-full rounded-md border-gray-300 text-black font-bold shadow-sm focus:border-green-600 focus:ring-green-600">
        <option value="">— Federação —</option>
        @isset($federacoes)
          @foreach($federacoes as $fed)
            <option value="{{ $fed->id }}" {{ (string)($federacaoId ?? '') === (string)$fed->id ? 'selected' : '' }}>{{ $fed->nome }}</option>
          @endforeach
        @endisset
      </select>
      <select name="per_page" class="w-full rounded-md border-gray-300 text-black font-bold shadow-sm focus:border-green-600 focus:ring-green-600">
        @foreach([10,15,25,50,100] as $n)
          <option value="{{ $n }}" {{ (isset($perPage) ? (int)$perPage : 15) === $n ? 'selected' : '' }}>{{ $n }} por página</option>
        @endforeach
      </select>
      <div class="flex gap-2">
        <button class="rounded-md bg-green-700 px-4 py-2 text-white hover:bg-green-800" type="submit">Filtrar</button>
        <a href="{{ route('tanabisaf.campeonatos') }}" class="rounded-md border px-4 py-2 text-gray-700 hover:bg-gray-50">Limpar</a>
      </div>
    </form>

    <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
      @forelse($campeonatos as $camp)
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
          <h3 class="text-xl font-semibold text-gray-900">{{ $camp->nome }}</h3>
          <p class="mt-2 text-sm text-gray-600">
            {{ $camp->cidade ? $camp->cidade . ' - ' : '' }}{{ $camp->uf }}
          </p>
          <p class="mt-1 text-sm text-gray-600">{{ $camp->pais }}</p>
          <p class="mt-2 text-sm text-gray-700"><span class="font-medium">Federação:</span> {{ optional($camp->federacao)->nome ?? '—' }}</p>
          <p class="mt-1 text-sm text-gray-700"><span class="font-medium">Categorias:</span> {{ ($camp->categorias && $camp->categorias->count()) ? $camp->categorias->pluck('nome')->join(', ') : '—' }}</p>
        </div>
      @empty
        <p class="text-gray-500">Nenhum campeonato cadastrado.</p>
      @endforelse
    </div>
    @if(method_exists($campeonatos, 'links'))
      <div class="mt-6">
        {{ $campeonatos->onEachSide(1)->links() }}
        <p class="mt-2 text-sm text-gray-500">
          Exibindo {{ $campeonatos->firstItem() ?? 0 }}–{{ $campeonatos->lastItem() ?? 0 }} de {{ $campeonatos->total() }}
        </p>
      </div>
    @endif
  </div>
</div>

@include('tanabisaf.pde')
