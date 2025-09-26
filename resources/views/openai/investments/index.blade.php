@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h4 mb-0 d-flex align-items-center gap-2">
      Contas / Investimentos
      <x-market.badge storageKey="investments.localBadge.visible" idPrefix="inv" />
    </h1>
    <div class="d-flex gap-2 align-items-center flex-wrap">
      <a href="{{ route('openai.records.index') }}" class="btn btn-outline-secondary">← Registros</a>
      <a href="{{ route('openai.chat') }}" class="btn btn-outline-dark">Chat</a>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newAccountModal">Nova Conta</button>
      @canany(['ASSET STATS - LISTAR','ASSET STATS - CRIAR'])
        <div class="input-group" style="width:210px;">
          <input type="text" id="invSymbolInput" class="form-control" placeholder="Símbolo" maxlength="12" style="max-width:90px;">
          @can('ASSET STATS - LISTAR')
          <a id="invStatsBtn" data-base="{{ route('asset-stats.index') }}" class="btn btn-outline-primary" title="Ver estatísticas" href="{{ route('asset-stats.index') }}">Stats</a>
          @endcan
          @can('ASSET STATS - CRIAR')
          <a id="invImportBtn" data-base="{{ route('asset-stats.importForm') }}" class="btn btn-outline-secondary" title="Importar estatísticas" href="{{ route('asset-stats.importForm') }}">Imp</a>
          @endcan
        </div>
      @endcanany
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success py-2">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
  @endif

  <div class="card shadow-sm mb-3">
    <div class="card-body">
  <form class="row g-2 align-items-end" method="GET" action="{{ route('openai.investments.index') }}">
        <div class="col-sm-3 col-md-2">
          <label class="form-label small mb-1">De</label>
          <input type="date" name="from" value="{{ $from ?? '' }}" class="form-control form-control-sm">
        </div>
        <div class="col-sm-3 col-md-2">
          <label class="form-label small mb-1">Até</label>
          <input type="date" name="to" value="{{ $to ?? '' }}" class="form-control form-control-sm">
        </div>
        <div class="col-sm-3 col-md-3">
          <label class="form-label small mb-1">Conta</label>
          <input type="text" name="account" value="{{ request('account', $account ?? '') }}" class="form-control form-control-sm" placeholder="Nome da conta">
        </div>
        <div class="col-sm-3 col-md-3">
          <label class="form-label small mb-1">Corretora</label>
          <input type="text" name="broker" value="{{ request('broker', $broker ?? '') }}" class="form-control form-control-sm" placeholder="Corretora">
        </div>
        <div class="col-sm-2 col-md-2 d-grid">
          <button class="btn btn-sm btn-outline-primary">Filtrar</button>
        </div>
        @if(($from??'') || ($to??'') || ($account??'') || ($broker??''))
          <div class="col-sm-2 col-md-2">
            <a href="{{ route('openai.investments.index') }}" class="btn btn-sm btn-outline-dark w-100">Limpar</a>
          </div>
        @endif
      </form>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-sm table-bordered align-middle">
      <thead class="table-dark">
        <tr>
          <th style="width:14%">Data</th>
          <th style="width:18%" class="text-end">Total investido</th>
          <th>Nome da conta</th>
          <th style="width:22%">Corretora</th>
          <th style="width:18%" class="text-center">Ações</th>
        </tr>
      </thead>
      <tbody>
        @forelse($accounts as $acc)
          <tr>
            <td>{{ optional($acc->date)->format('d/m/Y') }}</td>
            <td class="text-end">{{ number_format((float)$acc->total_invested, 2, ',', '.') }}</td>
            <td>{{ $acc->account_name }}</td>
            <td>{{ $acc->broker }}</td>
            <td class="text-center">
              <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editAcc_{{ $acc->id }}">Editar</button>
              <form action="{{ route('openai.investments.destroy', $acc) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir este registro?');">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">Excluir</button>
              </form>
            </td>
          </tr>
          <!-- Modal Editar -->
          <div class="modal fade" id="editAcc_{{ $acc->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Editar Registro</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <form method="POST" action="{{ route('openai.investments.update', $acc) }}">
                  @csrf
                  @method('PATCH')
                  <div class="modal-body vstack gap-2">
                    <div>
                      <label class="form-label small mb-1">Data</label>
                      <input type="date" name="date" class="form-control" value="{{ optional($acc->date)->format('Y-m-d') }}" required>
                    </div>
                    <div>
                      <label class="form-label small mb-1">Total investido</label>
                      <input type="text" name="total_invested" class="form-control mask-money-br" inputmode="decimal" value="{{ number_format((float)$acc->total_invested, 2, ',', '.') }}" required>
                    </div>
                    <div>
                      <label class="form-label small mb-1">Nome da conta</label>
                      <input type="text" name="account_name" class="form-control" value="{{ $acc->account_name }}" maxlength="100" required>
                    </div>
                    <div>
                      <label class="form-label small mb-1">Corretora</label>
                      <input type="text" name="broker" class="form-control" value="{{ $acc->broker }}" maxlength="100" required>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        @empty
          <tr><td colspan="5" class="text-center text-muted">Nenhum registro.</td></tr>
        @endforelse
      </tbody>
      <tfoot>
        <tr class="table-light">
          <th colspan="1" class="text-end">Total</th>
          <th class="text-end">{{ number_format((float)($totalSum ?? 0), 2, ',', '.') }}</th>
          <th colspan="3" class="text-start">
            <form id="snapshot-form" method="POST" action="{{ route('investments.daily-balances.store') }}" class="d-inline">
              @csrf
              @if(request('from')) <input type="hidden" name="from" value="{{ request('from') }}"> @endif
              @if(request('to')) <input type="hidden" name="to" value="{{ request('to') }}"> @endif
              @if(request('account')) <input type="hidden" name="account" value="{{ request('account') }}"> @endif
              @if(request('broker')) <input type="hidden" name="broker" value="{{ request('broker') }}"> @endif
              <button type="submit" class="btn btn-sm btn-outline-primary" title="Salvar snapshot do saldo atual (soma dos registros de contas filtradas)">Snapshot Saldo</button>
            </form>
            <a href="{{ route('investments.daily-balances.index') }}" class="btn btn-sm btn-outline-secondary ms-2" title="Ver evolução dos snapshots">Histórico Saldos</a>
            <small id="snapshot-status" class="text-muted ms-2"></small>
          </th>
        </tr>
      </tfoot>
    </table>
  </div>

  <div class="mt-3">
    {{ $accounts->links() }}
  </div>
