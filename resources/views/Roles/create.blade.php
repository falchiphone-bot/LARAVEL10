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

        <div class="card">

<hr>
<h1 class="text-center">Inclusão de função</h1>
<hr>
<form method="POST" action="/Funcoes" accept-charset="UTF-8">
    @include('Roles.campos')
</form>
@endsection
