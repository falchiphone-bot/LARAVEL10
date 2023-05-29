@csrf
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-6">

                <label for="Descricao">Nome</label>
                <input class="form-control @error('Descricao') is-invalid @else is-valid @enderror" name="Descricao"
                    type="text" id="Descricao" value="{{ $historico->Descricao ?? null }}">
                @error('Descricao')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-sm-6">

                <label for="PIX">PIX ou Complemento</label>
                <input class="form-control" name="PIX"
                    type="text" id="Pix" value="{{ $historico->PIX ?? null }}">
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <label for="EmpresaID">Empresas</label>
                <select name="EmpresaID" id="EmpresaID" class="form-control" wire:model='empresaID'>
                    <option value="">Selecione a conta de debito</option>
                    @foreach ($empresas->sort() as $empresaID => $empresaDescricao)
                        <option @selected($historico?->EmpresaID == $empresaID) value="{{ $empresaID }}">{{ $empresaDescricao }}
                        </option>
                    @endforeach
                </select>
                @error('EmpresaID')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <label for="ContaDebitoID">Conta débito</label>
                <select name="ContaDebitoID" id="ContaDebitoID" class="form-control" wire:model='contaDebitoID'>
                    <option value="">Selecione a conta de debito</option>
                    @foreach ($contas as $contaID => $contaDescricao)
                        <option @selected($historico?->ContaDebitoID == $contaID) value="{{ $contaID }}">{{ $contaDescricao }}</option>
                    @endforeach
                </select>
                @error('ContaDebitoID')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- {{ $Historico->ContaDebito->PlanoConta->Descricao ?? null }} --}}


        <div class="row">
            <div class="col-12">
                <label for="ContaCreditoID">Conta Crédito</label>
                <select name="ContaCreditoID" id="ContaCreditoID" class="form-control" wire:model='contaCreditoID'>
                    <option value="">Selecione a conta de crédito</option>
                    @foreach ($contas as $contaID => $contaDescricao)
                        <option @selected($historico?->ContaCreditoID == $contaID) value="{{ $contaID }}">{{ $contaDescricao }}</option>
                    @endforeach
                </select>
                @error('ContaCreditoID')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-sm-6">
            <button class="btn btn-primary">Salvar</button>
            <a href="{{ route('Historicos.index') }}" class="btn btn-warning">Retornar para lista</a>
        </div>
    </div>

</div>
@push('scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('select').select2();

            $("#EmpresaID").change(function (e){
                @this.set('empresaID',e.target.value);
            });
            $("#ContaDebitoID").change(function (e){
                @this.set('contaDebitoID',e.target.value);
            });
            $("#contaCreditoID").change(function (e){
                @this.set('contaCreditoID',e.target.value);
            });


        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Livewire.hook('component.initialized', (component) => {})
            // Livewire.hook('element.initialized', (el, component) => {})
            // Livewire.hook('element.updating', (fromEl, toEl, component) => {})
            // Livewire.hook('element.updated', (el, component) => {})
            // Livewire.hook('element.removed', (el, component) => {})
            // Livewire.hook('message.sent', (message, component) => {})
            // Livewire.hook('message.failed', (message, component) => {})
            // Livewire.hook('message.received', (message, component) => {})
            Livewire.hook('message.processed', (message, component) => {
                $('select').select2();
            })
        });
    </script>
@endpush
