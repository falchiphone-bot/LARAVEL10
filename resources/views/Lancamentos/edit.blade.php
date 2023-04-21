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
            <h1 class="text-center">Plano de contas padrão para contabilidade</h1>
        <div class="card">

            <h1 class="text-center">Edição da conta</h1>
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
            <form method="POST" action="{{route('PlanoContas.update',$cadastro->ID)}}" accept-charset="UTF-8">
                <input type="hidden" name="_method" value="PUT">
                @include('PlanoContas.campos')
            </form>
        </div>
    </div>
</div>
@endsection

