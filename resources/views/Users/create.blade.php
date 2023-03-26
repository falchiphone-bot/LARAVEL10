
@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">
          {{-- <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Users</a></li>
              <li class="breadcrumb-item active" aria-current="page">create</li>
            </ol>
          </nav> --}}

        <div class="card">
            <div class="card-header">
                Inclusão de usuários
            </div>


<form method="POST" action="/Usuarios" accept-charset="UTF-8">
    @include('Users.campos')
</form>

@endsection
