@extends('layouts.bootstrap5')

{{-- //Alterado --}}
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
            @php(session(['error' => null]))
            @endif

            <div class="card">
                <div class="card-header">
                    <div class="badge bg-warning text-wrap"
                        style="width: 100%; height: 50px;
                    ; font-size: 24px;">
                        Menu Principal do sistema administrativo e contábil -
                        versão: 24.09.2025 00:50
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        @if (session('googleUser'))
                        <div class="badge bg-success text-wrap" style="width: 100%;">
                            <div class="col-sm-1">
                                <img src="{{ asset(session('googleUser')->avatar) }}" alt="Minha imagem">

                            </div>

                        @else

                        <a href="{{ url('auth/google') }}" style="margin-top: 0px !important;background: rgb(13, 0, 128);color: #ffffff;padding: 5px;border-radius:7px;" class="ml-2 btn-google">
                            <strong>Login no Google</strong>
                          </a>
                        @endif



                        @if (session('googleUser'))
                                <div class="col-sm-12">
                                    <div class="badge bg-warning text-wrap"
                                        style="width: 40%; height: 30px;
                                             ; font-size: 16px;">
                                        <a href="{{ session('googleUser')->user['link'] }}" target="_blank">Abrir perfil
                                            Google
                                            do usuário em uma nova aba</a>
                                    </div>
                                </div>
                            @endif
                        </div>


                        <div class="badge bg-warning text-wrap" style="width: 100%;">
                            <div class="badge bg-success text-wrap" style="width: 100%;">
                                <thead class="table-light">
                                    <div class="badge bg-success text-wrap"
                                        style="width: 100%;
                                    ; font-size: 16px;">
                                        Opções para o sistema
                                    </div>
                            </div>
                        </div>

                        @can('IRMAOS_EMAUS_NOME_SERVICO - LISTAR')
                            <tr>
                                <th>
                                     <nav class="navbar navbar-red" style="background-color: hsla(234, 98%, 47%, 0.096);">
                                        <a class="btn btn-primary" href="/Irmaos_EmausServicos">Serviços Irmãos de Emaús</a>
                                    </nav>
                                </th>
                            </tr>
                         @endcan


                         @can('IRMAOS_EMAUS_NOME_PIA - LISTAR')
                            <tr>
                                <th>
                                     <nav class="navbar navbar-red" style="background-color: hsla(234, 98%, 47%, 0.096);">
                                        <a class="btn btn-primary" href="/Irmaos_EmausPia">Tópicos para o PIA de Irmãos de Emaús</a>
                                    </nav>
                                </th>
                            </tr>
                         @endcan

                        @can('IRMAOS_EMAUS_FICHA_CONTROLE - LISTAR')
                            <tr>
                                <th>
                                     <nav class="navbar navbar-red" style="background-color: hsla(234, 98%, 47%, 0.096);">
                                        <a class="btn btn-primary" href="/Irmaos_Emaus_FichaControle">Ficha de controle do Irmãos de Emaús</a>
                                    </nav>
                                </th>
                            </tr>
                         @endcan


                        @can('CONTABILIDADE - LISTAR-AQUI-TAMBEM')
                            <tr>
                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-warning" href="/Contabilidade">Contabilidade</a>
                                    </nav>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-warning" href="/Cobranca">Cobrança</a>
                                    </nav>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" href="{{ route('lancamentos.balancete') }}">Balancete por período</a>
                                    </nav>

                                </th>
                            </tr>
                         @endcan


                        <tr>

                            @can('CLIENTESIXCNETRUBI - LISTAR')
                                <th>
                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" href="Ixc">IXC NET RUBI</a>
                                    </nav>
                                </th>
                            @endcan
                        </tr>

                        <tr>

                            @can('PACPIE - LISTAR')
                                <th>
                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" href="Pacpie">EMPRESAS PARA PAC PIE - ESTADO DE SÃO PAULO</a>
                                    </nav>
                                </th>
                            @endcan
                        </tr>



                        <div class="card-body">
                            <div class="mb-3">
                                @if (session('googleUser'))
                                    <div class="badge bg-success text-wrap w-100 text-start">
                                        <div class="col-sm-1">
                                            <img src="{{ asset(session('googleUser')->avatar) }}" alt="Avatar" style="max-height:48px;border-radius:4px;">
                                        </div>
                                    @else
                                        <a href="{{ url('auth/google') }}" class="btn btn-sm" style="margin-top:0;background:#0d0080;color:#fff;padding:6px 10px;border-radius:7px;">
                                            <strong>Login no Google</strong>
                                        </a>
                                    @endif
                                    @if (session('googleUser'))
                                        <div class="col-sm-12 mt-2">
                                            <a class="badge bg-warning text-wrap" style="width:40%;height:30px;font-size:14px;" href="{{ session('googleUser')->user['link'] }}" target="_blank">Abrir perfil Google do usuário</a>
                                        </div>
                                    @endif
                                </div>

                            <div class="row g-3 mt-2">
                                                            @if(session('status'))
                                                                <div class="col-12">
                                                                    <div class="alert alert-success py-2 mb-2">{{ session('status') }}</div>
                                                                </div>
                                                            @endif
                                                            <div class="col-12 d-flex justify-content-end gap-2 mb-1">
                                                                <button id="load-counts-btn" type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Carregar os contadores desta página sob demanda (evita processar ao abrir o Dashboard)">
                                                                    Carregar contadores
                                                                </button>
                                                                <form method="POST" action="{{ route('dashboard.refresh-counters') }}" class="d-inline">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="Limpa o cache dos contadores para recalcular na próxima carga">
                                                                        Atualizar contadores
                                                                    </button>
                                                                </form>
                                                            </div>


                                {{-- Bloco Cadastros --}}
                                <div class="col-12 col-lg-6 col-xxl-4">
                                    @include('partials.cadastros_block')
                                </div>

                                {{-- Bloco Atletas --}}
                                <div class="col-12 col-lg-6 col-xxl-4">
                                    @include('partials.athletes_block')
                                </div>

                                {{-- Funções / permissões / usuários --}}
                                @can('PERMISSOES - LISTAR')
                                <div class="col-12 col-md-6 col-xl-3">
                                    <div class="card h-100">
                                        <div class="card-header py-2">Permissões & Usuários</div>
                                        <div class="card-body py-2 d-flex flex-column gap-2">
                                            <a class="btn btn-primary btn-sm" href="/Permissoes">Permissões</a>
                                            @can('USUARIOS - LISTAR')
                                                <a class="btn btn-primary btn-sm" href="/Usuarios">Usuários</a>
                                            @endcan
                                            @can('FUNCOES - LISTAR')
                                                <a class="btn btn-primary btn-sm" href="/Funcoes">Funções</a>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                                @endcan

                                {{-- SAF blocos --}}
                                @canany(['SAF_CLUBES - LISTAR','SAF_FEDERACOES - LISTAR','SAF_CAMPEONATOS - LISTAR','SAF_ANOS - LISTAR','SAF_TIPOS_PRESTADORES - LISTAR','SAF_FAIXASSALARIAIS - LISTAR','SAF_COLABORADORES - LISTAR'])
                                <div class="col-12 col-xl-6 col-xxl-4">
                                    <div class="card h-100">
                                        <div class="card-header py-2">SAF</div>
                                        <div class="card-body py-2 d-flex flex-wrap gap-2">
                                            @can('SAF_CLUBES - LISTAR')<a class="btn btn-outline-primary btn-sm" href="/SafClubes">Clubes</a>@endcan
                                            @can('SAF_FEDERACOES - LISTAR')<a class="btn btn-outline-primary btn-sm" href="/SafFederacoes">Federações</a>@endcan
                                            @can('SAF_CAMPEONATOS - LISTAR')<a class="btn btn-outline-primary btn-sm" href="/SafCampeonatos">Campeonatos</a>@endcan
                                            @can('SAF_ANOS - LISTAR')<a class="btn btn-outline-primary btn-sm" href="/SafAnos">Temporadas</a>@endcan
                                            @can('SAF_TIPOS_PRESTADORES - LISTAR')<a class="btn btn-outline-primary btn-sm" href="/SafTiposPrestadores">Tipos de Colaboradores</a>@endcan
                                            @can('SAF_FAIXASSALARIAIS - LISTAR')<a class="btn btn-outline-primary btn-sm" href="/SafFaixasSalariais">Faixas Salariais</a>@endcan
                                            @can('SAF_COLABORADORES - LISTAR')<a class="btn btn-outline-primary btn-sm" href="/SafColaboradores">Colaboradores</a>@endcan
                                        </div>
                                    </div>
                                </div>
                                @endcanany

                                {{-- Comunicação / WhatsApp --}}
                                @canany(['WHATSAPP - LISTAR','WHATSAPP - ATENDIMENTO'])
                                <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
                                    <div class="card h-100">
                                        <div class="card-header py-2">Comunicação</div>
                                        <div class="card-body py-2 d-flex flex-column gap-2">
                                            @can('WHATSAPP - LISTAR')<a class="btn btn-outline-primary btn-sm" href="/whatsapp/indexlista">Whatsapp</a>@endcan
                                            @can('WHATSAPP - ATENDIMENTO')<a class="btn btn-outline-primary btn-sm" href="whatsapp/atendimentoWhatsapp">Atendimento</a>@endcan
                                        </div>
                                    </div>
                                </div>
                                @endcanany

                                {{-- Contabilidade / Financeiro --}}
                                @canany(['CONTABILIDADE - LISTAR','CONTABILIDADE - LISTAR-AQUI-TAMBEM','CONTASPAGAR - LISTAR','COBRANCA - LISTAR','LANCAMENTOS DOCUMENTOS - LISTAR','EMPRESAS - LISTAR'])
                                <div class="col-12 col-xl-6 col-xxl-4">
                                    <div class="card h-100">
                                        <div class="card-header py-2">Financeiro & Contabilidade</div>
                                        <div class="card-body py-2 d-flex flex-wrap gap-2">
                                            @can('CONTABILIDADE - LISTAR')<a class="btn btn-outline-warning btn-sm" href="/Contabilidade">Contabilidade</a>@endcan
                                            @can('CONTABILIDADE - LISTAR-AQUI-TAMBEM')<a class="btn btn-outline-warning btn-sm" href="/Cobranca">Cobrança</a>@endcan
                                            @can('CONTASPAGAR - LISTAR')<a class="btn btn-outline-warning btn-sm" href="/ContasPagar">Contas a pagar</a>@endcan
                                            @can('LANCAMENTOS DOCUMENTOS - LISTAR')<a class="btn btn-outline-warning btn-sm" href="/LancamentosDocumentos">Documentos</a>@endcan
                                            @can('EMPRESAS - LISTAR')<a class="btn btn-outline-warning btn-sm" href="/Empresas">Empresas</a>@endcan
                                        </div>
                                    </div>
                                </div>
                                @endcanany

                                {{-- Energia / Agenda / Drive / Feriados / Moedas --}}
                                @canany(['ENERGIAINJETADA - DASHBOARD','AGENDA - LISTAR','GoogleDrive - Opções','FERIADOS - LISTAR','MOEDAS - LISTAR'])
                                <div class="col-12 col-xl-6 col-xxl-4">
                                    <div class="card h-100">
                                        <div class="card-header py-2">Operacional & Utilidades</div>
                                        <div class="card-body py-2 d-flex flex-wrap gap-2">
                                            @can('ENERGIAINJETADA - DASHBOARD')<a class="btn btn-outline-success btn-sm" href="EnergiaInjetada/dashboard">Energia injetada</a>@endcan
                                            @can('MOEDAS - LISTAR')<a class="btn btn-outline-success btn-sm" href="Moedas/dashboard">Moedas</a>@endcan
                                            @can('AGENDA - LISTAR')<a class="btn btn-outline-success btn-sm" href="Agenda/dashboard">Calendário</a>@endcan
                                            @can('GoogleDrive - Opções')<a class="btn btn-outline-success btn-sm" href="drive/dashboard">Google Drive</a>@endcan
                                            @can('FERIADOS - LISTAR')<a class="btn btn-outline-success btn-sm" href="Feriados">Feriados</a>@endcan
                                            @can('PIX - LISTAR')<a class="btn btn-outline-success btn-sm" href="{{ route('Pix.index') }}">PIX</a>@endcan
                                            @can('FORMA_PAGAMENTOS - LISTAR')<a class="btn btn-outline-success btn-sm" href="{{ route('FormaPagamento.index') }}">Formas de Pagamento</a>@endcan
                                            @can('ENVIOS - LISTAR')<a class="btn btn-outline-success btn-sm" href="{{ route('Envios.index') }}">Envios de arquivos</a>@endcan
                                        </div>
                                    </div>
                                </div>
                                @endcanany

                                {{-- Investimentos / Trade --}}
                                @can('TRADEIDEA - LISTAR')
                                <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
                                    <div class="card h-100">
                                        <div class="card-header py-2">Investimentos</div>
                                        <div class="card-body py-2 d-flex flex-column gap-2">
                                            <a class="btn btn-outline-info btn-sm" href="/Tradeidea">Tradeideas</a>
                                        </div>
                                    </div>
                                </div>
                                @endcan

                                {{-- Irmãos de Emaús --}}
                                @canany(['IRMAOS_EMAUS_NOME_SERVICO - LISTAR','IRMAOS_EMAUS_NOME_PIA - LISTAR','IRMAOS_EMAUS_FICHA_CONTROLE - LISTAR'])
                                <div class="col-12 col-xl-6 col-xxl-4">
                                    <div class="card h-100">
                                        <div class="card-header py-2">Irmãos de Emaús</div>
                                        <div class="card-body py-2 d-flex flex-wrap gap-2">
                                            @can('IRMAOS_EMAUS_NOME_SERVICO - LISTAR')<a class="btn btn-outline-secondary btn-sm" href="/Irmaos_EmausServicos">Serviços</a>@endcan
                                            @can('IRMAOS_EMAUS_NOME_PIA - LISTAR')<a class="btn btn-outline-secondary btn-sm" href="/Irmaos_EmausPia">PIA</a>@endcan
                                            @can('IRMAOS_EMAUS_FICHA_CONTROLE - LISTAR')<a class="btn btn-outline-secondary btn-sm" href="/Irmaos_Emaus_FichaControle">Ficha Controle</a>@endcan
                                        </div>
                                    </div>
                                </div>
                                @endcanany

                                {{-- PAC PIE / IXC --}}
                                @canany(['PACPIE - LISTAR','CLIENTESIXCNETRUBI - LISTAR'])
                                <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
                                    <div class="card h-100">
                                        <div class="card-header py-2">Integrações / Projetos</div>
                                        <div class="card-body py-2 d-flex flex-column gap-2">
                                            @can('PACPIE - LISTAR')<a class="btn btn-outline-dark btn-sm" href="Pacpie">PAC PIE</a>@endcan
                                            @can('CLIENTESIXCNETRUBI - LISTAR')<a class="btn btn-outline-dark btn-sm" href="Ixc">IXC NET RUBI</a>@endcan
                                        </div>
                                    </div>
                                </div>
                                @endcanany

                                {{-- Backups (card dedicado) --}}
                                @canany(['backup.executar','backup.executar.hd','backup.executar.ftp','backup.logs.view','backup.logs.clear','backup.logs.download'])
                                <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
                                    <div class="card h-100">
                                        <div class="card-header py-2">Backups</div>
                                        <div class="card-body py-2 d-flex flex-column gap-2">
                                            @canany(['backup.executar','backup.executar.hd','backup.executar.ftp'])
                                                <div class="d-flex flex-wrap align-items-center gap-2">
                                                    @canany(['backup.executar','backup.executar.hd'])
                                                    <button id="backup-btn" class="btn btn-outline-danger btn-sm">
                                                        Storage → HD Externo
                                                    </button>
                                                    @endcanany
                                                    <span id="backup-status" class="small text-muted"></span>
                                                </div>
                                                <div class="d-flex flex-wrap align-items-center gap-2">
                                                    @canany(['backup.executar','backup.executar.ftp'])
                                                    <button id="backup-ftp-btn" class="btn btn-outline-primary btn-sm">
                                                        Storage → FTP
                                                    </button>
                                                    <button id="backup-ftp-test-btn" class="btn btn-outline-secondary btn-sm" title="Testa conexão FTP (dry-run)">
                                                        Testar FTP (dry-run)
                                                    </button>
                                                    @endcanany
                                                    <span id="backup-ftp-status" class="small text-muted"></span>
                                                </div>
                                            @endcanany

                                            <div class="d-flex flex-wrap align-items-center gap-2">
                                                @can('backup.logs.view')
                                                    <a href="{{ url('/backup/ftp-logs') }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm">
                                                        Ver logs FTP
                                                    </a>
                                                @endcan
                                                @can('backup.logs.download')
                                                    <a href="{{ url('/backup/ftp-logs/download-last?n=500&format=ndjson') }}" class="btn btn-outline-secondary btn-sm" title="Baixar últimos 500 registros (NDJSON)">
                                                        Baixar últimos logs
                                                    </a>
                                                @endcan
                                                @can('backup.logs.clear')
                                                    <form method="POST" action="{{ url('/backup/ftp-logs/clear') }}" class="d-inline" onsubmit="return confirm('Limpar/arquivar logs de backup?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-warning btn-sm">Limpar logs</button>
                                                    </form>
                                                @endcan
                                                {{-- @can('backup.executar.ftp')
                                                    <a href="#" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center" title="Abrir navegador para realizar download de arquivos do FTP (bloqueado no IP do servidor)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-1">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2M7 10l5 5m0 0 5-5m-5 5V4" />
                                                        </svg>
                                                        Download FTP
                                                    </a>
                                                @endcan --}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endcanany

                            </div> <!-- /row -->

                            <div class="badge bg-warning text-wrap mt-4" style="width:100%;">
                                <div class="badge bg-success w-100"> . . </div>
                            </div>
                            <!-- Fim grid -->
                        </div><!-- /table-responsive -->
                        @push('scripts')
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                                tooltipTriggerList.map(el => new bootstrap.Tooltip(el));

                                let countsLoaded = false;
                                const idsAll = ['count-representantes','count-preparadores','count-funcao','count-categorias','count-posicoes','count-tipoarquivo','count-tipoesporte','count-formandos','count-flow','count-percentuais'];
                                const setText = (id, val) => {
                                    const el = document.getElementById(id);
                                    if (el) el.textContent = (val === null || typeof val === 'undefined') ? '-' : String(val);
                                };

                                function loadDashboardCounts() {
                                    if (countsLoaded) return; // evita chamadas repetidas
                                    countsLoaded = true;
                                    fetch("{{ route('dashboard.counts') }}", {
                                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                    })
                                    .then(resp => resp.json())
                                    .then(data => {
                                        const cad = data.cadastros || {};
                                        const ath = data.athletes || {};
                                        // Cadastros
                                        setText('count-representantes', cad.representantes);
                                        setText('count-preparadores', cad.preparadores);
                                        setText('count-funcao', cad.funcao);
                                        setText('count-categorias', cad.categorias);
                                        setText('count-posicoes', cad.posicoes);
                                        setText('count-tipoarquivo', cad.tipoarquivo);
                                        setText('count-tipoesporte', cad.tipoesporte);
                                        // Atletas
                                        setText('count-formandos', ath.formandos);
                                        setText('count-flow', ath.flow);
                                        setText('count-percentuais', ath.percentuais);
                                    })
                                    .catch(() => {
                                        idsAll.forEach(id => { const el = document.getElementById(id); if (el) el.textContent = '-'; });
                                    });
                                }

                                // Botão para carregar sob demanda
                                const btn = document.getElementById('load-counts-btn');
                                if (btn) {
                                    btn.addEventListener('click', function() {
                                        loadDashboardCounts();
                                    });
                                }
                            });
                        </script>
                        @endpush


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Backup HD externo
    const btn = document.getElementById('backup-btn');
    const status = document.getElementById('backup-status');
    if (btn) {
        btn.addEventListener('click', function(e) {
            if (!confirm('Deseja realmente fazer o backup do Storage para o HD externo?')) return;
            btn.disabled = true;
            status.innerHTML = 'Processando...';
            fetch("{{ url('/backup/storage-to-external') }}", {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
            })
            .then(resp => resp.json())
            .then(data => {
                if (data.status === 'ok') {
                    status.innerHTML = 'Backup realizado com sucesso! ('+data.total+' arquivos)';
                } else {
                    status.innerHTML = 'Erro: ' + (data.mensagem || 'Falha desconhecida');
                }
            })
            .catch(() => {
                status.innerHTML = 'Erro ao processar backup.';
            })
            .finally(() => {
                btn.disabled = false;
            });
        });
    }

    // Backup FTP
    const btnFtp = document.getElementById('backup-ftp-btn');
    const statusFtp = document.getElementById('backup-ftp-status');
    if (btnFtp) {
        let backupFtpInProgress = false;
        btnFtp.addEventListener('click', function(e) {
            if (backupFtpInProgress) return;
            backupFtpInProgress = true;
            if (!confirm('Deseja realmente fazer o backup do Storage para o servidor FTP?')) {
                backupFtpInProgress = false;
                return;
            }
            btnFtp.disabled = true;
            statusFtp.innerHTML = 'Processando...';
            fetch("{{ url('/backup/storage-to-ftp') }}", {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
            })
            .then(resp => {
                // Sempre tenta parsear como JSON, mesmo se erro
                return resp.json().catch(() => ({ status: 'erro', mensagem: 'Resposta inesperada do servidor.' }));
            })
            .then(data => {
                if (data.status === 'ok') {
                    statusFtp.innerHTML = data.mensagem ? data.mensagem : 'Backup FTP realizado com sucesso! ('+data.total+' arquivos)';
                } else {
                    statusFtp.innerHTML = data.mensagem || 'Backup enfileirado. Verifique o worker.';
                }
            })
            .catch((err) => {
                statusFtp.innerHTML = 'Backup enfileirado. Verifique o worker.';
                console.error('Erro no backup FTP:', err);
            })
            .finally(() => {
                btnFtp.disabled = false;
                backupFtpInProgress = false;
            });
        });
    }

    // Teste de conexão FTP (dry-run)
    const btnFtpTest = document.getElementById('backup-ftp-test-btn');
    if (btnFtpTest) {
        btnFtpTest.addEventListener('click', function() {
            btnFtpTest.disabled = true;
            const statusEl = document.getElementById('backup-ftp-status');
            if (statusEl) statusEl.innerHTML = 'Testando conexão FTP...';
            fetch("{{ route('backup.ftp-test') }}", {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
            })
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    statusEl.innerHTML = `Conexão OK em ${data.host}:${data.port}`;
                } else {
                    statusEl.innerHTML = 'Falha no teste FTP: ' + (data.message || 'erro desconhecido');
                }
            })
            .catch(() => {
                if (statusEl) statusEl.innerHTML = 'Erro ao testar FTP';
            })
            .finally(() => { btnFtpTest.disabled = false; });
        });
    }
});
</script>
@endpush
                        {{-- Removido bloco duplicado dos botões de backup --}}
@endsection
