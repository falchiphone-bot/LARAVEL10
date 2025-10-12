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

@push('styles')
<style>
    body.extrato-compact header { display:none !important; }
    body.extrato-compact .card > .card-body > .badge.bg-secondary,
    body.extrato-compact .card .card-footer,
    body.extrato-compact .card .card-header { display:none !important; }
    body.extrato-compact .toggle-extrato-layout.btn { background:#212529; color:#fff; }
</style>
@endpush

    @push('scripts')
    <script>
    (function(){
        const BTN_ID='btn-toggle-extrato-layout';
        const LS_KEY='extrato_layout_compact';
        function applyState(){
            const compact = localStorage.getItem(LS_KEY)==='1';
            document.body.classList.toggle('extrato-compact', compact);
            const btn = document.getElementById(BTN_ID);
            if(btn){ btn.textContent = compact ? 'Modo Completo' : 'Modo Compacto'; }
        }
        document.addEventListener('DOMContentLoaded', function(){
            applyState();
            const btn = document.getElementById(BTN_ID);
            if(btn){
                btn.addEventListener('click', function(){
                    const now = !(localStorage.getItem(LS_KEY)==='1');
                    localStorage.setItem(LS_KEY, now?'1':'0');
                    applyState();
                });
            }
        });
    })();
    </script>
    @endpush
    <div class="card extrato-wrapper">
        <div class="card-body">
            <div class="d-flex justify-content-end mb-2">
                <button type="button" id="btn-toggle-extrato-layout" class="btn btn-outline-dark btn-sm toggle-extrato-layout" title="Alterna exibição para ganhar espaço vertical">Modo Compacto</button>
            </div>
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

                <div class="row align-items-center g-2 mt-1">
                    @if($selConta == 19098)
                    <div class="col-12 col-md-8">
                        <div class="card shadow-sm border-0">
                            <div class="card-header">
                                <strong>Importar extrato Modobank</strong>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('lancamentos.preview.despesas') }}" method="post" enctype="multipart/form-data" class="row g-2" target="_blank">
                                    @csrf
                                    <input type="hidden" name="empresa_id" value="{{ $selEmpresa ?? '' }}">
                                    <input type="hidden" name="conta_credito_global_id" value="{{ $selConta ?? '' }}">
                                    <div class="col-12 col-sm-7 col-md-8">
                                        <input type="file" id="arquivo_excel_extrato" name="arquivo_excel" accept=".xlsx,.xls,.csv" class="d-none" required>
                                        <div class="d-flex align-items-center gap-2">
                                            <label for="arquivo_excel_extrato" class="btn btn-outline-danger btn-sm">
                                                ESCOLHER ARQUIVO MODOBANK
                                            </label>
                                            <span id="arquivo_excel_extrato_nome" class="badge bg-danger">Nenhum arquivo selecionado</span>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-5 col-md-4 d-grid d-md-block">
                                        <button type="submit" class="btn btn-sm btn-primary w-100" title="Importa um arquivo CSV/XLSX e abre a tela de pré-visualização para classificar o débito">
                                            Importar e pré-visualizar (CSV/XLSX)
                                        </button>
                                    </div>
                                    <div class="col-12">
                                        <div id="dropzone-extrato" class="dropzone-extrato text-center p-4 mt-1" role="button" aria-label="Arraste e solte ou clique para selecionar arquivo CSV ou XLSX">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="mb-2 d-flex align-items-center gap-3">
                                                    <span class="text-success" title="Excel (XLS/XLSX)"><i class="fa fa-file-excel-o fa-2x" aria-hidden="true"></i></span>
                                                    <span class="text-secondary" title="CSV"><i class="fa fa-file-text-o fa-2x" aria-hidden="true"></i></span>
                                                </div>
                                                <div><strong>Arraste e solte</strong> o arquivo CSV/XLSX aqui</div>
                                                <div class="small text-muted">ou clique para selecionar</div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="col-12 col-md-4">
                        <a href="{{ route('lancamentos.preview.despesas') }}" class="btn btn-sm btn-outline-secondary w-100" title="Abrir a tela de pré-visualização (para arquivos já presentes em storage/app/imports)" target="_blank" rel="noopener">
                            Abrir tela de pré-visualização
                        </a>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    var inp = document.getElementById('arquivo_excel_extrato');
    var out = document.getElementById('arquivo_excel_extrato_nome');
    var dz = document.getElementById('dropzone-extrato');
    if(inp && out){
        inp.addEventListener('change', function(){
            if(inp.files && inp.files.length){
                out.textContent = inp.files[0].name;
                out.classList.remove('bg-danger');
                out.classList.add('bg-secondary','text-dark');
            } else {
                out.textContent = 'Nenhum arquivo selecionado';
                out.classList.remove('bg-secondary','text-dark');
                out.classList.add('bg-danger');
            }
        });
    }
    if(dz && inp && out){
        var acceptExt = ['xlsx','xls','csv'];
        function getExt(name){
            var i = name.lastIndexOf('.');
            return i>=0 ? name.substring(i+1).toLowerCase() : '';
        }
        function setFiles(files){
            var dt = new DataTransfer();
            var added = 0;
            for(var i=0;i<files.length;i++){
                var f = files[i];
                if(acceptExt.indexOf(getExt(f.name))>=0){ dt.items.add(f); added++; }
            }
            if(added===0){
                out.textContent = 'Formato não suportado. Use CSV/XLSX.';
                out.classList.remove('bg-secondary','text-dark');
                out.classList.add('bg-danger');
                return;
            }
            inp.files = dt.files;
            inp.dispatchEvent(new Event('change', {bubbles:true}));
        }
        dz.addEventListener('click', function(){ inp.click(); });
        dz.addEventListener('dragover', function(e){ e.preventDefault(); dz.classList.add('drag-over'); });
        dz.addEventListener('dragleave', function(e){ dz.classList.remove('drag-over'); });
        dz.addEventListener('drop', function(e){
            e.preventDefault(); dz.classList.remove('drag-over');
            if(e.dataTransfer && e.dataTransfer.files){ setFiles(e.dataTransfer.files); }
        });
    }
});
</script>
@endpush

