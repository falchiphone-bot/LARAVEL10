@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">
        <div class="card">


<div class="badge bg-primary text-wrap" style="width: 100%;">
   CONTAS PARA CENTRO DE CUSTOS DO SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL - EDIÇÃO
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>

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

@endif

<form method="POST" action="{{route('ContasCentroCustos.update',$ContasCentroCustos->ID)}}" accept-charset="UTF-8">
    <input type="hidden" name="_method" value="PUT">
    @include('ContasCentroCustos.campos')
</form>

@endsection

