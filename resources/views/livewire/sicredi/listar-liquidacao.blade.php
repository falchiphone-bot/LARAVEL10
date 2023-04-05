<div>
    <div class="card">
        <div class="badge bg-primary text-wrap" style="width: 100%;">
            LISTA DE LIQUIDAÇÃO DE BOLETOS NO DIA {{ $consultaDiaDisplay }}
        </div>


        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @elseif (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif


            <div class="card-body">
                <section>
                    <div class="row mt-2">

                        <div class="col-3">
                            <div class="card">
                                <div class="card-header">
                                    <label for="contaCobranca">Carteira de Cobrança</label>
                                </div>
                                <div class="card-body">
                                    <select class="form-control" id="contaCobranca" wire:model='contaCobranca' wire:model='contaCobranca'>
                                        <option value="">Selecione uma conta</option>
                                        @foreach ($contasCobrancas as $idContaCobranca => $conta)
                                            <option value="{{ $idContaCobranca }}">{{ $conta }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-3">
                            <div class="card">
                                <div class="card-header">
                                    <label for="consultaDia">Consultar do Dia</label>
                                </div>
                                <div class="card-body">
                                    <input type="date" class="form-control" value="{{ $consultaDia }}"
                                        id="consultaDia" wire:model='consultaDia'>
                                </div>
                            </div>
                        </div>

                        <div class="col-3">
                            <div class="card">
                                <div class="card-header">
                                    <label for="consultaDia">Quantidade Liquidado</label>
                                </div>
                                <div class="card-body">
                                    <p>
                                        {{ count($consulta['items'] ?? null) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-3">
                            <div class="card">
                                <div class="card-header">
                                    <label for="consultaDia">Total Liquidado</label>
                                </div>
                                <div class="card-body">
                                    @php
                                        $totalLiquidado = 0;
                                        foreach ($consulta['items'] as $soma) {
                                            $totalLiquidado += $soma['valorLiquidado'];
                                        }
                                    @endphp
                                    <p>
                                        {{ number_format($totalLiquidado, 2, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="table-responsive mt-2">
                    <table class="table" style="background-color: rgb(247, 247, 213);">
                        <thead>
                            <tr>
                                {{-- <th scope="col" class="px-6 py-4">cooperativa</th> --}}
                                <th scope="col" class="px-6 py-4">nossoNumero</th>
                                <th scope="col" class="px-6 py-4">Conta</th>
                                {{-- <th scope="col" class="px-6 py-4">cooperativaPostoBeneficiario</th> --}}
                                <th scope="col" class="px-6 py-4">seuNumero</th>
                                {{-- <th scope="col" class="px-6 py-4">tipoCarteira</th> --}}
                                <th scope="col" class="px-6 py-4">dataPagamento</th>
                                <th scope="col" class="px-6 py-4">valor</th>
                                <th scope="col" class="px-6 py-4">valorLiquidado</th>
                                <th scope="col" class="px-6 py-4">jurosLiquido</th>
                                <th scope="col" class="px-6 py-4">descontoLiquido</th>
                                <th scope="col" class="px-6 py-4">multaLiquida</th>
                                <th scope="col" class="px-6 py-4">abatimentoLiquido</th>
                                <th scope="col" class="px-6 py-4">tipoLiquidacao</th>
                            </tr>
                        </thead>

                        <tbody>
                            @if ($consulta)
                                @foreach ($consulta['items'] as $item)
                                    <tr>
                                        {{-- <td>{{ $item['cooperativa'] }}</td> --}}
                                        <td>{{ $item['nossoNumero'] }}</td>
                                        <td>{{ $item['codigoBeneficiario'] }}</td>
                                        {{-- <td>{{ $item['cooperativaPostoBeneficiario'] }}</td> --}}
                                        <td>{{ $item['seuNumero'] }}</td>
                                        {{-- <td>{{ $item['tipoCarteira'] }}</td> --}}
                                        <td>{{ $item['dataPagamento'] }}</td>
                                        <td>{{ $item['valor'] }}</td>
                                        <td>{{ $item['valorLiquidado'] }}</td>
                                        <td>{{ $item['jurosLiquido'] }}</td>
                                        <td>{{ $item['descontoLiquido'] }}</td>
                                        <td>{{ $item['multaLiquida'] }}</td>
                                        <td>{{ $item['abatimentoLiquido'] }}</td>
                                        <td>{{ $item['tipoLiquidacao'] }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
