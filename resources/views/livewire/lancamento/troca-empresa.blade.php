<div id="trocaEmpresaWrapper" style="background:transparent;padding:4px;border:0;border-radius:4px;">
    <style>
        /* Tema azul claro consistente com restante do modal */
        #editarLancamentoModal #trocaEmpresaWrapper .card { background:#d5ecff !important; border:1px solid #8fc2ed; }
        #editarLancamentoModal #trocaEmpresaWrapper .card-body { background:transparent !important; }
        #trocaEmpresaWrapper label { font-weight:600; color:#063a60; }
        #trocaEmpresaWrapper select.form-control,
        #trocaEmpresaWrapper select { background:#ffffff !important; border:1px solid #6bb2ec !important; color:#043254 !important; }
        #trocaEmpresaWrapper select:focus { background:#f0f9ff !important; border-color:#0d5ca8 !important; box-shadow:0 0 0 .15rem rgba(13,92,168,.25) !important; }
        /* Select2 containers ajustes */
        #trocaEmpresaWrapper .select2-container { z-index: 9999; }
        #trocaEmpresaWrapper .select2-container .select2-selection--single { background:#ffffff !important; border:1px solid #6bb2ec !important; height:38px; }
        #trocaEmpresaWrapper .select2-container .select2-selection--single .select2-selection__rendered { line-height:36px; color:#043254 !important; }
        #trocaEmpresaWrapper .select2-dropdown { z-index: 10000; border:1px solid #0d5ca8; }
        #trocaEmpresaWrapper .select2-results__option--highlighted { background:#0d5ca8 !important; color:#fff !important; }
        #trocaEmpresaWrapper .alert { background:#fff !important; color:#063a60; border-color:#7fb6e8; }
        #trocaEmpresaWrapper button.btn-primary { background:#0d5ca8; border-color:#0d5ca8; color:#fff; }
        #trocaEmpresaWrapper button.btn-primary:hover { background:#0b4c88; }
        #trocaEmpresaWrapper .placeholder-msg { background:#ffffff;border:1px dashed #0d5ca8;padding:8px 10px;border-radius:4px;color:#063a60;font-size:.875rem; }
        #trocaEmpresaWrapper option { background:#ffffff; color:#043254; }
        /* Scrollbar leve */
        #trocaEmpresaWrapper ::-webkit-scrollbar { width:10px; }
        #trocaEmpresaWrapper ::-webkit-scrollbar-track { background:#c2e4ff; }
        #trocaEmpresaWrapper ::-webkit-scrollbar-thumb { background:#0d5ca8; border-radius:6px; }
        #trocaEmpresaWrapper ::-webkit-scrollbar-thumb:hover { background:#0b4c88; }
    </style>
    {{-- Success is as dangerous as failure. --}}
    <div class="card">
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
        <div class="card-body">
            <form wire:submit.prevent='transferirLancamento'>
                <div class="col-sm-12 mb-3">
                    <label for="empresaid">Nova Empresa</label>
                    @if($empresas && count($empresas))
                        <select wire:model='novaempresa' wire:change="empresaSelecionada" name="empresaid" id="empresaid"
                            class="form-control" aria-label="Selecionar nova empresa">
                            <option value="">Selecione</option>
                            @foreach ($empresas as $empresaID => $empresaDescricao)
                                <option value="{{ $empresaID }}">{{ $empresaDescricao }}</option>
                            @endforeach
                        </select>
                    @else
                        <div class="placeholder-msg">Nenhuma empresa disponível para este usuário. <button type="button" class="btn btn-sm btn-outline-light ms-2" wire:click="refreshData">Recarregar</button></div>
                    @endif
                    <small class="text-muted d-block mt-1">Debug: empresas={{ is_countable($empresas)?count($empresas):'n/a' }} lancamento_id={{ $lancamento_id ?? 'null' }}</small>
                </div>

                <div class="col-sm-12 mb-3">
                    <label for="novacontadebito">Conta Debito</label>
                    @if($contasnovas && count($contasnovas))
                        <select id="novacontadebito" class="form-control select2" wire:model='novacontadebito' aria-label="Selecionar conta débito">
                            <option value="">Selecione</option>
                            @foreach ($contasnovas as $contaID => $contaDescricao)
                                <option value="{{ $contaID }}">{{ $contaDescricao }}</option>
                            @endforeach
                        </select>
                    @else
                        <div class="placeholder-msg">Selecione uma empresa para carregar contas débito.</div>
                    @endif
                </div>

                <div class="col-sm-12 mb-3">
                    <label for="novacontacredito">Conta Crédito</label>
                    @if($contasnovas && count($contasnovas))
                        <select id="novacontacredito" class="form-control select2" wire:model='novacontacredito' aria-label="Selecionar conta crédito">
                            <option value="">Selecione</option>
                            @foreach ($contasnovas as $contaID => $contaDescricao)
                                <option value="{{ $contaID }}">{{ $contaDescricao }}</option>
                            @endforeach
                        </select>
                    @else
                        <div class="placeholder-msg">Selecione uma empresa para carregar contas crédito.</div>
                    @endif
                </div>
                <div class="col-sm-12 mt-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button class="btn btn-primary" type="submit">Transferir Lançamento</button>
                </div>
            </form>
        </div>
    </div>
</div>
