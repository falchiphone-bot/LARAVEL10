@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">

        <div class="card">

            @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            {{ session(['success' => null]) }}
            @elseif (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
            {{ session(['error' => null]) }}
            @endif

            @if (session('contabilidade'))
            <div class="alert alert-danger">
                {{ session('contabilidade') }}
            </div>
            {{ session(['contabilidade' => null]) }}
            @endif
            @if (session('Lancamento'))
            <div class="alert alert-danger">
                {{ session('Lancamento') }}
            </div>
            {{ session(['Lancamento' => null]) }}
            @endif


            <div class="badge bg-primary text-wrap" style="width: 100%;">
                CONTAS A PAGAR DO SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                @error('Descricao'))
                <div class="small text-danger">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <h1 class="text-center">Contas a pagar - Inclusão</h1>
            <hr>
            <form method="POST" action="/ContasPagar" accept-charset="UTF-8">
                @include('ContaPagar.camposIncluir')
            </form>
        </div>
    </div>
</div>
@endsection
