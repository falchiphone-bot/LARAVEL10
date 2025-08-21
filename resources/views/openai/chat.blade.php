<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uso OpenAI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
    <div class="mb-3">
        @canany(['OPENAI - CHAT', 'OPENAI - TRANSCRIBE - ESPANHOL'])
        <a href="{{ route('openai.menu') }}" class="btn btn-outline-secondary">← Voltar ao Menu</a>
        @endcanany
    </div>

    <h1 class="h4 mb-3">Consultar API da OpenAI</h1>

    @if(session('error'))
        <div class="alert alert-danger" role="alert">
            <strong>Erro!</strong> {{ session('error') }}
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('openai.chat') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="prompt" class="form-label">Digite seu prompt:</label>
                    <textarea id="prompt" name="prompt" rows="4" class="form-control" placeholder="Ex: Qual a capital do Brasil?">{{ old('prompt') }}</textarea>
                    @error('prompt')
                        <div class="form-text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-dark">Enviar</button>
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-end mb-3">
        <form action="{{ route('openai.chat.clear') }}" method="POST" onsubmit="return confirm('Tem certeza que deseja limpar o histórico?');">
            @csrf
            <button type="submit" class="btn btn-danger @if(!isset($messages) || count($messages) <= 1) disabled @endif" @if(!isset($messages) || count($messages) <= 1) disabled @endif>
                Limpar Histórico
            </button>
        </form>
    </div>

    @if(isset($messages) && count($messages) > 1)
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3">Histórico da Conversa:</h2>
                <div class="vstack gap-3">
                    @foreach(array_reverse($messages) as $message)
                        @if($message['role'] === 'system')
                            @continue
                        @endif
                        <div class="p-3 rounded {{ $message['role'] === 'user' ? 'bg-primary bg-opacity-10 border border-primary text-primary' : 'bg-light border' }}">
                            <div class="fw-bold mb-1">{{ $message['role'] === 'user' ? 'Você' : 'Assistente' }}:</div>
                            <div style="white-space: pre-wrap;">{{ $message['content'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
</body>
</html>
