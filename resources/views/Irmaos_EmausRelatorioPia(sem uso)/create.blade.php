@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">
            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;">
                    TÓPICOS PARA PIA DO SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>
                <h1 class="text-center">Nome do Tópico para PIA - Inclusão</h1>
                <hr>
                <form method="POST" action="/Irmaos_EmausPia" accept-charset="UTF-8">
                    @include('Irmaos_EmausPia.campos')
                </form>
            </div>
        </div>
    </div>
@endsection

