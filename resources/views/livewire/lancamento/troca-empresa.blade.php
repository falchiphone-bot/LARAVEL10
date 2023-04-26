<div>
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
                        class="form-control">
                        <option value="">Selecione</option>
                        @foreach ($empresas as $empresaID => $empresaDescricao)
                            <option value="{{ $empresaID }}">{{ $empresaDescricao }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-12">
                    <label for="novacontadebito">Conta Debito</label>
                    <select id="novacontadebito" class="form-control select2" wire:model='novacontadebito'>
                        <option value="">Selecione</option>
                        @foreach ($contasnovas as $contaID => $contaDescricao)
                            <option value="{{ $contaID }}">{{ $contaDescricao }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-12">
                    <label for="novacontacredito">Conta Crédito</label>
                    <select id="novacontacredito" class="form-control select2" wire:model='novacontacredito'>
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
