@php(/** @var string $dir */ '')
@php(/** @var string|null $parent */ '')
@php(/** @var array<int,array{basename:string,path:string}> $directories */ '')
@php(/** @var array<int,array{basename:string,path:string,size:int|null,size_human:?string}> $files */ '')
@php(/** @var callable $encoded */ '')

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Navegação FTP
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm text-gray-600">Diretório atual: <span class="font-mono">/{{ $dir === '' ? '' : $dir }}</span></p>
                        @if($parent !== null)
                            <a href="?p={{ $encoded($parent) }}" class="text-indigo-600 text-sm hover:underline">&larr; Voltar</a>
                        @endif
                    </div>
                    <div class="text-right text-xs text-gray-500">
                        <p>Downloads bloqueados a partir do IP 186.237.225.6</p>
                    </div>
                </div>

                <table class="min-w-full text-sm border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 text-left">Nome</th>
                            <th class="px-3 py-2 text-left w-32">Tamanho</th>
                            <th class="px-3 py-2 w-20">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($directories as $d)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2">
                                    <a href="?p={{ $encoded($d['path']) }}" class="text-indigo-600 font-semibold">📁 {{ $d['basename'] }}</a>
                                </td>
                                <td class="px-3 py-2 text-gray-400">—</td>
                                <td class="px-3 py-2 text-center">—</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-3 py-2 text-gray-400">Nenhum diretório</td>
                            </tr>
                        @endforelse
                        @foreach($files as $f)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 font-mono">📄 {{ $f['basename'] }}</td>
                                <td class="px-3 py-2">{{ $f['size_human'] ?? '—' }}</td>
                                <td class="px-3 py-2 text-center">
                                    <a href="{{ route('ftp.download', ['f' => $encoded($f['path'])]) }}" class="text-indigo-600 hover:underline">Baixar</a>
                                </td>
                            </tr>
                        @endforeach
                        @if(empty($files) && empty($directories))
                            <tr>
                                <td colspan="3" class="px-3 py-2 text-gray-400">Diretório vazio</td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                <p class="mt-4 text-xs text-gray-500">As listagens são obtidas diretamente do FTP a cada requisição. Para otimizar, poderemos adicionar cache posteriormente.</p>
            </div>
        </div>
    </div>
</x-app-layout>
