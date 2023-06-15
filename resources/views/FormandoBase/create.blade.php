@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;">
                    FORMANDOS DO SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>

                <h1 class="text-center">Formandos - atletas - Inclusão</h1>
                <hr>
                <form method="POST" action="/FormandoBase" accept-charset="UTF-8">
                    @include('FormandoBase.camposinclusao')
                </form>
            </div>
        </div>
    </div>
@endsection
