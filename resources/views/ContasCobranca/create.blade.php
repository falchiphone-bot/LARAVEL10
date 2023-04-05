@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;">
                    Gerenciamento de Contas
                </div>

                <h1 class="text-center">Contas Cobrança - Inclusão</h1>
                <hr>
                <form method="POST" action="{{ route('ContasCobranca.store') }}" accept-charset="UTF-8">
                    {{-- @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif --}}
                    @include('ContasCobranca.campos')
                </form>
            </div>
        </div>
    </div>
@endsection
