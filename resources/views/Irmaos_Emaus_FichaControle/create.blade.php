@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">
            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;">
                    FICHA DE CONTROLE DO SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>
                <h1 class="text-center">Nome na FICHA DE CONTROLE - Inclusão</h1>
                <hr>
                <form method="POST" action="/Irmaos_Emaus_FichaControle" accept-charset="UTF-8">
                    @include('Irmaos_Emaus_FichaControle.campos')
                </form>
            </div>
        </div>
    </div>
@endsection

