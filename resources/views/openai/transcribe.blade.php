@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
    <div class="mb-3">
        @canany(['OPENAI - CHAT', 'OPENAI - TRANSCRIBE - ESPANHOL'])
        <a href="{{ route('openai.menu') }}" class="btn btn-outline-secondary">← Voltar ao Menu</a>
        @endcanany
    </div>

    <h1 class="h4 mb-3">Transcrever Áudio (Espanhol → Português)</h1>

    @if(session('error'))
        <div class="alert alert-danger" role="alert">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Ocorreram erros:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form id="transcribe-form" action="{{ route('openai.transcribe') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="audio_file" class="form-label">Selecione o arquivo de áudio</label>
                    <input class="form-control" type="file" id="audio_file" name="audio_file" accept=".opus,.ogg,.mp3,.mp4,.mpeg,.mpga,.m4a,.wav,.webm" required>
                    <div class="form-text">Formatos suportados: opus/ogg, mp3, mp4, mpeg, mpga, m4a, wav, webm</div>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="async" name="async" value="1">
                    <label class="form-check-label" for="async">Processar em segundo plano (recomendado para arquivos grandes)</label>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Transcrever e Traduzir</button>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('audio_file').value = ''">Limpar</button>
                </div>
            </form>
        </div>
    </div>

    @if (session('transcribedText'))
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h2 class="h6">Texto Transcrito (Espanhol):</h2>
                <div class="text-body" style="white-space: pre-wrap;">{{ session('transcribedText') }}</div>
            </div>
        </div>
    @endif

    @if (session('translatedText'))
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h6">Texto Traduzido (Português):</h2>
                <div class="text-body" style="white-space: pre-wrap;">{{ session('translatedText') }}</div>
            </div>
        </div>
    @endif
</div>
@endsection
