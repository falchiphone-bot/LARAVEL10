@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detalhes da Forma de Pagamento</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Nome</dt>
                    <dd class="col-sm-9">{{ $model->nome }}</dd>
                </dl>
                <a href="{{ route('FormaPagamento.index') }}" class="btn btn-secondary">Voltar</a>
            </div>
        </div>
    </div>
    </div>
@endsection
