@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
                {{ session(['error' => null]) }}
            @endif

            <div class="card">
                <div class="card-header">
                    <div class="badge bg-warning text-wrap"
                        style="width: 100%; height: 50px;
                    ; font-size: 24px;align=˜Center˜ ">
                        Menu Principal do sistema administrativo e contábil -
                        versão: 19.09.2025 13:15
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-success table-striped">
                        @if (session('googleUser'))
                        <div class="badge bg-success text-wrap" style="width: 100%;align=˜left˜ ">
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
                                             ; font-size: 16px;align=˜Center˜ ">
                                        <a href="{{ session('googleUser')->user['link'] }}" target="_blank">Abrir perfil
                                            Google
                                            do usuário em uma nova aba</a>
                                    </div>
                                </div>
                            @endif
                        </div>


                        <div class="badge bg-warning text-wrap" style="width: 100%;align=˜Center˜ ">
                            <div class="badge bg-success text-wrap" style="width: 100%;align=˜Center˜ ">
                                <thead class="table-light">
                                    <div class="badge bg-success text-wrap"
                                        style="width: 100%;
                                    ; font-size: 16px;align=˜Center˜ ">
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
                                                            <div class="col-12 d-flex justify-content-end mb-1">
                                                                <form method="POST" action="{{ route('dashboard.refresh-counters') }}" class="d-inline">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="Recalcular imediatamente os contadores em cache">
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
                                            @can('backup.executar')
                                                <button id="backup-btn" class="btn btn-outline-danger btn-sm">
                                                    Backup Storage → HD Externo
                                                </button>
                                                <span id="backup-status" style="margin-left:10px;"></span>
                                            @endcan
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

                            </div> <!-- /row -->

                            <div class="badge bg-warning text-wrap mt-4" style="width:100%;">
                                <div class="badge bg-success w-100"> . . </div>
                            </div>
                            <!-- Fim grid -->
                        @endsection

                        @push('scripts')
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                                tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
                            });
                        </script>
                        @endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>
@endpush
