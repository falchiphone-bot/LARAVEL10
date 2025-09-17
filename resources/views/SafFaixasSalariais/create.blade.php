@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
    <h2>Nova Faixa Salarial</h2>
    <form method="post" action="{{ route('SafFaixasSalariais.store') }}" class="card card-body mt-3">
        @csrf
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Não foi possível salvar.</strong> Verifique os campos destacados.
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @include('SafFaixasSalariais._form')
        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary">Salvar</button>
            <a href="{{ route('SafFaixasSalariais.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@push('scripts')
<script>
    window.addEventListener('load', function() {
        const invalid = document.querySelector('.is-invalid');
        if (invalid && typeof invalid.focus === 'function') invalid.focus();
    });
</script>
@endpush
@endsection
