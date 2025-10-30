@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">
            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;">
                    FICHA DE CONTROLE - RELATÓRIO PIA - Edição
                </div>
                <h1 class="text-center">RELATÓRIO PIA - Edição</h1>
                <hr>
                <form method="POST" action="{{ route('Irmaos_Emaus_FichaControle.RelatorioPia.update', $model->id) }}" accept-charset="UTF-8">
                    @method('PUT')
                    @include('Irmaos_Emaus_FichaControle.camposRelatorioPia')
                </form>
            </div>
        </div>
    </div>
@endsection
