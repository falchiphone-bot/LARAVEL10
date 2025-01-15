@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

<h1 class="text-center">Empresas - Inclusão</h1>
<hr>
<form method="POST" action="/Empresas" accept-charset="UTF-8">
    @include('Empresas.campos')
</form>
@endsection
