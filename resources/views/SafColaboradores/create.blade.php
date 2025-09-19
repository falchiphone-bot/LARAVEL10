@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Incluir Colaborador</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('SafColaboradores.store') }}">
                    @include('SafColaboradores._form')
                </form>
            </div>
        </div>
    </div>
    </div>
@endsection
