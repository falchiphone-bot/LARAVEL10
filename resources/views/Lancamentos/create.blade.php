@extends('layouts.bootstrap5')
@section('content')

<h1 class="text-center">Inclusão de lancamento</h1>
<hr>
<form method="POST" action="/Lancamentos" accept-charset="UTF-8">
    @include('Lancamentos.campos')
</form>
@endsection
