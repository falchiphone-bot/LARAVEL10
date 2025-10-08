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
                <button class="btn btn-primary btn-sm mt-3" id="btn-submit-recarregar" type="button" data-action="recarregar">Recarregar</button>
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
            <a href="{{ route('lancamentos.preview.despesas', array_merge(request()->query(), ['file'=>$arquivo,'refresh'=>1])) }}" id="btn-reprocessar" class="btn btn-warning btn-sm align-self-end mt-3" data-action="reprocessar" title="Reprocessa a planilha ignorando o cache atual">Reprocessar (refresh)</a>
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
                <select id="empresa-global" class="form-select form-select-sm select2-basic" data-cache-key="{{ $cacheKey }}" data-placeholder="Selecione a empresa">
                    <option value=""></option>
                    @foreach($empresasLista as $emp)
                        <option value="{{ $emp->ID }}" {{ (string)($selectedEmpresaId ?? '') === (string)$emp->ID ? 'selected' : '' }}>{{ $emp->Descricao }}</option>
                    @endforeach
                </select>
            </div>
            <div class="small text-muted">
                Após escolher a empresa, selecione a conta para cada linha.
            </div>
            <div>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="toggle-pendentes" disabled>Ocultar linhas classificadas</button>
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
                                    <select class="form-select form-select-sm class-conta select2-conta-ajax" data-row="{{ $i }}" data-selected="{{ $r['_class_conta_id'] ?? '' }}" data-can="1" {{ empty($r['_class_empresa_id']) ? 'disabled' : '' }} data-placeholder="Pesquisar conta"></select>
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
@push('styles')
<style>
    .modal-confirm-msg code{background: #f8f9fa;padding:2px 4px;border-radius:4px;}
</style>
@endpush
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<style>
    .select2-container--bootstrap-5 .select2-selection { min-height: 32px; }
    .select2-selection__rendered { line-height:30px !important; }
    .select2-selection__arrow { height:30px !important; }
    tr.linha-sem-conta {outline:2px solid #dc3545;}
    tr.linha-sem-conta td {background-image:linear-gradient(90deg, rgba(220,53,69,0.08), transparent);} 
    .legend-inline {font-size:.75rem;}
</style>
@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
(function(){
        // Modal de confirmação
        const modalHtml = `
        <div class="modal fade" id="confirmModalPreview" tabindex="-1" aria-labelledby="confirmPreviewLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmPreviewLabel">Confirmar ação</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body modal-confirm-msg">
                        <p id="confirmPreviewMessage" class="mb-2"></p>
                        <ul class="small text-muted ps-3 mb-0">
                             <li>Históricos ajustados e contas selecionadas serão preservados.</li>
                             <li>Use esta ação somente se alterou filtros / precisa refazer parsing.</li>
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary btn-sm" id="confirmPreviewProceed">Prosseguir</button>
                    </div>
                </div>
            </div>
        </div>`;
        if(!document.getElementById('confirmModalPreview')){
                document.body.insertAdjacentHTML('beforeend', modalHtml);
        }
        const confirmModalEl = document.getElementById('confirmModalPreview');
        let bsModal = null; // instancia bootstrap modal
        let pendingAction = null;
        const msgEl = document.getElementById('confirmPreviewMessage');
        const btnProceed = document.getElementById('confirmPreviewProceed');
        function openConfirm(message, proceedCb){
                msgEl.innerHTML = message;
                pendingAction = proceedCb;
                if(!bsModal){ bsModal = new bootstrap.Modal(confirmModalEl); }
                bsModal.show();
        }
        btnProceed?.addEventListener('click', ()=>{
                if(pendingAction){ pendingAction(); }
                bsModal?.hide();
        });

        const btnReprocessar = document.getElementById('btn-reprocessar');
        const btnRecarregar = document.getElementById('btn-submit-recarregar');
        const formFiltros = document.querySelector('form[action="{{ route('lancamentos.preview.despesas') }}"][method="GET"]');
        if(btnReprocessar){
                btnReprocessar.addEventListener('click', function(e){
                        e.preventDefault();
                        const href = this.getAttribute('href');
                        openConfirm('<strong>Reprocessar planilha</strong>: isso relê o arquivo e recria as linhas. Deseja continuar?', ()=>{ window.location.href = href; });
                });
        }
        if(btnRecarregar && formFiltros){
                btnRecarregar.addEventListener('click', function(){
                        openConfirm('<strong>Recarregar pré-visualização</strong>: isso pode alterar o conjunto de linhas exibidas. Continuar?', ()=>{ formFiltros.submit(); });
                });
        }
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
        if(!window.jQuery || !jQuery().select2) return;
        jQuery('select.select2-basic').each(function(){
            const $el = jQuery(this);
            const placeholder = $el.data('placeholder') || ($el.attr('id')==='empresa-global' ? 'Selecione a empresa' : 'Conta');
            if($el.hasClass('select2-hidden-accessible')){ $el.select2('destroy'); }
            $el.select2({ theme:'bootstrap-5', width:'100%', allowClear:true, placeholder });
        });
        jQuery('select.select2-conta-ajax').each(function(){
            const $el = jQuery(this);
            if($el.hasClass('select2-hidden-accessible')){ $el.select2('destroy'); }
            const row = $el.data('row');
            $el.select2({
                theme:'bootstrap-5', width:'100%', allowClear:true,
                placeholder: $el.data('placeholder')||'Pesquisar conta',
                ajax:{
                    delay:250,
                    transport: function (params, success, failure){
                        const empId = empresaGlobalSelect.value || '';
                        if(!empId){ success({results:[]}); return; }
                        const term = params.data.q || '';
                        fetch(`/empresa/${empId}/contas-grau5?q=${encodeURIComponent(term)}`)
                          .then(r=> r.ok? r.json(): {data:{}})
                          .then(j=> {
                              const results = Object.entries(j.data||{}).map(([id,text])=>({id,text}));
                              success({results});
                          }).catch(failure);
                    },
                    processResults: function(data){ return data; }
                }
            });
            // Carregar texto pré-selecionado (se houver) via request isolado
            const selectedId = $el.data('selected');
            if(selectedId){
                const empId = empresaGlobalSelect.value || '';
                if(empId){
                    fetch(`/empresa/${empId}/contas-grau5?q=`) // pega primeira carga e filtra local
                        .then(r=> r.ok? r.json(): {data:{}})
                        .then(j=>{
                            const label = j.data && j.data[selectedId] ? j.data[selectedId] : selectedId;
                            const option = new Option(label, selectedId, true, true);
                            $el.append(option).trigger('change');
                        });
                }
            }
        });
    }
    function ordenarOpcoes(select){
        const opts = Array.from(select.querySelectorAll('option'))
            .filter(o=> o.value !== '');
        opts.sort((a,b)=> a.textContent.localeCompare(b.textContent,'pt-BR',{sensitivity:'base'}));
        const placeholder = select.querySelector('option[value=""]');
        select.innerHTML='';
        if(placeholder){ select.appendChild(placeholder); }
        opts.forEach(o=> select.appendChild(o));
    }
    function marcarLinhasSemConta(){
        document.querySelectorAll('table[data-cache-key] tbody tr').forEach(tr=>{
            const select = tr.querySelector('select.class-conta');
            if(!select || select.disabled || select.dataset.can!=='1'){ tr.classList.remove('linha-sem-conta'); return; }
            if(!select.value){ tr.classList.add('linha-sem-conta'); } else { tr.classList.remove('linha-sem-conta'); }
        });
    }
    async function aplicarEmpresaGlobal(){
        const empId = empresaGlobalSelect.value || '';
        // Feedback visual de carregamento
        empresaGlobalSelect.classList.add('border','border-warning');
        document.querySelectorAll('select.class-conta').forEach(sel=>{
            if(sel.dataset.can === '1'){
                // Sempre limpa seleção ao escolher (ou re-escolher) empresa
                // NÃO limpamos dataset.selected para preservar em reprocess
                sel.innerHTML = ''; sel.disabled = !empId;
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
            // Repovoa somente com opções das contas previamente selecionadas (para manter visual)
            const selecionados = [];
            document.querySelectorAll('select.class-conta').forEach(sel=>{
                const sid = sel.dataset.selected;
                if(sel.dataset.can==='1' && sid){ selecionados.push(sid); }
            });
            if(selecionados.length){
                try{
                    const respSel = await fetch(`/empresa/${empId}/contas-grau5?ids=${selecionados.join(',')}`);
                    if(respSel.ok){
                        const jsonSel = await respSel.json();
                        document.querySelectorAll('select.class-conta').forEach(sel=>{
                            const sid = sel.dataset.selected;
                            if(sel.dataset.can==='1' && sid && jsonSel.data && jsonSel.data[sid]){
                                const opt = document.createElement('option');
                                opt.value = sid; opt.textContent = jsonSel.data[sid]; opt.selected = true;
                                sel.appendChild(opt);
                            }
                        });
                    }
                }catch(e){ console.warn('Falha ao repovoar selecionados', e); }
            }
            // Selects continuam AJAX para novas buscas
            empresaGlobalSelect.setAttribute('data-prev', empId);
            initSelect2();
            marcarLinhasSemConta();
            setTimeout(()=> empresaGlobalSelect.classList.remove('border','border-warning'), 1200);
        }).catch(e=>console.error(e));
    }
    empresaGlobalSelect?.addEventListener('change', aplicarEmpresaGlobal);
    // Inicialização se já havia empresa selecionada
    if(empresaGlobalSelect?.value){
        empresaGlobalSelect.setAttribute('data-prev', empresaGlobalSelect.value);
        aplicarEmpresaGlobal();
    } else {
        initSelect2();
    }
    document.addEventListener('change', function(e){
        const selConta = e.target.closest('select.class-conta');
        if(selConta){
            const row = selConta.dataset.row;
            const contaId = selConta.value || null;
            // Persistimos a seleção no atributo data-selected para reutilizar após refresh/reprocess
            selConta.dataset.selected = contaId ? String(contaId) : '';
            updateClassificacao(row, contaId);
            marcarLinhasSemConta();
            atualizarToggleEstado();
        }
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
    // Se empresa vazia e não inicializou via aplicarEmpresaGlobal
    if(!empresaGlobalSelect?.value){ initSelect2(); }
    marcarLinhasSemConta();
    // Toggle de ocultar/mostrar classificadas
    const btnToggle = document.getElementById('toggle-pendentes');
    function atualizarToggleEstado(){
        if(!btnToggle) return;
        const totalClassificaveis = document.querySelectorAll('select.class-conta[data-can="1"]').length;
        const pendentes = Array.from(document.querySelectorAll('select.class-conta[data-can="1"]')).filter(s=> !s.value).length;
        btnToggle.disabled = totalClassificaveis === 0;
        btnToggle.dataset.pendentes = pendentes;
        if(btnToggle.classList.contains('mostrar-pendentes')){
            btnToggle.textContent = 'Mostrar todas ('+totalClassificaveis+')';
        } else {
            btnToggle.textContent = 'Ocultar linhas classificadas ('+pendentes+' pendentes)';
        }
    }
    function aplicarFiltroClassificadas(){
        const ocultar = !btnToggle.classList.contains('mostrar-pendentes');
        document.querySelectorAll('table[data-cache-key] tbody tr').forEach(tr=>{
            const sel = tr.querySelector('select.class-conta[data-can="1"]');
            if(!sel) return; // não classificável ou sem select
            const isClassificada = !!sel.value;
            if(ocultar){
                if(isClassificada){ tr.style.display='none'; }
                else { tr.style.display=''; }
            } else {
                tr.style.display='';
            }
        });
    }
    btnToggle?.addEventListener('click', ()=>{
        btnToggle.classList.toggle('mostrar-pendentes');
        aplicarFiltroClassificadas();
        atualizarToggleEstado();
    });
    atualizarToggleEstado();
})();
</script>
@endpush
