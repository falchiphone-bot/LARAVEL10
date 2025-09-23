@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
    <style>
    /* Foco vermelho para os selects nativos (conta e empresa) */
    #selConta:focus, #selConta:focus-visible,
    #selEmpresa:focus, #selEmpresa:focus-visible {
            border-color: #dc3545 !important; /* vermelho Bootstrap danger */
            box-shadow: 0 0 0 .2rem rgba(220, 53, 69, .25) !important;
            outline: none !important;
        }
        /* Foco vermelho para Select2 com tema Bootstrap 5 */
        .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .select2-container--bootstrap-5 .select2-selection:focus,
        .select2-container .select2-selection:focus {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 .2rem rgba(220, 53, 69, .25) !important;
            outline: none !important;
        }
    </style>
@endpush


    <div class="card">
        <div class="card-body">
            <div class="badge bg-secondary text-wrap" style="width: 100%;
              ;font-size: 16px; lign=˜Center˜">


                <div class="row">
                    <div class="row py-2">
                        <div class="col-2">
                            <a href="/PlanoContas/dashboard" class="btn btn-warning">Retornar para plano de contas</a>
                        </div>

                        <div class="col-2">
                            <a href="/PlanoContas/pesquisaavancada" class="btn btn-primary">Pesquisa avançada em
                                lançamentos</a>
                        </div>

                        <div class="col-2">
                            <button wire:click="editarLancamento('novo', {{ $this->selEmpresa }})"
                                class="btn btn-danger">Iniciar um novo
                                lançamento</button>
                        </div>

                        <div class="col-2">
                            <button onclick="alterarData()" class="btn btn-secondary">
                                Alterar data em múltiplos lançamentos
                            </button>
                        </div>
                        <div class="col-2">
                            <a class="btn btn-success" href="/Historicos">Históricos para lançamentos</a>
                        </div>

                        @can('LANCAMENTOS DOCUMENTOS - LISTAR')
                            <div class="col-2">
                                <a class="btn btn-primary" href="/LancamentosDocumentos">Documentos</a>
                            </div>
                        @endcan
                        @can('SOLICITACOES - LISTAR')
                            <div class="col-2">

                                <a class="btn btn-success" href="/lancamentos/solicitacoes">Solicitações para exclusão</a>


                            </div>
                        @endcan
                        <div class="col-2">
                            <a class="btn btn-success" href="/PlanoContas">Plano de Contas</a>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-6">
                        <select class="form-control select2" id="selEmpresa" wire:model="selEmpresa">
                            @foreach ($empresas as $empresa_id => $empresa_descricao)
                                <option @selected($selEmpresa == $empresa_id) value="{{ $empresa_id }}">
                                    {{ $empresa_descricao }}</option>

                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <select class="form-control select2" id="selConta" wire:model='selConta' aria-hidden="true">
                            <option value="0">Escolha uma conta</option>
                            @foreach ($contas as $conta)
                                <option @selected($selConta == $conta->ID) value="{{ $conta->ID }}">{{ $conta->Descricao }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

            </div>
        </div>



        <div class="badge bg-success text-wrap" style="width: 100%;
            font-size: 16px; lign=˜Center˜">

            @if ($errors->any())
                <div class="alert alert-danger mt-3">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{!! $error !!}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- <span>Conta Sel: {{ $Conta->ID }}</span> --}}
        <div class="card mt-3">
            <div class="card-header">
                <div class="badge bg-success text-wrap"
                    style="width: 100%;
             ;font-size: 16px; lign=˜Center˜">

                    Busca por periodo
                </div>

            </div>


            <div class="card-body card-block">
                <form id="idform" method="post" wire:submit.prevent="search">
                    <div class="row">
                        <div class="form-group col-sm-12 col-md-3">
                            <label for="de" class="pr-1  form-control-label">De</label>
                            <input type="date" value="" id="de" name="De" wire:model='De'
                                class="required form-control " autocomplete="off">
                        </div>
                        <div class="form-group col-sm-12 col-md-3">
                            <label for="ate" class="px-1  form-control-label">Até</label>
                            <input type="date" value="" id="ate" name="Ate" placeholder="Buscar até"
                                wire:model='Ate' class="required form-control " autocomplete="off">
                        </div>
                        <div class="form-group col-sm-12 col-md-3">
                            <label for="ate" class="px-1  form-control-label">Conferido/Saidas em geral</label>
                            <select name="Conferido" id="Conferido" class="form-control" wire:model='Conferido'>
                                <option value="">Todos</option>
                                <option value="true">Conferido</option>
                                <option value="false">Não conferido</option>
                                <option value="SaidasGeral">Saidas em geral</option>
                                <option value="EntradasGeral">Entradas em geral</option>
                                <option value="Transferencias">Transferencias</option>
                                <option value="SemDefinir">Sem definir</option>
                            </select>
                        </div>

                        <div class="form-group col-sm-12 col-md-3">
                            <label for="Notificacao" class="px-1  form-control-label">Notificação</label>
                            <select name="Notificacao" id="Notificacao" class="form-control"
                                wire:model='Notificacao'>
                                <option selected value="">Todos</option>
                                <option value="1">Notificar</option>
                                <option value="0">Não notificar</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-12 col-md-3">
                            <label for="de" class="pr-1  form-control-label">Buscar Descrição</label>
                            <input type="text" value="" id="descricao" class="form-control"
                                autocomplete="off" wire:model.lazy='Descricao'>
                        </div>
                        <div class="form-group col-sm-12 col-md-3">
                            <label for="de" class="pr-1  form-control-label">A partir De:</label>
                            <input type="date" value="" id="a_partir_de" class="form-control"
                                autocomplete="off" wire:model.lazy='DescricaoApartirDe'>
                        </div>
                        <div class="form-group col-sm-12 col-md-3">

                            <label for="data_bloqueio_conta" class="pr-1  form-control-label"
                                style="color: red;">Data Bloqueio Conta:</label>

                            <input type="date" value="" id="data_bloqueio_conta" class="form-control"
                                autocomplete="off" wire:model.defer='data_bloqueio_conta'
                                wire:change='updateDataBloqueioConta()' style="background-color: red; color: white">

                        </div>
                        <div class="form-group col-sm-12 col-md-3">
                            <label for="data_bloqueio_empresa" class="pr-1  form-control-label"
                                style="color: blue;">Data Bloqueio Empresa:</label>

                            <input type="date" value="" id="data_bloqueio_empresa" class="form-control"
                                autocomplete="off" wire:model.defer='data_bloqueio_empresa'
                                wire:change='updateDataBloqueioEmpresa()'
                                style="background-color: blue; color: white">

                        </div>
                    </div>
                </form>
            </div>

            <div class="card-footer">
                <div class="badge bg-warning text-wrap" style="width: 100%; ;font-size: 16px; lign=Center">
                    <button id="buscar" wire:click='search()' type="button" class="btn btn-primary btn-sm">
                        <i class="fa fa-dot-circle-o"></i>
                        Buscar informações e atualizar visualização
                    </button>

                    @can('LANCAMENTOS - CAIXAS GERAL')

                        <button title="Saldos Gabriel Magossi Falchi" type="button" class="btn-sm btn btn-outline-danger"
                            wire:click='contasGabrielMagossiFalchi()'>

                                <div class="card text-center" style="background-color: rgb(118, 14, 237); color: white;">
                                    <i class="cl-fa fa-check-square-o">Contas Gabriel Magossi Falchi</i>
                                </div>
                        </button>

                        @can('LANCAMENTOS - ATUALIZAR POUPANCA/AVENUE')
                            @if ($Lancamentos && count($Lancamentos))
                                @foreach ($Lancamentos as $lancament)
                                    @if ($lancament->AtualizarPoupancaAvenue)
                                        <button title="Saldos POUPANÇA/AVENUE" type="button" class="btn-sm btn btn-outline-success"
                                                wire:click='contasPoupancaAvenue'>
                                            <div class="card text-center" style="background-color: rgb(118, 14, 237); color: white;">
                                                <i class="cl-fa fa-check-square-o">Saldos POUPANÇA/AVENUE</i>
                                            </div>
                                        </button>
                                        @break {{-- Mostra o botão só uma vez, se houver pelo menos um true --}}
                                    @endif
                                @endforeach
                            @endif
                        @endcan


                        <button title="Saldos Gabriel Magossi Falchi por mês" type="button" class="btn-sm btn btn-outline-danger"
                            wire:click='contasGabrielMagossiFalchiMes()'>

                                <div class="card text-center" style="background-color: rgb(14, 237, 33); color: white;">
                                    <i class="cl-fa fa-check-square-o">Contas Gabriel Magossi Falchi - agrupar por mes</i>
                                </div>
                        </button>

                        <button title="Saldos Caio Cesar Magossi Falchi" type="button" class="btn-sm btn btn-outline-danger"
                        wire:click='contasCaioCesarMagossiFalchi()'>

                            <div class="card text-center" style="background-color: rgb(118, 14, 237); color: white;">
                                <i class="cl-fa fa-check-square-o">Contas Caio Cesar Magossi Falchi</i>
                            </div>
                    </button>

                        <button id="buscar" wire:click='searchSaidasGeral()' type="button"
                            class="btn btn-danger btn-sm">
                            <i class="fa fa-dot-circle-o"></i>Buscar lançamentos de saidas em geral
                        </button>

                        <button id="buscar" wire:click='searchSaidasGeralExcel()' type="button"
                                class="btn btn-secondaryr btn-sm">
                                <i class="fa fa-dot-circle-o"></i>Buscar lançamentos de saidas em geral e gerar arquivo EXCEL
                          </button>


                        <button id="buscar" wire:click='searchSaidasGeralSoma()' type="button"
                            class="btn btn-secondary btn-sm">
                            <i class="fa fa-dot-circle-o"></i>Buscar lançamentos de entradas e saidas em geral - CÁLCULOS
                        </button>

                        <button id="buscar" wire:click='searchEntradasGeral()' type="button"
                            class="btn btn-primary btn-sm">
                            <i class="fa fa-dot-circle-o"></i>Buscar lançamentos de entradas em geral
                        </button>

                        <button id="buscar" wire:click='searchSemDefinicao()' type="button"
                            class="btn btn-secondary btn-sm">
                            <i class="fa fa-dot-circle-o"></i>Buscar lançamentos de SEM DEFINIÇÃO
                        </button>

                        <button id="buscar" wire:click='searchEntradasGeralExcel()' type="button"
                            class="btn btn-secondary btn-sm">
                            <i class="fa fa-dot-circle-o"></i>Buscar lançamentos de entradas em geral e gerar arquivo EXCEL
                        </button>

                        <button id="buscar" wire:click='searchInvestimentos()' type="button"
                            class="btn btn-primary btn-sm">
                            <i class="fa fa-dot-circle-o"></i>Buscar lançamentos de investimentos
                        </button>
                        <button id="buscar" wire:click='searchTransferencias()' type="button"
                            class="btn btn-primary btn-sm">
                            <i class="fa fa-dot-circle-o"></i>Buscar lançamentos de transferências
                        </button>
                        <button id="buscar" wire:click='searchSemDefinir()' type="button"
                        class="btn btn-primary btn-sm">
                        <i class="fa fa-dot-circle-o"></i>Buscar lançamentos sem definição
                    </button>

                    @endcan

                    <button id="buscar" wire:click='searchPDF()' type="button" class="btn btn-danger btn-sm"
                        target="_blank">
                        <i class="fa fa-dot-circle-o"></i> Buscar informações e gerar PDF
                    </button>
                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                        <a class="btn btn-success" href="/lancamentos/ExportarExtratoExcel">Exportar lançamentos para
                            extrato por período e empresa selecionada no formato EXCEL</a>
                    </nav>
                </div>


                <div class="div-data-bloqueio form-group col-4" style="display: none;">
                    <label for="data-bloqueio" class="px-1  form-control-label">Data do bloqueio</label>
                    <input type="text" value="27/03/2023" id="data-bloqueio" placeholder="Informe uma data"
                        class="required form-control " autocomplete="off">
                </div>
                <div class="div-data-bloqueio form-group col-4" style="display: none;">
                    <button id="btn-salvar-bloqueio" type="button" class="btn btn-secondary btn-sm">
                        Salvar bloqueio
                    </button>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @elseif (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <div class="badge bg-success text-wrap" style="width: 100%;
             ;font-size: 16px; lign=˜Center˜">

            </div>
            <div class="card mt-4">
                <div class="row text-center" wire:loading>
                    <div class="spinner-border mx-auto mt-2" role="status">
                        <span class="sr-only"></span>
                    </div>
                </div>
                {{-- <div class="row text-center" wire:loading>
                    <div class="spinner-border mx-auto mt-2" role="statusSaidasGeral">
                        <span class="sr-only"></span>
                    </div>
                </div>
                <div class="row text-center" wire:loading>
                    <div class="spinner-border mx-auto mt-2" role="statusEntradasGeral">
                        <span class="sr-only"></span>
                    </div>
                </div> --}}

                <div class="card-header">
                    <div class="form-group col md-12">
                        <h3 class="content"> {{ $exibicao_pesquisa }}</h3>
                    </div>
                </div>
                <div class="card-body result">
                    <table class="table">
                        <thead class="thead" style="background-color: #00008B; color: white">
                            <tr>
                                <th></th>
                                <th></th>
                                <th colspan="2">Saldo Anterior</th>
                                <th>R$ {{ number_format($saldoAnterior, 2, ',', '.') }}</th>
                            </tr>
                            <tr>
                                <th></th>
                                <th></th>
                                <th colspan="2">Saldo Anterior em quantidade de dolares</th>
                                <th>US$ {{ number_format($saldoAnteriorDolar, 2, ',', '.') }}</th>
                            </tr>
                            <tr>
                                <th>Data</th>
                                <th></th>
                                <th style="width: 10%">Débito</th>
                                <th>Crédito</th>
                                <th style="width: 15%">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $saldo = $saldoAnterior;
                                $somatoria = $saldoAnterior;
                                $totalDebito = 0;
                                $totalCredito = 0;
                                $saldoDolar = $saldoAnteriorDolar;
                                $somatoriaDolar = $saldoAnteriorDolar;
                                $totalDebitoDolar = 0;
                                $totalCreditoDolar = 0;
                            @endphp
                            @if ($Lancamentos)
                                @foreach ($Lancamentos as $lancamento)
                                    <tr class="tr-{{ $lancamento->ID }} border-bottom-5 border-start-5">
                                        <td style="font-weight: bold; font-size: 1.2em;">

                                            {{ $lancamento->DataContabilidade->format('d/m/Y') }}
                                        </td>

                                        <td>

                                        </td>
                                        <td>
                                            {{-- //// extrato normal --}}
                                            @if ($Conta->ID == $lancamento->ContaDebitoID)
                                                <span style="color: blue;">
                                                    {{ number_format($lancamento->Valor, 2, ',', '.') }}
                                                </span>
                                                @if (!in_array($lancamento->ID, $listaSoma))
                                                    @php($totalDebito += $lancamento->Valor)
                                                    @php($saldo += $lancamento->Valor)
                                                    @php($somatoria += $lancamento->Valor)
                                                @endif
                                            @endif
                                            {{-- //// extrato normal --}}

                                            @if ($lancamento->ValorQuantidadeDolar > 0)
                                                <span style="color: rgb(255, 0, 0);">
                                                    Quantidade de dolares: {{ number_format($lancamento->ValorQuantidadeDolar, 2, ',', '.') }}
                                                </span>
                                                @if (!in_array($lancamento->ID, $listaSoma))
                                                    @php($totalDebitoDolar += $lancamento->ValorQuantidadeDolar)
                                                    @php($saldoDolar += $lancamento->ValorQuantidadeDolar)
                                                    @php($somatoriaDolar += $lancamento->ValorQuantidadeDolar)
                                                @endif
                                            @endif

                                            @if ($Conferido == 'EntradasGeral' or $Conferido == 'SemDefinir' or $Conferido == 'Transferencias')
                                                <span style="color: blue;">
                                                    {{ number_format($lancamento->Valor, 2, ',', '.') }}
                                                </span>
                                                @if (!in_array($lancamento->ID, $listaSoma))
                                                    @php($totalDebito += $lancamento->Valor)
                                                    @php($saldo += $lancamento->Valor)
                                                    @php($somatoria += $lancamento->Valor)
                                                @endif
                                            @endif
                                        </td>
                                        <td>

                                            @if ($Conferido == 'SaidasGeral' or $Conferido == 'SemDefinir' or $Conferido == 'Transferencias')
                                                <span style="color: blue;">
                                                    {{ number_format($lancamento->Valor, 2, ',', '.') }}
                                                </span>

                                                @if (!in_array($lancamento->ID, $listaSoma))
                                                    @php($totalCredito += $lancamento->Valor)
                                                    @php($saldo += $lancamento->Valor)
                                                    @php($somatoria += $lancamento->Valor)
                                                @endif
                                            @endif



                                            {{-- //// extrato normal --}}
                                            @if ($Conta->ID == $lancamento->ContaCreditoID and $Conferido !== 'SaidasGeral' and $Conferido !== 'EntradasGeral' and $Conferido !== 'SemDefinir' and $Conferido !== 'Transferencias')
                                                <span style="color: red;">
                                                    {{ number_format($lancamento->Valor, 2, ',', '.') }}
                                                </span>

                                                @if (!in_array($lancamento->ID, $listaSoma))
                                                    @php($totalCredito += $lancamento->Valor)
                                                    @php($saldo -= $lancamento->Valor)
                                                    @php($somatoria -= $lancamento->Valor)
                                                @endif
                                            @endif
                                            {{-- //// extrato normal --}}
                                            {{-- @if ($Conta->ID == $lancamento->ContaDebitoID and $Conferido !== 'EntradasGeral')
                                                {{ number_format($lancamento->Valor, 2, ',', '.') }}
                                                @if (!in_array($lancamento->ID, $listaSoma))
                                                    @php($totalDebito += $lancamento->Valor)
                                                    @php($saldo -= $lancamento->Valor)
                                                    @php($somatoria -= $lancamento->Valor)
                                                @endif
                                            @endif --}}

                                        </td>

                                        <td>
                                            <span style="color: {{ $saldo < 0 ? 'red' : 'blue' }}">
                                                {{ number_format($saldo, 2, ',', '.') }}
                                            </span>

                                        </td>
                                    </tr>
                                    <tr class="tr-{{ $lancamento->ID }}">
                                        <td colspan="5">
                                            {{ $lancamento->Descricao . '  ' . $lancamento->HistoricoDescricao }}
                                        </td>
                                    </tr>
                                    <tr class="tr-{{ $lancamento->ID }} border-bottom-5">
                                        <td colspan="3">
                                            <strong>Conta Partida: </strong>
                                            @if ($lancamento->ContaCreditoID != $Conta->ID)
                                                <span style="color: red;">
                                                    {{ $lancamento->ContaCredito->PlanoConta->Descricao ?? null }}
                                                </span>
                                            @else
                                                <span style="color: blue;">
                                                    {{ $lancamento->ContaDebito->PlanoConta->Descricao ?? null }}
                                                </span>
                                            @endif

                                        </td>


                                        <td colspan="2" align="right">
                                            <div class="card text-center"
                                                style="background-color: rgb(111, 14, 237); color: white;">

                                                <button title="Botão para atualizar" type="button"
                                                    class="btn-sm btn btn-outline-info"
                                                    wire:click='confirmarAtualizar({{ $lancamento->ID }})'>ATUALIZAR PÁGINA </div>
                                                </button>
                                            </div>
                                        </td>

                                        @can('LANCAMENTOS - ATUALIZAR POUPANCA/AVENUE')
                                          @if($lancamento->AtualizarPoupancaAvenue)
                                            <td colspan="2" align="right">
                                                <div class="card text-center"
                                                    style="background-color: rgb(14, 212, 93); color: white;">

                                                    <button title="Botão para atualizar" type="button"
                                                        class="btn-sm btn btn-outline-info"
                                                        wire:click="confirmarAtualizarSaldoPoupanca({{ $lancamento->ID }},
                                                        '{{ $saldo }}',
                                                        '{{ $lancamento->Descricao }}',
                                                        '{{ $lancamento->DataContabilidade}}',
                                                        '{{ $lancamento->ContaDebitoID}}',
                                                            '{{ $lancamento->ContaCreditoID}}',
                                                            '{{ $lancamento->EmpresaID}}'
                                                        )">
                                                                <h5 class="card-title" style="color: rgb(123, 0, 255);">ATUALIZAR/POUPANÇA</h5>
                                                    </button>
                                                </div>
                                            </td>
                                            @endif
                                        @endcan

                                        <td colspan="2" align="right">
                                            <div class="card text-center"
                                                style="background-color: rgb(185, 237, 14); color: white;">
                                                <h5 class="card-title" style="color: rgb(123, 0, 255);">CONFERIDO</h5>
                                                <button title="Botão de Conferência" type="button"
                                                    class="btn-sm btn btn-outline-info"
                                                    wire:click='confirmarLancamento({{ $lancamento->ID }})'>
                                                    @if ($lancamento->Conferido)
                                                        <div class="card text-center"
                                                            style="background-color: rgb(118, 14, 237); color: white;">
                                                            <i
                                                                class="cl-{{ $lancamento->ID }} fa fa-check-square-o"></i>
                                                        </div>
                                                    @else
                                                        <div class="card text-center"
                                                            style="background-color: rgb(237, 14, 14); color: white;">
                                                            <i class="cl-{{ $lancamento->ID }} fa fa-square-o"></i>
                                                        </div>
                                                    @endif
                                                </button>
                                            </div>



                                            @can('LANCAMENTOS - CAIXAS GERAL')
                                            @php($Marcacao = false)


                                            @if($lancamento->ClassificaCaixaGeral)
                                                <div class="card text-center"
                                                    style="background-color: #00ff2a; color: white;">

                                                    {{-- <div class="card-body"> --}}
                                                    <h5 class="card-title" style="color: red;">Saídas</h5>

                                                    <button title="Botão de Saídas em geral" type="button"
                                                        class="btn-sm btn btn-outline-danger"
                                                        wire:click='confirmarLancamentoSaidasGeral({{ $lancamento->ID }})'>
                                                        @if ($lancamento->SaidasGeral)
                                                                @php($Marcacao = true)
                                                            <div class="card text-center"
                                                                style="background-color: rgb(118, 14, 237); color: white;">
                                                                <i
                                                                    class="cl-{{ $lancamento->ID }} fa fa-check-square-o"></i>
                                                            </div>
                                                        @else
                                                            <i class="cl-{{ $lancamento->ID }} fa fa-square-o"></i>
                                                        @endif
                                                    </button>


                                                    <h5 class="card-title" style="color: rgb(39, 3, 196);">Entradas</h5>

                                                    <button title="Botão de Entradas em geral" type="button"
                                                        class="btn-sm btn btn-outline-primary"
                                                        wire:click='confirmarLancamentoEntradasGeral({{ $lancamento->ID }})'>
                                                        @if ($lancamento->EntradasGeral)

                                                                @php($Marcacao = true)
                                                            <div class="card text-center"
                                                                style="background-color: rgb(118, 14, 237); color: white;">
                                                                <i
                                                                    class="cl2-{{ $lancamento->ID }} fa fa-check-square-o"></i>
                                                            </div>
                                                        @else
                                                            <i class="cl2-{{ $lancamento->ID }} fa fa-square-o"></i>
                                                        @endif
                                                    </button>

                                                    <h5 class="card-title" style="color: rgb(39, 3, 196);">Investimentos</h5>
                                                    <button title="Botão de Investimentos" type="button"
                                                        class="btn-sm btn btn-outline-warning"
                                                        wire:click='confirmarInvestimentos({{ $lancamento->ID }})'>
                                                        @if ($lancamento->Investimentos)

                                                                @php($Marcacao = true)
                                                            <div class="card text-center"
                                                                style="background-color: rgb(118, 14, 237); color: white;">
                                                                <i
                                                                    class="cl2-{{ $lancamento->ID }} fa fa-check-square-o"></i>
                                                            </div>
                                                        @else
                                                            <i class="cl2-{{ $lancamento->ID }} fa fa-square-o"></i>
                                                        @endif
                                                    </button>

                                                    <h5 class="card-title" style="color: rgb(39, 3, 196);">Transferências</h5>
                                                    <button title="Botão de Transferências" type="button"
                                                        class="btn-sm btn btn-outline-secondary"
                                                        wire:click='confirmarTransferencias({{ $lancamento->ID }})'>
                                                        @if ($lancamento->Transferencias)
                                                              @php($Marcacao = true)
                                                            <div class="card text-center"
                                                                style="background-color: rgb(118, 14, 237); color: white;">
                                                                <i
                                                                    class="cl2-{{ $lancamento->ID }} fa fa-check-square-o"></i>
                                                            </div>
                                                        @else
                                                            <i class="cl2-{{ $lancamento->ID }} fa fa-square-o"></i>
                                                        @endif
                                                    </button>

                                                    <h5 class="card-title" style="color: rgb(39, 3, 196);">Sem definir</h5>
                                                    <button title="Botão sem definição" type="button"
                                                        class="btn-sm btn btn-outline-secondary"
                                                        wire:click='confirmarSemDefinir({{ $lancamento->ID }})'>
                                                        @if ($lancamento->SemDefinir)
                                                          @php($Marcacao = true)

                                                            <div class="card text-center"
                                                                style="background-color: rgb(118, 14, 237); color: white;">
                                                                <i
                                                                    class="cl2-{{ $lancamento->ID }} fa fa-check-square-o"></i>
                                                            </div>
                                                        @else
                                                            <i class="cl2-{{ $lancamento->ID }} fa fa-square-o"></i>
                                                        @endif
                                                    </button>


                                                    {{-- </div> --}}
                                                </div>


                                                @if (!$Marcacao == true)
                                                    <div class="bg-danger text-black p-4 rounded-lg shadow-md">
                                                        <h1 class="text-xl font-bold">SEM MARCAÇÕES INFORMADAS</h1>
                                                    </div>
                                                 @endif


                                                @endcan
                                            @endif

                                            {{-- <button title="Sem notificação" data-id="84264" data-dias="" type="button"
                                        class="btn-sm btn btn-outline-info ligar-notificacao">
                                        <i class="fa fa-bell-slash"></i>
                                    </button> --}}

                                            <button title="Editar"
                                                wire:click="editarLancamento({{ $lancamento->ID }})"
                                                class="btn btn-outline-secondary btn-sm btn-editar">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button title="Somar Valor" type="button"
                                                wire:click="somarLancamento({{ $lancamento->ID }})"
                                                class="btn-sm btn btn-outline-success autalizar-saldo"><i
                                                    class="fa {{ !in_array($lancamento->ID, $listaSoma) ? 'fa-check-square-o' : 'fa-square-o' }}"></i></button>

                                            <button title="Excluir Lançamento" type="button"
                                                wire:click="incluirExclusao({{ $lancamento->ID }},'{{ $lancamento->DataContabilidade->format('Y-m-d') }}')"
                                                class="btn-sm btn btn-outline-danger">
                                                <i
                                                    class="fa {{ in_array($lancamento->ID, $listaExclusao) ? 'fa-check-square-o' : 'fa-square-o' }}"></i>
                                            </button>
                                            <button
                                                wire:click="alterarDataVencidoRapido({{ $lancamento->ID }},'ontem')"
                                                title="Alterar data processamento para Ontem" type="button"
                                                class="btn-sm btn btn-outline-danger">
                                                <i class="fa fa-arrow-left"></i>
                                            </button>

                                            <button
                                                wire:click="alterarDataVencidoRapido({{ $lancamento->ID }},'hoje')"
                                                title="Alterar para dia Atual" type="button"
                                                class="btn-sm btn btn-outline-danger">
                                                <i class="fa fa-calendar-minus-o"></i>
                                            </button>

                                            <button
                                                wire:click="alterarDataVencidoRapido({{ $lancamento->ID }},'amanha')"
                                                title="Alterar data processamento para Amanhã" type="button"
                                                class="btn-sm btn btn-outline-danger bd-highlight">
                                                <i class="fa fa-arrow-right"></i>
                                            </button>

                                            @if ($lancamento->ContasPagarArquivo)
                                                @can('CONTASPAGAR - EDITAR')
                                                    <a href="{{ route('ContasPagar.edit', $lancamento->ContasPagarArquivo->ID) }}"
                                                        class="btn btn-success" tabindex="-1" role="button"
                                                        aria-disabled="true" target="_blank">Editar Contas pagar/ver
                                                        Documentos</a>
                                                @endcan
                                            @endif

                                        </td>
                                    </tr>
                                    <tr class="tr-{{ $lancamento->ID }}">
                                        <td colspan="5" style="background-color: #1146d8"></td>
                                    </tr>
                                @endforeach
                            @endif


                        </tbody>
                        <div class="card">

                            <div class="badge bg-success text-wrap"
                                style="width: 100%;
                 ;font-size: 16px; lign=˜Center˜">
                                INFORMAÇÕES DETALHADAS DA CONTA SELECIONADA

                                <thead class="thead">
                                    <tr>
                                        <th></th>
                                        <th>Total em real</th>


                                        <th id="totaldebito" style="color: blue;">
                                            R$ {{ number_format($totalDebito, 2, ',', '.') }}
                                        </th>

                                        <th id="totalcredito" style="color: red;">
                                            R$ {{ number_format($totalCredito, 2, ',', '.') }}
                                        </th>


                                        <span style="color: {{ $somatoria < 0 ? 'red' : 'blue' }}">
                                            <th id="total">R$ {{ number_format($somatoria, 2, ',', '.') }}
                                        </span>

                                        <th></th>
                                    </tr>

                                    <tr>
                                        <th></th>
                                        <th>Total em quantidade de dolares</th>


                                        <th id="totaldebitoDolar" style="color: blue;">
                                            US$ {{ number_format($totalDebitoDolar, 2, ',', '.') }}
                                        </th>

                                        <th id="totalcredito" style="color: red;">
                                            US$ {{ number_format($totalCreditoDolar, 2, ',', '.') }}
                                        </th>


                                        <span style="color: {{ $somatoriaDolar < 0 ? 'red' : 'blue' }}">
                                            <th id="total">US$ {{ number_format($somatoriaDolar, 2, ',', '.') }}
                                        </span>


                                        <th></th>
                                    </tr>
                                </thead>
                            </div>

                        </div>
                    </table>
                    <div class="badge bg-success text-wrap"
                        style="width: 100%;
             ;font-size: 16px; lign=˜Center˜">

                    </div>

                    <div class="card-footer">
                        @if ($listaExclusao)
                            <button id="processar-exclusao" type="button" class="btn btn-danger btn-sm"
                                wire:click='processarExclussao()'>
                                <i class="fa fa-trush"></i>Processar exclusão
                            </button>
                        @endif
                    </div>
                </div>

                <div class="modal fade" id="editarLancamentoModal" tabindex="-1"
                    aria-labelledby="editarLancamentoModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-body">
                                @if ($editar_lancamento)
                                    @livewire('lancamento.editar-lancamento', ['lancamento_id' => $editar_lancamento, 'contas' => $contas, 'empresas' => $empresas])
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>




@push('styles')
    <!-- Styles -->
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" /> --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
@endpush
@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
    <!-- Scripts -->
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script> --}}

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


    <script>
        window.addEventListener('alert', event => {
            alert(event.detail.message);
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.addEventListener('alert', event => {
                alert(event.detail.message);
            });
        });
    </script>


    <script>

        var modal = false;
        $(document).ready(function() {
            $('#selEmpresa').on('change', function(e) {
                // @this.set('selEmpresa', e.target.value);
                console.log(e.target.value);

                Livewire.emit('selectedSelEmpresaItem', e.target.value);
                // Fecha modal (se aberto) e dispara busca automática
                var myModalEl = document.getElementById('editarLancamentoModal');
                if (myModalEl) {
                    var modalInstance = bootstrap.Modal.getInstance(myModalEl);
                    if (modalInstance) modalInstance.hide();
                }
                Livewire.emit('search');
                // Mover foco para o select de conta (e container Select2, se presente)
                setTimeout(function() {
                    var selConta = document.getElementById('selConta');
                    if (selConta) {
                        selConta.focus();
                        var sel2 = $(selConta).siblings('.select2');
                        if (sel2 && sel2.length) {
                            sel2.find('.select2-selection').addClass('select2-container--focus');
                        }
                    }
                }, 0);
            });
            $('#selConta').on('change', function(e) {
                Livewire.emit('selectedSelContaItem', e.target.value);
                // @this.set('selConta', e.target.value);
                // Dispara uma busca imediata no front também
                Livewire.emit('search');
            });

            //scripts para troca de empresa
            $(document).on('change', '#novacontadebito', function(e) {
                Livewire.emitTo('lancamento.troca-empresa', 'setContaDebito', $(this).val());
            });
            $(document).on('change', '#novacontacredito', function(e) {
                Livewire.emitTo('lancamento.troca-empresa', 'setContaCredito', $(this).val());
            });
            ///troca de emprsa
            $(document).on('change', '#novaEmpresaID', function(e) {
                Livewire.emitTo('lancamento.editar-lancamento', 'changeEmpresaID', $(this).val());
            });
            //troca de historico
            $(document).on('change', '#historicoID', function(e) {
                Livewire.emitTo('lancamento.editar-lancamento', 'selectHistorico', e.target.value);
            });

            $(document).on('change', '#contadebito', function(e) {
                Livewire.emitTo('lancamento.editar-lancamento', 'changeContaDebitoID', e.target.value);
            });
            $(document).on('change', '#contacredito', function(e) {
                Livewire.emitTo('lancamento.editar-lancamento', 'changeContaCreditoID', e.target.value);
            });
        });

        window.addEventListener('remove-line-exclusao', event => {
            $('.tr-' + event.detail.lancamento_id).remove();
            console.log(event.detail.lancamento_id);
        });

        window.addEventListener('abrir-modal', event => {
            var myModal = new bootstrap.Modal(document.getElementById('editarLancamentoModal'));
            modal = true;
            myModal.show();
            var myModalEl = document.getElementById('editarLancamentoModal');

            myModalEl.addEventListener('hidden.bs.modal', function(event) {
                modal = false;
                Livewire.emit('search');
            })
        });

        window.addEventListener('fechar-modal', event => {
            var myModalEl = document.getElementById('editarLancamentoModal');
            var modalInstance = bootstrap.Modal.getInstance(myModalEl) || new bootstrap.Modal(myModalEl);
            modalInstance.hide();
        });

        window.addEventListener('limpar-selConta', event => {
            // Limpa valor do select nativo
            var selConta = document.getElementById('selConta');
            if (selConta) {
                selConta.value = '';
            }
            // Limpa via Select2, se presente
            try {
                if ($('#selConta').data('select2')) {
                    $('#selConta').val(null).trigger('change');
                }
            } catch (e) { /* noop */ }
        });

        window.addEventListener('desabilitar-selConta', event => {
            var selConta = document.getElementById('selConta');
            if (selConta) {
                selConta.setAttribute('disabled', 'disabled');
            }
            try {
                if ($('#selConta').data('select2')) {
                    $('#selConta').prop('disabled', true);
                }
            } catch (e) { /* noop */ }
        });

        document.addEventListener("DOMContentLoaded", () => {
            Livewire.hook('message.processed', (message, component) => {
                $(document).ready(function() {
                    $('.money').mask('000.000.000.000.000,00', {
                        reverse: true
                    });
                });
                if (modal) {
                    console.log('modal open');
                    $('.select2').select2({
                        dropdownParent: $('#editarLancamentoModal'),
                        theme: 'bootstrap-5'
                    });
                } else {
                    console.log('modal closed');
                    $('.select2').select2({
                        theme: 'bootstrap-5'
                    });

                }

                // Reabilitar select de conta após render (contas carregadas para a nova empresa)
                try {
                    var selConta = document.getElementById('selConta');
                    if (selConta) {
                        selConta.removeAttribute('disabled');
                    }
                    if ($('#selConta').data('select2')) {
                        $('#selConta').prop('disabled', false);
                    }
                } catch (e) { /* noop */ }

                // Aplicar foco visual vermelho ao Select2 de empresa ao abrir/fechar
                try {
                    $('#selEmpresa')
                        .off('select2:open.focusred select2:close.focusred')
                        .on('select2:open.focusred', function() {
                            var sel2 = $(this).siblings('.select2');
                            sel2.find('.select2-selection').addClass('select2-container--focus');
                        })
                        .on('select2:close.focusred', function() {
                            var sel2 = $(this).siblings('.select2');
                            sel2.find('.select2-selection').removeClass('select2-container--focus');
                        });
                } catch (e) { /* noop */ }
            })


        });



        function alterarData() {
            $.confirm({
                title: 'Alteração de Data em Massa!',
                content: '' +
                    '<form action="" class="formName">' +
                    '<div class="form-group">' +
                    '<label>Informe a data de Alteração</label>' +
                    '<input type="date" class="date form-control" required />' +
                    '</div>' +
                    '</form>',
                buttons: {
                    formSubmit: {
                        text: 'Submit',
                        btnClass: 'btn-blue',
                        action: function() {
                            var date = this.$content.find('.date').val();
                            if (!date) {
                                $.alert('Informe uma data');
                                return false;
                            }
                            $.confirm({
                                title: 'Confirmar!',
                                content: 'Deseja realmente continuar?',
                                buttons: {
                                    confirmar: function() {
                                        // $.alert('Confirmar!');
                                        Livewire.emit('alterarData', date)
                                    },
                                    cancelar: function() {
                                        // $.alert('Cancelar!');
                                    },

                                }
                            });
                        }
                    },
                    cancel: function() {
                        //close
                    },
                },
                onContentReady: function() {
                    // bind to events
                    var jc = this;
                    this.$content.find('form').on('submit', function(e) {
                        // if the user submits the form by pressing enter in the field.
                        e.preventDefault();
                        jc.$$formSubmit.trigger('click'); // reference the button and click it
                    });
                }
            });
        }

        window.addEventListener('confirmarLancamento', event => {
            if (event.detail.status) {
                $('.cl-' + event.detail.lancamento_id).removeClass('fa-square-o');
                $('.cl-' + event.detail.lancamento_id).addClass('fa-check-square-o');
            } else {
                $('.cl-' + event.detail.lancamento_id).removeClass('fa-check-square-o');
                $('.cl-' + event.detail.lancamento_id).addClass('fa-square-o');
            }
        });

        window.addEventListener('confirmarLancamentoSaidasGeral', event => {
            if (event.detail.statusSaidasGeral) {
                $('.cl-' + event.detail.lancamento_id).removeClass('fa-square-o');
                $('.cl-' + event.detail.lancamento_id).addClass('fa-check-square-o');
            } else {
                $('.cl-' + event.detail.lancamento_id).removeClass('fa-check-square-o');
                $('.cl-' + event.detail.lancamento_id).addClass('fa-square-o');
            }
        });

        window.addEventListener('confirmarLancamentoEntradasGeral', event => {
            if (event.detail.statusEntradasGeral) {
                $('.cl-' + event.detail.lancamento_id).removeClass('fa-square-o');
                $('.cl-' + event.detail.lancamento_id).addClass('fa-check-square-o');
            } else {
                $('.cl-' + event.detail.lancamento_id).removeClass('fa-check-square-o');
                $('.cl-' + event.detail.lancamento_id).addClass('fa-square-o');
            }
        });

        // In your Javascript (external .js resource or <script> tag)
        $('.select2').select2({
            theme: 'bootstrap-5'
        });

        function excluirArquivo(id) {
            $.confirm({
                title: 'Confirmar!',
                content: 'Deseja realmente continuar?',
                buttons: {
                    confirmar: function() {
                        // $.alert('Confirmar!');
                        Livewire.emit('excluir', id)
                    },
                    cancelar: function() {
                        // $.alert('Cancelar!');
                    },

                }
            });
        };

        function confirmar(params) {
            $.confirm({
                title: 'Confirmar!',
                content: 'Confirma?',
                buttons: {
                    confirmar: function() {
                        // $.alert('Confirmar!');
                        $.confirm({
                            title: 'Confirmar!',
                            content: 'Deseja realmente continuar?',
                            buttons: {
                                confirmar: function() {
                                    Livewire.emit('salvarLancamento', params);
                                },
                                cancelar: function() {
                                    // $.alert('Cancelar!');
                                },

                            }
                        });

                    },
                    cancelar: function() {
                        // $.alert('Cancelar!');
                    },

                }
            });
        }

    function scrollUp() {
      window.scrollBy({ top: -80, behavior: 'smooth' });
    }

    function scrollDown() {
      window.scrollBy({ top: 80, behavior: 'smooth' });
    }




    </script>
@endpush
