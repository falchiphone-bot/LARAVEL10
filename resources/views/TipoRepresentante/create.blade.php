@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;">
                    TIPO DE REPRESENTANTES PARA SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>

                <h1 class="text-center">Tipo de representante - Inclusão</h1>
                <hr>
                <form method="POST" action="/TipoRepresentantes" accept-charset="UTF-8">
                    @include('TipoRepresentante.campos')
                </form>
            </div>
        </div>
    </div>
@endsection
