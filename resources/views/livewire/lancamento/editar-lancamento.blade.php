<div>
    <div class="card">
        <div class="card-header">
            <h4>
                @if ($lancamento->ID)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const tabEl = document.getElementById('troca-empresa-tab');
    if(tabEl){
        tabEl.addEventListener('shown.bs.tab', function(){
            try {
                Livewire.emitTo('lancamento.troca-empresa','setLancamentoID', {{ $lancamento->ID ?? 'null' }});
                Livewire.emitTo('lancamento.troca-empresa','refreshData');
            } catch(e) { console.warn('refresh troca-empresa failed', e); }
        });
    }
    // Caso a aba já inicie ativa (ex: retorna após ação) forçar refresh inicial
    if(tabEl && tabEl.classList.contains('active')){
        Livewire.emitTo('lancamento.troca-empresa','setLancamentoID', {{ $lancamento->ID ?? 'null' }});
        Livewire.emitTo('lancamento.troca-empresa','refreshData');
    }
});
</script>
@endpush
                    <strong>{{ $lancamento->ID }} - Edição</strong> de lançamentos |
                    {{ $lancamento->Empresa->Descricao }}
                @else
                    <strong>Novo</strong> de lançamentos | {{ $empresa->Descricao ?? null }}
                @endif
            </h4>
        </div>
        <div class="card-body">

            <div class="col-sm-12">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (session()->has('message'))
                    <div class="alert alert-success">
                        {{ session('message') }}
                    </div>
                @endif
            </div>

            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link @if ($currentTab == 'lancamento') active @endif" id="lancamento-tab"
                        data-bs-toggle="tab" wire:click="sessionTab('lancamento')" data-bs-target="#lancamento"
                        type="button" role="tab" aria-controls="lancamento"
                        aria-selected="true">Lançamento</button>
                </li>
                @if ($lancamento->ID)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link @if ($currentTab == 'comentario') active @endif" id="comentarios-tab"
                            data-bs-toggle="tab" data-bs-target="#comentarios" type="button" role="tab"
                            aria-controls="comentarios" wire:click="sessionTab('comentario')"
                            aria-selected="false">Comentários</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link @if ($currentTab == 'arquivo') active @endif" id="arquivos-tab"
                            data-bs-toggle="tab" data-bs-target="#arquivos" type="button" role="tab"
                            aria-controls="arquivos" aria-selected="false"
                            wire:click="sessionTab('arquivo')">Arquivos</button>
                    </li>
                @endif
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade @if ($currentTab == 'lancamento') show active @endif" id="lancamento"
                    role="tabpanel" aria-labelledby="lancamento-tab">
                    <div class="card-body">
                        <form wire:submit.prevent="salvarLancamento()" id="form-lancamento">
                            <div class="row">
                                @if ($lancamento->ID)
                                    <div class="col-sm-12">
                                        <label class="form-label d-flex align-items-center gap-2" for="novaEmpresaID" style="font-weight:600;">
                                            <span>Nova Empresa</span>
                                            <button type="button" id="btnToggleTrocaInline" class="btn btn-sm btn-outline-primary py-0">Transferir</button>
                                        </label>
                                        <div wire:ignore>
                                            <select wire:model='lancamento.EmpresaID'
                                                    id="novaEmpresaID" class="form-control select2" data-placeholder="Selecione">
                                                <option value="">Selecione</option>
                                                @foreach ($empresas as $empresaID => $empresaDescricao)
                                                    <option value="{{ $empresaID }}">{{ $empresaDescricao }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <!-- Formulário inline de transferência (modo simples) -->
                                        <div id="trocaEmpresaInline" class="card mt-2" style="display:none;">
                                            <div class="card-body p-3">
                                                <form method="POST" action="{{ route('lancamento.troca-empresa.simples', $lancamento->ID) }}" id="trocaEmpresaInlineForm">
                                                    @csrf
                                                    <div class="row g-2">
                                                        <div class="col-md-4">
                                                            <label for="inlineNovaEmpresa" class="form-label mb-1">Empresa destino</label>
                                                            <select name="novaempresa" id="inlineNovaEmpresa" class="form-control select2-troca-inline" data-placeholder="Selecione">
                                                                <option value="">Selecione</option>
                                                                @foreach ($empresas as $empresaID => $empresaDescricao)
                                                                    <option value="{{ $empresaID }}">{{ $empresaDescricao }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="inlineContaDebito" class="form-label mb-1">Conta Débito</label>
                                                            <select name="novacontadebito" id="inlineContaDebito" class="form-control select2-troca-inline" data-placeholder="Selecione">
                                                                <option value="">Selecione empresa</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="inlineContaCredito" class="form-label mb-1">Conta Crédito</label>
                                                            <select name="novacontacredito" id="inlineContaCredito" class="form-control select2-troca-inline" data-placeholder="Selecione">
                                                                <option value="">Selecione empresa</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex gap-2 mt-3">
                                                        <button type="submit" class="btn btn-primary btn-sm">Confirmar Transferência</button>
                                                        <button type="button" id="btnCancelarTrocaInline" class="btn btn-outline-secondary btn-sm">Cancelar</button>
                                                    </div>
                                                    @if($errors->has('novaempresa') || $errors->has('novacontadebito') || $errors->has('novacontacredito'))
                                                        <div class="alert alert-danger mt-2 mb-0 p-2">
                                                            <ul class="mb-0 small">
                                                                @foreach($errors->all() as $err)
                                                                    <li>{{ $err }}</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                    @if(session('message'))
                                                        <div class="alert alert-success mt-2 mb-0 p-2 small">{{ session('message') }}</div>
                                                    @endif
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="form-group col-sm-12 mb-2">
                                    <label for="historicoID" class=" form-control-label">
                                        Histórico
                                    </label>
                                        <div wire:ignore>
                                    <select id="historicoID" name="HistoricoID" class="form-control select2" data-placeholder="Selecione"
                                        wire:model='lancamento.HistoricoID'>
                                        <option value=""></option>
                                        @foreach ($historicos as $historico)
                                            <option value="{{ $historico->ID }}">{{ $historico->Descricao }}</option>
                                        @endforeach
                                    </select>
                                        </div>
                                </div>
                                <hr>
                                <div class="form-group col-sm-12">
                                    <label for="descricao" class=" form-control-label">Descrição</label>
                                    <input type="text" id="descricao" name="Descricao" placeholder=""
                                        class="form-control" wire:model.lazy='lancamento.Descricao'>
                                    <span class="oculto badge badge-danger">Informação obrigatória</span>
                                </div>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="form-group col-sm-12">
                                            <label for="contadebito" class=" form-control-label">
                                                <a href="{{ $this->lancamento->ContaDebitoID }}">Conta Debito</a>
                                            </label>
                                                <div wire:ignore>
                                            <select id="contadebito" wire:model.lazy='lancamento.ContaDebitoID'
                                                class="form-control select2" data-placeholder="Selecione">
                                                <option value="">Selecione</option>
                                                @foreach ($contas as $contaID => $contaDescricao)
                                                    <option value="{{ $contaID }}">
                                                        {{-- {{ $contaDescricao . ' <=>'. $contas->UsarDolar }}</option> --}}
                                                        {{ $contaDescricao  }}</option>

                                                @endforeach
                                            </select>
                                                </div>
                                            Usar dolar {{ $UsarDolarDebito = $this->lancamento->ContaDebito->PlanoConta->UsarDolar ?? "NÃO USAR" }}
                                            <input type="hidden" wire:model="UsarDolarDebito">

                                        </div>
                                        <div class="form-group col-sm-12">
                                                <div wire:ignore>
                                            <label for="contacredito" class=" form-control-label">
                                                <a href="{{ $this->lancamento->ContaCreditoID }}">Conta Crédito</a>
                                            </label>
                                            <select id="contacredito" wire:model.lazy='lancamento.ContaCreditoID'
                                                class="form-control select2" data-placeholder="Selecione">
                                                <option value="">Selecione</option>
                                                @foreach ($contas as $contaID => $contaDescricao)
                                                    <option value="{{ $contaID }}">
                                                        {{-- {{ $contaDescricao . ' <=>'. $contas->UsarDolar }}</option> --}}
                                                </div>
                                                          {{ $contaDescricao  }}</option>
                                                @endforeach
                                            </select>
                                            Usar dolar {{ $UsarDolarCredito = $this->lancamento->ContaCredito->PlanoConta->UsarDolar ?? 'NÃO USAR' }}
                                            <input type="hidden" wire:model="UsarDolarCredito">
                                        </div>
                                    </div>
                                </div>

                                        @push('scripts')
                                        <script>
                                            document.addEventListener('livewire:load', function() {
                                                function initModalSelect2(){
                                                    const parent = $('#editarLancamentoModal');
                                                    ['#novaEmpresaID','#historicoID','#contadebito','#contacredito'].forEach(id => {
                                                        const el = $(id);
                                                        if(el.length){
                                                            if(el.hasClass('select2-hidden-accessible')){ el.select2('destroy'); }
                                                            el.select2({
                                                                dropdownParent: parent.length ? parent : undefined,
                                                                theme: 'bootstrap-5',
                                                                width: '100%',
                                                                allowClear: true,
                                                                placeholder: el.data('placeholder') || 'Selecione'
                                                            }).off('change.sync').on('change.sync', function(e){
                                                                const val = $(this).val();
                                                                if(this.id === 'historicoID') {
                                                                    Livewire.emitTo('lancamento.editar-lancamento','selectHistorico', val);
                                                                } else if(this.id === 'contadebito') {
                                                                    Livewire.emitTo('lancamento.editar-lancamento','changeContaDebitoID', val);
                                                                } else if(this.id === 'contacredito') {
                                                                    Livewire.emitTo('lancamento.editar-lancamento','changeContaCreditoID', val);
                                                                } else if(this.id === 'novaEmpresaID') {
                                                                    Livewire.emitTo('lancamento.editar-lancamento','changeEmpresaID', val);
                                                                }
                                                            });
                                                        }
                                                    });
                                                }
                                                // Inicializa ao abrir modal (evento já disparado no extrato)
                                                window.addEventListener('abrir-modal', function(){
                                                    setTimeout(initModalSelect2, 250);
                                                });
                                                // Re-init após updates Livewire
                                                Livewire.hook('message.processed', (m,c)=>{
                                                    if($('#editarLancamentoModal').hasClass('show')){
                                                        initModalSelect2();
                                                    }
                                                });
                                            });
                                        </script>
                                        @endpush
                                <div class="form-group col-sm-3">
                                    <label for="datacontabilidade" class=" form-control-label">Data
                                        Contabilidade</label>
                                    <input type="date" id="datacontabilidade" class="form-control"
                                        wire:model.lazy="lancamento.DataContabilidade">
                                    <span class="oculto badge badge-danger">Informação obrigatória</span>
                                </div>


                                <div class="form-group col-sm-2">
                                    <label for="valor" class=" form-control-label">Valor</label>
                                    <input type="text" id="valor" name="Valor" placeholder="R$"
                                        class="form-control required" wire:model="lancamento.Valor" autocomplete="off">
                                    <span class="oculto badge badge-danger">Informação obrigatória</span>
                                </div>


                                    @if($UsarDolarDebito == 1 || $UsarDolarCredito == 1)
                                        <div class="form-group col-sm-2">
                                            <label for="valorquantidadedolar" class=" form-control-label">Valor quantidade dolar</label>
                                            <input type="text" id="valorquantidadedolar" name="valorquantidadedolar" placeholder="US$"
                                                class="form-control  money" wire:model.lazy="lancamento.ValorQuantidadeDolar">
                                            <span class="oculto badge badge-danger">Informação não obrigatória</span>
                                        </div>
                                    @endif




                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Fechar</button>
                                    {{-- <button type="submit" wire:click="acao('limpar')" class="btn btn-seconday">Limpar</button> --}}


                                    <button type="submit" class="btn btn-primary"
                                    @if($errors->has('ContaDebitoID')) disabled @endif>
                                Salvar Lançamento
                            </button>

                            @if ($lancamento->ID)
                                <button type="button"
                                        onclick="confirmar(true)"
                                        class="btn btn-warning"
                                        @if($errors->has('ContaDebitoID')) disabled @endif>
                                    Salvar Como Novo
                                </button>
                            @endif

                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="tab-pane fade @if ($currentTab == 'comentario') show active @endif" id="comentarios"
                    role="tabpanel" aria-labelledby="comentarios-tab">
                    <div class="card">
                        <div class="card-body">
                            <form wire:submit.prevent='salvarComentario'>
                                <div class="col-sm-12">
                                    <label for="comentario">Inserir novo comentário</label>
                                    <input type="text" class="form-control" wire:model="comentario">
                                </div>
                                <div class="col-sm-12 mt-3">
                                    <button type="submit" class="btn btn-primary">Inserir novo comentário</button>
                                </div>
                            </form>
                        </div>
                        <div class="card-body">
                            <p>
                                @if ($comentarios)
                                    @foreach ($comentarios as $comentario)
                                        <li>{{ $comentario->Descricao }} <br />Em
                                            {{ $comentario->Created->format('d/m/Y H:i:s') }} | Por:
                                            {{ $comentario->user->name ?? null}}</li>
                                    @endforeach
                                @endif
                            </p>
                        </div>

                    </div>
                </div>
                <div class="tab-pane fade @if ($currentTab == 'arquivo') show active @endif" id="arquivos"
                    role="tabpanel" aria-labelledby="arquivos-tab">
                    @livewire('lancamento.arquivo-lancamento', ['lancamento_id' => $lancamento->ID])
                </div>
                <!-- Removida a aba Troca Empresa: agora operação inline no formulário principal -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function(){
    function initTrocaInline(){
        const btnToggle = document.getElementById('btnToggleTrocaInline');
        const container = document.getElementById('trocaEmpresaInline');
        const btnCancelar = document.getElementById('btnCancelarTrocaInline');
        const selectsTroca = ['#inlineNovaEmpresa','#inlineContaDebito','#inlineContaCredito'];
        if(!btnToggle || !container) return; // nada a fazer

        function initSelects(){
            const parent = $('#editarLancamentoModal');
            selectsTroca.forEach(id => {
                const $el = $(id);
                if($el.length && $.fn.select2){
                    if($el.hasClass('select2-hidden-accessible')){ $el.select2('destroy'); }
                    $el.select2({
                        dropdownParent: parent.length ? parent : undefined,
                        theme: 'bootstrap-5', width: '100%', allowClear:true,
                        placeholder: $el.data('placeholder') || 'Selecione'
                    });
                }
            });
        }
        function resetContasPlaceholders(){
            ['inlineContaDebito','inlineContaCredito'].forEach(id => {
                const sel = document.getElementById(id);
                if(sel){ sel.innerHTML = '<option value="">Selecione empresa</option>'; }
            });
        }
        if(!container.dataset.bound){
            btnToggle.addEventListener('click', () => {
                const show = container.style.display === 'none';
                container.style.display = show ? 'block' : 'none';
                if(show){
                    initSelects();
                    // Se já há empresa do lançamento, pré-seleciona destino igual (usuário altera se quiser)
                    const origem = document.getElementById('novaEmpresaID');
                    const destino = document.getElementById('inlineNovaEmpresa');
                    if(origem && destino && !destino.value){
                        destino.value = origem.value || '';
                        destino.dispatchEvent(new Event('change', {bubbles:true}));
                    }
                }
            });
            btnCancelar?.addEventListener('click', () => { container.style.display='none'; });
            container.dataset.bound = '1';
        }
        const novaEmpSel = document.getElementById('inlineNovaEmpresa');
        if(novaEmpSel && !novaEmpSel.dataset.bound){
            novaEmpSel.addEventListener('change', async function(){
                const empId = this.value;
                ['inlineContaDebito','inlineContaCredito'].forEach(id => {
                    const sel = document.getElementById(id); if(sel){ sel.innerHTML='<option value="">Carregando...</option>'; }
                });
                if(!empId){ resetContasPlaceholders(); return; }
                try {
                    const resp = await fetch('/empresa/' + empId + '/contas-grau5');
                    if(!resp.ok) throw new Error('Falha ao buscar contas');
                    const json = await resp.json();
                    const data = json.data || {};
                    const options = ['<option value="">Selecione</option>'];
                    Object.keys(data).forEach(k => options.push('<option value="'+k+'">'+data[k]+'</option>'));
                    ['inlineContaDebito','inlineContaCredito'].forEach(id => {
                        const sel = document.getElementById(id);
                        if(sel){ sel.innerHTML = options.join(''); }
                    });
                    initSelects();
                } catch(e){
                    console.warn(e);
                    ['inlineContaDebito','inlineContaCredito'].forEach(id => {
                        const sel = document.getElementById(id);
                        if(sel){ sel.innerHTML = '<option value="">Erro ao carregar</option>'; }
                    });
                }
            });
            novaEmpSel.dataset.bound = '1';
        }
        // Reinit quando modal abrir novamente
        window.addEventListener('abrir-modal', () => {
            if(container.style.display !== 'none'){ setTimeout(initSelects,150); }
        });
    }
    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', initTrocaInline);
    } else {
        initTrocaInline();
    }
})();
</script>
@endpush
