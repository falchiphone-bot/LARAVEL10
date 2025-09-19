@extends('layouts.bootstrap5')
@section('content')
<div class="py-4 bg-light"><div class="container">
  <div class="card"><div class="card-body">
    <h5 class="mb-3">Novo Envio</h5>
    <form method="POST" action="{{ route('Envios.store') }}" enctype="multipart/form-data">
      @include('Envios._form')
    </form>
  </div></div>
</div></div>
@endsection
