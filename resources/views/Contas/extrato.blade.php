@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        {{-- <div class="container"> --}}
            {{-- <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Conta</a></li>
              <li class="breadcrumb-item active" aria-current="page">Index</li>
            </ol>
          </nav> --}}

            @livewire('conta.extrato',[$contaID])

        {{-- </div> --}}
    </div>
@endsection
