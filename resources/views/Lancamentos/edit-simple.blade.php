@php
    // Assumindo que o controller forneceu: $lancamento, $empresas, $contasOrigem, $historicos
    $contaDebitoId  = $lancamento->ContaDebitoID ?? null;
    $contaCreditoId = $lancamento->ContaCreditoID ?? null;
    $urlExtratoDebito  = $contaDebitoId  ? url('/Contas/Extrato/'.$contaDebitoId).'#gsc.tab=0' : null;
    $urlExtratoCredito = $contaCreditoId ? url('/Contas/Extrato/'.$contaCreditoId).'#gsc.tab=0' : null;
@endphp
@extends('layouts.bootstrap5')
@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Editar Lançamento #{{ $lancamento->ID }}</h4>
            <div class="d-flex gap-2">
                @if($urlExtratoDebito)
                    <a href="{{ $urlExtratoDebito }}" target="_blank" rel="noopener" class="btn btn-primary btn-sm" title="Abrir extrato da conta débito em nova aba">
                        Extrato Débito
                    </a>
                @endif
                @if($urlExtratoCredito && $urlExtratoCredito !== $urlExtratoDebito)
                    <a href="{{ $urlExtratoCredito }}" target="_blank" rel="noopener" class="btn btn-secondary btn-sm" title="Abrir extrato da conta crédito em nova aba">
                        Extrato Crédito
                    </a>
                @endif
                <a href="{{ route('lancamentos.clone.simple',$lancamento->ID) }}" class="btn btn-warning btn-sm" title="Clonar este lançamento para um novo">Clonar</a>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">Voltar</a>
            </div>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-12">
            <div class="small bg-light border rounded p-2">
                <strong>Origem:</strong>
                <span class="me-2">Empresa ID: {{ $lancamento->EmpresaID }}</span>
                @php
                    $descDeb = $contasOrigem[$lancamento->ContaDebitoID] ?? 'Conta Débito';
                    $descCre = $contasOrigem[$lancamento->ContaCreditoID] ?? 'Conta Crédito';
                @endphp
                @if($lancamento->ContaDebitoID)
                    <a class="me-2" target="_blank" rel="noopener" href="{{ url('/Contas/Extrato/'.$lancamento->ContaDebitoID) }}#gsc.tab=0">Débito: {{ $descDeb }}</a>
                @endif
                @if($lancamento->ContaCreditoID)
                    <a class="me-2" target="_blank" rel="noopener" href="{{ url('/Contas/Extrato/'.$lancamento->ContaCreditoID) }}#gsc.tab=0">Crédito: {{ $descCre }}</a>
                @endif
                <span class="text-muted">(Esses links refletem o lançamento antes de salvar alterações)</span>
            </div>
        </div>
    </div>
    @if(session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="card shadow-sm" style="border:1px solid #0d5ca8;">
        <div class="card-header" style="background:#0d5ca8;color:#fff;">
            <strong>Dados Principais</strong>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('lancamentos.simple.update', $lancamento->ID) }}" id="formEditLancamentoSimple">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Data Contabilidade</label>
                        <input type="date" name="DataContabilidade" value="{{ old('DataContabilidade',$lancamento->DataContabilidade?->format('Y-m-d')) }}" class="form-control" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Descrição</label>
                        <input type="text" name="Descricao" value="{{ old('Descricao',$lancamento->Descricao) }}" class="form-control" maxlength="255">
                    </div>
                    <div class="col-md-4 position-relative">
                        <label class="form-label d-flex justify-content-between align-items-center">Empresa
                            <span class="ms-2 badge bg-warning text-dark d-none" id="empresaChangedBadge">Empresa alterada</span>
                        </label>
                        <select name="EmpresaID" id="EmpresaID" class="form-control select2-basic" data-original="{{ $lancamento->EmpresaID }}" data-placeholder="Selecione">
                            <option value="">Selecione</option>
                            @foreach($empresas as $id=>$desc)
                                <option value="{{ $id }}" @selected(old('EmpresaID',$lancamento->EmpresaID)==$id)>{{ $desc }}</option>
                            @endforeach
                        </select>
                        <div id="empresaChangeStatus" class="small text-muted mt-1"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Histórico</label>
                        <select name="HistoricoID" class="form-control select2-basic" data-placeholder="Selecione">
                            <option value=""></option>
                            @foreach($historicos as $h)
                                <option value="{{ $h->ID }}" @selected(old('HistoricoID',$lancamento->HistoricoID)==$h->ID)>{{ $h->Descricao }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Conta Débito
                            @if($lancamento->ContaDebitoID)
                                <a id="linkExtratoDebito" href="{{ url('/Contas/Extrato/'.$lancamento->ContaDebitoID) }}#gsc.tab=0" target="_blank" rel="noopener" class="small ms-1 text-decoration-none" title="Ver extrato da conta selecionada">🔗 ir para</a>
                            @else
                                <a id="linkExtratoDebito" href="#" class="small ms-1 text-decoration-none d-none" target="_blank" rel="noopener" title="Ver extrato da conta selecionada">🔗 ir para</a>
                            @endif
                        </label>
                        <select name="ContaDebitoID" id="ContaDebitoID" class="form-control select2-basic" data-placeholder="Selecione">
                            <option value=""></option>
                            @foreach($contasOrigem as $cid=>$cdesc)
                                <option value="{{ $cid }}" @selected(old('ContaDebitoID',$lancamento->ContaDebitoID)==$cid)>{{ $cdesc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Conta Crédito
                            @if($lancamento->ContaCreditoID)
                                <a id="linkExtratoCredito" href="{{ url('/Contas/Extrato/'.$lancamento->ContaCreditoID) }}#gsc.tab=0" target="_blank" rel="noopener" class="small ms-1 text-decoration-none" title="Ver extrato da conta selecionada">🔗 ir para</a>
                            @else
                                <a id="linkExtratoCredito" href="#" class="small ms-1 text-decoration-none d-none" target="_blank" rel="noopener" title="Ver extrato da conta selecionada">🔗 ir para</a>
                            @endif
                        </label>
                        <select name="ContaCreditoID" id="ContaCreditoID" class="form-control select2-basic" data-placeholder="Selecione">
                            <option value=""></option>
                            @foreach($contasOrigem as $cid=>$cdesc)
                                <option value="{{ $cid }}" @selected(old('ContaCreditoID',$lancamento->ContaCreditoID)==$cid)>{{ $cdesc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Valor (R$)</label>
                        <input type="text" name="Valor" value="{{ old('Valor',$lancamento->Valor) }}" class="form-control" autocomplete="off">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Qtd Dólar</label>
                        <input type="text" name="ValorQuantidadeDolar" value="{{ old('ValorQuantidadeDolar',$lancamento->ValorQuantidadeDolar) }}" class="form-control" autocomplete="off">
                    </div>
                </div>

                {{-- Card de transferência removido: fluxo unificado pela simples troca de Empresa + seleção de contas --}}

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-success">Salvar Alterações</button>
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<style>
    .select2-container--bootstrap-5 .select2-selection { min-height:38px; }
    .select2-selection__rendered { line-height:36px !important; }
    .select2-selection__arrow { height:36px !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
(function(){
    function initBasic(){
        $('.select2-basic').each(function(){
            const $el = $(this);
            if($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
            $el.select2({ theme:'bootstrap-5', width:'100%', allowClear:true, placeholder:$el.data('placeholder')||'Selecione' });
        });
    }
    document.addEventListener('DOMContentLoaded', function(){
        initBasic();
        const contasCache = {}; // cache leve: empresaID -> html options
        const selEmpresaPrincipal = document.getElementById('EmpresaID');
        const selDebito = document.getElementById('ContaDebitoID');
        const selCredito = document.getElementById('ContaCreditoID');
        const badgeEmpresa = document.getElementById('empresaChangedBadge');
        const btnSalvar = document.querySelector('#formEditLancamentoSimple button[type="submit"]');
        const valorInput = document.querySelector('input[name="Valor"]');

        function parseNumberBRL(str){
            if(str==null) return '';
            str = (''+str).trim();
            if(str==='') return '';
            if(str.includes(',')){
                str = str.replace(/\./g,'').replace(',','.');
            }
            return str;
        }
        function formatBRL(num){
            if(num==='' || num==null || isNaN(num)) return '';
            const n = Number(num);
            if(isNaN(n)) return '';
            return n.toLocaleString('pt-BR',{minimumFractionDigits:2, maximumFractionDigits:2});
        }
        if(valorInput){
            const raw = parseNumberBRL(valorInput.value);
            if(raw!=='') valorInput.value = formatBRL(raw);
            valorInput.addEventListener('blur', ()=>{
                const parsed = parseNumberBRL(valorInput.value);
                if(parsed!=='') valorInput.value = formatBRL(parsed);
            });
            document.getElementById('formEditLancamentoSimple').addEventListener('submit', ()=>{
                const parsed = parseNumberBRL(valorInput.value);
                if(parsed!=='') valorInput.value = parsed; // backend espera decimal com ponto
            });
        }

        // Helper para atualizar status
        const statusEl = document.getElementById('empresaChangeStatus');
        function setStatus(msg, type='muted'){
            if(!statusEl) return;
            const map = { 'muted':'text-muted', 'ok':'text-success', 'warn':'text-warning', 'err':'text-danger' };
            statusEl.className = 'small mt-1 '+(map[type]||'text-muted');
            statusEl.textContent = msg;
        }

        async function handleEmpresaChange(){
            const empId = this.value;
            const original = this.dataset.original;
            if(original && empId && original !== empId){
                badgeEmpresa?.classList.remove('d-none');
            }else{
                badgeEmpresa?.classList.add('d-none');
            }
            [selDebito, selCredito].forEach(s=>{ s.innerHTML='<option value="">Carregando...</option>'; });
            btnSalvar?.setAttribute('disabled','disabled');
            btnSalvar?.classList.add('disabled');
            setStatus(empId ? 'Carregando contas da empresa...' : '');
            if(!empId){
                [selDebito, selCredito].forEach(s=>{ s.innerHTML='<option value="">Selecione empresa</option>'; });
                initBasic();
                btnSalvar?.removeAttribute('disabled');
                btnSalvar?.classList.remove('disabled');
                return;
            }
            try {
                if(contasCache[empId]){
                    selDebito.innerHTML = contasCache[empId];
                    selCredito.innerHTML = contasCache[empId];
                    setStatus('Contas carregadas (cache)', 'ok');
                } else {
                    const resp = await fetch('/empresa/'+empId+'/contas-grau5');
                    if(!resp.ok) throw new Error('Falha ao buscar contas');
                    const json = await resp.json();
                    const opts = ['<option value="">Selecione</option>'];
                    Object.keys(json.data||{}).forEach(k=> opts.push('<option value="'+k+'">'+json.data[k]+'</option>'));
                    const htmlOptions = opts.join('');
                    contasCache[empId] = htmlOptions;
                    selDebito.innerHTML = htmlOptions;
                    selCredito.innerHTML = htmlOptions;
                    setStatus('Contas carregadas: '+(Object.keys(json.data||{}).length||0), 'ok');
                }
                // Destaque visual rápido
                [selDebito, selCredito].forEach(s=>{ s.classList.add('border','border-warning'); setTimeout(()=> s.classList.remove('border','border-warning'), 1800); });
            } catch(e){
                selDebito.innerHTML='<option value="">Erro</option>';
                selCredito.innerHTML='<option value="">Erro</option>';
                setStatus('Erro ao carregar contas', 'err');
            }
            initBasic();
            btnSalvar?.removeAttribute('disabled');
            btnSalvar?.classList.remove('disabled');
        }

        // Listener nativo
        selEmpresaPrincipal?.addEventListener('change', handleEmpresaChange);
        // Listener via Select2
        $(selEmpresaPrincipal).on('select2:select', function(){ handleEmpresaChange.call(this); });
        // Fluxo de transferência removido: agora tudo é feito ao trocar empresa e salvar

        // Atualização dinâmica dos links 'ir para' de extrato (débito/crédito)
        const linkDeb = document.getElementById('linkExtratoDebito');
        const linkCre = document.getElementById('linkExtratoCredito');
        function updateLinkExtrato(tipo){
            let selectEl, linkEl;
            if(tipo==='debito'){ selectEl = selDebito; linkEl = linkDeb; }
            else { selectEl = selCredito; linkEl = linkCre; }
            if(!selectEl || !linkEl) return;
            const val = selectEl.value;
            if(val){
                linkEl.href = '/Contas/Extrato/'+val+'#gsc.tab=0';
                linkEl.classList.remove('d-none','text-muted');
                linkEl.title = 'Ver extrato da conta selecionada (#'+val+')';
            } else {
                linkEl.href = '#';
                linkEl.classList.add('d-none');
            }
        }
        selDebito?.addEventListener('change', ()=> updateLinkExtrato('debito'));
        selCredito?.addEventListener('change', ()=> updateLinkExtrato('credito'));
        $(selDebito).on('select2:select select2:clear', ()=> updateLinkExtrato('debito'));
        $(selCredito).on('select2:select select2:clear', ()=> updateLinkExtrato('credito'));
        // Inicializa estado atual
        updateLinkExtrato('debito');
        updateLinkExtrato('credito');
    });
})();
</script>
@endpush
