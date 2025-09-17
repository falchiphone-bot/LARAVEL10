@extends('layouts.bootstrap5')
@section('content')
<div class="container">
  <h1 class="h3 mb-3">Editar Ano</h1>
  <form method="POST" action="{{ route('SafAnos.update', $model->id) }}">
    @method('PUT')
    @include('SafAnos.campos')
  </form>
</div>
@endsection
