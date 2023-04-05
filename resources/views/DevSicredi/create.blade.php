@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;">
                    DESENVOLVEDOR PARA O SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>

                <h1 class="text-center">Desenvolvedor - Inclusão</h1>
                <hr>
                <form method="POST" action="/DevSicredi" accept-charset="UTF-8">
                    @include('DevSicredi.campos')
                </form>
            </div>
        </div>
    </div>
@endsection
