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
                        versão: 16.09.2025 21:35
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



                        <tr>

                            @can('ENERGIAINJETADA - DASHBOARD')
                                <th>
                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" href="EnergiaInjetada/dashboard">Energia injetada</a>
                                    </nav>
                                </th>
                            @endcan
                        </tr>

                            <tr>
                                @can('WHATSAPP - LISTAR')
                                    <th>
                                        <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                            <a class="btn btn-primary" href="/whatsapp/indexlista">Whatsapp</a>
                                        </nav>

                                    </th>
                                @endcan
                                @can('WHATSAPP - ATENDIMENTO')
                                <th>
                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" href="whatsapp/atendimentoWhatsapp">Whatsapp - atendimento</a>
                                    </nav>

                                </th>
                            @endcan
                        </tr>


                        @can('TRADEIDEA - LISTAR')
                        <tr>
                            <th>

                                <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                    <a class="btn btn-primary" href="/Tradeidea">Investimentos</a>
                                </nav>

                            </th>

                        </tr>
                    @endcan

                        @can('USUARIOS - LISTAR')
                            <tr>
                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" href="/Usuarios">Usuários</a>
                                    </nav>

                                </th>

                            </tr>
                        @endcan

                        @can('SAF_CLUBES - LISTAR')
                            <tr>
                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" href="/SafClubes">SAF - Clubes</a>
                                    </nav>

                                </th>

                            </tr>
                        @endcan

                        @can('SAF_FEDERACOES - LISTAR')
                            <tr>
                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" href="/SafFederacoes">SAF - Federações</a>
                                    </nav>

                                </th>

                            </tr>
                        @endcan

                        @can('SAF_ANOS - LISTAR')
                            <tr>
                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" href="/SafAnos">SAF - Anos</a>
                                    </nav>

                                </th>

                            </tr>
                        @endcan

                        @can('SAF_TIPOS_PRESTADORES - LISTAR')
                            <tr>
                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" href="/SafTiposPrestadores">SAF - Tipos de Prestadores</a>
                                    </nav>

                                </th>

                            </tr>
                        @endcan

                        @can('SAF_FAIXASSALARIAIS - LISTAR')
                            <tr>
                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" href="/SafFaixasSalariais">SAF - Faixas Salariais</a>
                                    </nav>

                                </th>

                            </tr>
                        @endcan

                        @can('CADASTROS - LISTAR')
                            <tr>
                                <th>
                                    <div class="card mb-2" style="background-color: hsla(234, 92%, 47%, 0.04);">
                                        <div class="card-header">Cadastros</div>
                                        <div class="card-body">
                                            @php(
                                                $counts = [
                                                    'representantes' => \App\Models\Representantes::count(),
                                                    'preparadores' => class_exists(\App\Models\Preparadores::class) ? \App\Models\Preparadores::count() : null,
                                                    'funcao' => class_exists(\App\Models\FuncaoProfissional::class) ? \App\Models\FuncaoProfissional::count() : null,
                                                    'categorias' => class_exists(\App\Models\Categorias::class) ? \App\Models\Categorias::count() : null,
                                                    'posicoes' => class_exists(\App\Models\Posicoes::class) ? \App\Models\Posicoes::count() : null,
                                                    'tipoarquivo' => class_exists(\App\Models\TipoArquivo::class) ? \App\Models\TipoArquivo::count() : null,
                                                    'tipoesporte' => class_exists(\App\Models\TipoEsporte::class) ? \App\Models\TipoEsporte::count() : null,
                                                ]
                                            )
                                            <div class="d-flex flex-wrap gap-2">
                                                @can('REPRESENTANTES - LISTAR')
                                                    <a class="btn btn-primary" href="/Representantes">
                                                        Representantes
                                                        @isset($counts['representantes'])
                                                            <span class="badge bg-light text-dark ms-1">{{ $counts['representantes'] }}</span>
                                                        @endisset
                                                    </a>
                                                @endcan
                                                @can('PREPARADORES - LISTAR')
                                                    <a class="btn btn-primary" href="/Preparadores">
                                                        Preparadores
                                                        @isset($counts['preparadores'])
                                                            <span class="badge bg-light text-dark ms-1">{{ $counts['preparadores'] }}</span>
                                                        @endisset
                                                    </a>
                                                @endcan
                                                @can('FUNCAOPROFISSIONAL - LISTAR')
                                                    <a class="btn btn-primary" href="/FuncaoProfissional">
                                                        Função profissional
                                                        @isset($counts['funcao'])
                                                            <span class="badge bg-light text-dark ms-1">{{ $counts['funcao'] }}</span>
                                                        @endisset
                                                    </a>
                                                @endcan
                                                @can('CATEGORIAS - LISTAR')
                                                    <a class="btn btn-primary" href="/Categorias">
                                                        Categorias
                                                        @isset($counts['categorias'])
                                                            <span class="badge bg-light text-dark ms-1">{{ $counts['categorias'] }}</span>
                                                        @endisset
                                                    </a>
                                                @endcan
                                                @can('POSICOES - LISTAR')
                                                    <a class="btn btn-primary" href="/Posicoes">
                                                        Posições
                                                        @isset($counts['posicoes'])
                                                            <span class="badge bg-light text-dark ms-1">{{ $counts['posicoes'] }}</span>
                                                        @endisset
                                                    </a>
                                                @endcan
                                                @can('TIPOARQUIVO - LISTAR')
                                                    <a class="btn btn-primary" href="/TipoArquivo">
                                                        Tipo de Arquivo
                                                        @isset($counts['tipoarquivo'])
                                                            <span class="badge bg-light text-dark ms-1">{{ $counts['tipoarquivo'] }}</span>
                                                        @endisset
                                                    </a>
                                                @endcan
                                                @can('TIPOESPORTE - LISTAR')
                                                    <a class="btn btn-primary" href="/TipoEsporte">
                                                        Tipo de Esporte
                                                        @isset($counts['tipoesporte'])
                                                            <span class="badge bg-light text-dark ms-1">{{ $counts['tipoesporte'] }}</span>
                                                        @endisset
                                                    </a>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                </th>
                            </tr>
                        @endcan


                        @can('PERMISSOES - LISTAR')
                            <tr>
                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" href="/Permissoes">Permissões</a>
                                    </nav>

                                </th>
                            </tr>
                        @endcan

                        @can('SAF_CAMPEONATOS - LISTAR')
                            <tr>
                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" href="/SafCampeonatos">SAF - Campeonatos</a>
                                    </nav>

                                </th>

                            </tr>
                        @endcan


                        @can('FUNCOES - LISTAR')
                            <tr>
                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" href="/Funcoes">Funcões</a>

                                </th>
                            </tr>
                        @endcan


                        {{-- @can('PLANO DE CONTAS - LISTAR')
                                <tr>
                                    <th>

                                        <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                            <a class="btn btn-primary" href="/PlanoContas">Plano de contas padrão</a>
                                        </nav>

                                    </th>
                                </tr>
                            @endcan --}}

                        @can('EMPRESAS - LISTAR')
                            <tr>
                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" href="/Empresas">Empresas</a>
                                    </nav>

                                </th>
                            </tr>
                        @endcan





                        @can('CONTABILIDADE - LISTAR')
                            <tr>
                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" href="/Contabilidade">Contabilidade</a>
                                    </nav>

                                </th>
                            </tr>
                        @endcan

                        @can('CONTASPAGAR - LISTAR')
                            <tr>
                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" href="/ContasPagar">Contas a pagar</a>
                                    </nav>

                                </th>
                            </tr>
                        @endcan

                        @can('LANCAMENTOS DOCUMENTOS - LISTAR')
                            <tr>
                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" href="/LancamentosDocumentos">Documentos</a>
                                    </nav>

                                </th>
                            </tr>
                        @endcan

                        @can('COBRANCA - LISTAR')
                            <tr>
                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" href="/Cobranca">Cobrança</a>
                                    </nav>

                                </th>
                            </tr>
                        @endcan



                        @can('MOEDAS - LISTAR')
                            <tr>

                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" <a href="Moedas/dashboard">Moedas Dashboard</a>

                                    </nav>
                                </th>

                            </tr>
                        @endcan

                        @can('AGENDA - LISTAR')
                            <tr>

                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" <a href="Agenda/dashboard">Calendário Dashboard</a>

                                    </nav>
                                </th>

                            </tr>
                        @endcan

                        @can('GoogleDrive - Opções')
                            <tr>

                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" <a href="drive/dashboard">Google Drive Dashboard</a>

                                    </nav>
                                </th>

                            </tr>
                        @endcan

                        @can('FERIADOS - LISTAR')
                            <tr>

                                <th>

                                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                        <a class="btn btn-primary" <a href="Feriados">Feriados</a>

                                    </nav>
                                </th>

                            </tr>
                        @endcan

                        </tbody>
                    </table>
                    <div class="badge bg-warning text-wrap" style="width: 100%;align=˜Center˜ ">
                        <div class="badge bg-success text-wrap" style="width: 100%;align=˜Center˜ "> . .
                        </div>
                    </div>

                </div>
                <div class="b-example-divider"></div>
            </div>
        @endsection

        @push('scripts')
            <link rel="stylesheet"
                href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
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
