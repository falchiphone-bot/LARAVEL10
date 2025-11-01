@extends('layouts.bootstrap5')
@section('content')
<div class="container-fluid">
  <h1 class="h5 mb-3">Gerenciador de Excel</h1>
  @if(session('success'))
    <div class="alert alert-success py-2">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger py-2 small">
      <ul class="mb-0">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card mb-3 shadow-sm">
    <div class="card-body">
      <form method="POST" action="{{ route('openai.tools.excel.upload') }}" enctype="multipart/form-data" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-5">
          <label class="form-label small mb-1">Arquivo (xlsx/csv)</label>
          <input type="file" name="file" class="form-control form-control-sm" accept=".xlsx,.csv,.xls" required />
        </div>
        <div class="col-md-3">
          <button class="btn btn-sm btn-primary" type="submit">Carregar</button>
        </div>
        <div class="col-md-4 text-end">
          <a href="{{ route('openai.tools.excel.index') }}" class="btn btn-sm btn-outline-secondary">Limpar</a>
        </div>
      </form>
    </div>
  </div>

  @php
    $headers = $headers ?? [];
    $rows = $rows ?? [];
  @endphp

  @if(!empty($rows))
  <div class="card mb-3 shadow-sm">
    <div class="card-header d-flex flex-wrap gap-2 align-items-center">
      <strong class="me-2">Mapeamento de Colunas</strong>
      <div class="d-flex align-items-center gap-2 small">
        <label class="mb-0">Quantidade</label>
        <select id="mapQty" class="form-select form-select-sm" style="width: 220px;">
          <option value="">(selecionar)</option>
          @foreach($headers as $h)
            <option value="{{ $h }}">{{ $h }}</option>
          @endforeach
        </select>
      </div>
      <div class="d-flex align-items-center gap-2 small">
        <label class="mb-0">Preço (USD)</label>
        <select id="mapUnit" class="form-select form-select-sm" style="width: 220px;">
          <option value="">(selecionar)</option>
          @foreach($headers as $h)
            <option value="{{ $h }}">{{ $h }}</option>
          @endforeach
        </select>
      </div>
      <div class="d-flex align-items-center gap-2 small">
        <label class="mb-0">Meta (USD)</label>
        <select id="mapTarget" class="form-select form-select-sm" style="width: 220px;">
          <option value="">(selecionar)</option>
          @foreach($headers as $h)
            <option value="{{ $h }}">{{ $h }}</option>
          @endforeach
        </select>
      </div>
      <button type="button" id="btnRecalc" class="btn btn-sm btn-outline-primary ms-auto">Recalcular</button>
      <button type="button" id="btnExportCsv" class="btn btn-sm btn-outline-success">Exportar CSV</button>
    </div>
    <div class="table-responsive">
      <table class="table table-sm table-striped mb-0" id="excelTable">
        <thead class="table-light">
          <tr>
            @foreach($headers as $h)
              <th>{{ $h }}</th>
            @endforeach
            <th class="text-end">Total Compra (USD)</th>
            <th class="text-end">Total Meta (USD)</th>
            <th class="text-end">Dif. (USD)</th>
            <th class="text-end">Dif. (%)</th>
          </tr>
        </thead>
        <tbody>
          @foreach($rows as $row)
            <tr>
              @foreach($headers as $h)
                @php $val = data_get($row, $h); @endphp
                <td contenteditable="true" data-col="{{ $h }}">{{ is_scalar($val) ? $val : json_encode($val) }}</td>
              @endforeach
              <td class="text-end col-total-compra">—</td>
              <td class="text-end col-total-meta">—</td>
              <td class="text-end col-diff">—</td>
              <td class="text-end col-diffpct">—</td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <td colspan="{{ count($headers) }}" class="text-end"><strong>Totais</strong></td>
            <td class="text-end" id="sumTotalCompra">—</td>
            <td class="text-end" id="sumTotalMeta">—</td>
            <td class="text-end" id="sumDiff">—</td>
            <td class="text-end" id="sumDiffPct">—</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
  @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  function parseNum(s){
    if (s===undefined||s===null) return NaN;
    if (typeof s === 'number') return s;
    s = String(s).trim().replace(/\./g,'').replace(',','.');
    const n = parseFloat(s);
    return isNaN(n) ? NaN : n;
  }
  function fmt2(n){ return isNaN(n)||n===null ? '—' : new Intl.NumberFormat('pt-BR',{minimumFractionDigits:2, maximumFractionDigits:2}).format(n); }
  function recalc(){
    const tbl = document.getElementById('excelTable');
    if (!tbl) return;
    const mapQty = (document.getElementById('mapQty')?.value||'').toLowerCase();
    const mapUnit = (document.getElementById('mapUnit')?.value||'').toLowerCase();
    const mapTarget = (document.getElementById('mapTarget')?.value||'').toLowerCase();

    let sumCompra = 0, sumMeta = 0, sumDiff = 0;
    let baseForPct = 0; // soma de compras para % total
    tbl.querySelectorAll('tbody tr').forEach(tr => {
      const cells = tr.querySelectorAll('td[data-col]');
      let qty = NaN, unit = NaN, target = NaN;
      cells.forEach(td => {
        const col = (td.getAttribute('data-col')||'').toLowerCase();
        const val = td.textContent || '';
        if (col === mapQty) qty = parseNum(val);
        if (col === mapUnit) unit = parseNum(val);
        if (col === mapTarget) target = parseNum(val);
      });
      const totalCompra = (isFinite(qty)&&isFinite(unit)) ? (qty*unit) : NaN;
      const totalMeta = (isFinite(qty)&&isFinite(target)) ? (qty*target) : NaN;
      const diff = (isFinite(totalCompra)&&isFinite(totalMeta)) ? (totalMeta-totalCompra) : NaN;
      const diffPct = (isFinite(totalCompra) && totalCompra>0 && isFinite(diff)) ? ((diff/totalCompra)*100.0) : NaN;

      const tdCompra = tr.querySelector('.col-total-compra'); if (tdCompra) tdCompra.textContent = fmt2(totalCompra);
      const tdMeta = tr.querySelector('.col-total-meta'); if (tdMeta) tdMeta.textContent = fmt2(totalMeta);
      const tdDiff = tr.querySelector('.col-diff'); if (tdDiff){ tdDiff.textContent = fmt2(diff); tdDiff.className='text-end col-diff ' + (isFinite(diff)? (diff>0?'text-success':(diff<0?'text-danger':'text-secondary')) : 'text-secondary'); }
      const tdDiffPct = tr.querySelector('.col-diffpct'); if (tdDiffPct){ tdDiffPct.textContent = isFinite(diffPct)? fmt2(diffPct)+'%' : '—'; tdDiffPct.className='text-end col-diffpct ' + (isFinite(diffPct)? (diffPct>0?'text-success':(diffPct<0?'text-danger':'text-secondary')):'text-secondary'); }

      if (isFinite(totalCompra)) { sumCompra += totalCompra; baseForPct += totalCompra; }
      if (isFinite(totalMeta)) { sumMeta += totalMeta; }
      if (isFinite(diff)) { sumDiff += diff; }
    });
    const sumDiffPct = (baseForPct>0 && isFinite(sumDiff)) ? ((sumDiff/baseForPct)*100.0) : NaN;
    const el = id => document.getElementById(id);
    el('sumTotalCompra').textContent = fmt2(sumCompra);
    el('sumTotalMeta').textContent = fmt2(sumMeta);
    el('sumDiff').textContent = fmt2(sumDiff);
    el('sumDiffPct').textContent = isFinite(sumDiffPct) ? fmt2(sumDiffPct)+'%' : '—';
  }

  const btn = document.getElementById('btnRecalc'); if (btn) btn.addEventListener('click', recalc);
  document.getElementById('mapQty')?.addEventListener('change', recalc);
  document.getElementById('mapUnit')?.addEventListener('change', recalc);
  document.getElementById('mapTarget')?.addEventListener('change', recalc);
  // Recalcula automaticamente se os cabeçalhos estiverem selecionados
  setTimeout(recalc, 200);

  // Exporta CSV do que está na tabela + colunas calculadas
  const btnCsv = document.getElementById('btnExportCsv');
  if (btnCsv){ btnCsv.addEventListener('click', function(){
    const tbl = document.getElementById('excelTable');
    if (!tbl) return;
    const rows = [];
    // cabeçalho
    const head = Array.from(tbl.querySelectorAll('thead th')).map(th=> th.textContent||'');
    rows.push(head);
    // linhas
    tbl.querySelectorAll('tbody tr').forEach(tr =>{
      const cols = [];
      tr.querySelectorAll('td').forEach(td=> cols.push((td.textContent||'').replace(/\n/g,' ').trim()));
      rows.push(cols);
    });
    let csv = '\ufeff';
    rows.forEach(r => { csv += r.map(v=> '"'+v.replace(/"/g,'""')+'"').join(';') + '\n'; });
    const blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'excel_manager_export_'+ (new Date().toISOString().slice(0,19).replace(/[:T]/g,'-')) + '.csv';
    document.body.appendChild(a); a.click(); a.remove();
  }); }
});
</script>
@endsection
