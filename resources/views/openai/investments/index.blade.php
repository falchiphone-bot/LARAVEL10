@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h4 mb-0">Contas / Investimentos</h1>
    <div class="d-flex gap-2">
      <a href="{{ route('openai.records.index') }}" class="btn btn-outline-secondary">← Registros</a>
      <a href="{{ route('openai.chat') }}" class="btn btn-outline-dark">Chat</a>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newAccountModal">Nova Conta</button>
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
          <input type="text" name="account" value="{{ $account ?? '' }}" class="form-control form-control-sm" placeholder="Nome da conta">
        </div>
        <div class="col-sm-3 col-md-3">
          <label class="form-label small mb-1">Corretora</label>
          <input type="text" name="broker" value="{{ $broker ?? '' }}" class="form-control form-control-sm" placeholder="Corretora">
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
          <th colspan="3"></th>
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
            <input type="date" name="date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
          </div>
          <div>
            <label class="form-label small mb-1">Total investido</label>
            <input type="text" name="total_invested" class="form-control mask-money-br" inputmode="decimal" placeholder="0,00" required>
          </div>
          <div>
            <label class="form-label small mb-1">Nome da conta</label>
            <input type="text" name="account_name" class="form-control" maxlength="100" required>
          </div>
          <div>
            <label class="form-label small mb-1">Corretora</label>
            <input type="text" name="broker" class="form-control" maxlength="100" required>
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
</script>
@endpush
