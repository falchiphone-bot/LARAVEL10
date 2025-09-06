@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h4 mb-0">Registros de Conversas</h1>
    <div class="d-flex gap-2">
      <a href="{{ route('openai.menu') }}" class="btn btn-outline-secondary">← Menu</a>
      <a href="{{ route('openai.chats', ['view'=>'table']) }}" class="btn btn-outline-primary">Ver Conversas</a>
      <a href="{{ route('openai.chat') }}" class="btn btn-outline-dark">Chat</a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success py-2">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
  @endif

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form class="row g-2 align-items-end" method="GET" action="{{ route('openai.records.index') }}">
        <div class="col-sm-4 col-md-3">
          <label class="form-label small mb-1">Conversa</label>
          <select name="chat_id" class="form-select form-select-sm">
            <option value="">Todas</option>
            @foreach($chats as $c)
              <option value="{{ $c->id }}" {{ ($chatId ?? 0) == $c->id ? 'selected' : '' }}>{{ $c->title }} @if($c->code) ({{ $c->code }}) @endif</option>
            @endforeach
          </select>
        </div>
        <div class="col-sm-3 col-md-2">
          <label class="form-label small mb-1">De</label>
          <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
        </div>
        <div class="col-sm-3 col-md-2">
          <label class="form-label small mb-1">Até</label>
          <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
        </div>
        <div class="col-sm-2 col-md-2">
          <button class="btn btn-sm btn-outline-primary w-100" type="submit">Filtrar</button>
        </div>
        @if(request()->hasAny(['chat_id','from','to']) && (request('chat_id')||request('from')||request('to')))
          <div class="col-sm-2 col-md-2">
            <a href="{{ route('openai.records.index') }}" class="btn btn-sm btn-outline-dark w-100">Limpar</a>
          </div>
        @endif
      </form>
    </div>
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h2 class="h6 mb-3">Novo Registro</h2>
  <form id="newRecordForm" method="POST" action="{{ route('openai.records.store') }}" class="row g-2 align-items-end">
        @csrf
        <div class="col-sm-4 col-md-3">
          <label class="form-label small mb-1">Conversa *</label>
          <select name="chat_id" class="form-select form-select-sm" required>
            <option value="">Selecionar...</option>
            @foreach($chats as $c)
              <option value="{{ $c->id }}" {{ ($chatId ?? 0) == $c->id ? 'selected' : '' }}>{{ $c->title }} @if($c->code) ({{ $c->code }}) @endif</option>
            @endforeach
          </select>
        </div>
        <div class="col-sm-3 col-md-3">
          <label class="form-label small mb-1">Data/Hora * (dd/mm/aaaa HH:MM[:SS])</label>
            <input type="text" name="occurred_at" class="form-control form-control-sm mask-datetime-br" required placeholder="ex: 05/09/2025 19:41:00" value="{{ old('occurred_at') ?? now()->format('d/m/Y H:i:s') }}" autocomplete="off">
        </div>
        <div class="col-sm-3 col-md-2">
          <label class="form-label small mb-1">Valor *</label>
          <input type="number" step="0.01" name="amount" class="form-control form-control-sm" required>
        </div>
        <div class="col-sm-2 col-md-2">
          <button type="submit" class="btn btn-sm btn-success w-100">Adicionar</button>
        </div>
      </form>
    </div>
  </div>

  <div class="table-responsive">
  @push('scripts')
  <script>
  (()=>{
   function formatDateTimeBR(raw){
     let v = raw.replace(/\D/g,'').slice(0,14);
     let o='';
     if(v.length>0) o+=v.slice(0,2);
     if(v.length>=3) o+='/'+v.slice(2,4);
     if(v.length>=5) o+='/'+v.slice(4,8);
     if(v.length>=9) o+=' '+v.slice(8,10);
     if(v.length>=11) o+=':'+v.slice(10,12);
     if(v.length>=13) o+=':'+v.slice(12,14);
     return o;
   }
   function applyMask(el){ el.value = formatDateTimeBR(el.value); }
   document.querySelectorAll('.mask-datetime-br').forEach(el=>{
     el.addEventListener('input', ()=>applyMask(el));
     el.addEventListener('paste', ()=> setTimeout(()=>applyMask(el),0));
     el.addEventListener('blur', ()=>{ if(/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}$/.test(el.value)) el.value+=':00'; });
   });
  })();
  </script>
  @endpush
    @if($selectedChat)
      <div class="alert alert-info py-2 mb-2">
        <strong>Conversa Selecionada:</strong> {{ $selectedChat->title }} @if($selectedChat->code)<span class="badge bg-dark ms-1">{{ $selectedChat->code }}</span>@endif
      </div>
    @endif
    <table class="table table-sm table-bordered align-middle">
      <thead class="table-dark">
        <tr>
          <th style="width:22%">Conversa</th>
          <th style="width:10%">Código</th>
          <th style="width:18%">Data/Hora</th>
          <th style="width:15%" class="text-end">Valor</th>
          <th style="width:20%">Usuário</th>
          <th style="width:15%" class="text-center">Ações</th>
        </tr>
      </thead>
      <tbody>
        @forelse($records as $r)
          <tr>
            <td>{{ $r->chat?->title }}</td>
            <td class="text-center">{{ $r->chat?->code ?? '—' }}</td>
            @php
              $dt = $r->occurred_at;
              $formatted = $dt?->format('d/m/Y H:i:s');
              $d = $dt? (int)$dt->format('d') : null;
              $m = $dt? (int)$dt->format('m') : null;
              $suspect = $d && $m && $d <= 12 && $m <= 12 && $d !== $m; // ambígua
            @endphp
            <td class="{{ $suspect ? 'table-warning' : '' }}" @if($suspect) title="Data potencialmente invertida (dia/mês). Edite para confirmar." @endif>
              {{ $formatted }}
              @if($suspect)
                <span class="badge bg-warning text-dark ms-1">?</span>
              @endif
            </td>
            <td class="text-end">{{ number_format((float)$r->amount, 2, ',', '.') }}</td>
            <td>{{ $r->user?->name }}</td>
            <td class="text-center">
              <button type="button" class="btn btn-sm btn-success me-1" onclick="prepQuickAdd({{ $r->chat_id }})" title="Adicionar novo registro desta conversa">➕</button>
              <a href="{{ route('openai.records.edit', $r) }}" class="btn btn-sm btn-outline-primary me-1">Editar</a>
              <form action="{{ route('openai.records.destroy', $r) }}" method="POST" onsubmit="return confirm('Remover registro?');" class="d-inline">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">Excluir</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted">Nenhum registro.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-3">
    {{ $records->links() }}
  </div>
</div>
@endsection
@push('scripts')
<script>
function prepQuickAdd(chatId){
  const sel = document.querySelector('#newRecordForm select[name="chat_id"]');
  if(sel){ sel.value = chatId; }
  const dt = document.querySelector('#newRecordForm input[name="occurred_at"]');
  if(dt && !dt.value){
    const now = new Date();
    const pad = n=> n.toString().padStart(2,'0');
    dt.value = pad(now.getDate())+'/'+pad(now.getMonth()+1)+'/'+now.getFullYear()+' '+pad(now.getHours())+':'+pad(now.getMinutes())+':'+pad(now.getSeconds());
  }
  document.getElementById('newRecordForm').scrollIntoView({behavior:'smooth', block:'center'});
  if(dt) dt.focus();
}
</script>
@endpush
