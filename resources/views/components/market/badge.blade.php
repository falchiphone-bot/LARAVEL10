@props([
  'storageKey' => 'page.localBadge.visible',
  'idPrefix' => null,
  'showToggle' => true,
  'badgeClass' => 'badge bg-secondary',
  'buttonClass' => 'btn btn-sm btn-outline-secondary',
])
@php
  $prefix = $idPrefix ?: preg_replace('/[^a-z0-9_-]+/i','-', (string) $storageKey);
  $badgeId = $prefix.'-market-status';
  $toggleId = $prefix.'-toggle';
@endphp

<span id="{{ $badgeId }}" class="{{ $badgeClass }}" title="Status do mercado (NYSE)">Mercado: carregando…</span>
@if($showToggle)
  <button type="button" id="{{ $toggleId }}" class="{{ $buttonClass }}" title="Mostrar/ocultar badge local do mercado">
    Badge Mercado: <span data-state>ON</span>
  </button>
@endif

@push('scripts')
<script>
(function(){
  const endpoint = "{{ route('api.market.status') }}";
  const storageKey = @json($storageKey);
  const badgeId = @json($badgeId);
  const toggleId = @json($toggleId);
  const badge = document.getElementById(badgeId);
  const btn = document.getElementById(toggleId);
  function getVisible(){ try{ return localStorage.getItem(storageKey) !== '0'; }catch(_e){ return true; } }
  function setVisible(v){ try{ localStorage.setItem(storageKey, v ? '1' : '0'); }catch(_e){} }
  function apply(){ const vis = getVisible(); if (badge){ badge.classList.toggle('d-none', !vis); } if (btn){ const s=btn.querySelector('[data-state]'); if(s) s.textContent = vis ? 'ON' : 'OFF'; } }
  if (btn){ btn.addEventListener('click', function(){ setVisible(!getVisible()); apply(); }); }
  apply();
  (async function(){
    if(!badge) return;
    try{
      const resp = await fetch(endpoint, { headers: { 'Accept':'application/json' } });
      const data = await resp.json().catch(()=>null);
      if(!resp.ok || !data){ throw new Error('Falha ao obter status'); }
      const st = String(data.status||'').toLowerCase();
      const label = String(data.label||'Mercado');
      const next = data.next_change_at ? ' • Próx: ' + String(data.next_change_at).replace('T',' ').slice(0,16) : '';
      let cls = 'bg-secondary';
      if (st === 'open') cls = 'bg-success';
      else if (st === 'pre') cls = 'bg-warning text-dark';
      else if (st === 'after') cls = 'bg-info text-dark';
      else if (st === 'closed') cls = 'bg-secondary';
      badge.className = 'badge ' + cls;
      badge.textContent = 'Mercado: ' + label + next;
      if (data.reason){ badge.title = `${label} — ${data.reason}`; }
    }catch(_e){
      if (badge){ badge.className='badge bg-secondary'; badge.textContent='Mercado: indisponível'; }
    }
  })();
})();
</script>
@endpush
