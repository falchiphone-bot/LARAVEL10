<!DOCTYPE html>
@extends('layouts.bootstrap5')
@section('content')
{{-- <div class="container py-4"> --}}
    <div class="mb-3">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">← Dashboard</a>
    </div>

    <h1 class="h4 mb-2">OpenAI - Menu</h1>
    <p class="text-muted mb-4">Escolha uma funcionalidade abaixo:</p>

    @php
        $canChat = auth()->user()->can('OPENAI - CHAT');
        $canTranscribe = auth()->user()->can('OPENAI - TRANSCRIBE - ESPANHOL');
    @endphp

    @if($canChat || $canTranscribe)
        <div class="row g-3">
            @if($canChat)
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Chat</h5>
                        <p class="card-text text-muted">Converse com o assistente e mantenha o histórico da conversa.</p>
                        <a href="{{ route('openai.chat') }}" class="btn btn-dark">Abrir Chat</a>
                    </div>
                </div>
            </div>
            @endif

            @if($canTranscribe)
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Transcrição (ES → PT)</h5>
                        <p class="card-text text-muted">Envie um áudio em espanhol para transcrever e traduzir para português.</p>
                        <a href="{{ route('openai.transcribe') }}" class="btn btn-primary">Abrir Transcrição</a>
                    </div>
                </div>
            </div>
            @endif
        </div>
    @else
        <div class="alert alert-warning" role="alert">
            Você não tem permissão para acessar as ferramentas OpenAI.
        </div>
    @endif
{{-- </div> --}}
@endsection
