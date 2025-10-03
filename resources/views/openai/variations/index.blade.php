@extends('layouts.bootstrap5')
@section('content')
<div class="container-fluid">
  <h1 class="h4 mb-3">Variações Mensais Salvas</h1>
  <form method="get" class="row g-2 mb-3">
    <div class="col-auto">
      <label class="form-label mb-0 small">Ano</label>
      <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">Todos</option>
        @foreach($years as $y)
          <option value="{{ $y }}" @selected((string)$y === (string)$year)>{{ $y }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label mb-0 small">Mês</label>
      <select name="month" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">Todos</option>
        @for($m=1;$m<=12;$m++)
          <option value="{{ $m }}" @selected((int)($month ?? 0) === $m)>{{ str_pad($m,2,'0',STR_PAD_LEFT) }}</option>
        @endfor
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label mb-0 small">Código</label>
      <input type="text" name="code" value="{{ $code }}" class="form-control form-control-sm" placeholder="TSLA" />
    </div>
    <div class="col-auto">
      <label class="form-label mb-0 small">Sinal</label>
      <select name="polarity" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="" @selected(($polarity ?? '')==='')>Todos</option>
        <option value="positive" @selected(($polarity ?? '')==='positive')>Positivos</option>
        <option value="negative" @selected(($polarity ?? '')==='negative')>Negativos</option>
      </select>
    </div>
    <div class="col-auto align-self-end">
      <button class="btn btn-sm btn-primary">Filtrar</button>
    </div>
    <div class="col-auto align-self-end">
      @php
        $quickBase = array_filter([
          'year' => request('year') ?: null,
          'month' => request('month') ?: null,
          'code' => $code ?: null,
          'sort' => ($sort ?? 'year_desc') !== 'year_desc' ? $sort : null,
        ]);
      @endphp
      <div class="btn-group btn-group-sm" role="group" aria-label="Atalhos de sinal">
        <a href="{{ route('openai.variations.index', $quickBase) }}"
           class="btn btn-outline-secondary {{ (($polarity ?? '')==='' ) ? 'active' : '' }}"
           title="Mostrar todos (positivos e negativos)">Todos</a>
        <a href="{{ route('openai.variations.index', array_merge($quickBase, ['polarity'=>'positive'])) }}"
           class="btn btn-outline-success {{ (($polarity ?? '')==='positive') ? 'active' : '' }}"
           title="Mostrar apenas variações positivas">Somente positivos</a>
        <a href="{{ route('openai.variations.index', array_merge($quickBase, ['polarity'=>'negative'])) }}"
           class="btn btn-outline-danger {{ (($polarity ?? '')==='negative') ? 'active' : '' }}"
           title="Mostrar apenas variações negativas">Somente negativos</a>
      </div>
    </div>
    <div class="col-12"></div>
    <div class="col-auto align-self-end">
      @php
        $monthQuickBase = array_filter([
          'year' => request('year') ?: null,
          'code' => $code ?: null,
          'polarity'=> ($polarity ?? null) ?: null,
          'sort' => ($sort ?? 'year_desc') !== 'year_desc' ? $sort : null,
        ]);
        $curMonth = (int) (request('month') ?: 0);
      @endphp
      <div class="btn-group btn-group-sm flex-wrap" role="group" aria-label="Atalhos de mês">
        <a href="{{ route('openai.variations.index', $monthQuickBase) }}" class="btn btn-outline-secondary {{ $curMonth===0 ? 'active' : '' }}" title="Limpar filtro de mês">Limpar mês</a>
        @for($m=1;$m<=12;$m++)
          <a href="{{ route('openai.variations.index', array_merge($monthQuickBase, ['month'=>$m])) }}"
             class="btn btn-outline-primary {{ $curMonth===$m ? 'active' : '' }}"
             title="Filtrar por mês {{ str_pad($m,2,'0',STR_PAD_LEFT) }}">{{ str_pad($m,2,'0',STR_PAD_LEFT) }}</a>
        @endfor
      </div>
    </div>
  </form>

  @php
    $exportParams = array_filter([
      'year'=>request('year')?:null,
      'month'=>request('month')?:null,
      'code'=>$code?:null,
      'polarity'=> ($polarity ?? null) ?: null,
      'sort' => ($sort ?? 'year_desc') !== 'year_desc' ? $sort : null,
    ]);
  @endphp
  <div class="mb-2 d-flex gap-2">
    <a href="{{ route('openai.variations.exportCsv', $exportParams) }}" class="btn btn-sm btn-outline-secondary" title="Exportar visão atual em CSV">Exportar CSV</a>
    <a href="{{ route('openai.variations.exportXlsx', $exportParams) }}" class="btn btn-sm btn-outline-success" title="Exportar visão atual em XLSX">Exportar XLSX</a>
    <div class="vr mx-2 d-none d-md-block"></div>
    <button type="button" id="btn-var-batch-flags" class="btn btn-sm btn-outline-warning" title="Aplicar COMPRAR/NÃO COMPRAR por código conforme sinal da variação (usa a linha mais recente por código)">Aplicar flags (variação)</button>
  </div>

  <div class="table-responsive">
    <table class="table table-sm table-striped align-middle">
      <thead>
        <tr>
          <th>ID</th>
          @php
            // Parâmetros base preservados para os links de ordenação
            $baseParams = array_filter([
              'year'=>request('year')?:null,
              'month'=>request('month')?:null,
              'code'=>$code?:null,
              'polarity'=> ($polarity ?? null) ?: null,
            ]);
          @endphp
          @php
            $isCodeAsc = ($sort ?? '') === 'code_asc';
            $isCodeDesc = ($sort ?? '') === 'code_desc';
            $codeNext = $isCodeAsc ? 'code_desc' : ($isCodeDesc ? 'year_desc' : 'code_asc');
            $codeIcon = $isCodeAsc ? '↑' : ($isCodeDesc ? '↓' : '↕');
          @endphp
          <th>
            <a class="text-decoration-none" href="{{ route('openai.variations.index', array_merge($baseParams, ['sort'=>$codeNext])) }}" title="Ordenar / alternar ordenação por código">
              Código {{ $codeIcon }}
            </a>
          </th>
          @php
            $isYearAsc = ($sort ?? '') === 'year_asc';
            $isYearDesc = ($sort ?? '') === 'year_desc';
            $yearNext = $isYearAsc ? 'year_desc' : ($isYearDesc ? 'month_desc' : 'year_asc');
            // Nota: ciclo diferente pode confundir; manter padrão 3 estados como demais: asc->desc->padrão(year_desc). Mas year_desc é também o padrão, então: asc->desc->asc? Melhor replicar padrão: (none)->asc->desc->none. Como default já é year_desc, faremos: if default e user clica: year_asc.
            // Ajuste: se default (year_desc) e não explicitamente setado, mostrar ícone ↕ sem link extra.
            $yearIcon = $isYearAsc ? '↑' : ($isYearDesc ? '↓' : '↕');
            if($isYearDesc && request('sort') !== 'year_desc'){ /* year_desc vindo explicitamente */ }
            $yearNext = $isYearAsc ? 'year_desc' : ($isYearDesc ? 'year_asc' : 'year_asc');

            $isMonthAsc = ($sort ?? '') === 'month_asc';
            $isMonthDesc = ($sort ?? '') === 'month_desc';
            $monthNext = $isMonthAsc ? 'month_desc' : ($isMonthDesc ? 'year_desc' : 'month_asc');
            $monthIcon = $isMonthAsc ? '↑' : ($isMonthDesc ? '↓' : '↕');
          @endphp
          <th>
            <a class="text-decoration-none" href="{{ route('openai.variations.index', array_merge($baseParams, ['sort'=>$yearNext])) }}" title="Ordenar / alternar por ano">
              Ano {{ $yearIcon }}
            </a>
          </th>
          <th>
            <a class="text-decoration-none" href="{{ route('openai.variations.index', array_merge($baseParams, ['sort'=>$monthNext])) }}" title="Ordenar / alternar por mês">
              Mês {{ $monthIcon }}
            </a>
          </th>
          @php
            $isVarAsc = ($sort ?? '') === 'variation_asc';
            $isVarDesc = ($sort ?? '') === 'variation_desc';
            $nextSort = $isVarAsc ? 'variation_desc' : ($isVarDesc ? 'year_desc' : 'variation_asc');
            $icon = $isVarAsc ? '↑' : ($isVarDesc ? '↓' : '↕');
          @endphp
          <th>
            <a class="text-decoration-none" href="{{ route('openai.variations.index', array_merge($baseParams, ['sort'=>$nextSort])) }}" title="Ordenar / alternar ordenação pela variação">
              Variação (%) {{ $icon }}
            </a>
          </th>
          @php
            $isCreatedAsc = ($sort ?? '') === 'created_asc';
            $isCreatedDesc = ($sort ?? '') === 'created_desc';
            $createdNext = $isCreatedAsc ? 'created_desc' : ($isCreatedDesc ? 'year_desc' : 'created_asc');
            $createdIcon = $isCreatedAsc ? '↑' : ($isCreatedDesc ? '↓' : '↕');
            $isUpdatedAsc = ($sort ?? '') === 'updated_asc';
            $isUpdatedDesc = ($sort ?? '') === 'updated_desc';
            $updatedNext = $isUpdatedAsc ? 'updated_desc' : ($isUpdatedDesc ? 'year_desc' : 'updated_asc');
            $updatedIcon = $isUpdatedAsc ? '↑' : ($isUpdatedDesc ? '↓' : '↕');
          @endphp
          <th>
            <a class="text-decoration-none" href="{{ route('openai.variations.index', array_merge($baseParams, ['sort'=>$createdNext])) }}" title="Ordenar / alternar por data de criação">
              Criado {{ $createdIcon }}
            </a>
          </th>
          <th>
            <a class="text-decoration-none" href="{{ route('openai.variations.index', array_merge($baseParams, ['sort'=>$updatedNext])) }}" title="Ordenar / alternar por data de atualização">
              Atualizado {{ $updatedIcon }}
            </a>
          </th>
          <th title="Flag por usuário: COMPRAR ou NÃO COMPRAR">Flag</th>
        </tr>
      </thead>
      <tbody>
        @forelse($variations as $v)
          <tr>
            <td>{{ $v->id }}</td>
            <td>{{ $v->asset_code }}</td>
            <td>{{ $v->year }}</td>
            <td>{{ str_pad($v->month,2,'0',STR_PAD_LEFT) }}</td>
            <td>{{ number_format($v->variation, 4, ',', '.') }}</td>
            <td>{{ $v->created_at?->format('Y-m-d H:i') }}</td>
            <td>{{ $v->updated_at?->format('Y-m-d H:i') }}</td>
            <td class="text-center">
              @php $flagCode = strtoupper(trim($v->asset_code ?? '')); @endphp
              @if($flagCode !== '')
                <div class="d-inline-flex align-items-center gap-2">
                  <span class="badge bg-secondary" data-flag-code="{{ $flagCode }}">—</span>
                  <button type="button" class="btn btn-xs btn-outline-secondary" data-flag-toggle data-flag-code="{{ $flagCode }}" title="Alternar COMPRAR/NÃO COMPRAR">Alternar</button>
                </div>
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="text-center text-muted">Nenhuma variação encontrada.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div>
    {{ $variations->links() }}
  </div>
</div>
@endsection

@push('scripts')
<script>
  (function(){
    const btn = document.getElementById('btn-var-batch-flags');
    if (!btn) return;
    btn.addEventListener('click', function(){
      if (!confirm('Aplicar COMPRAR/NÃO COMPRAR para os códigos exibidos, conforme sinal da variação mais recente?')) return;
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = @json(route('openai.variations.batchFlags'));
      const tok = document.querySelector('meta[name="csrf-token"]');
      if (tok) {
        const inp = document.createElement('input'); inp.type = 'hidden'; inp.name = '_token'; inp.value = tok.getAttribute('content'); form.appendChild(inp);
      }
      // Copia filtros atuais
      try{
        const url = new URL(window.location.href);
        url.searchParams.forEach((v,k)=>{
          if (v !== null && v !== ''){
            const inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = k; inp.value = v;
            form.appendChild(inp);
          }
        });
      }catch(_e){}
      document.body.appendChild(form);
      form.submit();
    });
  })();
</script>
@endpush

@push('scripts')
<script>
  (function(){
    // Hidrata badges de flag por código
    const NO_BUY_GET = @json(route('openai.assets.noBuy.get'));
    (async function(){
      try{
        const els = Array.from(document.querySelectorAll('[data-flag-code]'));
        const codes = Array.from(new Set(els.map(e => e.getAttribute('data-flag-code')).filter(Boolean)));
        for (const code of codes){
          try{
            const resp = await fetch(`${NO_BUY_GET}?code=${encodeURIComponent(code)}`, { headers: { 'Accept':'application/json' } });
            const data = await resp.json().catch(()=>null);
            const noBuy = !!(data && data.no_buy);
            els.filter(e => e.getAttribute('data-flag-code')===code).forEach(e => {
              if (e.classList.contains('badge')){
                e.className = 'badge ' + (noBuy ? 'bg-danger' : 'bg-success');
                e.textContent = noBuy ? 'NÃO COMPRAR' : 'COMPRAR';
                e.dataset.noBuy = noBuy ? '1' : '0';
              }
            });
          }catch(_e){/* noop */}
        }
      }catch(_e){/* noop */}
    })();

    // Alternar flag via POST
    document.addEventListener('click', async function(ev){
      const btn = ev.target.closest('[data-flag-toggle]');
      if (!btn) return;
      const code = btn.getAttribute('data-flag-code') || '';
      if (!code) return;
      const badge = btn.parentElement?.querySelector('.badge[data-flag-code="' + code + '"]');
      const current = badge ? (badge.dataset.noBuy === '1') : false;
      const next = !current; // true => NÃO COMPRAR; false => COMPRAR
      const prevHtml = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
      try{
        const url = @json(route('openai.assets.noBuy.toggle'));
        const tok = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const resp = await fetch(url, {
          method: 'POST',
          headers: { 'Accept':'application/json', 'Content-Type':'application/json', 'X-CSRF-TOKEN': tok },
          body: JSON.stringify({ code, no_buy: next })
        });
        const data = await resp.json().catch(()=>null);
        if (!resp.ok || !data || data.ok !== true) {
          throw new Error((data && (data.message||data.error)) || 'Falha ao salvar flag');
        }
        if (badge){
          badge.className = 'badge ' + (next ? 'bg-danger' : 'bg-success');
          badge.textContent = next ? 'NÃO COMPRAR' : 'COMPRAR';
          badge.dataset.noBuy = next ? '1' : '0';
        }
      }catch(err){
        alert('Erro ao salvar flag: ' + String(err && err.message ? err.message : err));
      }finally{
        btn.disabled = false;
        btn.innerHTML = prevHtml;
      }
    });
  })();
</script>
@endpush
