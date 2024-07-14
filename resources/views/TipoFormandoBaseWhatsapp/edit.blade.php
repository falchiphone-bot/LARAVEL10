@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">
        <div class="card">


<div class="badge bg-primary text-wrap" style="width: 100%;">
    ORIGEM DAS EMPRESAS PARA PAC E PIE SP - SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{route('TipoFormandoBaseWhatsapp.update',$model->id)}}" accept-charset="UTF-8">
    <input type="hidden" name="_method" value="PUT">
    @include('TipoFormandoBaseWhatsapp.camposeditar')
</form>

@endsection

