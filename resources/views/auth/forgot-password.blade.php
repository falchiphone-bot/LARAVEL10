@extends('layouts.bootstrap5')

@section('content')
<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8 col-xl-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex align-items-center gap-2">
                    <i class="fa-solid fa-key"></i>
                    <strong>Esqueceu sua senha?</strong>
                </div>
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success mb-3">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <div class="fw-bold mb-1">Ops! Algo deu errado.</div>
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <p class="text-muted small mb-4">
                        Sem problemas. Informe seu endereço de e-mail e enviaremos um link para redefinir sua senha, permitindo escolher uma nova.
                    </p>

                    <form method="POST" action="{{ route('password.email') }}" novalidate>
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input id="email" name="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                                Voltar ao login
                            </a>
                            <button type="submit" class="btn btn-danger">
                                Enviar link de redefinição de senha
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