@push('styles')
<style>
.dropzone-extrato{ border:2px dashed #6c757d; border-radius:.5rem; color:#6c757d; cursor:pointer; }
.dropzone-extrato.drag-over{ border-color:#0d6efd; background:rgba(13,110,253,.05); color:#0d6efd; }
</style>
@endpush



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
                                <option value="1">Conferido</option>
                                <option value="0">Não conferido</option>
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
                                autocomplete="off" wire:model.debounce.500ms='Descricao'
                                wire:keydown.enter.prevent="search">
                        </div>
                        <div class="form-group col-sm-12 col-md-3">
                            <label for="de" class="pr-1  form-control-label">A partir De:</label>
                            <input type="date" value="" id="a_partir_de" class="form-control"
                                autocomplete="off" wire:model.debounce.500ms='DescricaoApartirDe'
                                wire:keydown.enter.prevent="search">
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

                    <div class="d-inline-flex align-items-center gap-2">
                        <select class="form-select form-select-sm" style="width:auto" wire:model="pdfSelecionado">
                            <option value="completo">PDF completo (com saldo anterior)</option>
                            <option value="resumo_total">PDF resumido: total do dia (sem saldo anterior)</option>
                            <option value="resumo_detalhe">PDF resumido: detalhe por dia (sem saldo anterior)</option>
                        </select>
                        <button id="btn-gerar-pdf" wire:click='gerarPDFSelecionado' type="button" class="btn btn-danger btn-sm" target="_blank">
                            <i class="fa fa-file-pdf-o"></i> Gerar PDF
                        </button>
                    </div>
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

                                            <button title="Editar (modal antigo)"
                                                wire:click="editarLancamento({{ $lancamento->ID }})"
                                                class="btn btn-outline-secondary btn-sm btn-editar me-1">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <a title="Editar em página" href="{{ route('lancamentos.edit.simple', $lancamento->ID) }}" target="_blank" class="btn btn-sm btn-primary me-1">
                                                <i class="fa fa-external-link"></i>
                                            </a>
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
                    <div class="modal-dialog modal-dialog-scrollable modal-md editar-lancamento-dialog">
                        <div class="modal-content">
                            <div class="modal-body">
                                {{-- Força renderização do modal para debug --}}
                                @livewire('lancamento.editar-lancamento', [
                                    'lancamento_id' => $editar_lancamento,
                                    'empresa_id' => $selEmpresa,
                                    'contas' => $contas,
                                    'empresas' => $empresas
                                ], key('editar-'.$editar_lancamento.'-'.$selEmpresa))
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
    <style>
        /* Redução específica do modal de edição de lançamento */
        .editar-lancamento-dialog { max-width: 720px; }
        @media (max-width: 768px){
            .editar-lancamento-dialog { max-width: 95%; margin: 0 auto; }
        }
        /* Mantém altura mínima do container de abas para evitar "pulos" ao alternar */
        #editarLancamentoModal .tab-content { min-height: clamp(360px, 55vh, 600px); transition: min-height .15s ease; }
        /* Suaviza mudança de conteúdo interno */
        #editarLancamentoModal .tab-pane { animation: fadeTab .12s ease; }
        @keyframes fadeTab { from { opacity: .6; } to { opacity: 1; } }

        /* NOVO: estabiliza a altura do modal usando flex layout */
        #editarLancamentoModal .modal-content { display:flex; flex-direction:column; min-height:60vh; max-height:85vh; }
        #editarLancamentoModal .modal-body { flex:1 1 auto; display:flex; flex-direction:column; overflow:hidden; }
        #editarLancamentoModal .modal-body > div.card { flex:1 1 auto; display:flex; flex-direction:column; margin:0; }
        #editarLancamentoModal .modal-body > div.card > .card-body { flex:1 1 auto; display:flex; flex-direction:column; padding-top:0.75rem; }
        #editarLancamentoModal .modal-body .nav-tabs { margin-top:.25rem; }
        #editarLancamentoModal .modal-body .tab-content { flex:1 1 auto; display:flex; flex-direction:column; }
        #editarLancamentoModal .modal-body .tab-content .tab-pane { flex:1 1 auto; display:flex; flex-direction:column; }
        #editarLancamentoModal .modal-body .tab-content .tab-pane .card-body { flex:1 1 auto; overflow-y:auto; }
        /* Scroll interno apenas na área de conteúdo evitando “pulos” no container */
        #editarLancamentoModal .modal-body .tab-content .tab-pane .card-body::-webkit-scrollbar { width: 10px; }
        #editarLancamentoModal .modal-body .tab-content .tab-pane .card-body::-webkit-scrollbar-track { background: #f1f1f1; }
        #editarLancamentoModal .modal-body .tab-content .tab-pane .card-body::-webkit-scrollbar-thumb { background:#c0c0c0; border-radius:6px; }
        #editarLancamentoModal .modal-body .tab-content .tab-pane .card-body::-webkit-scrollbar-thumb:hover { background:#a0a0a0; }
        @media (max-height: 640px){
            #editarLancamentoModal .modal-content { min-height:70vh; }
            #editarLancamentoModal .tab-content { min-height: clamp(300px, 65vh, 560px); }
        }

        /* =================== THEMA AZUL COMPLETO DO MODAL =================== */
        #editarLancamentoModal .modal-content { background:#e4f2ff; border:2px solid #0d5ca8; box-shadow:0 0 0 4px rgba(13,92,168,.15); }
        #editarLancamentoModal .modal-header { background:linear-gradient(90deg,#0d5ca8,#1172d4); color:#fff; border-bottom:2px solid #0b4c88; }
        #editarLancamentoModal .modal-header h5,
        #editarLancamentoModal .modal-header h4,
        #editarLancamentoModal .modal-header strong { color:#fff !important; }
        #editarLancamentoModal .btn-close { filter: invert(1); }

        /* Abas */
        #editarLancamentoModal .nav-tabs { border-bottom:2px solid #0d5ca8; }
        #editarLancamentoModal .nav-tabs .nav-link { background:#c5e2ff; color:#0b3d66; border:1px solid #7fb6e8; margin-right:4px; font-weight:500; }
        #editarLancamentoModal .nav-tabs .nav-link:hover { background:#b3d9ff; color:#08314f; }
        #editarLancamentoModal .nav-tabs .nav-link.active { background:#0d5ca8; color:#fff; border-color:#0d5ca8; box-shadow:0 0 0 2px #0d5ca8 inset; }

        /* Cards internos tornam-se translúcidos para ver azul de fundo */
        #editarLancamentoModal .card { background:#d5ecff; border:1px solid #8fc2ed; }
        #editarLancamentoModal .card-header { background:#b8ddfb; border-bottom:1px solid #8fc2ed; }
        #editarLancamentoModal .card-body { background:transparent; }
        #editarLancamentoModal .card-footer { background:#b8ddfb; }

        /* Inputs / selects */
        #editarLancamentoModal input.form-control,
        #editarLancamentoModal select.form-control,
        #editarLancamentoModal textarea.form-control,
        #editarLancamentoModal .select2-container .select2-selection--single { background:#ffffff; border:1px solid #6bb2ec; color:#043254; }
        #editarLancamentoModal .select2-container .select2-selection--single .select2-selection__rendered { color:#043254; }
        #editarLancamentoModal .select2-container--default .select2-selection--single .select2-selection__arrow { height:36px; }
        #editarLancamentoModal input.form-control:focus,
        #editarLancamentoModal select.form-control:focus,
        #editarLancamentoModal textarea.form-control:focus,
        #editarLancamentoModal .select2-container--default.select2-container--focus .select2-selection--single { background:#f0f9ff; border-color:#0d5ca8; box-shadow:0 0 0 .2rem rgba(13,92,168,.25); }

        /* Botões */
        #editarLancamentoModal .btn-primary { background:#0d5ca8; border-color:#0d5ca8; }
        #editarLancamentoModal .btn-primary:hover { background:#0b4c88; }
        #editarLancamentoModal .btn-outline-secondary,
        #editarLancamentoModal .btn-secondary { background:#c5e2ff; color:#08314f; border-color:#7fb6e8; }
        #editarLancamentoModal .btn-outline-secondary:hover,
        #editarLancamentoModal .btn-secondary:hover { background:#b3d9ff; }
        #editarLancamentoModal .btn-warning { background:#ffb347; border-color:#ffb347; color:#402600; }
        #editarLancamentoModal .btn-warning:hover { background:#ffa326; }

        /* Tabelas / listas (comentários / arquivos) */
        #editarLancamentoModal table { background:#fff; }
        #editarLancamentoModal table thead { background:#0d5ca8; color:#fff; }
        #editarLancamentoModal li { color:#043254; }

        /* Textos gerais */
        #editarLancamentoModal label { color:#063a60; font-weight:600; }
        #editarLancamentoModal .alert { background:#fff; border-color:#7fb6e8; color:#063a60; }
        #editarLancamentoModal small, #editarLancamentoModal .text-muted { color:#063a60 !important; opacity:.85; }

        /* Scrollbar personalizada dentro do modal azul */
        #editarLancamentoModal ::-webkit-scrollbar { width:10px; }
        #editarLancamentoModal ::-webkit-scrollbar-track { background:#c2e4ff; }
        #editarLancamentoModal ::-webkit-scrollbar-thumb { background:#0d5ca8; border-radius:6px; }
        #editarLancamentoModal ::-webkit-scrollbar-thumb:hover { background:#0b4c88; }

        /* Links */
        #editarLancamentoModal a { color:#0d5ca8; }
        #editarLancamentoModal a:hover { color:#0b4c88; }

        /* Mensagens placeholder na aba troca empresa (herdam tema) */
        #editarLancamentoModal #trocaEmpresaWrapper .placeholder-msg { background:#ffffff; border:1px dashed #0d5ca8; color:#063a60; }
    </style>
    </style>
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
        // Abre a URL do PDF em nova guia quando o Livewire disparar o evento
        // Lock simples evita abrir mais de uma aba por evento
        (function(){
            let opening = false;
            window.addEventListener('open-pdf', event => {
                if (opening) return; opening = true;
                const url = event.detail.url;
                if (!url) { opening = false; return; }
                setTimeout(() => { window.open(url, '_blank'); opening = false; }, 80);
            });
        })();
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


        // Função para aplicar/remover 'inert' no conteúdo fora do modal
        function setInertOnPageExceptModal(modalId, enable) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            // Seleciona todos os elementos filhos diretos do body exceto o modal E seus ancestrais
            document.querySelectorAll('body > *').forEach(el => {
                // Não aplica inert/aria-hidden no modal nem em nenhum ancestral do modal
                if ((el === modal || el.contains(modal) || modal.contains(el))) return;
                if (enable) {
                    el.setAttribute('inert', '');
                    el.setAttribute('aria-hidden', 'true');
                } else {
                    el.removeAttribute('inert');
                    el.removeAttribute('aria-hidden');
                }
            });
        }

        window.addEventListener('abrir-modal', event => {
            var myModal = new bootstrap.Modal(document.getElementById('editarLancamentoModal'));
            modal = true;
            myModal.show();
            var myModalEl = document.getElementById('editarLancamentoModal');

            // Força blur no elemento ativo antes de aplicar aria-hidden/inert
            if (document.activeElement) {
                document.activeElement.blur();
            }

            // Aplica 'inert' ao conteúdo fora do modal
            setInertOnPageExceptModal('editarLancamentoModal', true);

            // Foca o primeiro campo input, select ou textarea visível do modal
            setTimeout(function() {
                var firstField = myModalEl.querySelector('input:not([type=hidden]):not([disabled]):not([tabindex="-1"]), select:not([disabled]):not([tabindex="-1"]), textarea:not([disabled]):not([tabindex="-1"])');
                if (firstField) firstField.focus();
            }, 300);

            // Remove 'inert' ao fechar
            myModalEl.addEventListener('hidden.bs.modal', function(event) {
                modal = false;
                setInertOnPageExceptModal('editarLancamentoModal', false);
                // Força blur em qualquer elemento focado dentro do modal
                if (document.activeElement && myModalEl.contains(document.activeElement)) {
                    document.activeElement.blur();
                }
                Livewire.emit('search');
            }, { once: true });
        });

        window.addEventListener('fechar-modal', event => {
            var myModalEl = document.getElementById('editarLancamentoModal');
            var modalInstance = bootstrap.Modal.getInstance(myModalEl) || new bootstrap.Modal(myModalEl);
            modalInstance.hide();
            // Remove 'inert' ao fechar por evento externo
            setInertOnPageExceptModal('editarLancamentoModal', false);
            // Força blur em qualquer elemento focado dentro do modal
            if (document.activeElement && myModalEl.contains(document.activeElement)) {
                document.activeElement.blur();
            }
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

        /* Ajuste dinâmico de altura: calcula a maior altura entre as abas já renderizadas
           e fixa como min-height para impedir encolhimento quando o usuário visita uma aba menor. */
        (function(){
            let maxPaneHeight = 0;
            function measureAndLock(){
                const tc = document.querySelector('#editarLancamentoModal .tab-content');
                if(!tc) return;
                const panes = tc.querySelectorAll('.tab-pane');
                panes.forEach(p => {
                    const wasHidden = !p.classList.contains('active');
                    const orig = {display: p.style.display, visibility: p.style.visibility, position: p.style.position};
                    if(wasHidden){
                        p.style.visibility='hidden';
                        p.style.display='block';
                        p.style.position='absolute';
                        p.classList.add('force-measure');
                    }
                    const h = p.scrollHeight;
                    if(h > maxPaneHeight) maxPaneHeight = h;
                    if(wasHidden){
                        p.style.display = orig.display;
                        p.style.visibility = orig.visibility;
                        p.style.position = orig.position;
                        p.classList.remove('force-measure');
                    }
                });
                if(maxPaneHeight){
                    // Limita para não extrapolar viewport (menos margem de 120px)
                    const vp = window.innerHeight - 160;
                    const finalH = Math.min(maxPaneHeight, vp);
                    tc.style.minHeight = finalH + 'px';
                }
            }
            document.addEventListener('shown.bs.tab', measureAndLock);
            window.addEventListener('abrir-modal', () => setTimeout(measureAndLock, 450));
            document.addEventListener('livewire:load', () => {
                if(window.Livewire){
                    Livewire.hook('message.processed', (m,c)=>{ if(document.getElementById('editarLancamentoModal')?.classList.contains('show')) measureAndLock(); });
                }
            });
            window.addEventListener('resize', ()=>{ // Reaplica em resize
                setTimeout(measureAndLock, 150);
            });
        })();




    </script>
@endpush
