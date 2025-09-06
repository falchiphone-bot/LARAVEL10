@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h5 mb-0">Editar Registro</h1>
    <div class="d-flex gap-2">
      <a href="{{ route('openai.records.index', ['chat_id' => $record->chat_id, 'from'=> $record->occurred_at->format('Y-m-d'), 'to'=>$record->occurred_at->format('Y-m-d')]) }}" class="btn btn-outline-secondary">← Voltar</a>
    </div>
  </div>

  @if($errors->any())
    <div class="alert alert-danger py-2 small mb-3">{{ $errors->first() }}</div>
  @endif
  @if(session('success'))
    <div class="alert alert-success py-2 small mb-3">{{ session('success') }}</div>
  @endif

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="POST" action="{{ route('openai.records.update', $record) }}" class="row g-3">
        @csrf
        @method('PUT')
        <div class="col-md-5">
          <label class="form-label small mb-1">Conversa *</label>
          <select name="chat_id" class="form-select form-select-sm" required>
            @foreach($chats as $c)
              <option value="{{ $c->id }}" {{ $record->chat_id == $c->id ? 'selected' : '' }}>{{ $c->title }} @if($c->code) ({{ $c->code }}) @endif</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label small mb-1">Data/Hora * <span class="text-muted">dd/mm/aaaa HH:MM[:SS]</span></label>
          <input type="text" name="occurred_at" class="form-control form-control-sm mask-datetime-br" required value="{{ old('occurred_at', $record->occurred_at->format('d/m/Y H:i:s')) }}" placeholder="05/09/2025 19:41:00" autocomplete="off">
        </div>
        <div class="col-md-3">
          <label class="form-label small mb-1">Valor *</label>
          <input type="number" step="0.01" name="amount" class="form-control form-control-sm" required value="{{ old('amount', $record->amount) }}">
        </div>
        <div class="col-12 d-flex justify-content-between mt-2">
          <button type="submit" class="btn btn-sm btn-primary">Salvar</button>
          <a href="{{ route('openai.records.index', ['chat_id' => $record->chat_id]) }}" class="btn btn-sm btn-outline-secondary">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(()=>{
 function formatDateTimeBR(raw){
   let v = raw.replace(/\D/g,'').slice(0,14); // DD MM YYYY HH MM SS => 14 dígitos
   let o='';
   if(v.length>0) o+=v.slice(0,2);            // DD
   if(v.length>=3) o+='/'+v.slice(2,4);       // /MM
   if(v.length>=5) o+='/'+v.slice(4,8);       // /YYYY
   if(v.length>=9) o+=' '+v.slice(8,10);      // HH
   if(v.length>=11) o+=':'+v.slice(10,12);    // :MM
   if(v.length>=13) o+=':'+v.slice(12,14);    // :SS
   return o;
 }
 function applyMask(el){
   let before = el.value;
   el.value = formatDateTimeBR(el.value);
 }
 document.querySelectorAll('.mask-datetime-br').forEach(el=>{
   el.addEventListener('input', e=> applyMask(el));
   el.addEventListener('paste', e=> { setTimeout(()=>applyMask(el), 0); });
   el.addEventListener('blur', e=> {
     // Se usuário digitou só até minutos (16 dígitos com separadores), acrescenta :00
     if(/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}$/.test(el.value)){
       el.value += ':00';
     }
   });
 });
})();
</script>
@endpush
