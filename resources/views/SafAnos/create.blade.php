@extends('layouts.bootstrap5')
@section('content')
<div class="container">
  <h1 class="h3 mb-3">Novo Ano</h1>
  <form method="POST" action="{{ route('SafAnos.store') }}">
    @include('SafAnos.campos')
  </form>
</div>
@endsection
