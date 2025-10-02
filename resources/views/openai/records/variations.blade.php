@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
    <h2 class="mb-4">Variações de Ativos</h2>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Código do Ativo</th>
                <th>ID da Conversa</th>
                <th>Mês</th>
                <th>Ano</th>
                <th>Variação</th>
                <th>Data de Criação</th>
            </tr>
        </thead>
        <tbody>
            @forelse($variations as $variation)
                <tr>
                    <td>{{ $variation->id }}</td>
                    <td>{{ $variation->asset_code }}</td>
                    <td>{{ $variation->chat_id }}</td>
                    <td>{{ $variation->month }}</td>
                    <td>{{ $variation->year }}</td>
                    <td>{{ number_format($variation->variation, 4, ',', '.') }}</td>
                    <td>{{ $variation->created_at }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">Nenhuma variação encontrada.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
