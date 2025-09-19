@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Formas de Pagamento</h5>
                <div class="d-flex gap-2">
                    @can('FORMA_PAGAMENTOS - EXPORTAR')
                    <div class="btn-group">
                        <a href="{{ route('FormaPagamento.export', request()->query()) }}" class="btn btn-outline-success btn-sm">Exportar CSV</a>
                        <a href="{{ route('FormaPagamento.exportPdf', request()->query()) }}" class="btn btn-outline-danger btn-sm">Exportar PDF</a>
                    </div>
                    @endcan
                    @can('FORMA_PAGAMENTOS - INCLUIR')
                    <a href="{{ route('FormaPagamento.create') }}" class="btn btn-primary btn-sm">Incluir</a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @elseif (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form method="GET" class="row g-2 align-items-end mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Buscar</label>
                        <input type="text" name="q" class="form-control" placeholder="Nome" value="{{ $q ?? '' }}">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary" type="submit">Buscar</button>
                    </div>
                    @if(($q ?? '') !== '')
                        <div class="col-auto">
                            <a class="btn btn-outline-secondary" href="{{ route('FormaPagamento.index') }}">Limpar</a>
                        </div>
                    @endif
                </form>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($model as $item)
                            <tr>
                                <td>{{ $item->nome }}</td>
                                <td class="text-end">
                                    @can('FORMA_PAGAMENTOS - VER')
                                    <a href="{{ route('FormaPagamento.show', ['forma_pagamento' => $item->getRouteKey()]) }}" class="btn btn-secondary btn-sm">Ver</a>
                                    @endcan
                                    @can('FORMA_PAGAMENTOS - EDITAR')
                                    <a href="{{ route('FormaPagamento.edit', ['forma_pagamento' => $item->getRouteKey()]) }}" class="btn btn-success btn-sm">Editar</a>
                                    @endcan
                                    @can('FORMA_PAGAMENTOS - EXCLUIR')
                                    <form action="{{ route('FormaPagamento.destroy', ['forma_pagamento' => $item->getRouteKey()]) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirma exclusão?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm">Excluir</button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted">Nenhuma forma de pagamento encontrada.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div>
                    {{ $model->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
