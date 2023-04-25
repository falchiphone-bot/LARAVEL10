<div>
    {{-- Success is as dangerous as failure. --}}
    <div class="card">
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
                        @foreach ($contasnovas as $contaID => $contaDescricao)
                            <option value="{{ $contaID }}">{{ $contaDescricao }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-12">
                    <label for="novacontacredito">Conta Crédito</label>
                    <select id="novacontacredito" class="form-control select2" wire:model='novacontacredito'>
                        @foreach ($contasnovas as $contaID => $contaDescricao)
                            <option value="{{ $contaID }}">{{ $contaDescricao }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-12 mt-3">
                    <button class="btn btn-primary" type="submit">Transferir Lançamento</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('styles')
    <!-- Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endpush
@push('scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js"></script>
    <script>
        $(document).ready(function() {
            //inicio-empresas
            function select2() {
                $('.select2').select2({
                    theme: 'bootstrap-5'
                });
            }
            $('#novacontadebito').on('change', function(e) {
                @this.set('novacontadebito',e.target.value);
            });
            $('#novacontacredito').on('change', function(e) {
                @this.set('novacontacredito',e.target.value);
            });
            window.livewire.on('select2', () => {
                select2();
            });
            // select2();
            //fim-empresa
        });
    </script>
@endpush
