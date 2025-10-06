<div id="trocaEmpresaWrapper">
    <style>
        /* Escopo apenas dentro da aba Troca Empresa */
        #trocaEmpresaWrapper .card { background:#eaf4ff; border-color:#b6dbff; }
        #trocaEmpresaWrapper .card-body { background:#eaf4ff; }
        #trocaEmpresaWrapper select.form-control,
        #trocaEmpresaWrapper select { background:#d7ebff !important; border:1px solid #94c9ff; color:#003153; }
        #trocaEmpresaWrapper select:focus { background:#c4e2ff !important; border-color:#4aa3ff; box-shadow:0 0 0 .15rem rgba(13,110,253,.15); }
        #trocaEmpresaWrapper label { font-weight:600; color:#003153; }
        #trocaEmpresaWrapper .alert { background:#fff; }
        #trocaEmpresaWrapper button.btn-primary { background:#0d6efd; border-color:#0d6efd; }
        #trocaEmpresaWrapper button.btn-primary:hover { background:#0b5ed7; }
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
