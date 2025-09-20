@extends('layouts.bootstrap5')

@section('content')
<div class="container my-4">
        <div class="row mb-3 align-items-center">
        <div class="col">
                <h1 class="h3 mb-0"><i class="fa-solid fa-user"></i> Perfil</h1>
            <small class="text-muted">Atualize seus dados e senha da conta.</small>
        </div>
        <div class="col-auto">
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="btn btn-outline-danger" title="Encerrar sessão">
                    <i class="fa-solid fa-right-from-bracket"></i> Desconectar
                </button>
            </form>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row g-4">
                <div class="col-12 col-lg-6">
                    <div class="card shadow-sm border-danger">
                                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                                    <strong><i class="fa-solid fa-id-card"></i> Informações do perfil</strong>
                                    @php $user = $user ?? Auth::user(); @endphp
                                    @if($user && method_exists($user, 'hasVerifiedEmail') ? $user->hasVerifiedEmail() : !!$user->email_verified_at)
                                        <span class="badge bg-light text-success"><i class="fa-solid fa-check-circle"></i> Email verificado</span>
                                    @else
                                        <span class="badge bg-warning text-dark"><i class="fa-solid fa-envelope-circle-xmark"></i> Email não verificado</span>
                                    @endif
                </div>
                <div class="card-body">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>

                <div class="col-12 col-lg-6">
                    <div class="card shadow-sm border-success">
                        <div class="card-header bg-success text-white">
                            <strong><i class="fa-solid fa-key"></i> Alterar senha</strong>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>

                    @can('PERFIL - EXCLUIR CONTA')
                        <div class="col-12">
                            <div class="card shadow-sm border-warning">
                                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                                    <strong><i class="fa-solid fa-triangle-exclamation"></i> Excluir conta</strong>
                                    <span class="badge bg-danger"><i class="fa-solid fa-user-slash"></i> Ação irreversível</span>
                                </div>
                                <div class="card-body">
                                    @include('profile.partials.delete-user-form')
                                </div>
                            </div>
                        </div>
                    @endcan

        {{--
        <div class="col-12">
            <div class="card border-danger">
                <div class="card-header text-danger"><strong>Excluir conta</strong></div>
                <div class="card-body">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
        --}}
    </div>
</div>
@endsection
