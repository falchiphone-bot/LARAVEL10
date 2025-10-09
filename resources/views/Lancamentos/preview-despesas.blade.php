@extends('layouts.bootstrap5')
@section('content')
<div class="container-fluid py-3" id="preview-despesas-root" data-empresa-id-from-file="{{ $empresaIdFromFile ?? '' }}" data-conta-credito-id-from-file="{{ $contaCreditoIdFromFile ?? '' }}">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
        <h4 class="mb-0">Preview Despesas (Excel) - {{ $arquivo }}</h4>
        <div class="d-flex align-items-center gap-2">
            <button type="button" id="btn-toggle-layout" class="btn btn-outline-dark btn-sm" title="Alterna exibição do cabeçalho para ganhar espaço">Modo Compacto</button>
        </div>
    </div>
    <div class="mb-3 d-flex gap-3 flex-wrap">
    <form method="GET" action="{{ route('lancamentos.preview.despesas') }}" class="row g-2 align-items-end" id="form-filtros">
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
                <form method="POST" enctype="multipart/form-data" action="{{ route('lancamentos.preview.despesas') }}" class="d-flex align-items-end gap-2" id="form-upload-xlsx">
                        @csrf
                        <div>
                                <label class="form-label mb-1">Upload XLSX</label>
                                <input type="file" name="arquivo_excel" accept=".xlsx,.xls" class="form-control form-control-sm">
                        </div>
                        <div class="pb-1">
                                <button class="btn btn-success btn-sm mt-3">Enviar</button>
                        </div>
                </form>
                @push('scripts')
                                <script>
                                // Seleciona automaticamente empresa e conta crédito após upload XLSX
                                document.addEventListener('DOMContentLoaded', function(){
                                    const formUpload = document.getElementById('form-upload-xlsx');
                                    if(formUpload){
                                        formUpload.addEventListener('submit', function(){
                                            localStorage.setItem('preview_despesas_auto_select', '1');
                                        });
                                    }
                                    function autoSelectFromDatasetIfAvailable(){
                                        var root = document.getElementById('preview-despesas-root');
                                        if(!root) return;
                                        // Empresa
                                        var empresaSel = document.getElementById('empresa-global');
                                        var empresaIdFromFile = root.dataset.empresaIdFromFile || '';
                                        if(empresaSel && !empresaSel.value){
                                            if(empresaIdFromFile){
                                                // Garante que o select esteja habilitado (pode ter sido travado previamente)
                                                empresaSel.disabled = false;
                                                empresaSel.value = String(empresaIdFromFile);
                                                if(window.jQuery){ jQuery(empresaSel).trigger('change.select2'); }
                                                else empresaSel.dispatchEvent(new Event('change', {bubbles:true}));
                                                var btnLockEmpresa = document.getElementById('btn-lock-empresa');
                                                if(btnLockEmpresa){
                                                    btnLockEmpresa.dataset.locked = '0';
                                                    btnLockEmpresa.disabled = false;
                                                    btnLockEmpresa.classList.add('btn-outline-danger');
                                                    btnLockEmpresa.classList.remove('btn-danger');
                                                    btnLockEmpresa.textContent = 'Travar Empresa';
                                                }
                                            } else if(empresaSel.options.length===2){
                                                empresaSel.disabled = false;
                                                empresaSel.selectedIndex = 1;
                                                if(window.jQuery){ jQuery(empresaSel).trigger('change.select2'); }
                                                else empresaSel.dispatchEvent(new Event('change', {bubbles:true}));
                                            }
                                        }
                                        // Conta Crédito
                                        var contaSel = document.getElementById('conta-credito-global');
                                        var contaCreditoIdFromFile = root.dataset.contaCreditoIdFromFile || '';
                                        if(contaSel && !contaSel.value && contaCreditoIdFromFile){
                                            // Garante que o select esteja habilitado
                                            contaSel.disabled = false;
                                            var opt = contaSel.querySelector('option[value="'+contaCreditoIdFromFile+'"]');
                                            if(!opt){
                                                opt = document.createElement('option');
                                                opt.value = contaCreditoIdFromFile;
                                                opt.textContent = contaCreditoIdFromFile;
                                                contaSel.appendChild(opt);
                                            }
                                            contaSel.value = contaCreditoIdFromFile;
                                            if(window.jQuery){ jQuery(contaSel).trigger('change.select2'); }
                                            else contaSel.dispatchEvent(new Event('change', {bubbles:true}));
                                            var btnLockCredito = document.getElementById('btn-lock-conta-credito');
                                            if(btnLockCredito){
                                                btnLockCredito.dataset.locked = '0';
                                                btnLockCredito.disabled = false;
                                                btnLockCredito.classList.add('btn-outline-primary');
                                                btnLockCredito.classList.remove('btn-primary');
                                                btnLockCredito.textContent = 'Travar Crédito';
                                            }
                                        }
                                    }
                                    // Após reload, se flag setada, tenta selecionar automaticamente
                                    if(localStorage.getItem('preview_despesas_auto_select')==='1'){
                                        localStorage.removeItem('preview_despesas_auto_select');
                                        setTimeout(autoSelectFromDatasetIfAvailable, 400);
                                    } else {
                                        // Mesmo sem flag (por exemplo, Importar Export), tenta aplicar dataset
                                        setTimeout(autoSelectFromDatasetIfAvailable, 400);
                                    }
                                });
                                </script>
                @endpush
        <form method="POST" enctype="multipart/form-data" action="{{ route('lancamentos.preview.despesas.importExported') }}" class="d-flex align-items-end gap-2">
            @csrf
            <div>
                <label class="form-label mb-1">Importar Export</label>
                <input type="file" name="arquivo_exportado" accept=".xlsx,.xls" class="form-control form-control-sm" required>
            </div>
            <div class="pb-1">
                <button class="btn btn-outline-success btn-sm mt-3" title="Importa arquivo gerado pelo botão Exportar Excel">Importar</button>
            </div>
        </form>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm align-self-end mt-3">Voltar</a>
                @if($existe && !$erro)
                        <a href="{{ route('lancamentos.preview.despesas', array_merge(request()->query(), ['file'=>$arquivo,'refresh'=>1])) }}" id="btn-reprocessar" class="btn btn-warning btn-sm align-self-end mt-3" data-action="reprocessar" title="Reprocessa a planilha ignorando o cache atual">Reprocessar (refresh)</a>
                        <button type="button" id="btn-apply-autoclass" class="btn btn-outline-primary btn-sm align-self-end mt-3" title="Executa as regras de auto-classificação agora nas linhas pendentes">Aplicar Regras Agora</button>
                        <button type="button" id="btn-snapshot-cache" class="btn btn-info btn-sm align-self-end mt-3" title="Força salvar no cache as contas e históricos ajustados visíveis" data-action="snapshot">Salvar Cache</button>
                        <button type="button" id="btn-export-xlsx" class="btn btn-success btn-sm align-self-end mt-3" title="Exporta a tabela atual (com classificações) para Excel">Exportar Excel</button>
                        <button type="button" id="btn-efetuar-lancamento" class="btn btn-outline-success btn-sm align-self-end mt-3">Efetuar lançamento contábil</button>
                        <!-- Modal de confirmação lançamento contábil -->
                        <div class="modal fade" id="modalEfetuarLancamento" tabindex="-1" aria-labelledby="modalEfetuarLancamentoLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalEfetuarLancamentoLabel">Efetuar lançamento contábil</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                    </div>
                                    <div class="modal-body">
                                        Deseja realmente seguir com os lançamentos contábeis?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Não</button>
                                        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Sim</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal de confirmação final (validação dos lançamentos) -->
                        <div class="modal fade" id="modalLancamentoPronto" tabindex="-1" aria-labelledby="modalLancamentoProntoLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLancamentoProntoLabel">Validação dos Lançamentos</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                    </div>
                                    <div class="modal-body" id="modalLancamentoProntoMsg"></div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Não</button>
                                        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Sim</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                @endif
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    // Debug mode: enable via ?debug_preview=1 or localStorage('preview_despesas_debug') or Alt+D
    var __previewDebug = (localStorage.getItem('preview_despesas_debug')==='1') || (new URLSearchParams(location.search).get('debug_preview')==='1');
    window.__previewDebug = __previewDebug;
    document.addEventListener('keydown', function(e){ if(e.altKey && e.key && e.key.toLowerCase()==='d'){ __previewDebug = !__previewDebug; window.__previewDebug = __previewDebug; localStorage.setItem('preview_despesas_debug', __previewDebug?'1':'0'); console.info('Preview despesas debug:', __previewDebug); } });
    var btnEfetuar = document.getElementById('btn-efetuar-lancamento');
    var modalEfetuar = document.getElementById('modalEfetuarLancamento');
    var modalPronto = document.getElementById('modalLancamentoPronto');
    var msgPronto = document.getElementById('modalLancamentoProntoMsg');
    if(btnEfetuar){
        btnEfetuar.addEventListener('click', function(){
            if(window.bootstrap && modalEfetuar){
                var bsModal = bootstrap.Modal.getOrCreateInstance(modalEfetuar);
                bsModal.show();
            }
        });
    }
    // Ao clicar SIM no modal de confirmação, valida as linhas
    if(modalEfetuar){
        modalEfetuar.addEventListener('shown.bs.modal', function(){
            var btnSim = modalEfetuar.querySelector('.btn-success');
            if(btnSim){
                btnSim.onclick = function(){
                    // Evita foco permanecer em elemento dentro de modal que será oculto
                    try { if(document.activeElement){ document.activeElement.blur(); } } catch(e){}
                    var bsEfetuar = bootstrap.Modal.getInstance(modalEfetuar) || bootstrap.Modal.getOrCreateInstance(modalEfetuar);
                    // Fecha o modal de confirmação antes de validar/abrir o próximo
                    if(bsEfetuar){ bsEfetuar.hide(); }
                    // Validação das linhas da tabela
                    var ok = true;
                    var erros = [];
                    var linhas = document.querySelectorAll('table[data-cache-key] tbody tr');
                    // Descobre o índice da coluna número pela header '#'
                    var ths = document.querySelectorAll('table[data-cache-key] thead th');
                    var idxNum = 0;
                    ths.forEach(function(th, idx){
                        if(th.textContent.trim() === '#') idxNum = idx;
                    });
                    // Descobre os índices relevantes no header
                    var idxValor = -1;
                    var idxContaDebId = -1;
                    var idxContaDebLabel = -1;
                    var idxContaCredGlobalId = -1;
                    var idxData = -1;
                    // Passo 1: match exato 'DATA'
                    ths.forEach(function(th, idx){
                        var nome = th.textContent.trim().toUpperCase();
                        if(nome === 'DATA') idxData = idx;
                    });
                    // Passo 2: fallback: contém 'DATA' se não achou exato
                    if(idxData < 0){
                        ths.forEach(function(th, idx){
                            var nome = th.textContent.trim().toUpperCase();
                            if(nome.includes('DATA')) idxData = idx;
                        });
                    }
                    ths.forEach(function(th, idx){
                        var nome = th.textContent.trim().toUpperCase();
                        if(nome.includes('VALOR')) idxValor = idx;
                        var hasDeb = (nome.includes('DEBIT') || nome.includes('DEBITO') || nome.includes('DÉBITO'));
                        var hasCred = (nome.includes('CREDIT') || nome.includes('CREDITO') || nome.includes('CRÉDITO'));
                        if(nome.includes('CONTA') && hasDeb && nome.includes('ID')) idxContaDebId = idx;
                        if(nome.includes('CONTA') && hasDeb && nome.includes('LABEL')) idxContaDebLabel = idx;
                        if(nome.includes('CONTA') && hasCred && nome.includes('GLOBAL') && nome.includes('ID')) idxContaCredGlobalId = idx;
                    });

                    // Utilitários de data
                    function isValidYMD(y,m,d){
                        y = parseInt(y,10); m = parseInt(m,10); d = parseInt(d,10);
                        if(!y||!m||!d) return false;
                        if(m<1||m>12||d<1||d>31) return false;
                        var dt = new Date(y, m-1, d);
                        return dt.getFullYear()===y && (dt.getMonth()+1)===m && dt.getDate()===d;
                    }
                    function pad2(n){ n = String(n); return n.length===1 ? '0'+n : n; }
                    function fmtBR(y,m,d){ return pad2(d)+'/'+pad2(m)+'/'+String(y); }
                    function fromExcelSerial(serial){
                        var base = new Date(1899,11,30); // Excel bug 1900, usa 1899-12-30
                        var ms = Math.round(parseFloat(serial))*86400000;
                        var dt = new Date(base.getTime()+ms);
                        return fmtBR(dt.getFullYear(), dt.getMonth()+1, dt.getDate());
                    }
                    function parseDataFlex(raw){
                        if(raw==null) return null;
                        var v = String(raw).trim();
                        if(v==='') return null;
                        // Remove componente de hora (" 12:34", "T12:34:56Z", etc.)
                        v = v.replace(/[T ]\d{1,2}:\d{2}(?::\d{2})?(?:\.\d+)?(?:Z|[+-]\d{2}:?\d{2})?$/, '');
                        // Excel serial puro
                        if(/^\d{2,6}$/.test(v)){
                            var n = parseInt(v,10);
                            if(n>59 && n<60000){ try { return fromExcelSerial(n); } catch(e){} }
                        }
                        // Normaliza separadores
                        var vSep = v.replace(/[\.\-]/g,'/').replace(/\s+/g,'/');
                        // yyyy/mm/dd
                        var mYMD = vSep.match(/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/);
                        if(mYMD){
                            var y=parseInt(mYMD[1],10), m=parseInt(mYMD[2],10), d=parseInt(mYMD[3],10);
                            if(isValidYMD(y,m,d)) return fmtBR(y,m,d);
                        }
                        // dd/mm/yyyy ou d/m/yy
                        var mDMY = vSep.match(/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})$/);
                        if(mDMY){
                            var d2=parseInt(mDMY[1],10), m2=parseInt(mDMY[2],10), y2=mDMY[3];
                            if(y2.length===2){ y2 = '20'+y2; }
                            y2=parseInt(y2,10);
                            if(isValidYMD(y2,m2,d2)) return fmtBR(y2,m2,d2);
                        }
                        // Contíguo: yyyymmdd
                        var mContYMD = v.match(/^(\d{4})(\d{2})(\d{2})$/);
                        if(mContYMD){
                            var y3=parseInt(mContYMD[1],10), m3=parseInt(mContYMD[2],10), d3=parseInt(mContYMD[3],10);
                            if(isValidYMD(y3,m3,d3)) return fmtBR(y3,m3,d3);
                        }
                        // Contíguo: ddmmyyyy
                        var mContDMY = v.match(/^(\d{2})(\d{2})(\d{4})$/);
                        if(mContDMY){
                            var d4=parseInt(mContDMY[1],10), m4=parseInt(mContDMY[2],10), y4=parseInt(mContDMY[3],10);
                            if(isValidYMD(y4,m4,d4)) return fmtBR(y4,m4,d4);
                        }
                        return null;
                    }
                    for(var i=0;i<linhas.length;i++){
                        var tr = linhas[i];
                        var tds = tr.querySelectorAll('td');
                        // Remove destaque de erro de todas as linhas antes de validar
                        tr.classList.remove('table-danger', 'linha-erro-validacao');
                        // Só valida se a primeira coluna (número da linha) for >= 4
                        var linhaNum = tds[idxNum] ? tds[idxNum].textContent.trim() : '';
                        if(!tds[idxNum] || isNaN(linhaNum) || parseInt(linhaNum,10) < 4) continue;
                        // DATA: busca e valida formato (dd/mm/yyyy), tenta ajustar se possível
                        var temData = false;
                        var dataInvalida = false;
                        var tds = tr.querySelectorAll('td');
                        if(idxData >= 0 && tds[idxData]){
                            var tdDataCell = tds[idxData];
                            var inputData = tdDataCell.querySelector('input');
                            var valorData = inputData ? (inputData.value||'').trim() : tdDataCell.textContent.trim();
                            var normal = parseDataFlex(valorData);
                            if(normal){
                                if(inputData){
                                    tdDataCell.textContent = normal;
                                    // dispara snapshot para persistir a alteração mesmo sem blur
                                    var btnSnapshotTmp = document.getElementById('btn-snapshot-cache');
                                    if(btnSnapshotTmp) btnSnapshotTmp.click();
                                } else if(valorData !== normal){
                                    tdDataCell.textContent = normal; valorData = normal;
                                }
                                temData = true;
                            } else {
                                // Só marca inválida se houve algum conteúdo
                                dataInvalida = valorData !== '';
                            }
                        }
                        // Se data inválida, abre campo de edição
                        if(dataInvalida && idxData >= 0 && tds[idxData]) {
                            var td = tds[idxData];
                            var valorAtual = td.textContent.trim();
                            td.innerHTML = '<input type="text" class="form-control form-control-sm" value="'+valorAtual+'" placeholder="dd/mm/aaaa">';
                            var input = td.querySelector('input');
                            if(input){
                                input.addEventListener('blur', function(){
                                    var v = input.value;
                                    var norm = parseDataFlex(v) || v;
                                    td.textContent = norm;
                                    var btnSnapshot = document.getElementById('btn-snapshot-cache');
                                    if(btnSnapshot) btnSnapshot.click();
                                });
                                // Opcional: salvar ao pressionar Enter
                                input.addEventListener('keydown', function(e){
                                    if(e.key === 'Enter'){
                                        input.blur();
                                    }
                                });
                            }
                        }
                        // EMPRESA_ID: busca na célula da coluna EMPRESA_ID
                        var empresaId = '';
                        var ths = document.querySelectorAll('table[data-cache-key] thead th');
                        var idxEmpresa = -1;
                        // Prioriza 'EMPRESA_ID' exato
                        ths.forEach(function(th, idx){ var n=th.textContent.trim().toUpperCase(); if(n==='EMPRESA_ID') idxEmpresa = idx; });
                        if(idxEmpresa < 0){ ths.forEach(function(th, idx){ if(th.textContent.trim().toUpperCase().includes('EMPRESA')) idxEmpresa = idx; }); }
                        if(idxEmpresa >= 0) {
                            var tds = tr.querySelectorAll('td');
                            if(tds[idxEmpresa]) empresaId = tds[idxEmpresa].textContent.trim();
                        }
                        // fallback: select.class-conta
                        if(!empresaId) empresaId = tr.querySelector('select.class-conta')?.dataset.empresaId || '';
                        // fallback extra: dataset da linha e seleção global
                        if(!empresaId) empresaId = tr.dataset.classEmpresaId || document.getElementById('empresa-global')?.value || '';
                        if(!empresaId){ var rootEl = document.getElementById('preview-despesas-root'); if(rootEl && rootEl.dataset.empresaIdFromFile){ empresaId = rootEl.dataset.empresaIdFromFile; } }
                        // CONTA_DEBITO (somente se a linha for classificável)
                        var sel = tr.querySelector('select.class-conta');
                        var linhaClassificavel = !!(sel && sel.dataset.can==='1');
                        var contaDebitoId = '';
                        if(linhaClassificavel){
                            contaDebitoId = sel?.value || sel?.dataset.selected || '';
                        }
                        var contaDebitoLabel = '';
                        if(linhaClassificavel){
                            if(sel && sel.selectedIndex>=0){ contaDebitoLabel = sel.options[sel.selectedIndex]?.textContent || ''; }
                            // Fallback adicional de ID vindo do dataset da linha
                            if(!contaDebitoId){ contaDebitoId = tr.dataset.classContaId || ''; }
                            // Fallback a partir das colunas CONTA_DEBITO_ID / LABEL quando reimportado de export
                            if(!contaDebitoId && idxContaDebId>=0 && tds[idxContaDebId]){
                                var rawId = tds[idxContaDebId].textContent.trim();
                                if(rawId !== '') contaDebitoId = rawId;
                            }
                            if(!contaDebitoLabel && idxContaDebLabel>=0 && tds[idxContaDebLabel]){
                                var rawLbl = tds[idxContaDebLabel].textContent.trim();
                                if(rawLbl !== '') contaDebitoLabel = rawLbl;
                            }
                            // Fallback: se label vazio mas há id, usa o próprio id como label para não acusar falso positivo
                            if(!contaDebitoLabel && contaDebitoId){ contaDebitoLabel = String(contaDebitoId); }
                        }
                        // CONTA_CREDITO_GLOBAL_ID: select#conta-credito-global value
                        var contaCreditoId = document.getElementById('conta-credito-global')?.value || '';
                        // Fallback da coluna exportada (quando reimportado)
                        if(!contaCreditoId && idxContaCredGlobalId>=0 && tds[idxContaCredGlobalId]){
                            var rawCred = tds[idxContaCredGlobalId].textContent.trim();
                            if(rawCred !== '') contaCreditoId = rawCred;
                        }
                        // Fallback do dataset raiz (quando UI ainda não aplicou select)
                        if(!contaCreditoId){
                            var root = document.getElementById('preview-despesas-root');
                            if(root && root.dataset.contaCreditoIdFromFile){ contaCreditoId = root.dataset.contaCreditoIdFromFile; }
                        }
                        // VALOR: busca e valida formato numérico (robusto: R$, parênteses p/ negativo, sinal ao final, milhar, vírgula/ponto)
                        var temValor = false;
                        var valorInvalido = false;
                        function parseMonetario(v){
                            if(v==null) return null;
                            v = String(v).trim();
                            if(v==='') return null;
                            var negative = false;
                            // Parênteses para negativo
                            if(/^\(.*\)$/.test(v)) { negative = true; v = v.replace(/^\(|\)$/g,''); }
                            // Sinal negativo ao final
                            if(/-$/.test(v)) { negative = true; v = v.replace(/-$/,''); }
                            // Remove símbolos de moeda e plus
                            v = v.replace(/R\$|BRL|USD|\+/gi,'');
                            // Remove espaços
                            v = v.replace(/\s+/g,'');
                            // Mantém apenas dígitos, vírgula, ponto e um possível sinal negativo inicial
                            v = v.replace(/[^0-9.,-]/g,'');
                            // Se vírgula e ponto coexistem, assume vírgula como decimal -> remove pontos e troca vírgula por ponto
                            var hasDot = v.indexOf('.')>-1; var hasComma = v.indexOf(',')>-1;
                            if(hasDot && hasComma){ v = v.replace(/\./g,'').replace(/,/g,'.'); }
                            else if(hasComma && !hasDot){ v = v.replace(/,/g,'.'); }
                            // Se ainda houver múltiplos pontos, junta milhares
                            var dots = (v.match(/\./g)||[]).length;
                            if(dots>1){ var parts=v.split('.'); var dec=parts.pop(); v=parts.join('')+'.'+dec; }
                            var num = parseFloat(v);
                            if(isNaN(num)) return null;
                            return negative ? -num : num;
                        }
                        if(idxValor >= 0 && tds[idxValor]){
                            var valorCampo = tds[idxValor].textContent.trim();
                            var num = parseMonetario(valorCampo);
                            if(num !== null){ temValor = true; }
                            else { valorInvalido = true; }
                        }
                        // Diagnóstico (opcional)
                        if(window.__previewDebug){
                            var selectValue = sel ? (sel.value||'') : '';
                            var dataSelected = sel ? (sel.dataset.selected||'') : '';
                            console.debug('Validação linha', linhaNum, {
                                linhaClassificavel: linhaClassificavel,
                                empresaId: empresaId,
                                contaDebitoId: contaDebitoId,
                                contaDebitoLabel: contaDebitoLabel,
                                contaCreditoId: contaCreditoId,
                                temValor: temValor,
                                valorInvalido: valorInvalido,
                                fontesConta: { selectValue, dataSelected, trDataset: tr.dataset.classContaId||'' }
                            });
                            // Se faltar conta nesta linha, dispara breakpoint para inspecionar no DevTools
                            if(linhaClassificavel && temValor && !contaDebitoId){ debugger; }
                        }
                        var camposFaltando = [];
                        if(idxData >= 0){
                            if(dataInvalida){
                                camposFaltando.push('DATA_INVÁLIDA');
                            } else if(!temData){
                                camposFaltando.push('DATA');
                            }
                        }
                        if(!empresaId) camposFaltando.push('EMPRESA_ID');
                        // Exige conta débito somente para linhas classificáveis
                        if(linhaClassificavel && temValor){
                            if(!contaDebitoId) camposFaltando.push('CONTA_DEBITO_ID');
                            // Label não é obrigatória se ID presente
                        }
                        if(!contaCreditoId) camposFaltando.push('CONTA_CREDITO_GLOBAL_ID');
                        if(!temValor) camposFaltando.push('VALOR');
                        if(valorInvalido) camposFaltando.push('VALOR_INVÁLIDO');
                        if(camposFaltando.length > 0){
                            ok = false;
                            erros.push('Linha '+linhaNum+': faltando '+camposFaltando.join(', '));
                            // Destaca a linha
                            tr.classList.add('table-danger', 'linha-erro-validacao');
                            // Ativa edição nos campos faltantes
                            var tds = tr.querySelectorAll('td');
                            camposFaltando.forEach(function(campo){
                                // Busca índice do campo no header
                                var idxCampo = -1;
                                ths.forEach(function(th, idx){
                                    var headerNome = th.textContent.trim().toUpperCase();
                                    var campoHeader = campo;
                                    if(campoHeader.startsWith('DATA')) campoHeader = 'DATA';
                                    campoHeader = campoHeader.replace('_ID','').replace('_LABEL','').replace('GLOBAL','').replace('CONTA','CONTA').replace('EMPRESA','EMPRESA').replace('VALOR','VALOR');
                                    if(headerNome.includes(campoHeader)) idxCampo = idx;
                                });
                                if(idxCampo >= 0 && tds[idxCampo]) {
                                    // Se já não for input, ativa edição
                                    if(!tds[idxCampo].querySelector('input')) {
                                        var valorAtual = tds[idxCampo].textContent.trim();
                                        tds[idxCampo].innerHTML = '<input type="text" class="form-control form-control-sm" value="'+valorAtual+'" placeholder="'+campo+'">';
                                    }
                                }
                            });
                        }
                    }
                    // Exibe modal de pronto ou erro
                    if(window.bootstrap && modalPronto && msgPronto){
                        var bsModalPronto = bootstrap.Modal.getOrCreateInstance(modalPronto);
                        // Rola até a primeira linha com erro, se houver
                        var linhaErro = document.querySelector('.linha-erro-validacao');
                        if(linhaErro) linhaErro.scrollIntoView({behavior:'smooth',block:'center'});
                        let avisoFim = '<div class="mt-2"><b>Validação concluída.</b></div><div class="mb-2">Deseja seguir com o lançamento?</div>';
                        var btnSimModal = modalPronto.querySelector('.btn-success');
                        if(ok){
                            msgPronto.innerHTML = '<div class="alert alert-success mb-2">Está tudo pronto para seguir com os lançamentos contábeis!</div>' + avisoFim;
                            if(btnSimModal) btnSimModal.disabled = false;
                        }else{
                            msgPronto.innerHTML = '<div class="alert alert-danger mb-2">Existem linhas com dados obrigatórios ausentes:<ul><li>'+erros.join('</li><li>')+'</li></ul></div>' + avisoFim;
                            if(btnSimModal) btnSimModal.disabled = true;
                        }
                        setTimeout(function(){ bsModalPronto.show(); try{ modalPronto.focus(); }catch(e){} }, 150);
                    }
                };
            }
        });
    }
});
</script>
@endpush
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
            <div class="d-flex align-items-end gap-2">
                <div>
                    <label class="form-label mb-1">Empresa (global)</label>
                    <select id="empresa-global" class="form-select form-select-sm select2-basic" data-cache-key="{{ $cacheKey }}" data-placeholder="Selecione a empresa" data-locked="{{ $empresaLocked ? '1':'0' }}">
                        <option value=""></option>
                        @foreach($empresasLista as $emp)
                            <option value="{{ $emp->ID }}" {{ (string)($selectedEmpresaId ?? '') === (string)$emp->ID ? 'selected' : '' }}>{{ $emp->Descricao }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="pb-1">
                    <button type="button" id="btn-lock-empresa" class="btn btn-outline-danger btn-sm" data-locked="{{ $empresaLocked ? '1':'0' }}" {{ $empresaLocked ? '' : 'disabled' }}>
                        {{ $empresaLocked ? 'Travada (não seleciona empresa)' : 'Travar' }}
                    </button>
                </div>
            </div>
            <div>
                <label class="form-label mb-1">Conta Crédito (global)</label>
                <select id="conta-credito-global" class="form-select form-select-sm select2-conta-ajax" data-selected="{{ $globalCreditContaId ?? '' }}" data-placeholder="Conta crédito" {{ (empty($selectedEmpresaId) || !empty($globalCreditContaLocked)) ? 'disabled' : '' }}>
                    @if(!empty($globalCreditContaId))
                        <option value="{{ $globalCreditContaId }}" selected>{{ $globalCreditContaLabel ?? $globalCreditContaId }}</option>
                    @endif
                </select>
                <div class="form-text small text-muted">Linhas da tabela = Conta Débito; esta será usada como Crédito.</div>
            </div>
            <div>
                <label class="form-label mb-1">Travamento Crédito</label><br>
                <button type="button" id="btn-lock-conta-credito" class="btn btn-outline-primary btn-sm mt-1" data-locked="{{ !empty($globalCreditContaLocked)?'1':'0' }}" {{ !empty($globalCreditContaId) ? '' : 'disabled' }}>
                    {{ !empty($globalCreditContaLocked) ? 'Destravar Crédito' : 'Travar Crédito' }}
                </button>
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
                            // Agora basta ter um VALOR para permitir classificação
                            $canClass = $hasValor; // antes: $hasDate && $hasValor
                        @endphp
                        <tr class="{{ !empty($r['_auto_classified']) ? 'table-success auto-class' : '' }}" data-class-empresa-id="{{ $r['_class_empresa_id'] ?? '' }}" data-class-conta-id="{{ $r['_class_conta_id'] ?? '' }}">
                            <td style="position:sticky;left:0;background:#fff;z-index:1;">{{ $i+1 }}</td>
                            @foreach($headers as $h)
                                <td class="small">{{ $r[$h] }}</td>
                            @endforeach
                            <td style="min-width:260px;">
                                <input type="text" class="form-control form-control-sm hist-ajustado" value="{{ $r['_hist_ajustado'] }}" data-row="{{ $i }}" data-orig="{{ $histCol }}" placeholder="Ajuste aqui">
                                <div class="form-text text-muted small d-flex justify-content-between">
                                    <span>Origem: {{ $histCol ?? 'N/D' }}</span>
                                    @if(!empty($r['_auto_classified']))
                                        <span class="badge bg-success text-uppercase" title="Linha auto-classificada por tokens (hits: {{ $r['_auto_hits'] ?? '?' }})">AUTO</span>
                                    @endif
                                </div>
                            </td>
                            <td style="min-width:240px;">
                                @if($canClass)
                                    <select class="form-select form-select-sm class-conta select2-conta-ajax" data-row="{{ $i }}" data-selected="{{ $r['_class_conta_id'] ?? '' }}" data-can="1" {{ empty($r['_class_empresa_id']) ? 'disabled' : '' }} data-placeholder="Pesquisar conta">
                                        @if(!empty($r['_class_conta_id']))
                                            <option value="{{ $r['_class_conta_id'] }}" selected>{{ $r['_class_conta_label'] ?? $r['_class_conta_id'] }}</option>
                                        @endif
                                    </select>
                                @else
                                    <span class="text-muted small" data-can="0">(sem Valor)</span>
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
    body.preview-compact header { display: none !important; }
    body.preview-compact .container-fluid { padding-top: .75rem !important; }
    body.preview-compact #btn-toggle-layout { background:#212529; color:#fff; }
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
    tr.auto-class td { position: relative; }
    tr.auto-class td:first-child::before{ content:"★"; color:#198754; font-size:.75rem; position:absolute; left:4px; top:2px; }
</style>
@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
(function(){
        // Toggle de layout compacto (oculta header do layout bootstrap5)
        const toggleLayoutBtn = document.getElementById('btn-toggle-layout');
        const LS_KEY = 'preview_despesas_layout_compact';
        function applyLayoutState(){
            const compact = localStorage.getItem(LS_KEY) === '1';
            document.body.classList.toggle('preview-compact', compact);
            if(toggleLayoutBtn){ toggleLayoutBtn.textContent = compact ? 'Modo Completo' : 'Modo Compacto'; }
        }
        toggleLayoutBtn?.addEventListener('click', ()=>{
            const compact = document.body.classList.toggle('preview-compact');
            localStorage.setItem(LS_KEY, compact ? '1':'0');
            toggleLayoutBtn.textContent = compact ? 'Modo Completo' : 'Modo Compacto';
        });
        applyLayoutState();
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

    // Fallbacks de auto-seleção independentes de upload/import
    document.addEventListener('DOMContentLoaded', function(){
        // Empresa: se há exatamente 1 opção além do vazio e nada selecionado, seleciona
        const empresaSel = document.getElementById('empresa-global');
        if(empresaSel && !empresaSel.value && empresaSel.options.length === 2){
            empresaSel.selectedIndex = 1;
            if(window.jQuery){ jQuery(empresaSel).trigger('change.select2'); }
            else empresaSel.dispatchEvent(new Event('change', {bubbles:true}));
        }
        // Conta Crédito: se há data-selected no select e não há valor selecionado, aplica
        const contaSel = document.getElementById('conta-credito-global');
        if(contaSel && !contaSel.value){
            const ds = contaSel.dataset.selected || '';
            if(ds){
                let opt = contaSel.querySelector('option[value="'+ds+'"]');
                if(!opt){
                    opt = document.createElement('option');
                    opt.value = ds; opt.textContent = ds; opt.selected = true;
                    contaSel.appendChild(opt);
                }
                contaSel.value = ds;
                if(window.jQuery){ jQuery(contaSel).trigger('change.select2'); }
                else contaSel.dispatchEvent(new Event('change', {bubbles:true}));
            }
        }
        // Aplicação imediata dos data-* se presentes (sem depender da flag do upload)
        const root = document.getElementById('preview-despesas-root');
        if(root){
            const empId = root.dataset.empresaIdFromFile || '';
            const credId = root.dataset.contaCreditoIdFromFile || '';
            if(empId && empresaSel && !empresaSel.value){
                empresaSel.disabled = false;
                empresaSel.value = String(empId);
                if(window.jQuery){ jQuery(empresaSel).trigger('change.select2'); }
                else empresaSel.dispatchEvent(new Event('change', {bubbles:true}));
                const btnLockEmpresa = document.getElementById('btn-lock-empresa');
                if(btnLockEmpresa){ btnLockEmpresa.dataset.locked='0'; btnLockEmpresa.disabled=false; btnLockEmpresa.classList.add('btn-outline-danger'); btnLockEmpresa.classList.remove('btn-danger'); btnLockEmpresa.textContent='Travar Empresa'; }
            }
            if(credId && contaSel && !contaSel.value){
                contaSel.disabled = false;
                let opt = contaSel.querySelector('option[value="'+credId+'"]');
                if(!opt){ opt = document.createElement('option'); opt.value = credId; opt.textContent = credId; opt.selected = true; contaSel.appendChild(opt); }
                contaSel.value = credId;
                if(window.jQuery){ jQuery(contaSel).trigger('change.select2'); }
                else contaSel.dispatchEvent(new Event('change', {bubbles:true}));
                const btnLockCredito = document.getElementById('btn-lock-conta-credito');
                if(btnLockCredito){ btnLockCredito.dataset.locked='0'; btnLockCredito.disabled=false; btnLockCredito.classList.add('btn-outline-primary'); btnLockCredito.classList.remove('btn-primary'); btnLockCredito.textContent='Travar Crédito'; }
            }
        }
        // Extra: se reimportou export e global está vazio, tenta deduzir da tabela
        try{
            if(empresaSel && !empresaSel.value){
                const ths = document.querySelectorAll('table[data-cache-key] thead th');
                let idxEmp = -1; ths.forEach((th,idx)=>{ const n=th.textContent.trim().toUpperCase(); if(n==='EMPRESA_ID') idxEmp=idx; });
                if(idxEmp<0){ ths.forEach((th,idx)=>{ if(th.textContent.trim().toUpperCase().includes('EMPRESA')) idxEmp=idx; }); }
                if(idxEmp>=0){
                    const firstRow = document.querySelector('table[data-cache-key] tbody tr');
                    const cell = firstRow?.querySelectorAll('td')[idxEmp];
                    const v = (cell?.textContent||'').trim();
                    if(v){ empresaSel.disabled=false; empresaSel.value=String(v); if(window.jQuery){ jQuery(empresaSel).trigger('change.select2'); } else { empresaSel.dispatchEvent(new Event('change',{bubbles:true})); } }
                }
            }
            const contaSel = document.getElementById('conta-credito-global');
            if(contaSel && !contaSel.value){
                const ths = document.querySelectorAll('table[data-cache-key] thead th');
                let idxCred = -1; ths.forEach((th,idx)=>{ const n=th.textContent.trim().toUpperCase(); if(n.includes('CONTA') && n.includes('CREDITO') && n.includes('GLOBAL') && n.includes('ID')) idxCred=idx; });
                if(idxCred>=0){
                    const firstRow = document.querySelector('table[data-cache-key] tbody tr');
                    const cell = firstRow?.querySelectorAll('td')[idxCred];
                    const v = (cell?.textContent||'').trim();
                    if(v){
                        contaSel.disabled=false;
                        let opt=contaSel.querySelector('option[value="'+v+'"];');
                        if(!opt){ opt=document.createElement('option'); opt.value=v; opt.textContent=v; contaSel.appendChild(opt); }
                        contaSel.value=v;
                        if(window.jQuery){ jQuery(contaSel).trigger('change.select2'); } else { contaSel.dispatchEvent(new Event('change',{bubbles:true})); }
                    }
                }
            }
        }catch(e){ console.warn('auto-apply from table headers failed', e); }
    });

    const btnReprocessar = document.getElementById('btn-reprocessar');
    const btnSnapshot = document.getElementById('btn-snapshot-cache');
    const btnApplyAuto = document.getElementById('btn-apply-autoclass');
    const btnExport = document.getElementById('btn-export-xlsx');
    const selectContaCredito = document.getElementById('conta-credito-global');
    const btnLockCredito = document.getElementById('btn-lock-conta-credito');
        async function executarSnapshot(manual=false){
            if(!cacheKey) return;
            const linhas = [];
            document.querySelectorAll('table[data-cache-key] tbody tr').forEach(tr=>{
                const idxCell = tr.querySelector('td:first-child');
                const rowIndex = idxCell ? (parseInt(idxCell.textContent,10)-1) : null;
                if(rowIndex===null || isNaN(rowIndex)) return;
                const sel = tr.querySelector('select.class-conta');
                const inpHist = tr.querySelector('input.hist-ajustado');
                if(!inpHist) return;
                // Lê DATA a partir do índice global do cabeçalho
                let dataValor = null;
                if(idxDataGlobal >= 0){
                    const tds = tr.querySelectorAll('td');
                    const tdData = tds[idxDataGlobal];
                    if(tdData){ dataValor = (tdData.textContent||'').trim(); }
                }
                linhas.push({
                    i: rowIndex,
                    conta_id: sel && sel.value ? parseInt(sel.value,10) : null,
                    conta_label: sel && sel.options && sel.selectedIndex>=0 ? sel.options[sel.selectedIndex].textContent : null,
                    hist_ajustado: inpHist.value,
                    data: dataValor
                });
            });
            try{
                const r = await fetch("{{ route('lancamentos.preview.despesas.snapshot') }}",{
                    method:'POST',
                    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
                    body: JSON.stringify({
                        cache_key: cacheKey,
                        rows: linhas,
                        global_credit_conta_id: selectContaCredito && selectContaCredito.value ? parseInt(selectContaCredito.value,10) : null,
                        global_credit_conta_label: (selectContaCredito && selectContaCredito.options && selectContaCredito.selectedIndex>=0) ? selectContaCredito.options[selectContaCredito.selectedIndex].textContent : null
                    })
                });
                const j = await r.json();
                if(!j.ok){ console.warn('Falha snapshot', j); return false; }
                if(manual){
                    btnSnapshot?.classList.add('btn-success');
                    btnSnapshot.textContent = 'Cache Salvo';
                    setTimeout(()=>{ btnSnapshot.classList.remove('btn-success'); btnSnapshot.textContent='Salvar Cache'; }, 2000);
                }
                return true;
            }catch(e){ console.error(e); return false; }
        }
        btnSnapshot?.addEventListener('click', ()=> executarSnapshot(true));
        btnExport?.addEventListener('click', async()=>{
            if(!cacheKey) return;
            // Faz snapshot rápido antes de exportar para garantir persistência
            await executarSnapshot(false);
            const url = "{{ route('lancamentos.preview.despesas.exportXlsx') }}" + '?cache_key='+encodeURIComponent(cacheKey);
            window.location.href = url;
        });
        btnApplyAuto?.addEventListener('click', async()=>{
            if(!cacheKey){ return; }
            btnApplyAuto.disabled = true;
            btnApplyAuto.textContent = 'Aplicando...';
            try{
                const r = await fetch("/lancamentos/preview-despesas-excel/apply-auto-class",{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},body: JSON.stringify({cache_key: cacheKey})});
                const j = await r.json();
                if(j.ok){
                    // Atualiza visual: para simplicidade recarrega página (mantém snapshot prévio salvo manualmente se quiser); alternativa: fetch cache e patch DOM
                    location.reload();
                } else {
                    console.warn('Falha auto-class', j);
                    btnApplyAuto.textContent = 'Aplicar Regras Agora';
                    btnApplyAuto.disabled = false;
                }
            }catch(e){ console.error(e); btnApplyAuto.textContent='Aplicar Regras Agora'; btnApplyAuto.disabled=false; }
        });
    const btnRecarregar = document.getElementById('btn-submit-recarregar');
    const formFiltros = document.getElementById('form-filtros');
        if(btnReprocessar){
        btnReprocessar.addEventListener('click', async function(e){
            e.preventDefault();
            const href = this.getAttribute('href');
            openConfirm('<strong>Reprocessar planilha</strong>: será feito snapshot antes para preservar suas seleções. Continuar?', async ()=>{
                await executarSnapshot(false);
                window.location.href = href;
            });
        });
        }
        if(btnRecarregar && formFiltros){
        btnRecarregar.addEventListener('click', function(){
            openConfirm('<strong>Recarregar pré-visualização</strong>: será salvo snapshot antes. Continuar?', async ()=>{ await executarSnapshot(false); formFiltros.submit(); });
                });
        }
    const table = document.querySelector('table[data-cache-key]');
    const cacheKey = table?.dataset.cacheKey;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    // Índice da coluna DATA para edição inline
    let idxDataGlobal = -1;
    function recomputeIdxDataGlobal(){
        idxDataGlobal = -1;
        if(!table) return;
        const ths = table.querySelectorAll('thead th');
        // Match exato primeiro, depois fallback
        ths.forEach((th, idx)=>{ if(th.textContent.trim().toUpperCase() === 'DATA') idxDataGlobal = idx; });
        if(idxDataGlobal < 0){ ths.forEach((th, idx)=>{ if(th.textContent.trim().toUpperCase().includes('DATA')) idxDataGlobal = idx; }); }
    }
    recomputeIdxDataGlobal();
    // Parser/normalizador de datas (mesma lógica do validador)
    function parseDataFlexInline(raw){
        if(raw==null) return null;
        let v = String(raw).trim();
        if(v==='') return null;
        v = v.replace(/[T ]\d{1,2}:\d{2}(?::\d{2})?(?:\.\d+)?(?:Z|[+-]\d{2}:?\d{2})?$/, '');
        const pad2 = n=> (String(n).length===1? '0'+n: String(n));
        const fmt = (y,m,d)=> pad2(d)+'/'+pad2(m)+'/'+String(y);
        const isValid = (y,m,d)=>{ y=+y; m=+m; d=+d; if(!y||!m||!d) return false; const dt=new Date(y,m-1,d); return dt.getFullYear()===y && (dt.getMonth()+1)===m && dt.getDate()===d; };
        const fromSerial = (s)=>{ const base=new Date(1899,11,30); const ms=Math.round(parseFloat(s))*86400000; const dt=new Date(base.getTime()+ms); return fmt(dt.getFullYear(), dt.getMonth()+1, dt.getDate()); };
        if(/^\d{2,6}$/.test(v)){ const n=parseInt(v,10); if(n>59 && n<60000){ try{ return fromSerial(n); }catch(e){} } }
        const vSep = v.replace(/[\.\-]/g,'/').replace(/\s+/g,'/');
        let m = vSep.match(/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/);
        if(m){ const y=+m[1], mm=+m[2], dd=+m[3]; if(isValid(y,mm,dd)) return fmt(y,mm,dd); }
        m = vSep.match(/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})$/);
        if(m){ let dd=+m[1], mm=+m[2], yy=m[3]; if(yy.length===2) yy='20'+yy; yy=+yy; if(isValid(yy,mm,dd)) return fmt(yy,mm,dd); }
        m = v.match(/^(\d{4})(\d{2})(\d{2})$/);
        if(m){ const y=+m[1], mm=+m[2], dd=+m[3]; if(isValid(y,mm,dd)) return fmt(y,mm,dd); }
        m = v.match(/^(\d{2})(\d{2})(\d{4})$/);
        if(m){ const dd=+m[1], mm=+m[2], y=+m[3]; if(isValid(y,mm,dd)) return fmt(y,mm,dd); }
        return null;
    }
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
    // Snapshot automático ao sair de inputs na tabela
    document.addEventListener('blur', function(e){
        if(e.target && e.target.closest('table[data-cache-key]') && e.target.tagName === 'INPUT'){
            btnSnapshot?.click();
        }
    }, true);
    // Edição inline de DATA com duplo clique
    if(table && idxDataGlobal >= 0){
        table.addEventListener('dblclick', function(e){
            const td = e.target.closest('td');
            if(!td) return;
            const tr = td.parentElement;
            const tds = Array.from(tr.querySelectorAll('td'));
            const cellIdx = tds.indexOf(td);
            if(cellIdx !== idxDataGlobal) return;
            if(td.querySelector('input')) return; // já editando
            const valorAtual = td.textContent.trim();
            td.innerHTML = '<input type="text" class="form-control form-control-sm" value="'+valorAtual+'" placeholder="dd/mm/aaaa">';
            const input = td.querySelector('input');
            input.focus();
            input.select();
            const commit = ()=>{
                const v = input.value.trim();
                const normal = parseDataFlexInline(v);
                td.textContent = normal || v; // mantém o digitado se não normalizar
                // snapshot após alterar
                btnSnapshot?.click();
            };
            input.addEventListener('blur', commit);
            input.addEventListener('keydown', function(ev){
                if(ev.key==='Enter'){ ev.preventDefault(); input.blur(); }
                if(ev.key==='Escape'){ ev.preventDefault(); td.textContent = valorAtual; }
            });
        });
    }
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
    let primeiraAplicacaoEmpresa = true;
    async function aplicarEmpresaGlobal(){
        const empId = empresaGlobalSelect.value || '';
        if(empresaGlobalSelect.dataset.locked==='1'){
            // Se travado, impedir troca diferente da atual
            const prev = empresaGlobalSelect.getAttribute('data-prev');
            if(prev && prev !== empId){
                // Reverter seleção
                empresaGlobalSelect.value = prev;
                if(window.jQuery){ jQuery('#empresa-global').trigger('change.select2'); }
                return;
            }
        }
        // Feedback visual de carregamento
        empresaGlobalSelect.classList.add('border','border-warning');
        const mesmaEmpresa = empresaGlobalSelect.getAttribute('data-prev') && empresaGlobalSelect.getAttribute('data-prev') === empId;
        document.querySelectorAll('select.class-conta').forEach(sel=>{
            if(sel.dataset.can === '1'){
                // Se empresa mudou realmente, limpamos opções visíveis (dataset.selected preserva)
                if(!mesmaEmpresa){
                    sel.innerHTML = '';
                }
                sel.disabled = !empId;
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
            if(!mesmaEmpresa || primeiraAplicacaoEmpresa){
                // Repovoa somente com opções das contas previamente selecionadas (para manter visual) quando empresa mudou
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
            } else {
                // Empresa igual: garantir que option selecionada reapareça sem nova chamada
                document.querySelectorAll('select.class-conta').forEach(sel=>{
                    const sid = sel.dataset.selected;
                    if(sel.dataset.can==='1' && sid && !sel.querySelector(`option[value="${sid}"]`)){
                        const opt = document.createElement('option');
                        opt.value = sid; opt.textContent = sid; opt.selected = true; // label será resolvida quando buscar novamente via Select2
                        sel.appendChild(opt);
                    }
                });
            }
            // Selects continuam AJAX para novas buscas
            empresaGlobalSelect.setAttribute('data-prev', empId);
            initSelect2();
            marcarLinhasSemConta();
            setTimeout(()=> empresaGlobalSelect.classList.remove('border','border-warning'), 1200);
            primeiraAplicacaoEmpresa = false;
        }).catch(e=>console.error(e));
    }
    empresaGlobalSelect?.addEventListener('change', aplicarEmpresaGlobal);
    // Conta crédito global: inicializa Select2 após empresa setada
    function initContaCreditoSelect(){
        if(!window.jQuery || !selectContaCredito) return;
        const $el = jQuery(selectContaCredito);
        if($el.hasClass('select2-hidden-accessible')){ $el.select2('destroy'); }
        $el.select2({
            theme:'bootstrap-5', width:'100%', allowClear:true, placeholder:selectContaCredito.dataset.placeholder||'Conta crédito',
            ajax:{
                delay:250,
                transport: function(params, success, failure){
                    const empId = empresaGlobalSelect.value || '';
                    if(!empId){ success({results:[]}); return; }
                    const term = params.data.q || '';
                    fetch(`/empresa/${empId}/contas-grau5?q=${encodeURIComponent(term)}`)
                      .then(r=> r.ok? r.json(): {data:{}})
                      .then(j=> { const results = Object.entries(j.data||{}).map(([id,text])=>({id,text})); success({results}); })
                      .catch(failure);
                },
                processResults: d=>d
            }
        });
    }
    initContaCreditoSelect();
    if(selectContaCredito){
        selectContaCredito.addEventListener('change', function(){
            const contaId = this.value || null;
            fetch("{{ route('lancamentos.preview.despesas.credito') }}",{
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
                body: JSON.stringify({cache_key: cacheKey, conta_id: contaId})
            }).then(r=>r.json()).then(j=>{ if(!j.ok){ console.warn('Falha definir conta crédito', j); }}).catch(e=>console.error(e));
        });
    }
    function habilitarContaCreditoSeEmpresa(){
        if(!selectContaCredito) return;
        const temEmpresa = !!empresaGlobalSelect.value;
        selectContaCredito.disabled = !temEmpresa;
        if(temEmpresa){ initContaCreditoSelect(); }
        atualizarBotaoLockCredito();
    }
    empresaGlobalSelect?.addEventListener('change', habilitarContaCreditoSeEmpresa);
    habilitarContaCreditoSeEmpresa();
    function atualizarBotaoLockCredito(){
        if(!btnLockCredito) return;
        const locked = btnLockCredito.dataset.locked === '1';
        const temConta = !!(selectContaCredito && selectContaCredito.value);
        btnLockCredito.disabled = !temConta && !locked;
        if(locked){
            btnLockCredito.classList.remove('btn-outline-primary');
            btnLockCredito.classList.add('btn-primary');
            btnLockCredito.textContent = 'Destravar Crédito';
            if(selectContaCredito){ selectContaCredito.disabled = true; }
        } else {
            btnLockCredito.classList.add('btn-outline-primary');
            btnLockCredito.classList.remove('btn-primary');
            btnLockCredito.textContent = 'Travar Crédito';
            // Reabilita somente se há empresa selecionada
            if(selectContaCredito && empresaGlobalSelect.value){ selectContaCredito.disabled = false; }
        }
    }
    btnLockCredito?.addEventListener('click', ()=>{
        const locked = btnLockCredito.dataset.locked === '1';
        const novo = locked ? 0 : 1;
        if(novo === 1 && (!selectContaCredito || !selectContaCredito.value)) return;
        const confirmarMsg = novo===1 ? '<strong>Travar Conta Crédito</strong>: não será possível alterar até destravar. Confirmar?' : '<strong>Destravar Conta Crédito</strong>: permitirá alterar a conta crédito. Prosseguir?';
        openConfirm(confirmarMsg, async ()=>{
            try{
                const r = await fetch("{{ route('lancamentos.preview.despesas.credito.lock') }}",{
                    method:'POST',
                    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
                    body: JSON.stringify({cache_key: cacheKey, locked: !!novo})
                });
                const j = await r.json();
                if(j.ok){
                    btnLockCredito.dataset.locked = j.locked ? '1':'0';
                    if(j.locked){
                        // desabilita select
                        if(selectContaCredito){ selectContaCredito.disabled = true; }
                    } else {
                        if(selectContaCredito){ selectContaCredito.disabled = false; }
                    }
                    atualizarBotaoLockCredito();
                } else {
                    console.warn('Falha lock crédito', j);
                }
            }catch(e){ console.error(e); }
        });
    });
    document.addEventListener('change', function(e){
        if(e.target === selectContaCredito){ atualizarBotaoLockCredito(); }
    });
    atualizarBotaoLockCredito();

    // Botão de lock/unlock
    const btnLock = document.getElementById('btn-lock-empresa');
    function atualizarBotaoLock(){
        if(!btnLock) return;
        const locked = empresaGlobalSelect.dataset.locked === '1';
        const temEmpresa = !!empresaGlobalSelect.value;
        btnLock.disabled = !temEmpresa && !locked; // só habilita lock quando empresa definida
        if(locked){
            btnLock.classList.remove('btn-outline-danger');
            btnLock.classList.add('btn-danger');
            btnLock.textContent = 'Destravar Empresa';
        } else {
            btnLock.classList.add('btn-outline-danger');
            btnLock.classList.remove('btn-danger');
            btnLock.textContent = temEmpresa ? 'Travar Empresa' : 'Travada: não (selecione empresa)';
        }
    }
    async function toggleLock(){
        const locked = empresaGlobalSelect.dataset.locked === '1';
        const novo = locked ? 0 : 1;
        if(novo === 1 && !empresaGlobalSelect.value){ return; }
        const executar = async ()=>{
            try{
                const r = await fetch("{{ route('lancamentos.preview.despesas.empresa.lock') }}",{
                    method:'POST',
                    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
                    body: JSON.stringify({cache_key: cacheKey, locked: !!novo})
                });
                const j = await r.json();
                if(!j.ok){ console.warn('Falha toggle lock', j); return; }
                empresaGlobalSelect.dataset.locked = j.locked ? '1':'0';
                if(j.locked){
                    empresaGlobalSelect.setAttribute('data-prev', empresaGlobalSelect.value || '');
                    // Desabilita select (Select2 e elemento base)
                    if(window.jQuery){ jQuery('#empresa-global').prop('disabled', true).trigger('change.select2'); }
                    empresaGlobalSelect.disabled = true;
                } else {
                    // Reabilita select
                    empresaGlobalSelect.disabled = false;
                    if(window.jQuery){ jQuery('#empresa-global').prop('disabled', false).trigger('change.select2'); }
                }
                atualizarBotaoLock();
            }catch(e){ console.error(e); }
        };
        if(novo === 1){
            // Confirmação antes de travar
            openConfirm('<strong>Travar Empresa</strong>: após travar não será possível alterar a empresa sem destravar. Confirmar?', executar);
        } else {
            // Confirmação antes de destravar
            openConfirm('<strong>Destravar Empresa</strong>: permitirá alterar a empresa e exigirá reclassificação das contas. Prosseguir?', executar);
        }
    }
    btnLock?.addEventListener('click', toggleLock);
    atualizarBotaoLock();
    // Sempre que empresa mudar, reavalia botão (quando não travado)
    empresaGlobalSelect?.addEventListener('change', atualizarBotaoLock);
    // Estado inicial: se já estava travada, desabilita select
    if(empresaGlobalSelect?.dataset.locked==='1'){
        empresaGlobalSelect.disabled = true;
        if(window.jQuery){ jQuery('#empresa-global').prop('disabled', true).trigger('change.select2'); }
    }
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
    function scrollParaProximaPendente(currentRow){
        const linhas = Array.from(document.querySelectorAll('table[data-cache-key] tbody tr'))
            .map(tr=>({tr, sel: tr.querySelector('select.class-conta[data-can="1"]')}))
            .filter(o=> o.sel && !o.sel.disabled && o.tr.style.display !== 'none');
        const proximas = linhas.filter(o=> parseInt(o.sel.dataset.row,10) > currentRow && !o.sel.value);
        if(!proximas.length){
            // Tenta procurar antes (caso usuário esteja no final)
            const ciclo = linhas.filter(o=> !o.sel.value);
            if(!ciclo.length) return; // tudo classificado
            // Rolagem opcional para primeira pendente
            const alvo = ciclo[0];
            alvo.tr.scrollIntoView({behavior:'smooth', block:'center'});
            // Foca select se ainda não aberto
            setTimeout(()=> alvo.sel.focus(), 300);
            return;
        }
        const alvo = proximas[0];
        alvo.tr.scrollIntoView({behavior:'smooth', block:'center'});
        setTimeout(()=> alvo.sel.focus(), 300);
    }
    btnToggle?.addEventListener('click', ()=>{
        btnToggle.classList.toggle('mostrar-pendentes');
        aplicarFiltroClassificadas();
        atualizarToggleEstado();
    });
    atualizarToggleEstado();

    // Observa mudanças de seleção para auto-scroll (delegação já configurada acima; aqui usamos MutationObserver fallback para selects carregados dinamicamente via Select2)
    document.addEventListener('change', function(e){
        const selConta = e.target.closest('select.class-conta');
        if(selConta && selConta.value){
            const row = parseInt(selConta.dataset.row,10);
            scrollParaProximaPendente(row);
        }
    });
})();
</script>
@endpush
