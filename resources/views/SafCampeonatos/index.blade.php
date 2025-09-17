@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">SAF - Campeonatos</h5>
                @can('SAF_CAMPEONATOS - INCLUIR')
                <a href="{{ route('SafCampeonatos.create') }}" class="btn btn-primary btn-sm">Incluir</a>
                @endcan
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @elseif (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                <form method="GET" class="row g-2 align-items-center mb-3">
                    <input type="hidden" name="sort" value="{{ $sort ?? 'nome' }}">
                    <input type="hidden" name="dir" value="{{ $dir ?? 'asc' }}">
                    <input type="hidden" name="per_page" value="{{ request('per_page', $model->perPage()) }}">
                    <div class="col-md-4">
                        <input type="text" name="q" class="form-control" placeholder="Buscar por nome, cidade, UF ou país" value="{{ $q ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <select name="federacao_id" class="form-select" aria-label="Filtrar por Federação">
                            <option value="">Todas as Federações</option>
                            @isset($federacoes)
                                @foreach($federacoes as $f)
                                    <option value="{{ $f->id }}" {{ (string)request('federacao_id') === (string)$f->id ? 'selected' : '' }}>{{ $f->nome }}</option>
                                @endforeach
                            @endisset
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="ano_id" class="form-select" aria-label="Filtrar por Ano">
                            <option value="">Todos os Anos</option>
                            @isset($anos)
                                @foreach($anos as $a)
                                    <option value="{{ $a->id }}" {{ (string)request('ano_id') === (string)$a->id ? 'selected' : '' }}>{{ $a->ano }}</option>
                                @endforeach
                            @endisset
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary" type="submit">Buscar</button>
                    </div>
                    @php $hasAnyFilter = ($q ?? '') !== '' || request('federacao_id') || request('ano_id'); @endphp
                    @if($hasAnyFilter)
                        <div class="col-auto">
                            <a class="btn btn-outline-secondary" href="{{ route('SafCampeonatos.index', ['sort' => $sort ?? 'nome', 'dir' => $dir ?? 'asc', 'per_page' => request('per_page', $model->perPage())]) }}">Limpar</a>
                        </div>
                    @endif
                </form>
                <p class="text-muted">Total: {{ $model->total() }}</p>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                @php
                                    $nextDir = ($dir ?? 'asc') === 'asc' ? 'desc' : 'asc';
                                @endphp
                                <th>
                                    <a href="{{ route('SafCampeonatos.index', ['sort' => 'nome', 'dir' => ($sort ?? 'nome') === 'nome' ? $nextDir : 'asc', 'per_page' => request('per_page', $model->perPage()), 'q' => $q ?? null]) }}">Nome
                                        @if(($sort ?? 'nome') === 'nome')
                                            <small>{!! ($dir ?? 'asc') === 'asc' ? '&#9650;' : '&#9660;' !!}</small>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('SafCampeonatos.index', ['sort' => 'cidade', 'dir' => ($sort ?? 'nome') === 'cidade' ? $nextDir : 'asc', 'per_page' => request('per_page', $model->perPage()), 'q' => $q ?? null]) }}">Cidade
                                        @if(($sort ?? 'nome') === 'cidade')
                                            <small>{!! ($dir ?? 'asc') === 'asc' ? '&#9650;' : '&#9660;' !!}</small>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('SafCampeonatos.index', ['sort' => 'uf', 'dir' => ($sort ?? 'nome') === 'uf' ? $nextDir : 'asc', 'per_page' => request('per_page', $model->perPage()), 'q' => $q ?? null]) }}">UF
                                        @if(($sort ?? 'nome') === 'uf')
                                            <small>{!! ($dir ?? 'asc') === 'asc' ? '&#9650;' : '&#9660;' !!}</small>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('SafCampeonatos.index', ['sort' => 'pais', 'dir' => ($sort ?? 'nome') === 'pais' ? $nextDir : 'asc', 'per_page' => request('per_page', $model->perPage()), 'q' => $q ?? null]) }}">País
                                        @if(($sort ?? 'nome') === 'pais')
                                            <small>{!! ($dir ?? 'asc') === 'asc' ? '&#9650;' : '&#9660;' !!}</small>
                                        @endif
                                    </a>
                                </th>
                                <th>Federação</th>
                                <th>Ano</th>
                                <th>Categorias</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($model as $item)
                                <tr>
                                    <td>{{ $item->nome }}</td>
                                    <td>{{ $item->cidade }}</td>
                                    <td>{{ $item->uf }}</td>
                                    <td>{{ $item->pais }}</td>
                                    <td>{{ optional($item->federacao)->nome ?? '—' }}</td>
                                    <td>{{ optional($item->ano)->ano ?? '—' }}</td>
                                    <td>
                                        @if($item->categorias && $item->categorias->count())
                                            <span class="badge bg-secondary">{{ $item->categorias->pluck('nome')->join(', ') }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @can('SAF_CAMPEONATOS - VER')
                                        <a href="{{ route('SafCampeonatos.show', $item->id) }}" class="btn btn-secondary btn-sm">Ver</a>
                                        @endcan
                                        @can('SAF_CAMPEONATOS - EDITAR')
                                        <a href="{{ route('SafCampeonatos.edit', $item->id) }}" class="btn btn-success btn-sm">Editar</a>
                                        @endcan
                                        @can('SAF_CAMPEONATOS - EXCLUIR')
                                        <form action="{{ route('SafCampeonatos.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirma exclusão?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm">Excluir</button>
                                        </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted">Nenhum campeonato cadastrado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center gap-3">
                    <div class="text-muted">
                        Exibindo {{ $model->firstItem() }}–{{ $model->lastItem() }} de {{ $model->total() }}
                    </div>
                    <form method="GET" class="d-flex align-items-center gap-2">
                        <input type="hidden" name="sort" value="{{ $sort ?? 'nome' }}">
                        <input type="hidden" name="dir" value="{{ $dir ?? 'asc' }}">
                        <input type="hidden" name="q" value="{{ $q ?? '' }}">
                        <label for="per_page" class="form-label m-0">por página</label>
                        <select id="per_page" name="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                            @foreach ([10,20,50,100] as $n)
                                <option value="{{ $n }}" {{ (int)request('per_page', $model->perPage()) === $n ? 'selected' : '' }}>{{ $n }}</option>
                            @endforeach
                        </select>
                    </form>
                    <div>
                        {{ $model->appends([
                            'sort' => $sort ?? null,
                            'dir' => $dir ?? null,
                            'per_page' => request('per_page', $model->perPage()),
                            'q' => $q ?? null,
                            'federacao_id' => request('federacao_id'),
                            'ano_id' => request('ano_id'),
                        ])->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
