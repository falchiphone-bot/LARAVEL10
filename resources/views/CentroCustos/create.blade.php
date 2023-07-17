@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;">
                    CENTRO DE CUSTOS DO SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                    @error('Descricao'))
                    <div class="small text-danger">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <h1 class="text-center">Centro de custos - Inclusão</h1>
                <hr>
                <form method="POST" action="/CentroCustos" accept-charset="UTF-8">
                    @include('CentroCustos.campos')
                </form>
            </div>
        </div>
    </div>
@endsection
