@extends('layouts.bootstrap5')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Entradas x Saídas - Cálculos</h1>
        <div class="d-flex gap-2">
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">Voltar</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="row g-3 align-items-center">
                <div class="col-auto"><strong>Período:</strong></div>
                <div class="col-auto">
                    <span class="badge bg-primary">De: {{ $de ?? '-' }}</span>
                    <span class="badge bg-primary">Até: {{ $ate ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card h-100 border-success">
                <div class="card-body">
                    <h6 class="card-title text-success">Total de Entradas</h6>
                    <p class="display-6 text-success mb-0">R$ {{ number_format($entradas, 2, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-danger">
                <div class="card-body">
                    <h6 class="card-title text-danger">Total de Saídas</h6>
                    <p class="display-6 text-danger mb-0">R$ {{ number_format($saidas, 2, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-{{ $resultado >= 0 ? 'success' : 'danger' }}">
                <div class="card-body">
                    <h6 class="card-title">Resultado</h6>
                    <p class="display-6 mb-0" style="color: {{ $resultado >= 0 ? '#198754' : '#dc3545' }};">
                        R$ {{ number_format($resultado, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="alert mt-3 {{ $resultado >= 0 ? 'alert-success' : 'alert-danger' }}" role="alert">
        {{ $resultado >= 0 ? 'Superávit no período.' : 'Déficit no período.' }}
    </div>
</div>
@endsection
