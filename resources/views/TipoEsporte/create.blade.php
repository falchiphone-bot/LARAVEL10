@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">
            
            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;">
                    TIPO DE ESPORTES PARA SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>

                <h1 class="text-center">Tipo de esportes - Inclusão</h1>
                <hr>
                <form method="POST" action="/TipoEsporte" accept-charset="UTF-8">
                    @include('TipoEsporte.campos')
                </form>
            </div>
        </div>
    </div>
@endsection
