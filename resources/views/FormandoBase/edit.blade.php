@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">
        <div class="card">



<div class="badge bg-primary text-wrap" style="width: 100%;">
   FORMANDOS DO SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL - EDIÇÃO
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

<form method="POST" action="{{route('FormandoBase.update',$model->id)}}" accept-charset="UTF-8">
    <input type="hidden" name="_method" value="PUT">
    @include('FormandoBase.editcampos')
</form>

@endsection

 
