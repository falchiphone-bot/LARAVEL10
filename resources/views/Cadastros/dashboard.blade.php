@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                @if (session('Lancamento'))
                    <div class="alert alert-success">
                        {{ session('Lancamento') }}
                    </div>
                    {{session(['Lancamento' => null]) }}
                 @endif

                <div class="card-header">
                    <div class="badge bg-primary text-wrap"
                        style="width: 100%;
                    ;font-size: 24px;lign=˜Center˜">
                        Menu Principal do sistema administrativo e contábil - CADASTROS

                    </div>
                </div>
                <div class="card-body">
                    <div class="badge bg-warning text-wrap w-100 mb-3" style="font-size:16px;">Opções para o sistema de cadastros</div>
                    <div class="row g-3">
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
                        <div class="col-12 col-xl-6 col-xxl-4">
                            @include('partials.cadastros_block')
                        </div>
                        <div class="col-12 col-xl-6 col-xxl-4">
                            @include('partials.athletes_block')
                        </div>

                        @canany(['PACPIE - LISTAR'])
                        <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
                            <div class="card h-100">
                                <div class="card-header py-2">PAC PIE</div>
                                <div class="card-body py-2 d-flex flex-column gap-2">
                                    @can('PACPIE - LISTAR')<a class="btn btn-outline-primary btn-sm" href="/Pacpie">Empresas PAC PIE</a>@endcan
                                    @can('PACPIE - LISTAR')<a class="btn btn-outline-primary btn-sm" href="/OrigemPacpie">Origem PAC PIE</a>@endcan
                                </div>
                            </div>
                        </div>
                        @endcanany

                        @canany(['AGRUPAMENTOS CONTAS - LISTAR','CENTROCUSTOS - LISTAR'])
                        <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
                            <div class="card h-100">
                                <div class="card-header py-2">Estrutura Contábil</div>
                                <div class="card-body py-2 d-flex flex-column gap-2">
                                    @can('AGRUPAMENTOS CONTAS - LISTAR')<a class="btn btn-outline-secondary btn-sm" href="/AgrupamentosContas">Agrupamentos de Contas</a>@endcan
                                    @can('CENTROCUSTOS - LISTAR')<a class="btn btn-outline-secondary btn-sm" href="/CentroCustos/dashboard">Centro de custos</a>@endcan
                                </div>
                            </div>
                        </div>
                        @endcanany

                        @canany(['REPRESENTANTES - LISTAR','REPRESENTANTES - CADASTRO DO REPRESENTANTE','TIPOREPRESENTANTES - LISTAR','FORMANDOBASERECEBIMENTOS - LISTAR'])
                        <div class="col-12 col-xl-6 col-xxl-4">
                            <div class="card h-100">
                                <div class="card-header py-2">Representação</div>
                                <div class="card-body py-2 d-flex flex-wrap gap-2">
                                    @can('REPRESENTANTES - LISTAR')<a class="btn btn-outline-dark btn-sm" href="/Representantes">Representantes</a>@endcan
                                    @can('REPRESENTANTES - CADASTRO DO REPRESENTANTE')<a class="btn btn-outline-dark btn-sm" href="/Representantes/RepresentantesCadastro">Cadastro Representante</a>@endcan
                                    @can('TIPOREPRESENTANTES - LISTAR')<a class="btn btn-outline-dark btn-sm" href="/TipoRepresentantes">Tipos</a>@endcan
                                    @can('FORMANDOBASERECEBIMENTOS - LISTAR')<a class="btn btn-outline-dark btn-sm" href="/FormandoBaseRecebimentos">Recebimentos</a>@endcan
                                </div>
                            </div>
                        </div>
                        @endcanany

                        @canany(['SAF_CLUBES - LISTAR','SAF_FEDERACOES - LISTAR','SAF_CAMPEONATOS - LISTAR','SAF_ANOS - LISTAR','SAF_TIPOS_PRESTADORES - LISTAR','SAF_FAIXASSALARIAIS - LISTAR'])
                        <div class="col-12 col-xl-6 col-xxl-4">
                            <div class="card h-100">
                                <div class="card-header py-2">SAF</div>
                                <div class="card-body py-2 d-flex flex-wrap gap-2">
                                    @can('SAF_CLUBES - LISTAR')<a class="btn btn-outline-primary btn-sm" href="/SafClubes">Clubes</a>@endcan
                                    @can('SAF_FEDERACOES - LISTAR')<a class="btn btn-outline-primary btn-sm" href="/SafFederacoes">Federações</a>@endcan
                                    @can('SAF_CAMPEONATOS - LISTAR')<a class="btn btn-outline-primary btn-sm" href="/SafCampeonatos">Campeonatos</a>@endcan
                                    @can('SAF_ANOS - LISTAR')<a class="btn btn-outline-primary btn-sm" href="/SafAnos">Anos</a>@endcan
                                    @can('SAF_TIPOS_PRESTADORES - LISTAR')<a class="btn btn-outline-primary btn-sm" href="/SafTiposPrestadores">Tipos Prestadores</a>@endcan
                                    @can('SAF_FAIXASSALARIAIS - LISTAR')<a class="btn btn-outline-primary btn-sm" href="/SafFaixasSalariais">Faixas</a>@endcan
                                </div>
                            </div>
                        </div>
                        @endcanany

                        @canany(['REDESOCIAL - LISTAR','TIPOARQUIVO - LISTAR','TIPOESPORTE - LISTAR','CATEGORIAS - LISTAR','POSICOES - LISTAR','PREPARADORES - LISTAR','CARGOPROFISSIONAL - LISTAR','FUNCAOPROFISSIONAL - LISTAR'])
                        <div class="col-12 col-xl-6 col-xxl-4">
                            <div class="card h-100">
                                <div class="card-header py-2">Esportes & Arquivos</div>
                                <div class="card-body py-2 d-flex flex-wrap gap-2">
                                    @can('TIPOESPORTE - LISTAR')<a class="btn btn-outline-success btn-sm" href="/TipoEsporte">Tipos Esporte</a>@endcan
                                    @can('CATEGORIAS - LISTAR')<a class="btn btn-outline-success btn-sm" href="/Categorias">Categorias</a>@endcan
                                    @can('POSICOES - LISTAR')<a class="btn btn-outline-success btn-sm" href="/Posicoes">Posições</a>@endcan
                                    @can('PREPARADORES - LISTAR')<a class="btn btn-outline-success btn-sm" href="/Preparadores">Preparadores</a>@endcan
                                    @can('CARGOPROFISSIONAL - LISTAR')<a class="btn btn-outline-success btn-sm" href="/CargoProfissional">Cargos</a>@endcan
                                    @can('FUNCAOPROFISSIONAL - LISTAR')<a class="btn btn-outline-success btn-sm" href="/FuncaoProfissional">Funções</a>@endcan
                                    @can('TIPOARQUIVO - LISTAR')<a class="btn btn-outline-success btn-sm" href="/TipoArquivo">Tipos Arquivo</a>@endcan
                                    @can('REDESOCIAL - LISTAR')<a class="btn btn-outline-success btn-sm" href="/RedeSocial">Redes Sociais</a>@endcan
                                </div>
                            </div>
                        </div>
                        @endcanany

                    </div>
                </div>
            </div>
            <div class="badge bg-warning text-wrap" style="width: 100%;
            ; font-size: 16px;a lign=˜Center˜ ">
            </div>
            <div class="b-example-divider"></div>
        </div>
    @endsection

    @push('scripts')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
            });
        </script>
        {{-- <script>
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
        </script> --}}
    @endpush
