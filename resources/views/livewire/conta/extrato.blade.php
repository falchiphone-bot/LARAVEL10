<div>
    {{-- Close your eyes. Count to one. That is how long forever feels. --}}

    <div class="card">
        <div class="card-body">
            <div class="badge bg-success text-wrap" style="width: 100%;
             ;font-size: 16px; lign=˜Center˜">
                <div class="row">
                    <div class="row py-2">
                        <div class="col-2">
                            <a href="/PlanoContas/dashboard" class="btn btn-warning">Retornar para plano de contas</a>
                        </div>

                        <div class="col-2">
                            <a href="/PlanoContas/pesquisaavancada" class="btn btn-primary">Pesquisa avançada em lançamentos</a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <select class="form-control select2" id="selEmpresa" wire:model="selEmpresa">
                            @foreach ($empresas as $empresa_id => $empresa_descricao)
                                <option value="{{ $empresa_id }}">{{ $empresa_descricao }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <select class="form-control select2" id="selConta" wire:model='selConta' aria-hidden="true">
                            <option value="0">Escolha uma conta</option>
                            @foreach ($contas as $conta_id => $conta_descricao)
                                <option value="{{ $conta_id }}">{{ $conta_descricao }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

            </div>
        </div>
        <div class="badge bg-success text-wrap" style="width: 100%;
     ;font-size: 16px; lign=˜Center˜">

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
                <form id="idform" method="post" class="form">
                    <div class="row">
                        <div class="form-group col-sm-12 col-md-3">
                            <label for="de" class="pr-1  form-control-label">De</label>
                            <input type="date" value="" id="de" name="De" wire:model.defer='De'
                                class="required form-control " autocomplete="off">
                        </div>
                        <div class="form-group col-sm-12 col-md-3">
                            <label for="ate" class="px-1  form-control-label">Até</label>
                            <input type="date" value="" id="ate" name="Ate" placeholder="Buscar até"
                                wire:model.defer='Ate' class="required form-control " autocomplete="off">
                        </div>
                        <div class="form-group col-sm-12 col-md-3">
                            <label for="ate" class="px-1  form-control-label">Conferido</label>
                            <select name="Conferido" id="Conferido" class="form-control" wire:model.defer='Conferido'>
                                <option value="">Todos</option>
                                <option value="1">Conferido</option>
                                <option value="0">Não conferido</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-12 col-md-3">
                            <label for="Notificacao" class="px-1  form-control-label">Notificação</label>
                            <select name="Notificacao" id="Notificacao" class="form-control"
                                wire:model.defer='Notificacao'>
                                <option selected="" value="todos">Todos</option>
                                <option value="1">Notificar</option>
                                <option value="0">Não notificar</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-12 col-md-3">
                            <label for="de" class="pr-1  form-control-label">Buscar Descrição</label>
                            <input type="text" value="" id="descricao" class="form-control" autocomplete="off"
                                wire:model.debounce.800ms='Descricao'>
                        </div>
                        <div class="form-group col-sm-12 col-md-3">
                            <label for="de" class="pr-1  form-control-label">A partir De:</label>
                            <input type="date" value="" id="a_partir_de" class="form-control"
                                autocomplete="off" wire:model.defer='DescricaoApartirDe'>
                        </div>
                        <div class="form-group col-sm-12 col-md-3">
                            <label for="data_bloqueio_conta" class="pr-1  form-control-label">Data Bloqueio
                                Conta:</label>
                            <input type="date" value="" id="data_bloqueio_conta" class="form-control"
                                autocomplete="off" wire:model.defer='data_bloqueio_conta'
                                wire:change='updateDataBloqueioConta()'>
                        </div>
                        <div class="form-group col-sm-12 col-md-3">
                            <label for="data_bloqueio_empresa" class="pr-1  form-control-label">Data Bloqueio
                                Empresa:</label>
                            <input type="date" value="" id="data_bloqueio_empresa" class="form-control"
                                autocomplete="off" wire:model.defer='data_bloqueio_empresa'
                                wire:change='updateDataBloqueioEmpresa()'>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-footer">
                <div class="badge bg-warning text-wrap"
                    style="width: 100%;
             ;font-size: 16px; lign=˜Center˜">


                    <button id="buscar" wire:click='search()' type="button" class="btn btn-primary btn-sm">
                        <i class="fa fa-dot-circle-o"></i>Buscar informações e atualizar visualização
                    </button>
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
                            @endphp
                            @if ($Lancamentos)
                                @foreach ($Lancamentos as $lancamento)
                                    <tr class="tr-{{ $lancamento->ID }} border-bottom-5 border-start-5">
                                        <td>
                                            {{ $lancamento->DataContabilidade->format('d/m/Y') }}
                                        </td>
                                        <td>

                                        </td>
                                        <td>
                                            @if ($Conta->ID == $lancamento->ContaDebitoID)
                                                {{ number_format($lancamento->Valor, 2, ',', '.') }}
                                                @if (!in_array($lancamento->ID, $listaSoma))
                                                    @php($totalDebito += $lancamento->Valor)
                                                    @php($saldo += $lancamento->Valor)
                                                    @php($somatoria += $lancamento->Valor)
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            @if ($Conta->ID == $lancamento->ContaCreditoID)
                                                {{ number_format($lancamento->Valor, 2, ',', '.') }}
                                                @if (!in_array($lancamento->ID, $listaSoma))
                                                    @php($totalCredito += $lancamento->Valor)
                                                    @php($saldo -= $lancamento->Valor)
                                                    @php($somatoria -= $lancamento->Valor)
                                                @endif
                                            @endif
                                        </td>

                                        <td>
                                            {{ number_format($saldo, 2, ',', '.') }}
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
                                                {{ $lancamento->ContaCredito->PlanoConta->Descricao }}
                                            @else
                                                {{ $lancamento->ContaDebito->PlanoConta->Descricao }}
                                            @endif
                                        </td>
                                        <td colspan="2" align="right">

                                            <button title="Botão de Conferência" type="button"
                                                class="btn-sm btn btn-outline-info"
                                                wire:click='confirmarLancamento({{ $lancamento->ID }})'>
                                                @if ($lancamento->Conferido)
                                                    <i class="cl-{{ $lancamento->ID }} fa fa-check-square-o"></i>
                                                @else
                                                    <i class="cl-{{ $lancamento->ID }} fa fa-square-o"></i>
                                                @endif
                                            </button>

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
                                        <th>Total</th>

                                        <th id="totaldebito">R$ {{ number_format($totalDebito, 2, ',', '.') }}</th>
                                        <th id="totalcredito">R$ {{ number_format($totalCredito, 2, ',', '.') }}</th>
                                        <th id="total">R$ {{ number_format($somatoria, 2, ',', '.') }}
                                        </th>
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
                            {{-- <div class="modal-header">
                                <h5 class="modal-title" id="editarLancamentoModalLabel">New message</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div> --}}
                            <div class="modal-body">
                                @if ($editar_lancamento)
                                    @livewire('lancamento.editar-lancamento', ['lancamento_id' => $editar_lancamento])
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">Fechar</button>
                                <button type="button" class="btn btn-primary">Salvar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
            window.initselEmpresaDrop = () => {
                $('#selEmpresa').select2({
                    theme: 'bootstrap-5'
                });
            }
            initselEmpresaDrop();
            $('#selEmpresa').on('change', function(e) {
                livewire.emit('selectedSelEmpresaItem', e.target.value);
            });
            window.livewire.on('select2', () => {
                initselEmpresaDrop();
            });
            //fim-empresa
            //inicio-contas
            window.initselContaDrop = () => {
                $('#selConta').select2({
                    theme: 'bootstrap-5'
                });
            }
            initselContaDrop();
            $('#selConta').on('change', function(e) {
                livewire.emit('selectedSelContaItem', e.target.value);
            });
            window.livewire.on('select2', () => {
                initselContaDrop();
            });
            //fim-contas
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

        // ouvindo eventos do livewire
        window.addEventListener('remove-line-exclusao', event => {
            $('.tr-' + event.detail.lancamento_id).remove();
            console.log(event.detail.lancamento_id);
        });
        window.addEventListener('abrir-modal', event => {
            var myModal = new bootstrap.Modal(document.getElementById('editarLancamentoModal'))
            myModal.show();
        });
        window.addEventListener('confirmarLancamento', event => {
            if (event.detail.status) {
                $('.cl-' + event.detail.lancamento_id).removeClass('fa-square-o');
                $('.cl-' + event.detail.lancamento_id).addClass('fa-check-square-o');
            } else {
                $('.cl-' + event.detail.lancamento_id).removeClass('fa-check-square-o');
                $('.cl-' + event.detail.lancamento_id).addClass('fa-square-o');
            }
        })
    </script>
@endpush
