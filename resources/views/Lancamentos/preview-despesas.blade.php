@extends('layouts.bootstrap5')
@section('content')
<div class="container-fluid py-3">
    <h4 class="mb-3">Preview Despesas (Excel) - {{ $arquivo }}</h4>
    <div class="mb-3 d-flex gap-3 flex-wrap">
        <form method="GET" action="{{ route('lancamentos.preview.despesas') }}" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label mb-1">Arquivo (imports)</label>
                <input type="text" name="file" value="{{ $arquivo }}" class="form-control form-control-sm" placeholder="DESPESAS-08-2025-TEC.xlsx">
            </div>
            <div class="col-auto">
                <label class="form-label mb-1">Limite</label>
                <input type="number" name="limite" value="{{ $limite }}" class="form-control form-control-sm" min="1" max="2000">
            </div>
            <div class="col-auto">
                <label class="form-label mb-1">Upper</label>
                <select name="upper" class="form-select form-select-sm">
                    <option value="0" {{ empty($flagUpper)?'selected':'' }}>Não</option>
                    <option value="1" {{ !empty($flagUpper)?'selected':'' }}>Sim</option>
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label mb-1">Trim múltiplos</label>
                <select name="trim_multi" class="form-select form-select-sm">
                    <option value="0" {{ empty($flagTrimMulti)?'selected':'' }}>Não</option>
                    <option value="1" {{ !empty($flagTrimMulti)?'selected':'' }}>Sim</option>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label mb-1">Substituições simples (find=>replace|...)</label>
                <input type="text" name="subs" value="{{ $subsRaw }}" class="form-control form-control-sm" placeholder="PIX=>PAGAMENTO| SUPERMERCADO=>MERCADO">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label mb-1">Regex ( /expr/flags=>replace|... )</label>
                <input type="text" name="regex" value="{{ $regexRaw }}" class="form-control form-control-sm" placeholder="/\d{2}\/\d{2}\/\d{4}/=>DATA">
            </div>
            <div class="col-auto pt-1">
                <button class="btn btn-primary btn-sm mt-3">Recarregar</button>
            </div>
        </form>
        <form method="POST" enctype="multipart/form-data" action="{{ route('lancamentos.preview.despesas') }}" class="d-flex align-items-end gap-2">
            @csrf
            <div>
                <label class="form-label mb-1">Upload XLSX</label>
                <input type="file" name="arquivo_excel" accept=".xlsx,.xls" class="form-control form-control-sm">
            </div>
            <div class="pb-1">
                <button class="btn btn-success btn-sm mt-3">Enviar</button>
            </div>
        </form>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm align-self-end mt-3">Voltar</a>
        @if($existe && !$erro)
            <a href="{{ route('lancamentos.preview.despesas', array_merge(request()->query(), ['file'=>$arquivo,'refresh'=>1])) }}" class="btn btn-warning btn-sm align-self-end mt-3" title="Reprocessa a planilha ignorando o cache atual">Reprocessar (refresh)</a>
        @endif
    </div>
    @if($erro)
        <div class="alert alert-danger">{{ $erro }}</div>
    @elseif(!$existe)
        <div class="alert alert-warning">Arquivo não localizado. Copie para <code>storage/app/imports</code>.</div>
    @else
        <div class="alert alert-info small mb-2">
            Visualização somente leitura. Ajuste o texto em "Histórico Ajustado" (não salva).<br>
            Atalhos: <code>Ctrl/Cmd+S</code> exporta JSON ajustado.
                <br>Classificação de contas disponível apenas em linhas que contenham Data e Valor.
        </div>
        <div class="mb-2 d-flex flex-wrap align-items-end gap-2">
            <div>
                <label class="form-label mb-1">Empresa (global)</label>
                <select id="empresa-global" class="form-select form-select-sm" data-cache-key="{{ $cacheKey }}">
                    <option value="">-- selecione --</option>
                    @foreach($empresasLista as $emp)
                        <option value="{{ $emp->ID }}" {{ (string)($selectedEmpresaId ?? '') === (string)$emp->ID ? 'selected' : '' }}>{{ $emp->Descricao }}</option>
                    @endforeach
                </select>
            </div>
            <div class="small text-muted">
                Após escolher a empresa, selecione a conta para cada linha.
            </div>
        </div>
        <div class="table-responsive" style="max-height:70vh; overflow:auto;">
            <table class="table table-sm table-striped table-bordered align-middle" data-cache-key="{{ $cacheKey ?? '' }}">
                <thead class="table-light sticky-top">
                    <tr>
                        <th style="position:sticky;left:0;background:#f8f9fa;z-index:2;">#</th>
                        @foreach($headers as $h)
                            <th>{{ $h }}</th>
                        @endforeach
                        <th>Histórico Ajustado</th>
                        <th>Conta</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $i=>$r)
                        @php 
                            $histCol = $r['_hist_original_col'] ?? null; 
                            // Heurística para detectar coluna de data e valor na linha
                            $hasDate = false; $hasValor = false; 
                            foreach($headers as $hDetect){
                                $valCell = $r[$hDetect];
                                if(!$hasDate && is_string($valCell) && preg_match('/\b\d{2}\/\d{2}\/\d{4}\b/',$valCell)) $hasDate = true;
                                if(!$hasDate && $valCell instanceof \DateTimeInterface) $hasDate = true;
                                if(!$hasValor && (is_numeric($valCell) || (is_string($valCell) && preg_match('/\d+[\.,]?\d*/',$valCell)))) $hasValor = true;
                                if($hasDate && $hasValor) break;
                            }
                            $canClass = $hasDate && $hasValor;
                        @endphp
                        <tr>
                            <td style="position:sticky;left:0;background:#fff;z-index:1;">{{ $i+1 }}</td>
                            @foreach($headers as $h)
                                <td class="small">{{ $r[$h] }}</td>
                            @endforeach
                            <td style="min-width:260px;">
                                <input type="text" class="form-control form-control-sm hist-ajustado" value="{{ $r['_hist_ajustado'] }}" data-row="{{ $i }}" data-orig="{{ $histCol }}" placeholder="Ajuste aqui">
                                <div class="form-text text-muted small">Origem: {{ $histCol ?? 'N/D' }}</div>
                            </td>
                            <td style="min-width:240px;">
                                @if($canClass)
                                    <select class="form-select form-select-sm class-conta" data-row="{{ $i }}" data-selected="{{ $r['_class_conta_id'] ?? '' }}" data-can="1" {{ empty($r['_class_empresa_id']) ? 'disabled' : '' }}>
                                        <option value="">-- Conta --</option>
                                    </select>
                                @else
                                    <span class="text-muted small" data-can="0">(sem Data/Valor)</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($headers)+3 }}" class="text-center">Sem dados</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
