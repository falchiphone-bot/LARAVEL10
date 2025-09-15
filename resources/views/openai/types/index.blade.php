@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0 d-flex align-items-center gap-2">
      Tipos de Conversa
      <x-market.badge storageKey="types.localBadge.visible" idPrefix="types" />
    </h1>
    <div class="d-flex gap-2">
      <a href="{{ route('openai.chats') }}" class="btn btn-outline-secondary">← Minhas Conversas</a>
    </div>
  </div>

  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form action="{{ route('openai.types.store') }}" method="POST" class="row g-2 align-items-end">
        @csrf
        <div class="col-sm-6 col-md-4">
          <label for="name" class="form-label">Nome do tipo</label>
          <input type="text" name="name" id="name" class="form-control" maxlength="100" required>
        </div>
        <div class="col-sm-auto">
          <button type="submit" class="btn btn-primary">Adicionar</button>
        </div>
      </form>
      @error('name')
        <div class="text-danger small mt-2">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <h2 class="h6">Tipos cadastrados</h2>
      @if(($types ?? collect())->count())
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>Nome</th>
                <th class="text-center" style="width: 140px;">Chats</th>
                <th class="text-end" style="width: 220px;">Ações</th>
              </tr>
            </thead>
            <tbody>
              @foreach($types as $type)
                <tr>
                  <td>{{ $type->name }}</td>
                  <td class="text-center">{{ (int)($counts[$type->id] ?? 0) }}</td>
                  <td class="text-end">
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editType{{ $type->id }}">Editar</button>
                    <form action="{{ route('openai.types.destroy', $type) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir este tipo?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger" {{ (int)($counts[$type->id] ?? 0) > 0 ? 'disabled' : '' }}>Excluir</button>
                    </form>
                  </td>
                </tr>
                <!-- Modal Edit -->
                <div class="modal fade" id="editType{{ $type->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Editar tipo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <form action="{{ route('openai.types.update', $type) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="modal-body">
                          <div class="mb-3">
                            <label for="name{{ $type->id }}" class="form-label">Nome</label>
                            <input type="text" name="name" id="name{{ $type->id }}" class="form-control" value="{{ $type->name }}" maxlength="100" required>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                          <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <div class="text-muted">Nenhum tipo cadastrado.</div>
      @endif
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const endpointStatus = "{{ route('api.market.status') }}";
(function(){
  const KEY = 'types.localBadge.visible';
  const btn = document.getElementById('toggle-local-badge');
  const badge = document.getElementById('market-status-badge');
  function getVisible(){ try{ return localStorage.getItem(KEY) !== '0'; }catch(_e){ return true; } }
  function setVisible(v){ try{ localStorage.setItem(KEY, v ? '1' : '0'); }catch(_e){} }
  function apply(){ const vis = getVisible(); if (badge){ badge.classList.toggle('d-none', !vis); } if (btn){ const s=btn.querySelector('[data-state]'); if(s) s.textContent = vis ? 'ON' : 'OFF'; } }
  if (btn){ btn.addEventListener('click', function(){ setVisible(!getVisible()); apply(); }); }
  apply();
})();
(async function(){
  try{
    const badge = document.getElementById('market-status-badge');
    if(!badge) return;
    const resp = await fetch(endpointStatus, { headers: { 'Accept':'application/json' } });
    const data = await resp.json().catch(()=>null);
    if(!resp.ok || !data){ throw new Error('Falha ao obter status'); }
    const st = String(data.status||'').toLowerCase();
    const label = String(data.label||'Mercado');
    const next = data.next_change_at ? ` • Próx: ${String(data.next_change_at).replace('T',' ').slice(0,16)}` : '';
    let cls = 'bg-secondary';
    if (st === 'open') cls = 'bg-success';
    else if (st === 'pre') cls = 'bg-warning text-dark';
    else if (st === 'after') cls = 'bg-info text-dark';
    else if (st === 'closed') cls = 'bg-secondary';
    badge.className = 'badge ' + cls;
    badge.textContent = `Mercado: ${label}` + next;
    if (data.reason){ badge.title = `${label} — ${data.reason}`; }
  }catch(_e){
    const badge = document.getElementById('market-status-badge');
    if (badge){ badge.className='badge bg-secondary'; badge.textContent='Mercado: indisponível'; }
  }
})();
</script>
@endpush
