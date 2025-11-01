@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 mb-0">IBKR • Status da conexão</h1>
    <div class="d-flex gap-2">
      <a class="btn btn-sm btn-outline-primary" href="{{ ($base ?? null) ? $base.'/sso/Dispatcher' : route('ibkr.connect') }}" data-busy="1" target="_blank" rel="noopener">Conectar</a>
  <a class="btn btn-sm btn-outline-secondary" href="{{ route('ibkr.accounts') }}" data-busy="1" target="_blank" rel="noopener">Contas</a>
      <a class="btn btn-sm btn-link text-decoration-none" href="{{ route('ibkr.accounts', ['raw'=>1]) }}" data-busy="1">Ver JSON puro</a>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <h2 class="h6">Tokens</h2>
      @php $t = $tokens ?? []; @endphp
      <ul class="small">
        <li>Access token: <code>{{ isset($t['access_token']) ? '•••••• (presente)' : '—' }}</code></li>
        <li>Refresh token: <code>{{ isset($t['refresh_token']) ? '•••••• (presente)' : '—' }}</code></li>
        <li>Expira em (seg): <code>{{ $t['expires_in'] ?? '—' }}</code></li>
        <li>Salvo em: <code>{{ isset($t['saved_at']) ? \Carbon\Carbon::parse($t['saved_at'])->format('d/m/Y H:i:s') : '—' }}</code></li>
      </ul>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <h2 class="h6">Auth Status</h2>
      @if(!empty($status))
        @if(($status['ok'] ?? false)===true)
          <pre class="small mb-0">{{ json_encode($status['data'] ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) }}</pre>
        @else
          <div class="text-danger small">Falha ao consultar status ({{ $status['reason'] ?? 'erro' }}).</div>
        @endif
      @else
        <div class="text-muted small">Conecte-se para consultar o status.</div>
      @endif
    </div>
  </div>
</div>
@endsection
