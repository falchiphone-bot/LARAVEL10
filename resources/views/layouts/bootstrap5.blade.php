<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.css"
        crossorigin="anonymous">

    {{-- <link rel="stylesheet" href="https://getbootstrap.com/docs/5.3/examples/features/features.css" crossorigin="anonymous"> --}}
    @stack('styles')
  {{-- Prism.js para destaque de código --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/themes/prism.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/plugins/line-numbers/prism-line-numbers.min.css">

    {{-- LINK PARA PEGAR NOME DE ICONES --}}
    {{-- https://fontawesome.com/search?q=money&o=r&m=free --}}

    <title>Sistema administrativo e contábil</title>
    <style>
        .custom-tooltip {
            --bs-tooltip-bg: var(--bs-danger);
        }

    /* Quebrar a navegação em linhas com 5 itens por linha */
    #main-nav-links {
      width: 100%;
      flex-wrap: wrap; /* permite quebrar linha */
    }
    #main-nav-links > li {
      flex: 0 0 20%; /* 5 por linha */
      max-width: 20%;
      display: flex; /* manter altura uniforme */
      align-items: stretch;
    }
    #main-nav-links > li > .nav-link {
      width: 100%;
    }
    </style>
    @livewireStyles
</head>


{{-- <script type="text/javascript"> //<![CDATA[
    var tlJsHost = ((window.location.protocol == "https:") ? "https://secure.trust-provider.com/" : "http://www.trustlogo.com/");
    document.write(unescape("%3Cscript src='" + tlJsHost + "trustlogo/javascript/trustlogo.js' type='text/javascript'%3E%3C/script%3E"));
  //]]></script>
  <script language="JavaScript" type="text/javascript">
    TrustLogo("https://www.positivessl.com/images/seals/positivessl_trust_seal_lg_222x54.png", "POSDV", "none");
  </script> --}}


<body>

  <!-- Toggle Layout (Enable/Disable header/navigation) -->
  <button id="toggle-layout" class="toggle-layout-button" title="Desativar/Ativar layout (oculta/exibe cabeçalho)">Layout</button>

    <main>
        <header>
@can('GOOGLE - PESQUISA')
<div class="px-3 py-2 text-bg-primary text-center">
  <h3 class="m-0">PESQUISAR NO GOOGLE</h3>

  <script async src="https://cse.google.com/cse.js?cx=6766aee62d05f4aa7"></script>
  <div class="gcse-search"></div>
</div>
@endcan


            <div class="px-3 py-2 text-bg-primary">
                <div class="container">
                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
                        <a href="/"
                            class="d-flex align-items-center my-2 my-lg-0 me-lg-auto text-white text-decoration-none">
                            <svg class="bi me-2" width="40" height="32" role="img" aria-label="Bootstrap">
                                <use xlink:href="#bootstrap"></use>
                            </svg>
                        </a>

                        <ul id="main-nav-links" class="nav col-12 col-lg-auto my-2 justify-content-center my-md-0 text-small">

                            <li>
                                <style>
                                    .link-esquerda {
                                        position: fixed;
                                        left: 500px;
                                        top: 3%;
                                        transform: translateY(-50%);
                                        color: white;
                                    }
                                </style>

                                {{-- <a href="#" onclick="scrollToBottom('left'); return false;" class="link-esquerda">Ir para a parte inferior</a> --}}
                                <button onclick="scrollToBottom('left');" class="botao-navegacao">⬇️ Ir para o fim. Parte inferior.</button>

                            </li>
<!-- Font Awesome CDN (adicione no <head>) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

<!-- Botão Toggle -->
<button id="toggle-menu" class="toggle-button" title="Menu">
  <i class="fas fa-bars"></i>
</button>

<!-- Menu Lateral -->
<div id="menu-lateral" class="menu-lateral fechado">
  <button onclick="window.history.back();" title="Voltar">
    <i class="fas fa-arrow-left"></i>
  </button>
  <button onclick="window.history.forward();" title="Avançar">
    <i class="fas fa-arrow-right"></i>
  </button>
  <button onclick="window.scrollTo({ top: 0, behavior: 'smooth' });" title="Topo">
    <i class="fas fa-arrow-up"></i>
  </button>
  <button onclick="scrollToBottom();" title="Fim">
    <i class="fas fa-arrow-down"></i>
  </button>

      <button onclick="scrollByLines(-6);" title="Subir 6 linhas">
      <i class="fas fa-angle-double-up"></i>
    </button>


    <button onclick="scrollByLines(6);" title="Descer 6 linhas">
      <i class="fas fa-angle-double-down"></i>
    </button>





</div>

<!-- Estilo -->
<style>
  .toggle-button {
    position: fixed;
    top: 20px;
    left: 10px;
    z-index: 1100;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 8px 10px;
    cursor: pointer;
    font-size: 20px;
    transition: background-color 0.3s ease;
  }

  .toggle-button:hover {
    background-color: #0056b3;
  }

  .menu-lateral {
    position: fixed;
    top: 80px;
    left: 10px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    z-index: 1000;
    transition: transform 0.3s ease;
  }

  .menu-lateral.fechado {
    transform: translateX(-100px);
    opacity: 0;
    pointer-events: none;
  }

  .menu-lateral button {
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 50%;
    width: 44px;
    height: 44px;
    font-size: 18px;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }

  .menu-lateral button:hover {
    background-color: #0056b3;
  }

  .menu-lateral button i {
    pointer-events: none;
  }
</style>

<!-- Script -->
<script>
  // Garante que só exista UM script aberto e tudo execute corretamente
  document.addEventListener('DOMContentLoaded', function() {
    var toggleButton = document.getElementById('toggle-menu');
    var menu = document.getElementById('menu-lateral');
    if (toggleButton && menu) {
      toggleButton.addEventListener('click', function() {
        menu.classList.toggle('fechado');
      });
    }
    // Funções globais para rolar
    window.scrollToBottom = function() {
      window.scrollTo({
        top: document.body.scrollHeight,
        behavior: 'smooth'
      });
    }
    window.scrollByLines = function(lines) {
      const lineHeight = 30;
      window.scrollBy({
        top: lines * lineHeight,
        behavior: 'smooth'
      });
    }
  });
</script>





<script>

function aplicarMascaraValor() {
  var input = document.getElementById('valor');
  if (input && !input.hasAttribute('data-mascara')) {
    input.setAttribute('data-mascara', 'true');
    input.addEventListener('blur', function () {
      var val = input.value.replace(/\D/g, ''); // só números
      if (val.length > 2) {
        var inteiro = val.slice(0, -2);
        var centavos = val.slice(-2);
        var novoValor = parseInt(inteiro, 10).toLocaleString('pt-BR') + ',' + centavos;
        input.value = novoValor;
      } else if (val.length === 2) {
        input.value = '0,' + val;
      } else if (val.length === 1) {
        input.value = '0,0' + val;
      }
      if (input.cleave) {
        input.cleave.setRawValue(input.value);
      }
      input.dispatchEvent(new Event('input', { bubbles: true }));
    });
  }
}

document.addEventListener('DOMContentLoaded', aplicarMascaraValor);
if (window.Livewire) {
  window.Livewire.hook('message.processed', function () {
    aplicarMascaraValor();
  });
}
</script>

<style>
  /* Botão flutuante para ativar/desativar o layout (sempre visível, mesmo com header oculto) */
  .toggle-layout-button {
    position: fixed;
    top: 20px;
    left: 60px; /* evita sobrepor o #toggle-menu */
    z-index: 1200;
    background-color: #6c757d;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 8px 10px;
    cursor: pointer;
    font-size: 13px;
    line-height: 1;
    transition: background-color 0.2s ease;
  }
  .toggle-layout-button:hover { background-color: #5a6268; }
  /* Quando layout estiver desativado, ocultar o header */
  body.layout-off header { display: none !important; }
  /* Dar destaque ao botão quando off */
  body.layout-off #toggle-layout { background-color: #212529; }
</style>

<script>
  // Toggle do layout (exibe/oculta cabeçalho/navigation do layout bootstrap5)
  (function(){
    const LS_KEY = 'bootstrap5.layout.enabled';
    function isEnabled(){
      try{ const v = localStorage.getItem(LS_KEY); return v === null ? true : v === '1'; }catch(_e){ return true; }
    }
    function setEnabled(on){
      try{ localStorage.setItem(LS_KEY, on ? '1' : '0'); }catch(_e){}
      document.body.classList.toggle('layout-off', !on);
      const btn = document.getElementById('toggle-layout');
      if(btn){ btn.textContent = on ? 'Desativar Layout' : 'Ativar Layout'; }
    }
    document.addEventListener('DOMContentLoaded', function(){
      // Checa parâmetro de URL (?layout=off|on) para forçar estado inicial
      try{
        const url = new URL(window.location.href);
        const qp = (url.searchParams.get('layout') || '').toLowerCase();
        if(qp === 'off'){ setEnabled(false); }
        else if(qp === 'on'){ setEnabled(true); }
        else { setEnabled(isEnabled()); }
      }catch(_e){ setEnabled(isEnabled()); }
      const btn = document.getElementById('toggle-layout');
      if(btn){ btn.addEventListener('click', function(){ setEnabled(!isEnabled()); }); }
    });
  })();
</script>

<script>
  // Preenchimento das badges do dropdown Contabilidade.
  // Observação: só buscamos o endpoint se houver ao menos um badge-alvo no DOM
  // para evitar requisições desnecessárias em todas as páginas.
  document.addEventListener('DOMContentLoaded', function() {
    try {
      const badgeIds = [
        'count-contaspagar',
        'count-empresas',
        'count-centro_custos',
        'count-contas_centro_custos'
      ];

      let loaded = false;
      function fillBadges() {
        if (loaded) return;
        const hasAnyBadge = badgeIds.some(id => document.getElementById(id));
        if (!hasAnyBadge) return;
        loaded = true;
        fetch(@json(route('dashboard.counts')), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
          .then(r => r.ok ? r.json() : null)
          .then(data => {
            if (!data) return;
            const fin = data.finance || {};
            const setBadge = (id, val) => {
              const el = document.getElementById(id);
              if (!el) return;
              if (val === null || typeof val === 'undefined') {
                el.style.display = 'none';
              } else {
                el.textContent = String(val);
                el.style.display = '';
              }
            };
            setBadge('count-contaspagar', fin.contaspagar);
            setBadge('count-empresas', fin.empresas);
            setBadge('count-centro_custos', fin.centro_custos);
            setBadge('count-contas_centro_custos', fin.contas_centro_custos);
          })
          .catch(() => {/* silencioso */});
      }

      // Adia a carga até interação no menu (mouseover ou click do dropdown Contabilidade)
  const contabMenu = document.getElementById('dropdown-contabilidade');
      if (contabMenu) {
        contabMenu.addEventListener('mouseenter', fillBadges, { once: true });
        contabMenu.addEventListener('click', fillBadges, { once: true });
      } else {
        // Fallback: se o elemento não existir, não faz fetch algum
      }
    } catch (e) { /* noop */ }
  });
  </script>




              @php
                  // Super Admin sempre deve ver o link de Início do sistema
                  $isSuperAdmin = auth()->check() && auth()->user()->hasAnyRole(['super-admin','Super-Admin','Super Admin','SuperAdmin']);
              @endphp
              @if($isSuperAdmin)
                <li>
                  <!-- Super Admin: link ativo -->
                  <a href="{{ route('dashboard') }}" data-bs-toggle="tooltip" data-bs-placement="top"
                     data-bs-custom-class="custom-tooltip"
                     data-bs-title="Ir para a página inicial (Super Admin)"
                     class="nav-link text-white">
                     <i class="fa-solid fa-house"></i>
                     Início do sistema
                  </a>
                </li>
              @else
                @canany(['IRMAOS_EMAUS_NOME_SERVICO - LISTAR','IRMAOS_EMAUS_NOME_PIA - LISTAR','IRMAOS_EMAUS_FICHA_CONTROLE - LISTAR'])
                  {{-- Usuários com permissões de Emaús não veem o link do Início --}}
                @else
                  <li>
              <a href="/" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                data-bs-title="Ir para a página inicial"
                class="nav-link text-white">
                       <i class="fa-solid fa-house"></i>
                       Início do sistema
                    </a>
                  </li>
                @endcanany
              @endif
              {{-- Market status desabilitado --}}
              {{-- @can('MERCADO - VER STATUS')
              <li class="ms-2 d-none d-md-flex align-items-center">
                <span id="market-status-global" class="badge rounded-pill bg-secondary" title="Status do mercado (NYSE)">Mercado: carregando…</span>
              </li>
              @endcan --}}

              {{-- Link: Rádio online (público) --}}
              <li>
                <a href="{{ route('radio.liveprf') }}"
                   class="nav-link text-white"
                   data-bs-toggle="tooltip" data-bs-placement="top"
                   data-bs-custom-class="custom-tooltip"
                   data-bs-title="Abrir o player da rádio ao vivo"
                   target="_blank" rel="noopener noreferrer">
                  <i class="fa-solid fa-tower-broadcast"></i>
                  Rádio online
                </a>
              </li>

              {{-- Dropdown: IBKR --}}
              @can('IBKR - VER')
              <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle text-white" id="dropdown-ibkr" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                   data-bs-custom-class="custom-tooltip" data-bs-title="Atalhos IBKR: Status/OAuth e API Web">
                  <i class="fa-solid fa-building-columns me-1"></i>
                  IBKR
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdown-ibkr">
                  <li>
                    <a class="dropdown-item" href="{{ route('ibkr.status') }}" title="Status de conexão (OAuth)">
                      <i class="fa-solid fa-signal me-1"></i> Status / OAuth
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item" href="{{ route('ibkr.api-web') }}" title="Atalhos API Web (SSO, contas, status, sessão)">
                      <i class="fa-solid fa-plug me-1"></i> API Web
                    </a>
                  </li>
                </ul>
              </li>
              @endcan



                            {{-- Dropdown: Contabilidade / Financeiro --}}
                            @canany(['CONTABILIDADE - LISTAR','CONTABILIDADE - LISTAR-AQUI-TAMBEM','CONTASPAGAR - LISTAR','COBRANCA - LISTAR','LANCAMENTOS DOCUMENTOS - LISTAR','EMPRESAS - LISTAR','CENTROCUSTOS - LISTAR'])
                            <li class="nav-item dropdown">
                              <a href="#" class="nav-link dropdown-toggle text-white" id="dropdown-contabilidade" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                                 data-bs-togglex="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="Atalhos de Contabilidade & Financeiro">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                                Contabilidade
                              </a>
                              <ul class="dropdown-menu dropdown-menu-end">
                                @can('CONTABILIDADE - LISTAR')
                                <li><a class="dropdown-item" href="/Contabilidade">Contabilidade</a></li>
                                @endcan
                                @canany(['COBRANCA - LISTAR','CONTABILIDADE - LISTAR-AQUI-TAMBEM'])
                                <li><a class="dropdown-item" href="/Cobranca">Cobrança</a></li>
                                @endcanany
                                @can('CONTASPAGAR - LISTAR')
                                <li>
                                  <a class="dropdown-item d-flex justify-content-between align-items-center" href="/ContasPagar">
                                    <span>Contas a pagar</span>
                                    <span id="count-contaspagar" class="badge rounded-pill text-bg-secondary">…</span>
                                  </a>
                                </li>
                                @endcan
                                @can('LANCAMENTOS DOCUMENTOS - LISTAR')
                                <li><a class="dropdown-item" href="/LancamentosDocumentos">Documentos</a></li>
                                @endcan
                                @can('CONTABILIDADE - LISTAR')
                                <li><a class="dropdown-item" href="{{ route('lancamentos.preview.despesas') }}" title="Pré-visualização e classificação de planilha de despesas (Excel)">Preview Despesas (Excel)</a></li>
                                <li><a class="dropdown-item" href="/lancamentos/balancete#gsc.tab=0" title="Balancete por período">Balancete</a></li>
                                @endcan
                                @can('EMPRESAS - LISTAR')
                                <li>
                                  <a class="dropdown-item d-flex justify-content-between align-items-center" href="/Empresas">
                                    <span>Empresas</span>
                                    <span id="count-empresas" class="badge rounded-pill text-bg-secondary">…</span>
                                  </a>
                                </li>
                                @endcan
                                @can('CENTROCUSTOS - LISTAR')
                                <li><hr class="dropdown-divider"></li>
                                <li class="dropdown-header">Centro de Custos</li>
                                <li>
                                  <a class="dropdown-item d-flex justify-content-between align-items-center" href="/CentroCustos">
                                    <span>Centro de Custos</span>
                                    <span id="count-centro_custos" class="badge rounded-pill text-bg-secondary">…</span>
                                  </a>
                                </li>
                                <li>
                                  <a class="dropdown-item d-flex justify-content-between align-items-center" href="/ContasCentroCustos">
                                    <span>Contas por Centro de Custos</span>
                                    <span id="count-contas_centro_custos" class="badge rounded-pill text-bg-secondary">…</span>
                                  </a>
                                </li>
                                <li><a class="dropdown-item" href="{{ route('CentroCustos.dashboard') }}">Dashboard Centro de Custos</a></li>
                                @endcan
                              </ul>
                            </li>
                            @endcanany

                            {{-- Dropdown: Permissões & Usuários --}}
                            @canany(['PERMISSOES - LISTAR','USUARIOS - LISTAR','FUNCOES - LISTAR'])
                            <li class="nav-item dropdown">
                              <a href="#" class="nav-link dropdown-toggle text-white" id="dropdown-permissoes-usuarios" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                                 data-bs-togglex="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="Atalhos de Permissões & Usuários">
                                <i class="fa-solid fa-users-gear"></i>
                                Permissões & Usuários
                              </a>
                              <ul class="dropdown-menu dropdown-menu-end">
                                @can('PERMISSOES - LISTAR')
                                <li><a class="dropdown-item" href="/Permissoes">Permissões</a></li>
                                @endcan
                                @can('USUARIOS - LISTAR')
                                <li><a class="dropdown-item" href="/Usuarios">Usuários</a></li>
                                @endcan
                                @can('FUNCOES - LISTAR')
                                <li><a class="dropdown-item" href="/Funcoes">Funções</a></li>
                                @endcan
                              </ul>
                            </li>
                            @endcanany

                            {{-- Dropdown: Backup --}}
                            @canany(['backup.executar.hd','backup.executar.ftp','backup.logs.view','backup.logs.download','backup.logs.clear'])
                            <li class="nav-item dropdown">
                              <a href="#" class="nav-link dropdown-toggle text-white" id="dropdown-backup" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                                 data-bs-togglex="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="Ações de Backup e Logs">
                                <i class="fa-solid fa-database"></i>
                                Backup
                              </a>
                              <ul class="dropdown-menu dropdown-menu-end">
                                @can('backup.executar.hd')
                                <li>
                                  <a class="dropdown-item" href="{{ route('backup.external.view') }}">
                                    <i class="fa-solid fa-hard-drive me-2"></i>Backup: Storage → HD externo (view)
                                  </a>
                                </li>
                                @endcan

                                @can('backup.executar.ftp')
                                <li>
                                  <a class="dropdown-item" href="{{ route('backup.ftp.view') }}">
                                    <i class="fa-solid fa-cloud-arrow-up me-2"></i>Backup: Storage → FTP (view)
                                  </a>
                                </li>
                                <li>
                                  <a class="dropdown-item" href="{{ route('backup.ftp-test.view') }}">
                                    <i class="fa-solid fa-plug-circle-check me-2"></i>Testar conexão FTP (dry-run)
                                  </a>
                                </li>
                                {{-- Download FTP (navegação e download de arquivos) --}}
                                <li><hr class="dropdown-divider"></li>
                                <li class="dropdown-header">Download</li>
                                <li>
                                  <a class="dropdown-item" href="{{ url('/ftp-browser') }}">
                                    <i class="fa-solid fa-download me-2"></i>Download FTP
                                  </a>
                                </li>
                                @endcan

                                @canany(['backup.logs.view','backup.logs.download','backup.logs.clear'])
                                <li><hr class="dropdown-divider"></li>
                                <li class="dropdown-header">Logs do Backup FTP</li>
                                @endcanany

                                @can('backup.logs.view')
                                <li>
                                  <a class="dropdown-item" href="/backup/ftp-logs">
                                    <i class="fa-solid fa-list-ul me-2"></i>Ver logs
                                  </a>
                                </li>
                                @endcan

                                @can('backup.logs.download')
                                <li>
                                  <a class="dropdown-item" href="/backup/ftp-logs/download">
                                    <i class="fa-solid fa-file-arrow-down me-2"></i>Baixar logs (todos)
                                  </a>
                                </li>
                                <li>
                                  <a class="dropdown-item" href="/backup/ftp-logs/download-last">
                                    <i class="fa-solid fa-file-circle-down me-2"></i>Baixar último log
                                  </a>
                                </li>
                                @endcan

                                @can('backup.logs.clear')
                                <li>
                                  <form action="/backup/ftp-logs/clear" method="POST" onsubmit="return confirm('Limpar/arquivar os logs atuais de backup FTP?');">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                      <i class="fa-solid fa-trash-can me-2"></i>Limpar/Arquivar logs
                                    </button>
                                  </form>
                                </li>
                                @endcan
                              </ul>
                            </li>
                            @endcanany

              {{-- Dropdown: Irmãos de Emaús --}}
              @canany(['IRMAOS_EMAUS_NOME_SERVICO - LISTAR','IRMAOS_EMAUS_NOME_PIA - LISTAR','IRMAOS_EMAUS_FICHA_CONTROLE - LISTAR'])
              <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle text-white" id="dropdown-emaus" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                   data-bs-togglex="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="Atalhos para módulos dos Irmãos de Emaús">
                  <i class="fa-solid fa-people-arrows"></i>
                  Irmãos de Emaús
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  @can('IRMAOS_EMAUS_NOME_SERVICO - LISTAR')
                  <li><a class="dropdown-item" href="/Irmaos_EmausServicos">Serviços</a></li>
                  @endcan
                  @can('IRMAOS_EMAUS_NOME_PIA - LISTAR')
                  <li><a class="dropdown-item" href="/Irmaos_EmausPia">PIA</a></li>
                  @endcan
                  @can('IRMAOS_EMAUS_FICHA_CONTROLE - LISTAR')
                  <li><a class="dropdown-item" href="/Irmaos_Emaus_FichaControle">Ficha Controle</a></li>
                  @endcan
                </ul>
              </li>
              @endcanany





              {{-- Dropdown: OpenAI • Ativos Americanos --}}
              @canany(['OPENAI - CHAT','ASSET STATS - LISTAR','INVESTIMENTOS SNAPSHOTS - LISTAR'])
              <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle text-white" id="dropdown-openai-assets" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                  <i class="fa-solid fa-robot me-1"></i>
                  OpenAI & Ações EUA
                  <i class="fa-solid fa-globe-americas ms-1"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  @can('OPENAI - CHAT')
                  <li>
                    <a class="dropdown-item" href="{{ route('openai.records.index') }}" title="Conversas e registros (filtros por ativo e período)">
                      <i class="fa-regular fa-comments me-1"></i> Conversas OpenAI
                    </a>
                  </li>
                  <li><hr class="dropdown-divider"></li>
                  @canany(['OPENAI - CHAT','ASSET STATS - LISTAR'])
                  <!-- Desktop (md+): submenu Mercado abre à direita -->
                  <li class="dropend d-none d-md-block">
                    <a class="dropdown-item dropdown-toggle" href="#" id="submenu-mercado" aria-expanded="false">
                      <i class="fa-solid fa-chart-line me-1"></i> Mercado
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="submenu-mercado">
                      @can('OPENAI - CHAT')
                      <li>
                        <a class="dropdown-item" href="{{ route('openai.records.assets') }}" title="Resumo de ativos únicos, baseline e tendências">
                          <i class="fa-solid fa-layer-group me-1"></i> Ativos (Resumo)
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="{{ route('openai.variations.index') }}#gsc.tab=0" title="Variações por mês (por código)">
                          <i class="fa-solid fa-arrow-up-right-dots me-1"></i> Variações (Mês)
                        </a>
                      </li>
                      @endcan
                      @can('ASSET STATS - LISTAR')
                      <li>
                        <a class="dropdown-item" href="{{ route('asset-stats.index') }}#gsc.tab=0" title="Estatísticas diárias (close, acurácia)">
                          <i class="fa-solid fa-database me-1"></i> Estatísticas de Ativos
                        </a>
                      </li>
                      @endcan
                    </ul>
                  </li>
                  <!-- Mobile (< md): lista expandida -->
                  <li class="d-block d-md-none">
                    <h6 class="dropdown-header"><i class="fa-solid fa-chart-line me-1"></i> Mercado</h6>
                  </li>
                  @can('OPENAI - CHAT')
                  <li class="d-block d-md-none">
                    <a class="dropdown-item ps-4" href="{{ route('openai.records.assets') }}" title="Resumo de ativos únicos, baseline e tendências">
                      <i class="fa-solid fa-layer-group me-1"></i> Ativos (Resumo)
                    </a>
                  </li>
                  <li class="d-block d-md-none">
                    <a class="dropdown-item ps-4" href="{{ route('openai.variations.index') }}#gsc.tab=0" title="Variações por mês (por código)">
                      <i class="fa-solid fa-arrow-up-right-dots me-1"></i> Variações (Mês)
                    </a>
                  </li>
                  @endcan
                  @can('ASSET STATS - LISTAR')
                  <li class="d-block d-md-none">
                    <a class="dropdown-item ps-4" href="{{ route('asset-stats.index') }}#gsc.tab=0" title="Estatísticas diárias (close, acurácia)">
                      <i class="fa-solid fa-database me-1"></i> Estatísticas de Ativos
                    </a>
                  </li>
                  @endcan
                  @endcanany
                  @canany(['OPENAI - CHAT','INVESTIMENTOS SNAPSHOTS - LISTAR'])
                  <!-- Desktop (md+): submenu abre à direita -->
                  <li class="dropend d-none d-md-block">
                    <a class="dropdown-item dropdown-toggle" href="#" id="submenu-investimentos" aria-expanded="false">
                      <i class="fa-solid fa-wallet me-1"></i> Investimentos
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="submenu-investimentos">
                      @can('OPENAI - CHAT')
                      <li>
                        <a class="dropdown-item" href="{{ route('openai.portfolio.index') }}#gsc.tab=0" title="Carteira consolidada (holdings, P/L, variações)">
                          <i class="fa-solid fa-table-list me-1"></i> Carteira
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="{{ route('openai.investments.index') }}" title="Contas de investimento vinculadas">
                          <i class="fa-solid fa-wallet me-1"></i> Contas
                        </a>
                      </li>
                      @endcan
                      @can('INVESTIMENTOS SNAPSHOTS - LISTAR')
                      <li>
                        <a class="dropdown-item" href="{{ route('investments.daily-balances.index') }}" title="Snapshots diários (saldo e variação)">
                          <i class="fa-solid fa-chart-line me-1"></i> Snapshots
                        </a>
                      </li>
                      @endcan
                      @can('CASH EVENTS - LISTAR')
                      <li>
                        <a class="dropdown-item" href="{{ route('cash.events.index') }}#gsc.tab=0" title="Listagem de eventos de caixa (dividendos, impostos, depósitos, retiradas)">
                          <i class="fa-solid fa-coins me-1"></i> Eventos de Caixa
                        </a>
                      </li>
                      @endcan
                      @can('HOLDINGS - IMPORTAR')
                      <li><hr class="dropdown-divider"></li>
                      <li>
                        <a class="dropdown-item" href="{{ route('holdings.screen.quick.form') }}" title="Importar/colar holdings (Avenue Screen)">
                          <i class="fa-solid fa-file-import me-1"></i> Importar Holdings (Avenue)
                        </a>
                      </li>
                      @endcan
                      @can('CASH EVENTS - IMPORTAR')
                      <li>
                        <a class="dropdown-item" href="{{ route('cash.import.form') }}#gsc.tab=0" title="Importar bloco de caixa (saldo e eventos)">
                          <i class="fa-solid fa-sack-dollar me-1"></i> Importar Caixa (Avenue)
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="{{ route('cash.import.csv.form') }}#gsc.tab=0" title="Importar CSV Avenue (avenue-report-statement.csv)">
                          <i class="fa-solid fa-file-csv me-1"></i> Importar Caixa CSV (Avenue)
                        </a>
                      </li>
                      @endcan
                    </ul>
                  </li>
                  <!-- Mobile (< md): lista expandida -->
                  <li class="d-block d-md-none">
                    <h6 class="dropdown-header"><i class="fa-solid fa-wallet me-1"></i> Investimentos</h6>
                  </li>
                  @can('OPENAI - CHAT')
                  <li class="d-block d-md-none">
                    <a class="dropdown-item ps-4" href="{{ route('openai.portfolio.index') }}#gsc.tab=0" title="Carteira consolidada (holdings, P/L, variações)">
                      <i class="fa-solid fa-table-list me-1"></i> Carteira
                    </a>
                  </li>
                  <li class="d-block d-md-none">
                    <a class="dropdown-item ps-4" href="{{ route('openai.investments.index') }}" title="Contas de investimento vinculadas">
                      <i class="fa-solid fa-wallet me-1"></i> Contas
                    </a>
                  </li>
                  @endcan
                  @can('INVESTIMENTOS SNAPSHOTS - LISTAR')
                  <li class="d-block d-md-none">
                    <a class="dropdown-item ps-4" href="{{ route('investments.daily-balances.index') }}" title="Snapshots diários (saldo e variação)">
                      <i class="fa-solid fa-chart-line me-1"></i> Snapshots
                    </a>
                  </li>
                  @endcan
                  @can('CASH EVENTS - LISTAR')
                  <li class="d-block d-md-none">
                    <a class="dropdown-item ps-4" href="{{ route('cash.events.index') }}#gsc.tab=0" title="Listagem de eventos de caixa (dividendos, impostos, depósitos, retiradas)">
                      <i class="fa-solid fa-coins me-1"></i> Eventos de Caixa
                    </a>
                  </li>
                  @endcan
                  @can('HOLDINGS - IMPORTAR')
                  <li class="d-block d-md-none"><hr class="dropdown-divider"></li>
                  <li class="d-block d-md-none">
                    <a class="dropdown-item ps-4" href="{{ route('holdings.screen.quick.form') }}" title="Importar/colar holdings (Avenue Screen)">
                      <i class="fa-solid fa-file-import me-1"></i> Importar Holdings (Avenue)
                    </a>
                  </li>
                  @endcan
                  @can('CASH EVENTS - IMPORTAR')
                  <li class="d-block d-md-none">
                    <a class="dropdown-item ps-4" href="{{ route('cash.import.form') }}#gsc.tab=0" title="Importar bloco de caixa (saldo e eventos)">
                      <i class="fa-solid fa-sack-dollar me-1"></i> Importar Caixa (Avenue)
                    </a>
                  </li>
                  <li class="d-block d-md-none">
                    <a class="dropdown-item ps-4" href="{{ route('cash.import.csv.form') }}#gsc.tab=0" title="Importar CSV Avenue (avenue-report-statement.csv)">
                      <i class="fa-solid fa-file-csv me-1"></i> Importar Caixa CSV (Avenue)
                    </a>
                  </li>
                  @endcan
                  @endcanany
                  @endcan


                </ul>
              </li>
              @endcanany

              {{-- Link: SAF - Colaboradores --}}
              @can('SAF_COLABORADORES - LISTAR')
              <li>
                <a href="{{ route('SafColaboradores.index') }}"
                   class="nav-link text-white"
                   data-bs-toggle="tooltip" data-bs-placement="top"
                   data-bs-custom-class="custom-tooltip"
                   data-bs-title="Gerenciar Colaboradores (filtros, exportar CSV/XLSX/PDF)">
                  <i class="fa-solid fa-people-group"></i>
                  Colaboradores
                </a>
              </li>
              @endcan

              {{-- Link: Envios de arquivos --}}
              @can('ENVIOS - LISTAR')
              <li>
                <a href="{{ route('Envios.index') }}"
                   class="nav-link text-white"
                   data-bs-toggle="tooltip" data-bs-placement="top"
                   data-bs-custom-class="custom-tooltip"
                   data-bs-title="Registrar envios e anexar vários arquivos (até 100 MB)">
                  <i class="fa-solid fa-paperclip"></i>
                  Envios de arquivos
                </a>
              </li>
              @endcan


              <!-- Link: Percentuais Atletas TANABI -->
              @can('TANABI ATLETAS PERCENTUAIS - LISTAR')
              <li>
                <a href="{{ route('tanabi.athletes.percentages.index') }}"
                   class="nav-link text-white"
                   data-bs-toggle="tooltip" data-bs-placement="top"
                   data-bs-custom-class="custom-tooltip"
                   data-bs-title="Gerenciar percentuais TANABI x outros clubes, breakdown multi-clubes e exportar CSV">
                  <i class="fa-solid fa-percent"></i>
                  Percentuais Atletas
                </a>
              </li>
              @endcan

              <li class="d-flex align-items-center gap-2">
                <a href="/profile" data-bs-toggle="tooltip" data-bs-placement="top"
                  data-bs-custom-class="custom-tooltip"
                  data-bs-title="Email: {{ optional(Auth::user())->email ?? 'Não autenticado' }} Clique para efetuar logout, alterar nome, senha, alterar email(atualizar o cadastro)."
                  class="nav-link text-white">
                  <i class="fa-solid fa-user"></i>
                  Perfil do usuário: {{ optional(Auth::user())->name ?? 'Visitante' }}
                </a>
                @auth
                <button type="button" class="btn btn-sm btn-outline-light d-inline-flex align-items-center gap-1 ms-1 js-force-logout-close" data-logout-url="{{ route('logout') }}" title="Desconectar">
                  <i class="fa-solid fa-right-from-bracket"></i>
                  <span>Desconectar</span>
                </button>
                @endauth
              </li>

                            {{-- <li>
                      <a href="#" class="nav-link text-white">
                        <i class="fa-solid fa-dashboard"></i>
                        .
                      </a>
                    </li> --}}
                            {{-- <li>
                      <a href="#" class="nav-link text-white">
                        <i class="fa-solid fa-dashboard"></i>
                        .
                      </a>
                    </li> --}}
                            {{-- <li>
                      <a href="#" class="nav-link text-white">
                        <i class="fa-solid fa-dashboard"></i>
                        .
                      </a>
                    </li> --}}
                        </ul>
                    </div>
                </div>
            </div>
            {{-- <div class="px-3 py-2 border-bottom mb-3">
              <div class="container d-flex flex-wrap justify-content-center">
                <form class="col-12 col-lg-auto mb-2 mb-lg-0 me-lg-auto" role="search">
                  <input type="search" class="form-control" placeholder="Buscar..." aria-label="Search">
                </form>

                <div class="text-end">
                  <button type="button" class="btn btn-light text-dark me-2">Login</button>
                  <button type="button" class="btn btn-primary">Sign-up</button>
                </div>
              </div>
            </div> --}}
        </header>

        {{-- Toasts de feedback via sessão (exceto erros, que abrem em modal) --}}
        <div aria-live="polite" aria-atomic="true" class="position-relative">
          <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 2000;">
            @foreach (['success'=>'success','info'=>'info','warning'=>'warning'] as $k => $cls)
              @if (session($k))
                <div class="toast align-items-center text-bg-{{ $cls }} border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
                  <div class="d-flex">
                    <div class="toast-body">
                      {{ session($k) }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                  </div>
                </div>
              @endif
            @endforeach
          </div>
        </div>

        {{-- Modal de Erros (centralizado) --}}
        @php
          $hasErrorModal = session('error') || ($errors && $errors->any());
        @endphp
        <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true" data-has-errors="{{ $hasErrorModal ? '1' : '0' }}">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
              <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="errorModalLabel">
                  @if(session('error') && !($errors && $errors->any()))
                    Ocorreu um erro
                  @elseif($errors && $errors->any())
                    Ocorreram erros de validação
                  @else
                    Erro
                  @endif
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
              </div>
              <div class="modal-body" style="max-height: 60vh; overflow:auto;">
                @if (session('error'))
                  <div class="alert alert-danger" role="alert">
                    {{ session('error') }}
                  </div>
                @endif
                @if ($errors && $errors->any())
                  <div class="alert alert-danger" role="alert">
                    <ul class="mb-0 ps-3">
                      @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                      @endforeach
                    </ul>
                  </div>
                @endif
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
              </div>
            </div>
          </div>
        </div>

        {{-- Modal de "Processando/Aguarde" (para ações como Conectar e Contas JSON) --}}
        <div class="modal fade" id="busyModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-body text-center py-4">
                <div class="d-flex flex-column align-items-center gap-3">
                  <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                  <div class="fw-semibold">Aguarde…</div>
                  <div class="text-muted small">Estamos processando sua solicitação.</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Alerta fixo com links públicos recém-criados --}}
        @if (session()->has('public_links_created') || session()->has('public_link'))
          @php
            $pl = session('public_links_created');
            if (!$pl && session('public_link')) {
              $one = session('public_link');
              $lnks = [];
              if (!empty($one['view'] ?? null)) { $lnks[] = ['label' => 'Visualizar', 'url' => $one['view']]; }
              if (!empty($one['download'] ?? null)) { $lnks[] = ['label' => 'Download', 'url' => $one['download']]; }
              $pl = [ 'expires_at' => $one['expires_at'] ?? '—', 'links' => $lnks ];
            }
          @endphp
          <div class="position-fixed top-0 start-50 translate-middle-x mt-2" style="z-index: 2100; width: min(960px, 95vw);">
            <div class="alert alert-success alert-dismissible fade show shadow" role="alert">
              <div class="d-flex flex-column gap-2">
                <div><strong>Links públicos gerados</strong> (expira em: {{ $pl['expires_at'] ?? '—' }})</div>
                <div class="d-flex flex-column gap-1">
                  @foreach(($pl['links'] ?? []) as $lnk)
                    <div class="input-group input-group-sm">
                      <span class="input-group-text">{{ $lnk['label'] ?? 'Link' }}</span>
                      <input type="text" class="form-control" value="{{ $lnk['url'] ?? '' }}" readonly>
                      <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText('{{ $lnk['url'] ?? '' }}').then(()=>{this.textContent='Copiado'; setTimeout(()=>this.textContent='Copiar',1500);})">Copiar</button>
                      <a class="btn btn-outline-primary" target="_blank" href="{{ $lnk['url'] ?? '' }}">Abrir</a>
                    </div>
                  @endforeach
                </div>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          </div>
        @endif

        @php($cotacaoAviso = session('moedas.cotacao_aviso'))
        @if ($cotacaoAviso)
        <div class="container mt-3">
          <div class="alert alert-warning d-flex align-items-center fw-bold border-2 border-warning shadow-sm alert-dismissible fade show" role="alert">
            <span class="me-2" aria-hidden="true">🕒</span>
            <div>
              Cotação utilizada é anterior ao dia atual:
              <strong>{{ $cotacaoAviso['moeda_nome'] ?? 'Moeda' }}</strong>
              em <strong>{{ $cotacaoAviso['data_utilizada'] ?? '-' }}</strong>
              <span class="badge bg-secondary ms-2">{{ strtoupper($cotacaoAviso['fonte'] ?? 'LOCAL') }}</span>
              @if(!empty($cotacaoAviso['provider']))
                <span class="badge bg-info ms-2">{{ $cotacaoAviso['provider'] }}</span>
              @endif
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        </div>
        @endif

        @yield('content')

    </main>

    <footer class="text-muted py-5">

        <div class="container">
    <button onclick="window.history.back();" style="margin-right: 10px;">⬅️ Voltar</button>
    <button onclick="window.history.forward();" style="margin-right: 10px;">➡️ Avançar</button>

    <p class="float-end mb-1">
        <button onclick="window.scrollTo({ top: 0, behavior: 'smooth' });">🔝 Ir para o topo</button>
    </p>
</div>

    </footer>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <script src="https://getbootstrap.com/docs/5.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <style>
      /* Suporte a submenus (dropend) dentro de dropdowns */
      .dropdown-menu .dropend { position: relative; }
      .dropdown-menu .dropend .dropdown-menu {
        top: 0;
        left: 100%;
        margin-top: 0;
        margin-left: .25rem;
      }
      @media (max-width: 767.98px) {
        /* No mobile, desabilita sobreposição lateral */
        .dropdown-menu .dropend .dropdown-menu {
          position: static;
          float: none;
          display: block;
          left: auto;
          margin-left: 0;
        }
      }
    </style>
    <script>
      // Habilita submenus (dropend) dentro de dropdowns no Bootstrap 5
      document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.dropdown-menu .dropdown-toggle').forEach(function (el) {
          el.addEventListener('click', function (e) {
            // evita fechar o dropdown principal
            e.preventDefault();
            e.stopPropagation();
            const subMenu = this.nextElementSibling;
            if (subMenu && subMenu.classList.contains('dropdown-menu')) {
              // fecha outros submenus no mesmo nível
              this.closest('.dropdown-menu').querySelectorAll('.dropdown-menu.show').forEach(function (openMenu) {
                if (openMenu !== subMenu) openMenu.classList.remove('show');
              });
              subMenu.classList.toggle('show');
              // posicionamento é controlado via CSS (ver <style> acima)
            }
          });
        });
        // Fecha submenus quando fecha o dropdown principal
        document.querySelectorAll('.dropdown').forEach(function (dropdown) {
          dropdown.addEventListener('hide.bs.dropdown', function () {
            this.querySelectorAll('.dropdown-menu.show').forEach(function (openMenu) {
              openMenu.classList.remove('show');
            });
          });
        });
      });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" crossorigin="anonymous">
    </script>
    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>
    <script>
    // Inicializa toasts presentes no DOM
    (function(){
      try{
        document.querySelectorAll('.toast').forEach(function(el){
          const t = new bootstrap.Toast(el);
          t.show();
        });
      }catch(_e){}
    })();
  </script>
  <script>
    // Exibe automaticamente o modal de erros quando houver mensagens
    document.addEventListener('DOMContentLoaded', function(){
      try{
        var modalEl = document.getElementById('errorModal');
        if (!modalEl) return;
        var hasErrors = modalEl.getAttribute('data-has-errors') === '1';
        if (hasErrors && window.bootstrap && bootstrap.Modal) {
          var m = new bootstrap.Modal(modalEl);
          m.show();
        }
      }catch(_e){}
    });
  </script>
  <script>
    // Abre o modal de "Aguarde" para elementos marcados com data-busy="1"
    document.addEventListener('DOMContentLoaded', function(){
      function showBusy(){
        try{
          var el = document.getElementById('busyModal');
          if (!el) return;
          var m = new bootstrap.Modal(el);
          m.show();
        }catch(_e){}
      }
      // Click em links/botões
      document.querySelectorAll('[data-busy="1"]').forEach(function(node){
        node.addEventListener('click', function(){
          showBusy();
        });
      });
      // Submissão de formulários
      document.querySelectorAll('form[data-busy="1"]').forEach(function(form){
        form.addEventListener('submit', function(){
          showBusy();
        });
      });
    });
  </script>
  {{-- Market status desabilitado --}}
  {{-- <script>
      // Poller compartilhado via localStorage para evitar 429 quando múltiplas abas estão abertas
      (function(){
        const BADGE_ID = 'market-status-global';
  const MARKET_STATUS_URL = new window.URL('/api/market/status', window.location.origin).toString();
        const LS_KEY_CACHE = 'marketStatusCacheV1'; // { data, ts }
        const LS_KEY_LEADER = 'marketStatusLeaderV1'; // { id, until }
        const POLL_OK_MS = 60000;      // 60s normal
        const POLL_BACKOFF_MS = 180000; // 3min quando 429
        const CACHE_STALE_MS = 55000;  // 55s (próximo do TTL do servidor)

        const myId = Math.random().toString(36).slice(2);
        let nextMs = POLL_OK_MS;

        function getBadge(){ return document.getElementById(BADGE_ID); }

        function fmtBR(s){
          if (!s) return '';
          try{
            const d = new Date(String(s).replace(' ','T'));
            if (isNaN(d.getTime())) return s;
            const dd = String(d.getDate()).padStart(2,'0');
            const mm = String(d.getMonth()+1).padStart(2,'0');
            const yy = d.getFullYear();
            const HH = String(d.getHours()).padStart(2,'0');
            const MM = String(d.getMinutes()).padStart(2,'0');
            return `${dd}/${mm}/${yy} ${HH}:${MM}`;
          }catch(_e){ return s; }
        }

        function renderBadge(data, hint){
          const badge = getBadge();
          if (!badge || !data) return;
          const st = String(data.status||'').toLowerCase();
          const label = String(data.label||'Mercado');
          const nextStr = data.next_change_at ? ` • Próx: ${fmtBR(data.next_change_at)}` : '';
          let cls = 'bg-secondary';
          if (st === 'open') cls = 'bg-success';
          else if (st === 'pre') cls = 'bg-warning text-dark';
          else if (st === 'after') cls = 'bg-info text-dark';
          else if (st === 'closed') cls = 'bg-secondary';
          badge.className = 'badge rounded-pill ' + cls;
          badge.textContent = `Mercado: ${label}` + nextStr + (hint ? ` ${hint}` : '');
          if (data.reason){ badge.title = `${label} — ${data.reason}`; }
        }

        function renderUnavailable(msg){
          const badge = getBadge();
          if (!badge) return;
          badge.className='badge rounded-pill bg-secondary';
          badge.textContent = msg || 'Mercado: indisponível';
        }

        function readCache(){
          try{ return JSON.parse(localStorage.getItem(LS_KEY_CACHE)||'null'); }catch(_e){ return null; }
        }
        function writeCache(data){
          try{ localStorage.setItem(LS_KEY_CACHE, JSON.stringify({ data, ts: Date.now() })); }catch(_e){}
        }
        function isCacheFresh(obj){ return obj && obj.ts && (Date.now() - obj.ts) < CACHE_STALE_MS; }

        function getLeader(){
          try{ return JSON.parse(localStorage.getItem(LS_KEY_LEADER)||'null'); }catch(_e){ return null; }
        }
        function becomeLeader(){
          const until = Date.now() + Math.max(nextMs, POLL_OK_MS) + 5000; // margem
          const me = { id: myId, until };
          try{ localStorage.setItem(LS_KEY_LEADER, JSON.stringify(me)); }catch(_e){}
          // verifique se permaneceu meu
          const nowLeader = getLeader();
          return nowLeader && nowLeader.id === myId;
        }
        function hasLeader(){
          const l = getLeader();
          return !!(l && l.until && l.until > Date.now());
        }

        async function poll(){
          const cached = readCache();
          if (isCacheFresh(cached)){
            renderBadge(cached.data);
          }
          // Se já existe líder válido, não faz fetch
          if (hasLeader()) return;
          if (!becomeLeader()) return;
          try{
            const resp = await fetch(MARKET_STATUS_URL, { headers: { 'Accept':'application/json' } });
            if (resp.status === 429){
              nextMs = POLL_BACKOFF_MS;
              renderUnavailable('Mercado: limite temporário');
              return;
            }
            const data = await resp.json().catch(()=>null);
            if(!resp.ok || !data) throw new Error('fail');
            writeCache(data);
            renderBadge(data);
            nextMs = POLL_OK_MS;
          }catch(_e){
            renderUnavailable();
            nextMs = POLL_BACKOFF_MS;
          } finally {
            // libera liderança após pequena janela para outros não disputarem imediatamente
            try{ localStorage.removeItem(LS_KEY_LEADER); }catch(_e){}
          }
        }

        // Atualiza quando outra aba atualizar o cache
        window.addEventListener('storage', function(ev){
          if (ev.key === LS_KEY_CACHE && ev.newValue){
            try{ const obj = JSON.parse(ev.newValue); if (obj && obj.data){ renderBadge(obj.data); } }catch(_e){}
          }
        });

        // Loop de agendamento com backoff
        (function loop(){
          try{ poll(); }catch(_e){}
          setTimeout(loop, nextMs);
        })();
      })();
    </script> --}}
    <script>
      // Logout silencioso: POST /logout (AJAX) e fechar a guia sem navegação.
      (function(){
        function showDisconnectedToast(){
          try{
            const div = document.createElement('div');
            div.style.position='fixed';
            div.style.left='50%';
            div.style.top='20px';
            div.style.transform='translateX(-50%)';
            div.style.zIndex='2147483647';
            div.style.background='#dc3545';
            div.style.color='#fff';
            div.style.padding='12px 20px';
            div.style.borderRadius='8px';
            div.style.boxShadow='0 2px 10px rgba(0,0,0,0.2)';
            div.style.fontWeight='600';
            div.style.fontFamily='system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Helvetica, Arial, sans-serif';
            div.style.fontSize='20px';
            div.textContent='VOCÊ SE DESCONECTOU!';
            document.body.appendChild(div);
            setTimeout(()=>{ try{ div.remove(); }catch(_e){} }, 2500);
          }catch(_e){}
        }
        function tryClose(){
          try{ window.open('', '_self'); }catch(_e){}
          try{ window.close(); }catch(_e){}
        }
        async function doLogoutAndClose(url){
          showDisconnectedToast();
          try{
            const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
            await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
          }catch(_e){}
          // Não navegar para nenhuma página; fechar silenciosamente
          tryClose();
        }
        document.addEventListener('click', function(ev){
          const btn = ev.target.closest('.js-force-logout-close');
          if (!btn) return;
          const url = btn.getAttribute('data-logout-url');
          if (url){ ev.preventDefault(); doLogoutAndClose(url); }
        });
        // Intercepta qualquer formulário legado de logout, se presente
        document.addEventListener('submit', function(ev){
          const form = ev.target;
          if (form && form.matches('form[action="{{ route('logout') }}"][method="POST"]')){
            ev.preventDefault();
            doLogoutAndClose(form.getAttribute('action'));
          }
        }, true);
      })();
    </script>
    @stack('scripts')

    {{-- Prism.js core e plugins --}}
    <script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-core.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/plugins/line-numbers/prism-line-numbers.min.js"></script>
    <script>
      // Configura o autoloader para buscar componentes
      if (window.Prism && Prism.plugins && Prism.plugins.autoloader) {
        Prism.plugins.autoloader.languages_path = 'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/';
      }
      // Carrega conteúdo de texto via fetch e injeta no <code>
      document.addEventListener('DOMContentLoaded', function(){
        const nodes = document.querySelectorAll('code[data-text-src]');
        nodes.forEach(async function(code){
          const url = code.getAttribute('data-text-src');
          try {
            const resp = await fetch(url, { headers: { 'Accept': 'text/plain,*/*' } });
            const text = await resp.text();
            // Escapa para exibição segura
            code.textContent = text;
            if (window.Prism) {
              Prism.highlightElement(code);
            }
          } catch(e) {
            code.textContent = 'Não foi possível carregar o conteúdo.';
          }
        });
      });
    </script>

    <script src="https://kit.fontawesome.com/941fc38062.js" crossorigin="anonymous"></script>

    {{-- LINK PARA PEGAR NOME DE ICONES --}}
    {{-- https://fontawesome.com/search?q=money&o=r&m=free --}}

    @livewireScripts
</script>
<script>
function aplicarMascaraValor() {
  var inputs = document.querySelectorAll('input[name="Valor"]');
  inputs.forEach(function(input) {
    if (!input.hasAttribute('data-mascara')) {
      input.setAttribute('data-mascara', 'true');
      input.addEventListener('blur', function () {
        var val = input.value.replace(/\D/g, ''); // só números
        if (val.length > 2) {
          var inteiro = val.slice(0, -2);
          var centavos = val.slice(-2);
          var novoValor = parseInt(inteiro, 10).toLocaleString('pt-BR') + ',' + centavos;
          input.value = novoValor;
        } else if (val.length === 2) {
          input.value = '0,' + val;
        } else if (val.length === 1) {
          input.value = '0,0' + val;
        }
        input.dispatchEvent(new Event('input', { bubbles: true }));
      });
    }
  });
}

document.addEventListener('DOMContentLoaded', aplicarMascaraValor);
if (window.Livewire) {
  window.Livewire.hook('message.processed', function () {
    aplicarMascaraValor();
  });
}
</script>
</script>
</body>

</html>
<script>
    function scrollToBottom() {
      window.scrollTo(0, document.body.scrollHeight);
    }
    </script>