@push('scripts')
<script>
(function(){
    const table = document.querySelector('table[data-cache-key]');
    const cacheKey = table?.dataset.cacheKey;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    let timers = {};
    function sendUpdate(row, valor){
        if(!cacheKey) return;
        fetch("{{ route('lancamentos.preview.despesas.update') }}",{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
            body: JSON.stringify({cache_key:cacheKey,row:row,valor:valor})
        }).then(r=>r.json()).then(json=>{
            if(!json.ok){ console.warn('Falha update linha', row, json); }
        }).catch(e=> console.error(e));
    }
    function debounceUpdate(row, valor){
        clearTimeout(timers[row]);
        timers[row] = setTimeout(()=> sendUpdate(row, valor), 400);
    }
    document.querySelectorAll('input.hist-ajustado').forEach(inp=>{
        inp.addEventListener('input', ()=>{
            debounceUpdate(inp.dataset.row, inp.value);
            inp.classList.add('border','border-warning');
        });
        inp.addEventListener('blur', ()=>{
            sendUpdate(inp.dataset.row, inp.value);
            setTimeout(()=> inp.classList.remove('border','border-warning'), 1000);
        });
    });
    // --- Classificação (empresa global + conta por linha) ---
    function updateClassificacao(row, contaId){
        if(!cacheKey) return;
        fetch("{{ route('lancamentos.preview.despesas.classificacao') }}", {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
            body: JSON.stringify({cache_key: cacheKey, row: row, conta_id: contaId || null})
        }).then(r=>r.json()).then(j=>{ if(!j.ok){ console.warn('Falha class linha', row, j); }}).catch(e=>console.error(e));
    }
    async function carregarContas(empresaId){
        if(!empresaId) return {};
        const url = "{{ url('/empresa') }}/"+empresaId+"/contas-grau5";
        try{
            const r = await fetch(url);
            if(!r.ok) return {};
            const j = await r.json();
            return j.data || {};
        }catch(e){ console.error(e); return {}; }
    }
    async function preencherContasSelect(selectConta, empresaId){
        const contas = await carregarContas(empresaId);
        const selected = selectConta.dataset.selected || '';
        selectConta.innerHTML = '<option value="">-- Conta --</option>';
        Object.entries(contas).forEach(([id, desc])=>{
            const opt = document.createElement('option');
            opt.value = id; opt.textContent = desc;
            if(selected && selected === String(id)) opt.selected = true;
            selectConta.appendChild(opt);
        });
        if(Object.keys(contas).length){ selectConta.disabled = false; } else { selectConta.disabled = true; }
    }
    const empresaGlobalSelect = document.getElementById('empresa-global');
    function initSelect2(){
        if(window.jQuery && jQuery().select2){
            jQuery('select.class-conta').not('.select2-hidden-accessible').each(function(){
                jQuery(this).select2({width:'resolve', dropdownAutoWidth:true});
            });
        }
    }
    async function aplicarEmpresaGlobal(){
        const empId = empresaGlobalSelect.value || '';
        document.querySelectorAll('select.class-conta').forEach(sel=>{
            if(sel.dataset.can === '1'){
                sel.innerHTML = '<option value="">-- Conta --</option>'; sel.disabled = true; sel.dataset.selected='';
            }
        });
        if(!empId){ return; }
        // salva empresa global no cache
        fetch("{{ route('lancamentos.preview.despesas.empresa') }}", {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
            body: JSON.stringify({cache_key: cacheKey, empresa_id: empId})
        }).then(r=>r.json()).then(async j=>{
            if(!j.ok){ console.warn('Falha set empresa', j); return; }
            // Carrega contas e preenche cada linha
            const contas = await carregarContas(empId);
            document.querySelectorAll('select.class-conta').forEach(sel=>{
                if(sel.dataset.can !== '1') return;
                const selected = sel.dataset.selected || '';
                sel.innerHTML = '<option value="">-- Conta --</option>';
                Object.entries(contas).forEach(([id, desc])=>{
                    const opt = document.createElement('option'); opt.value=id; opt.textContent=desc; if(selected===String(id)) opt.selected=true; sel.appendChild(opt);
                });
                sel.disabled = false;
            });
            initSelect2();
        }).catch(e=>console.error(e));
    }
    empresaGlobalSelect?.addEventListener('change', aplicarEmpresaGlobal);
    // Inicialização se já havia empresa selecionada
    if(empresaGlobalSelect?.value){ aplicarEmpresaGlobal(); }
    document.querySelectorAll('select.class-conta').forEach(selConta=>{
        selConta.addEventListener('change', ()=>{
            const row = selConta.dataset.row;
            const contaId = selConta.value || null;
            updateClassificacao(row, contaId);
        });
    });
    // Export JSON (Ctrl/Cmd+S)
    document.addEventListener('keydown', function(e){
        if(e.key==='s' && (e.metaKey||e.ctrlKey)){
            e.preventDefault();
            const linhas=[];
            document.querySelectorAll('input.hist-ajustado').forEach(inp=>{
                 linhas.push({linha: inp.dataset.row, origem_coluna: inp.dataset.orig, historico_ajustado: inp.value});
            });
            const blob = new Blob([JSON.stringify(linhas,null,2)],{type:'application/json'});
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url; a.download = 'historicos-ajustados.json'; a.click();
            URL.revokeObjectURL(url);
        }
    });
    // Inicializa select2 caso empresa já setada
    initSelect2();
})();
</script>
@endpush
