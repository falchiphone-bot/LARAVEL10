<div>
    {{-- Close your eyes. Count to one. That is how long forever feels. --}}

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-6">
                    <select class="form-control select2" id="selEmpresa">
                        @foreach ($empresas as $empresa_id => $empresa_descricao)
                        <option value="{{$empresa_id}}">{{ $empresa_descricao }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <select class="form-control select2" id="selConta" tabindex="-1" aria-hidden="true">
                        <option value="0">Escolha uma conta</option>
                        <option value="19198"> NET RUBI SERVICOS DE TECNOLOGIA LTDA TRANSFERENCIA ENTRE
                            CONTAS
                            11382- 9 - AGENCIA 0703- SICREDI E CONTA 1129-3 - AGENCIA 0001 - MODOBANK
                        </option>
                    </select>
                </div>
            </div>
            <div class="row py-2">
                <div class="col-2">
                    <a href="/PlanoContas/dashboard" class="btn btn-warning">Voltar</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            Busca por periodo
        </div>
        <div class="card-body card-block">
            <form id="idform" method="post" class="form">
                <div class="row">
                    <div class="form-group col-sm-12 col-md-3">
                        <label for="de" class="pr-1  form-control-label">De</label>
                        <input type="date" value="" id="de" name="De"
                        wire:model='De' class="required form-control " autocomplete="off">
                    </div>
                    <div class="form-group col-sm-12 col-md-3">
                        <label for="ate" class="px-1  form-control-label">Até</label>
                        <input type="date" value="" id="ate" name="Ate" placeholder="Buscar até"
                            wire:model='Ate' class="required form-control " autocomplete="off">
                    </div>
                    <div class="form-group col-sm-12 col-md-3">
                        <label for="ate" class="px-1  form-control-label">Conferido</label>
                        <select name="Conferido" id="Conferido" class="form-control" wire:model.lazy='Conferido'>
                            <option selected="" value="todos">Todos</option>
                            <option value="1">Conferido</option>
                            <option value="0">Não conferido</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-12 col-md-3">
                        <label for="Notificacao" class="px-1  form-control-label">Notificação</label>
                        <select name="Notificacao" id="Notificacao" class="form-control" wire:model.lazy='Notificacao'>
                            <option selected="" value="todos">Todos</option>
                            <option value="1">Notificar</option>
                            <option value="0">Não notificar</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-sm-12 col-md-3">
                        <label for="de" class="pr-1  form-control-label">Buscar Descrição</label>
                        <input type="text" value="" id="descricao" class="form-control" autocomplete="off" wire:model.debounce.800ms='Descricao'>
                    </div>
                    <div class="form-group col-sm-12 col-md-3">
                        <label for="de" class="pr-1  form-control-label">A partir De:</label>
                        <input type="date" value="" id="a_partir_de" class="form-control" autocomplete="off"
                            wire:model.lazy='DescricaoApartirDe'>
                    </div>
                    <div class="form-group col-sm-12 col-md-3">
                        <label for="data_bloqueio" class="pr-1  form-control-label">Data Bloqueio:</label>
                        <input type="date" value="" id="data_bloqueio" class="form-control"
                            autocomplete="off" wire:model.lazy='DataBloqueio'>
                    </div>
                </div>
            </form>
        </div>

        {{-- <div class="card-footer">
            <button id="buscar" type="button" class="btn btn-secondary btn-sm">
                <i class="fa fa-dot-circle-o"></i>Buscar
            </button>
            <button id="btn-desbloquear" type="button" class="btn btn-warning btn-sm">
                Desbloquear
            </button>
            <button id="btn-bloqueio" type="button" class="btn btn-warning btn-sm" style="display: none;">
                Bloquear
            </button>
            <span>Data bloqueio: 27/03/2023</span>
            <button id="btn-alterar-datas" type="button" class="btn btn-primary btn-sm ">
                Alterar Datas
            </button>
        </div> --}}


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

    <div class="card mt-4">
        <div class="row text-center" wire:loading>
            <div class="spinner-border mx-auto mt-2" role="status">
                <span class="sr-only"></span>
            </div>
        </div>
        <div class="card-header">
            <div class="form-group col md-12">
                <h3 class="content"> {{ $exibicao_pesquisa }}</h3>
            </div>
        </div>
        <div class="card-body result">
            <table class="table">
                <thead class="thead" style="background-color: #00008B; color: white">
                    <tr>
                        <th>Data</th>
                        <th>Descrição</th>
                        <th>Conferido</th>
                        <th>Conta Partida</th>
                        <th style="width: 120px">Débito</th>
                        <th style="width: 120px">Crédito</th>
                        <th style="width: 120px">Saldo</th>
                        <th style="width: 250px"></th>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Saldo Anterior</td>
                        <td id="saldoanterior">R$ -25.288,25</td>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $totalDebito = 0;
                    $totalCredito = 0;
                    @endphp
                    @foreach ($Lancamentos as $lancamento)
                        <tr data-tipo="+">
                            <td class="td-alterar-data"> {{ $lancamento->DataContabilidade->format('d/m/Y') }} </td>
                            <td data-id="84264" class="td-descricao"> {{ $lancamento->Descricao }}</td>
                            <td>
                                <button title="Aguardando Confimação" data-id="84264" data-situacao="0"
                                    type="button" class="btn-sm btn btn-outline-info confirmar-lancamento">
                                    <i class="fa fa-square-o"></i>&nbsp;
                                </button>
                            </td>
                            <td title="{{ $lancamento->ContaDebitoID }}">
                                {{ $lancamento->ContaDebitoID }}
                            </td>
                            <td>{{ $lancamento->ContaCreditoID }}</td>
                            <td>
                                @if ($Conta->ID == $lancamento->ContaDebitoID)
                                    {{ $lancamento->Valor }}
                                    @php($totalDebito += $lancamento->Valor)
                                @endif
                            </td>
                            <td>
                                @if ($Conta->ID == $lancamento->ContaCreditoID)
                                    {{ $lancamento->Valor }}
                                    @php($totalCredito += $lancamento->Valor)
                                @endif
                            </td>
                            <td class="actions">

                                <button title="Sem notificação" data-id="84264" data-dias="" type="button"
                                    class="btn-sm btn btn-outline-info ligar-notificacao">
                                    <i class="fa fa-bell-slash"></i>&nbsp;
                                </button>

                                <a title="Editar" href="/financeiro/lancamentos/edit/84264/5860"
                                    class="btn btn-outline-secondary btn-sm btn-editar">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <button title="Somar Valor" data-valor="1" value="84264" type="button"
                                    class="btn-sm btn btn-outline-success autalizar-saldo"><i
                                        class="fa fa-check-square-o"></i>&nbsp;</button>
                                <button name="excluirlacamento[]" data-valor="0" title="Excluir Lançamento"
                                    value="84264" type="button"
                                    class="btn-sm btn btn-outline-danger excluir-lancamento">
                                    <i class="fa fa-square-o"></i>&nbsp;
                                </button>
                                <button data-valor="anterior" data-id="84264"
                                    title="Alterar data processamento para Ontem" type="button"
                                    class="btn-sm btn btn-outline-danger btn-atualizar-data-processamento">
                                    <i class="fa fa-arrow-left"></i>&nbsp;
                                </button>
                                <button data-valor="atual" data-id="84264" title="Alterar para dia Atual"
                                    type="button"
                                    class="btn-sm btn btn-outline-danger btn-atualizar-data-processamento">
                                    <i class="fa fa-calendar-minus-o"></i>&nbsp;
                                </button>
                                <button data-valor="posterior" data-id="84264"
                                    title="Alterar data processamento para Amanhã" type="button"
                                    class="btn-sm btn btn-outline-danger btn-atualizar-data-processamento">
                                    <i class="fa fa-arrow-right"></i>&nbsp;
                                </button>
                            </td>
                        </tr>
                    @endforeach


                </tbody>
                <thead class="thead">
                    <tr>
                        <th>Total</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th id="totaldebito">R$ {{ $totalDebito}}</th>
                        <th id="totalcredito">R$ {{ $totalCredito }}</th>
                        <th>=</th>
                        <th id="total">R$ {{ $total = $totalDebito - $totalCredito }}</th>
                    </tr>
                </thead>

            </table>
        </div>
        <div class="card-footer">
            <button id="processar-exclusao" type="button" class="btn btn-danger btn-sm">
                <i class="fa fa-trush"></i>Processar exclusão
            </button>
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
            $('.select2').select2();
        });

        $('form').submit(function(e) {
            e.preventDefault();
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
                                    // $.alert('Confirmar!');
                                    e.currentTarget.submit()
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
        });
    </script>
@endpush
