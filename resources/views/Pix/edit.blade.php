@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
  <div class="container">
    <div class="card shadow-sm">
      <div class="card-header"><h5 class="mb-0">Editar PIX</h5></div>
      <div class="card-body">
  <form method="POST" action="{{ route('Pix.update', ['pix' => $model->getRouteKey()]) }}">
          @method('PUT')
          @include('Pix._form')
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
