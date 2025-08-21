@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Minhas Conversas</h1>
        <div>
            <a href="{{ route('openai.menu') }}" class="btn btn-outline-secondary">← Menu OpenAI</a>
            <a href="{{ route('openai.chat') }}" class="btn btn-primary">Novo Chat</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($chats->count())
        <div class="row g-3">
            @foreach($chats as $chat)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">{{ $chat->title }}</h5>
                            <p class="text-muted small mb-2">Atualizado em {{ $chat->updated_at->format('d/m/Y H:i') }}</p>
                            <div class="mt-auto d-flex gap-2">
                                <a href="{{ route('openai.chat.load', $chat) }}" class="btn btn-sm btn-secondary">Carregar</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-3">{{ $chats->links() }}</div>
    @else
        <div class="alert alert-info">Você ainda não salvou nenhuma conversa.</div>
    @endif
</div>
@endsection
