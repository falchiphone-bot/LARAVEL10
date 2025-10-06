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
                    <li class="nav-item" role="presentation">
                        <button class="nav-link @if ($currentTab == 'troca-empresa') active @endif" id="troca-empresa-tab"
                            data-bs-toggle="tab" data-bs-target="#troca-empresa" type="button" role="tab"
                            aria-controls="troca-empresa" aria-selected="false"
                            wire:click="sessionTab('troca-empresa')">Troca
                            Empresa</button>
                    </li>
                @endif
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade @if ($currentTab == 'lancamento') show active @endif" id="lancamento"
                    role="tabpanel" aria-labelledby="lancamento-tab">
                    <div class="card-body">
                        <form wire:submit.prevent="salvarLancamento()" id="form-lancamento">
                            <div class="row">
                                @if ($empresa)
                                    <div class="col-sm-12">
                                        <label for="empresaid">Nova Empresa</label>
                                            <div wire:ignore>
                                        <select wire:model='lancamento.EmpresaID'
                                            id="novaEmpresaID" class="form-control select2" data-placeholder="Selecione">
                                            <option value="">Selecione</option>
                                            @foreach ($empresas as $empresaID => $empresaDescricao)
                                                <option value="{{ $empresaID }}">{{ $empresaDescricao }}</option>
                                            @endforeach
                                        </select>
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
                <div class="tab-pane fade @if ($currentTab == 'troca-empresa') show active @endif" id="troca-empresa"
                    role="tabpanel" aria-labelledby="troca-empresa-tab">
                    @livewire('lancamento.troca-empresa', ['lancamento_id' => $lancamento->ID], key('troca-empresa-'.$lancamento->ID))
                </div>
            </div>
        </div>
    </div>
</div>
