@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;">
                    ORIGEM DE EMPRESAS PARA PAC E PIE SP - SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>

                <h1 class="text-center">Origem das Empresas PAC PIE - Inclusão</h1>
                <hr>
                <form method="POST" action="/TipoFormandoBaseWhatsapp" accept-charset="UTF-8">
                    @include('TipoFormandoBaseWhatsapp.camposincluir')
                </form>
            </div>
        </div>
    </div>
@endsection
