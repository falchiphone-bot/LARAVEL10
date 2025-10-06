<div id="trocaEmpresaWrapper" style="background:#000;padding:6px;border:1px solid #222;border-radius:6px;">
    <style>
        /* Tema preto (dark) somente dentro da aba Troca Empresa */
        #editarLancamentoModal #trocaEmpresaWrapper,
        #editarLancamentoModal #trocaEmpresaWrapper .card,
        #editarLancamentoModal #trocaEmpresaWrapper .card-body { background:#000 !important; }
        #trocaEmpresaWrapper .card { border-color:#222 !important; box-shadow:0 0 0 1px #333 inset; }
        #trocaEmpresaWrapper label { font-weight:600; color:#f2f2f2; }
        #trocaEmpresaWrapper select.form-control,
        #trocaEmpresaWrapper select { background:#111 !important; border:1px solid #444 !important; color:#f5f5f5 !important; }
        #trocaEmpresaWrapper select:focus { background:#181818 !important; border-color:#777 !important; box-shadow:0 0 0 .15rem rgba(255,255,255,.25) !important; }
        /* Select2 containers */
        #trocaEmpresaWrapper .select2-container .select2-selection--single { background:#111 !important; border:1px solid #444 !important; height:38px; }
        #trocaEmpresaWrapper .select2-container .select2-selection--single .select2-selection__rendered { line-height:36px; color:#f5f5f5 !important; }
        #trocaEmpresaWrapper .select2-container--default .select2-selection--single .select2-selection__arrow { height:36px; }
        #trocaEmpresaWrapper .alert { background:#111 !important; color:#ddd; border-color:#333; }
        #trocaEmpresaWrapper button.btn-primary { background:#111; border-color:#444; color:#fff; }
        #trocaEmpresaWrapper button.btn-primary:hover { background:#1b1b1b; border-color:#666; }
        #trocaEmpresaWrapper .form-control:disabled { opacity:.85; }
        #trocaEmpresaWrapper .placeholder-msg { background:#111;border:1px dashed #444;padding:8px 10px;border-radius:4px;color:#ccc;font-size:.875rem; }
        #trocaEmpresaWrapper option { background:#111; color:#f5f5f5; }
        /* Barra de rolagem escura */
        #trocaEmpresaWrapper ::-webkit-scrollbar { width:10px; }
        #trocaEmpresaWrapper ::-webkit-scrollbar-track { background:#0a0a0a; }
        #trocaEmpresaWrapper ::-webkit-scrollbar-thumb { background:#333; border-radius:6px; }
        #trocaEmpresaWrapper ::-webkit-scrollbar-thumb:hover { background:#444; }
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
                <div class="col-sm-12">
                    <label for="empresaid">Nova Empresa</label>
                    <select wire:model='novaempresa' wire:change="empresaSelecionada" name="empresaid" id="empresaid"
                        class="form-control" aria-label="Selecionar nova empresa">
                        <option value="">Selecione</option>
                        @foreach ($empresas as $empresaID => $empresaDescricao)
                            <option value="{{ $empresaID }}">{{ $empresaDescricao }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-12">
                    <label for="novacontadebito">Conta Debito</label>
                    <select id="novacontadebito" class="form-control select2" wire:model='novacontadebito' aria-label="Selecionar conta débito">
                        <option value="">Selecione</option>
                        @foreach ($contasnovas as $contaID => $contaDescricao)
                            <option value="{{ $contaID }}">{{ $contaDescricao }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-12">
                    <label for="novacontacredito">Conta Crédito</label>
                    <select id="novacontacredito" class="form-control select2" wire:model='novacontacredito' aria-label="Selecionar conta crédito">
                        <option value="">Selecione</option>
                        @foreach ($contasnovas as $contaID => $contaDescricao)
                            <option value="{{ $contaID }}">{{ $contaDescricao }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-12 mt-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button class="btn btn-primary" type="submit">Transferir Lançamento</button>
                </div>
            </form>
        </div>
    </div>
</div>
