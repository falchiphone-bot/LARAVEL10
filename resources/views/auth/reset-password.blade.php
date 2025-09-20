@extends('layouts.bootstrap5')

@section('content')
<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8 col-xl-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex align-items-center gap-2">
                    <i class="fa-solid fa-lock"></i>
                    <strong>Redefinir senha</strong>
                </div>
                <div class="card-body">
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

                    <form method="POST" action="{{ route('password.store') }}" novalidate>
                        @csrf
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input id="email" name="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Nova senha</label>
                            <input id="password" name="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" required autocomplete="new-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirmar nova senha</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control form-control-lg @error('password_confirmation') is-invalid @enderror" required autocomplete="new-password">
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('login') }}" class="btn btn-outline-secondary">Voltar ao login</a>
                            <button type="submit" class="btn btn-danger">Redefinir senha</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
