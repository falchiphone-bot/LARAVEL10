@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Permissions</a></li>
              <li class="breadcrumb-item active" aria-current="page">create</li>
            </ol>
          </nav>

        <div class="card">
<h1 class="text-center">Inclusão de permissões</h1>
<hr>
<form method="POST" action="/Permissoes" accept-charset="UTF-8">
    @include('Permissions.campos')
</form>
@endsection
