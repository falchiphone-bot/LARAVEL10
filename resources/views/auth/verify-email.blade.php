@extends('layouts.bootstrap5')

@section('content')
<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8 col-xl-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex align-items-center gap-2">
                    <i class="fa-solid fa-envelope"></i>
                    <strong>Verifique seu e-mail</strong>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Obrigado por se registrar! Antes de começar, confirme seu endereço de e-mail clicando no link que acabamos de enviar. Se você não recebeu o e-mail, podemos enviar outro.
                    </p>

                    @if (session('status') == 'verification-link-sent')
                        <div class="alert alert-success mb-3">
                            Um novo link de verificação foi enviado para o e-mail informado no cadastro.
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <form method="POST" action="{{ route('verification.send') }}" class="mb-0">
                            @csrf
                            <button type="submit" class="btn btn-danger">Reenviar e-mail de verificação</button>
                        </form>

                        <form method="POST" action="{{ route('logout') }}" class="mb-0">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary">Desconectar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
