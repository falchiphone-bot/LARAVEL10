@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 mb-0">IBKR • Contas</h1>
    <div class="d-flex gap-2">
      <a href="{{ route('ibkr.status') }}" class="btn btn-outline-secondary btn-sm">Status</a>
      <a href="{{ route('ibkr.accounts', ['raw'=>1]) }}" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener noreferrer">Ver JSON puro</a>
    </div>
  </div>

  @if(request()->query('note')==='cors_fallback')
    <div class="alert alert-warning small">
      A leitura direta do gateway foi bloqueada pelo navegador (CORS). Exibindo a view do app como alternativa.
    </div>
  @endif

  @if(!empty($source))
    <div class="alert alert-light border small mb-3">
      Fonte: <strong>{{ $source === 'gateway' ? 'Gateway local' : strtoupper($source) }}</strong>
      @if(!empty($base))
        <span class="ms-2">(<code>{{ $base }}</code>)</span>
      @endif
    </div>
  @endif

  @php
    // Normaliza para array
    $list = is_array($accounts) ? $accounts : [];
    // Caso venha como objeto único, transforma em lista
    if (!empty($accounts) && !is_array($accounts) && is_object($accounts)) { $list = [$accounts]; }
  @endphp

  @if(empty($list))
    <div class="alert alert-info">Nenhuma conta retornada.</div>
  @else
    @php
      // Detecta colunas conhecidas presentes nos itens
      $known = ['accountId','account','id','accountTitle','displayName','type','desc','isPaper','currency'];
      $present = [];
      foreach ($list as $item) {
        $arr = (array) $item;
        foreach ($known as $k) {
          if (array_key_exists($k, $arr)) { $present[$k] = true; }
        }
      }
      $cols = array_values(array_filter($known, fn($k)=>isset($present[$k])));
    @endphp

    @if(count($cols) >= 2)
      <div class="card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-striped mb-0 align-middle">
              <thead>
                <tr>
                  @foreach($cols as $c)
                    <th class="text-nowrap">{{ $c }}</th>
                  @endforeach
                </tr>
              </thead>
              <tbody>
                @foreach($list as $row)
                  @php $rowArr = (array) $row; @endphp
                  <tr>
                    @foreach($cols as $c)
                      <td class="text-nowrap">{{ is_array($rowArr[$c] ?? null) || is_object($rowArr[$c] ?? null) ? json_encode($rowArr[$c], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) : ($rowArr[$c] ?? '') }}</td>
                    @endforeach
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    @else
      <div class="card">
        <div class="card-body">
          <h2 class="h6">Resposta</h2>
          <pre class="mb-0 small">{{ json_encode($accounts, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) }}</pre>
        </div>
      </div>
    @endif
  @endif
</div>
@endsection