</div>

<!-- Modal Novo -->
<div class="modal fade" id="newAccountModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Novo Registro</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
  <form method="POST" action="{{ route('openai.investments.store') }}">
        @csrf
        <div class="modal-body vstack gap-2">
          <div>
            <label class="form-label small mb-1">Data</label>
    <input type="date" name="date" class="form-control" value="{{ request('date', now()->format('Y-m-d')) }}" required>
          </div>
          <div>
            <label class="form-label small mb-1">Total investido</label>
    <input type="text" name="total_invested" class="form-control mask-money-br" inputmode="decimal" placeholder="0,00" value="{{ request('total_invested') }}" required>
          </div>
          <div>
            <label class="form-label small mb-1">Nome da conta</label>
    <input type="text" name="account_name" class="form-control" maxlength="100" value="{{ request('account_name') }}" required>
          </div>
          <div>
            <label class="form-label small mb-1">Corretora</label>
    <input type="text" name="broker" class="form-control" maxlength="100" value="{{ request('broker') }}" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  (function(){
    const sym = document.getElementById('invSymbolInput');
    if(!sym) return;
    const statsBtn = document.getElementById('invStatsBtn');
    const importBtn = document.getElementById('invImportBtn');
    function upd(btn){
      if(!btn) return;
      const base = btn.getAttribute('data-base');
      const v = (sym.value||'').trim();
      btn.href = v ? base + '?symbol=' + encodeURIComponent(v) : base;
    }
    sym.addEventListener('input', function(){ upd(statsBtn); upd(importBtn); });
  })();
</script>
@endpush

@push('scripts')
<script>
// Máscara moeda BR (2 casas)
(function(){
  function formatMoneyBR(v){
    v = (v+"").replace(/[^0-9]/g,'');
    if(!v) return '';
    if(v.length===1) return '0,0'+v;
    if(v.length===2) return '0,'+v;
    return v.slice(0,-2).replace(/^0+/,'') + ',' + v.slice(-2);
  }
  document.querySelectorAll('.mask-money-br').forEach(el=>{
    el.addEventListener('input', ()=>{ el.value = formatMoneyBR(el.value); });
    el.addEventListener('blur', ()=>{ if(el.value==='') el.value='0,00'; });
    el.form?.addEventListener('submit', ()=>{ if(el.value){ el.value = el.value.replace(/\./g,'').replace(',','.'); } });
  });
})();

// Abrir modal automaticamente quando solicitado via query (open_new=1)
(function(){
  const params = new URLSearchParams(window.location.search);
  if(params.get('open_new') === '1'){
    const modalEl = document.getElementById('newAccountModal');
    if(modalEl){
      const m = new bootstrap.Modal(modalEl);
      m.show();
    }
  }
})();
</script>
@endpush
