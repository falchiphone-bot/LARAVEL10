@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">
            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;">
                    FICHA DE CONTROLE - RELATÓRIO PIA - SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>
                <h1 class="text-center">RELATÓRIO PIA - Inclusão</h1>
                <hr>
                <form method="POST" action="/GravaRelatorioPia" accept-charset="UTF-8">
                    @include('Irmaos_Emaus_FichaControle.camposRelatorioPia')
                </form>
            </div>
        </div>
    </div>
@endsection

