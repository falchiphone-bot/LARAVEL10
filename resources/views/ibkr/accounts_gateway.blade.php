@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 mb-0">IBKR • Contas (Gateway)</h1>
    <div class="d-flex gap-2">
      <a href="{{ route('ibkr.api-web') }}" class="btn btn-outline-secondary btn-sm">Api Web IBKR</a>
      <a href="{{ $base }}/v1/api/portfolio/accounts" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener noreferrer">Abrir JSON no gateway</a>
    </div>
  </div>

  <div class="alert alert-light border small mb-3">
    Fonte: <strong>Gateway local</strong>
    @if(!empty($base))
      <span class="ms-2">(<code>{{ $base }}</code>)</span>
    @endif
  </div>

  <div id="gw-status" class="alert alert-warning small">Carregando contas do gateway…</div>
  <div id="gw-table" class="d-none"></div>
  <div id="gw-json-wrapper" class="card d-none">
    <div class="card-body">
      <h2 class="h6">Resposta (JSON)</h2>
      <pre id="gw-json" class="mb-0 small"></pre>
    </div>
  </div>

  <script>
  (function(){
    const base = @json($base);
    const url = base + '/v1/api/portfolio/accounts';
    const elStatus = document.getElementById('gw-status');
    const elTableWrap = document.getElementById('gw-table');
    const elJsonWrap = document.getElementById('gw-json-wrapper');
    const elJson = document.getElementById('gw-json');

    function renderTable(list){
      // Colunas conhecidas
      const known = ['accountId','account','id','accountTitle','displayName','type','desc','currency'];
      const present = new Set();
      list.forEach(item => {
        const obj = (typeof item === 'object' && item) ? item : {};
        known.forEach(k => { if(Object.prototype.hasOwnProperty.call(obj,k)) present.add(k); });
      });
      const cols = known.filter(k => present.has(k));
      if(cols.length < 2) return false;
      const thead = '<thead><tr>' + cols.map(c=>`<th class="text-nowrap">${c}</th>`).join('') + '</tr></thead>';
      const rows = list.map(item => {
        const obj = (typeof item === 'object' && item) ? item : {};
        const tds = cols.map(c => {
          const v = obj[c];
          if(v && (typeof v === 'object')) return `<td class="text-nowrap">${JSON.stringify(v)}</td>`;
          return `<td class="text-nowrap">${v ?? ''}</td>`;
        }).join('');
        return `<tr>${tds}</tr>`;
      }).join('');
      const html = `
        <div class="card">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-sm table-striped mb-0 align-middle">
                ${thead}
                <tbody>${rows}</tbody>
              </table>
            </div>
          </div>
        </div>`;
      elTableWrap.innerHTML = html;
      elTableWrap.classList.remove('d-none');
      return true;
    }

    fetch(url, { credentials: 'include' })
      .then(async r => {
        if(!r.ok){
          const txt = await r.text().catch(()=> '');
          throw new Error(`HTTP ${r.status} ${r.statusText} - ${txt.slice(0,200)}`);
        }
        return r.json();
      })
      .then(json => {
        const list = Array.isArray(json) ? json : (json ? [json] : []);
        if(!renderTable(list)){
          elJson.textContent = JSON.stringify(json, null, 2);
          elJsonWrap.classList.remove('d-none');
        }
        elStatus.className = 'alert alert-success small';
        elStatus.textContent = 'Contas carregadas do gateway.';
      })
      .catch(err => {
        // Aviso e fallback automático para a view do app (OAuth) mantendo alternativa de JSON cru
        const jsonCru = base + '/v1/api/portfolio/accounts';
        elStatus.className = 'alert alert-warning small';
        elStatus.innerHTML = 'Não foi possível ler do gateway nesta origem (possível CORS). Redirecionando para a view do app... ' +
          '<a href="'+jsonCru+'" target="_blank" rel="noopener">ver JSON cru</a><br>'+
          '<code>' + (err && err.message ? err.message : err) + '</code>';
        setTimeout(function(){
          window.location.href = @json(route('ibkr.accounts', ['note' => 'cors_fallback']));
        }, 1200);
      });
  })();
  </script>
</div>
@endsection
