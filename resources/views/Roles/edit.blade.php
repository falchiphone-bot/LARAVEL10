@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">
          {{-- <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="Funcoes/index">Funcao</a></li>
              <li class="breadcrumb-item active" aria-current="page">edit</li>
            </ol>
          </nav> --}}


        <div class="card">
            <div class="card-header">
                Funções para o sistema administrativo e contábil
            </div>
            <a href="{{ route('Funcoes.index') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
            aria-disabled="true">Retornar para a lista de funções</a>
            <div class="card-body">
                {{-- <p>Total de funções: {{ $linhas}}</p> --}}
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
            <form method="POST" action="{{route('Funcoes.update',$cadastro->id)}}" accept-charset="UTF-8">
                <input type="hidden" name="_method" value="PUT">
                @include('Roles.campos')


            </form>
        </div>
    </div>
</div>

@endsection
