<div>
    @csrf
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-4">
                    <label for="EmpresaID">Empresa</label>
                    <br>
                    {{ $this->empresa }}
                    <input type="hidden" name='EmpresaID' value="{{ $contaCobranca->id }}" >
                </div>

                <div class="col-2">
                    <label for="Conta">Conta</label>
                    <input type="number" name="conta" class="form-control" id="Conta"
                        value="{{ $contaCobranca->conta ?? null }}" required>
                    @error('conta')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-3">
                    <label for="agencia">Agência</label>
                    <input type="number" name="agencia" class="form-control" id="agencia"
                        value="{{ $contaCobranca->agencia ?? null }}" required>
                    @error('agencia')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>


                <div class="col-2">
                    <label for="posto">Posto</label>
                    <input type="number" name="posto" class="form-control" id="posto"
                        value="{{ $contaCobranca->posto ?? null }}" required>
                    @error('posto')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="associadobeneficiario">Associado Beneficiário</label>
                    <input type="text" name="associadobeneficiario" class="form-control" id="associadobeneficiario"
                        value="{{ $contaCobranca->associadobeneficiario ?? null }}" required>
                    @error('associadobeneficiario')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="token_conta">Token da Conta</label>
                    <input type="text" name="token_conta" class="form-control" id="token_conta"
                        value="{{ $contaCobranca->token_conta ?? null }}" required>
                    @error('token_conta')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="idDevSicredi">Contas de Desenvolvedores</label>
                    <select name="idDevSicredi" id="idDevSicredi" class="form-control" required>
                        <option value="">Selecione a conta do Desenvolvedor</option>
                        @foreach ($contasDev as $idDev => $DESENVOLVEDOR)
                            <option
                                @if ($contaCobranca ?? null) @selected($contaCobranca->idDevSicredi==$idDev) @endif
                                value="{{ $idDev }}">{{ $DESENVOLVEDOR }}</option>
                        @endforeach
                    </select>
                    @error('idDevSicredi')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="card mt-2">
                    <div class="card-header">
                        Históricos Crédito
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <label for="selectContaCredito">Histórico para Conta Credito</label>
                                <select name="Tarifa_Cobranca" id="selectContaCredito" class="form-control" required wire:model="historicoCredito">
                                    <option value="">Selecione o Histórico</option>
                                    @foreach ($historicos as $historicoID => $descricao)
                                        <option value="{{ $historicoID }}">{{ $descricao }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- @if ($historicoCredito) --}}
                            <div class="col-6">
                                <label for="">Conta Debito</label>
                                <br>
                                <strong>{{ $historicoCreditoContaDebito }}</strong>
                            </div>
                            <div class="col-6">
                                <label for="">Conta Credito</label>
                                <br>
                                <strong>{{ $historicoCreditoContaCredito }}</strong>
                            </div>
                            {{-- @endif --}}
                        </div>
                    </div>
                </div>


                <div class="card mt-2">
                    <div class="card-header">
                        Histórico Debito
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <label for="selectContaDebito">Histórico de Taxa</label>
                                <select name="Credito_Cobranca" id="selectContaDebito" class="form-control" required wire:model="historicoDebito">
                                    <option value="">Selecione o Histórico</option>
                                    @foreach ($historicos as $historicoID => $descricao)
                                        <option value="{{ $historicoID }}">{{ $descricao }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- @if ($historicoCredito) --}}
                            <div class="col-6">
                                <label for="">Conta Debito</label>
                                <br>
                                <strong>{{ $historicoDebitoContaDebito }}</strong>
                            </div>
                            <div class="col-6">
                                <label for="">Conta Credito</label>
                                <br>
                                <strong>{{ $historicoDebitoContaCredito }}</strong>
                            </div>
                            {{-- @endif --}}
                        </div>
                    </div>
                </div>

            </div>

        </div>
        <div class="card-footer">
            <div class="row mt-2">
                <div class="col-6">
                    <button class="btn btn-primary">Salvar</button>
                    <a href="{{ route('ContasCobranca.index') }}" class="btn btn-warning">Retornar para lista de
                        contas</a>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            //selectContaCredito
            window.initclienteDrop = () => {
                $('#selectContaCredito').select2();
            }
            initclienteDrop();
            $('#selectContaCredito').on('change', function(e) {
                livewire.emit('selectContaCredito', e.target.value);
            });
            window.livewire.on('select2', () => {
                initclienteDrop();
            });
            //selectContaCredito
            //selectContaDebito
            window.initclienteDrop = () => {
                $('#selectContaDebito').select2();
            }
            initclienteDrop();
            $('#selectContaDebito').on('change', function(e) {
                livewire.emit('selectContaDebito', e.target.value);
            });
            window.livewire.on('select2', () => {
                initclienteDrop();
            });
            //selectContaCredito

        });
    </script>
@endpush
