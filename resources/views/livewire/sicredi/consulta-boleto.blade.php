<div>
    <div class="py-5 bg-light">

        <div class="container">
            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;">
                    Consulta de Boletos por Nosso Numero
                </div>


                <div class="card-body">
                    @if (session('message'))
                        <div class="alert alert-success">
                            {{ session('message') }}
                        </div>
                    @endif


                    <div class="card-body">
                        <div class="row mt-2">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{!! $error !!}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="col-2">
                                <div class="card">
                                    <div class="card-header">
                                        <label for="nosso_numero">Nosso Número</label>
                                    </div>
                                    <div class="card-body">
                                        <input type="text" class="form-control" value="" id="nosso_numero"
                                            wire:model.lazy='nosso_numero'>
                                    </div>
                                </div>
                            </div>

                            <div class="col-8">
                                <div class="card">
                                    <div class="card-header">
                                        <label for="contaCobranca">Carteira de Cobrança</label>
                                    </div>
                                    <div class="card-body">
                                        <select class="form-control" id="contaCobrancaID"
                                            wire:model='contaCobrancaID'>
                                            <option value="">Selecione uma conta</option>
                                            @foreach ($contasCobrancas as $idContaCobranca => $conta)
                                                <option value="{{ $idContaCobranca }}">{{ $conta }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-3" wire:loading>
                                <span class="badge rounded-pill bg-info text-dark">Processando requisição ...</span>
                            </div>
                            <div class="col-12">
                                <button type="button" class="btn btn-primary" wire:click="buscar()">Buscar</button>
                            </div>
                        </div>

                        @if ($resultado['status'])
                            <div class="card">
                                <div class="card-header">
                                    Informações do Banco
                                </div>
                                <div class="card-body">
                                    <div class="col-sm-12 mt-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        Linha Digitavel: {{ $resultado['dados']['linhaDigitavel'] }}<br>
                                                        Codigo de Barras: {{ $resultado['dados']['codigoBarras'] }}<br>
                                                        Carteira: {{ $resultado['dados']['carteira'] }}<br>
                                                        SeuNumero: {{ $resultado['dados']['seuNumero'] }}<br>
                                                        NossoNumero: {{ $resultado['dados']['nossoNumero'] }}<br>
                                                        Pagador: {{ $resultado['dados']['pagador']['nome'] }}<br>
                                                        DataEmissao: {{ $resultado['dados']['dataEmissao'] }}<br>
                                                        Data Vencimento: {{ $resultado['dados']['dataVencimento'] }}<br>
                                                        Data Baixa: {{ $resultado['dados']['dataBaixa'] }}<br>
                                                        Valor Nominal: {{ $resultado['dados']['valorNominal'] }}<br>
                                                        Situacao: {{ $resultado['dados']['situacao'] }}<br>
                                                        Multa: {{ $resultado['dados']['multa'] }}<br>
                                                        Abatimento: {{ $resultado['dados']['abatimento'] }}<br>
                                                        Tipo de Juros: {{ $resultado['dados']['tipoJuros'] }}<br>
                                                        Juros: {{ $resultado['dados']['juros'] }}<br>
                                                        Dias Protesto: {{ $resultado['dados']['diasProtesto'] }}<br>
                                                        Validade Apos Vencimento: {{ $resultado['dados']['validadeAposVencimento'] }}<br>
                                                        Dias Negativacao: {{ $resultado['dados']['diasNegativacao'] }}<br>
                                                        Tipo Desconto: {{ $resultado['dados']['tipoDesconto'] }}<br>
                                                        Desconto Antecipacao: {{ $resultado['dados']['descontoAntecipacao'] }}<br>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
