@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">
        <div class="card">

<h1 class="text-center">Edição da moeda</h1>
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

<form method="POST" action="{{route('Moedas.update',$moedas->id)}}" accept-charset="UTF-8">
    <input type="hidden" name="_method" value="PUT">
    @include('Moedas.campos')
</form>

@endsection

