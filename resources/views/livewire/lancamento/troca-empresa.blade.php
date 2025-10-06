<div id="trocaEmpresaWrapper" style="background:#e0f2ff;padding:6px;border:1px solid #8cc7ff;border-radius:6px;">
    <style>
        /* Escopo apenas dentro da aba Troca Empresa - alta especificidade e !important para vencer Bootstrap/Select2 */
        #editarLancamentoModal #trocaEmpresaWrapper,
        #editarLancamentoModal #trocaEmpresaWrapper .card,
        #editarLancamentoModal #trocaEmpresaWrapper .card-body { background:#e0f2ff !important; }
        #trocaEmpresaWrapper .card { border-color:#8cc7ff !important; box-shadow:0 0 0 1px #b9e2ff inset; }
        #trocaEmpresaWrapper label { font-weight:600; color:#003153; }
        #trocaEmpresaWrapper select.form-control,
        #trocaEmpresaWrapper select { background:#cfe8ff !important; border:1px solid #63b4ff !important; color:#003153 !important; }
        #trocaEmpresaWrapper select:focus { background:#bfe0ff !important; border-color:#2795ff !important; box-shadow:0 0 0 .15rem rgba(39,149,255,.35) !important; }
        /* Select2 containers */
        #trocaEmpresaWrapper .select2-container .select2-selection--single { background:#cfe8ff !important; border:1px solid #63b4ff !important; height:38px; }
        #trocaEmpresaWrapper .select2-container .select2-selection--single .select2-selection__rendered { line-height:36px; color:#003153 !important; }
        #trocaEmpresaWrapper .select2-container--default .select2-selection--single .select2-selection__arrow { height:36px; }
        #trocaEmpresaWrapper button.btn-primary { background:#0d6efd; border-color:#0d6efd; }
        #trocaEmpresaWrapper button.btn-primary:hover { background:#0b5ed7; }
        #trocaEmpresaWrapper .form-control:disabled { opacity:.85; }
        #trocaEmpresaWrapper .placeholder-msg { background:#fff;border:1px dashed #63b4ff;padding:8px 10px;border-radius:4px;color:#003153;font-size:.875rem; }
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
