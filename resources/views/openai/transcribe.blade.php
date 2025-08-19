@extends('layouts.bootstrap5')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Transcrever Áudio (Espanhol para Português)</div>

                    <div class="card-body">
                        @if (session('error'))
                            <div class="alert alert-danger" role="alert">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('openai.transcribe') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="audio_file" class="form-label">Selecione o arquivo de áudio (opus,mp3, mp4, mpeg, mpga, m4a, wav, webm)</label>
                                <input class="form-control" type="file" id="audio_file" name="audio_file" accept=".opus,.mp3,.mp4,.mpeg,.mpga,.m4a,.wav,.webm" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Transcrever e Traduzir</button>
                        </form>

                        @if (isset($transcribedText) && !empty($transcribedText))
                            <div class="mt-4">
                                <hr>
                                <h5 class="mt-3">Texto Transcrito (Espanhol):</h5>
                                <div class="p-3 bg-light border rounded">
                                    <p>{{ $transcribedText }}</p>
                                </div>
                            </div>
                        @endif

                        @if (isset($translatedText) && !empty($translatedText))
                            <div class="mt-4">
                                <h5 class="mt-3">Texto Traduzido (Português):</h5>
                                <div class="p-3 bg-light border rounded">
                                    <p>{{ $translatedText }}</p>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
