@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Editar Federação</h5>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form action="{{ route('SafFederacoes.update', $model->id) }}" method="POST">
                    @method('PUT')
                    @include('SafFederacoes.campos')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
