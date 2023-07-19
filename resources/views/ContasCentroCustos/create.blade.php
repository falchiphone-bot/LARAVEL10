@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;">
                    CONTAS PARA O CENTRO DE CUSTOS DO SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                    {{-- @error('Descricao'))
                    <div class="small text-danger">
                            {{ $message }}
                        </div>
                    @enderror --}}
                </div>

                <h1 class="text-center">Contas para centro de custos - Inclusão</h1>
                <hr>
                <form method="POST" action="/ContasCentroCustos" accept-charset="UTF-8">
                    @include('ContasCentroCustos.campos')
                </form>
            </div>
        </div>
    </div>
@endsection
