@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 mb-0">Api Web IBKR</h1>
    <a href="{{ route('ibkr.status') }}" class="btn btn-outline-secondary btn-sm">Voltar ao Status</a>
  </div>

  <div class="alert alert-info small">
    Endpoints da Client Portal Web API serão abertos em: <code>{{ $base }}</code>.
    Alguns exigem sessão/autenticação via cookie e podem responder 401/403 se abertos diretamente no navegador.
    Para visualizar com layout da aplicação, prefira a rota interna de <a href="{{ route('ibkr.accounts') }}">Contas</a>.
  </div>

  <div class="row g-3">
    <div class="col-md-6 col-lg-4">
      <div class="card h-100">
        <div class="card-body d-flex flex-column">
          <h2 class="h6">Conectar (SSO Dispatcher)</h2>
          <p class="small text-muted">Inicia fluxo de login SSO no ambiente IBKR.</p>
          <a class="btn btn-primary mt-auto" href="{{ $base }}/sso/Dispatcher" target="_blank" rel="noopener noreferrer">Abrir {{ $base }}/sso/Dispatcher</a>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-lg-4">
      <div class="card h-100">
        <div class="card-body d-flex flex-column">
          <h2 class="h6">Contas</h2>
          <p class="small text-muted">Lista contas vinculadas (equivalente a GET <code>/v1/api/portfolio/accounts</code>) em uma página formatada.</p>
          <div class="mt-auto d-flex gap-2 flex-wrap">
            <a class="btn btn-outline-primary" href="{{ route('ibkr.accounts') }}" target="_blank" rel="noopener">Contas</a>
            <a class="btn btn-link text-decoration-none" href="{{ $base }}/v1/api/portfolio/accounts" target="_blank" rel="noopener noreferrer">Ver JSON no gateway</a>
            <a class="btn btn-link text-decoration-none" href="{{ route('ibkr.gateway.accounts') }}" target="_blank" rel="noopener">Ver JSON no gateway (view)</a>
            <a class="btn btn-link text-decoration-none" href="{{ route('ibkr.import.accounts.form') }}" target="_blank" rel="noopener">Importar JSON (colar/arquivo)</a>
            <a class="btn btn-link text-decoration-none" href="{{ route('ibkr.gateway.accounts.save') }}" target="_blank" rel="noopener">Salvar JSON do gateway e visualizar</a>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-lg-4">
      <div class="card h-100">
        <div class="card-body d-flex flex-column">
          <h2 class="h6">Status da Conexão</h2>
          <p class="small text-muted">Verifica autenticação da sessão atual.</p>
          <a class="btn btn-outline-primary mt-auto" href="{{ $base }}/v1/api/iserver/auth/status" target="_blank" rel="noopener noreferrer">GET /v1/api/iserver/auth/status</a>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-lg-4">
      <div class="card h-100">
        <div class="card-body d-flex flex-column">
          <h2 class="h6">Iniciar Sessão (SSODH)</h2>
          <p class="small text-muted">Inicializa sessão de dados históricos (pode exigir POST via API).</p>
          <a class="btn btn-outline-primary mt-auto" href="{{ $base }}/v1/api/iserver/auth/ssodh/init" target="_blank" rel="noopener noreferrer">/v1/api/iserver/auth/ssodh/init</a>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-lg-4">
      <div class="card h-100">
        <div class="card-body d-flex flex-column">
          <h2 class="h6">Reautenticar</h2>
          <p class="small text-muted">Renova a autenticação da sessão (pode exigir POST via API).</p>
          <a class="btn btn-outline-primary mt-auto" href="{{ $base }}/v1/api/iserver/reauthenticate" target="_blank" rel="noopener noreferrer">/v1/api/iserver/reauthenticate</a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
