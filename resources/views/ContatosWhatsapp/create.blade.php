@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;">
                    CONTATOS DO WHATSAPP PARA SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>

                <h1 class="text-center">Contatos do WhatsApp - Inclusão</h1>
                <hr>
                <form method="POST" action="/ContatosWhatsapp" accept-charset="UTF-8">
                    @include('ContatosWhatsapp.camposinclusao')
                </form>
            </div>
        </div>
    </div>
@endsection



