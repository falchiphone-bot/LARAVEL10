@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">
        {{-- <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Permissions</a></li>
              <li class="breadcrumb-item active" aria-current="page">edit</li>
            </ol>
          </nav> --}}

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


        <div class="card">
            <h1 class="text-center">Edição de Contas a pagar</h1>
            <hr>
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif


            <form method="POST" action="{{route('ContasPagar.update', $contasPagar->ID), $contasPagar->LancamentoID}}" accept-charset="UTF-8">
                <input type="hidden" name="_method" value="PUT">
                @include('ContaPagar.campos')
            </form>

            @endsection
