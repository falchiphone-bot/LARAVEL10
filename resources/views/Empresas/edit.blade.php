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

          <div class="card-body" style="background-color: yellow">

            <div class="badge bg-warning text-wrap" style="width: 100%; color: blue;">

            EDIÇÃO DO CADASTRO DA EMPRESA: {{ $cadastro->Descricao }}
        </div>
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
<form method="POST" action="{{route('Empresas.update',$cadastro->ID)}}" accept-charset="UTF-8">
    <input type="hidden" name="_method" value="PUT">
    @include('Empresas.campos')
</form>

@endsection
