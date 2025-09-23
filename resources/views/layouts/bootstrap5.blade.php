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

    <main>
        <header>
<div class="px-3 py-2 text-bg-primary text-center">
  <h3 class="m-0">PESQUISAR NO GOOGLE</h3>

  <script async src="https://cse.google.com/cse.js?cx=6766aee62d05f4aa7"></script>
  <div class="gcse-search"></div>
</div>


            <div class="px-3 py-2 text-bg-primary">
                <div class="container">
                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
                        <a href="/"
                            class="d-flex align-items-center my-2 my-lg-0 me-lg-auto text-white text-decoration-none">
                            <svg class="bi me-2" width="40" height="32" role="img" aria-label="Bootstrap">
                                <use xlink:href="#bootstrap"></use>
                            </svg>
                        </a>

                        <ul class="nav col-12 col-lg-auto my-2 justify-content-center my-md-0 text-small">

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
  const toggleButton = document.getElementById('toggle-menu');
  const menu = document.getElementById('menu-lateral');

  toggleButton.addEventListener('click', () => {
    menu.classList.toggle('fechado');
  });

  function scrollToBottom() {
    window.scrollTo({
      top: document.body.scrollHeight,
      behavior: 'smooth'
    });
  }



  // Função para rolar a página para cima ou para baixo por um número "lines" de linhas
  function scrollByLines(lines) {
    const lineHeight = 30; // altura aproximada de uma linha em pixels (ajuste conforme sua fonte/estilo)
    window.scrollBy({
      top: lines * lineHeight, // positivo para descer, negativo para subir
      behavior: 'smooth'       // rolagem suave
    });
  }





</script>




              <li>
                                <a href="/dashboard" data-bs-toggle="tooltip" data-bs-placement="top" . . .
                                    data-bs-custom-class="custom-tooltip"
                                    data-bs-title="Ir para o início do sistema com as opções disponíveis"
                                    class="nav-link text-white">
                                    <i class="fa-solid fa-house"></i>
                                    Início do sistema
                                </a>
                            </li>
              @can('MERCADO - VER STATUS')
              <li class="ms-2 d-none d-md-flex align-items-center">
                <span id="market-status-global" class="badge rounded-pill bg-secondary" title="Status do mercado (NYSE)">Mercado: carregando…</span>
              </li>
              @endcan

                            @canany(['OPENAI - CHAT', 'OPENAI - TRANSCRIBE - ESPANHOL'])
                            <li>
                                <a href="{{ route('openai.menu') }}" data-bs-toggle="tooltip" data-bs-placement="top"
                                   data-bs-custom-class="custom-tooltip"
                                   data-bs-title="Acessar as ferramentas OpenAI (Chat e Transcrição)"
                                   class="nav-link text-white">
                                    <i class="fa-brands fa-openai"></i>
                                    OpenAI
                                </a>
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

              <!-- Link: Snapshots de Investimentos -->
              @can('INVESTIMENTOS SNAPSHOTS - LISTAR')
              <li>
                <a href="{{ route('investments.daily-balances.index') }}"
                   class="nav-link text-white"
                   data-bs-toggle="tooltip" data-bs-placement="top"
                   data-bs-custom-class="custom-tooltip"
                   data-bs-title="Ver e gerar snapshots diários (saldo consolidado, variação e exportação CSV)">
                  <i class="fa-solid fa-chart-line"></i>
                  Investimentos (Snapshots)
                </a>
              </li>
              @endcan

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
                <form method="POST" action="{{ route('logout') }}" class="ms-1">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-outline-light d-inline-flex align-items-center gap-1" title="Desconectar">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Desconectar</span>
                  </button>
                </form>
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

        {{-- Toasts de feedback via sessão --}}
        <div aria-live="polite" aria-atomic="true" class="position-relative">
          <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 2000;">
            @foreach (['success'=>'success','info'=>'info','warning'=>'warning','error'=>'danger'] as $k => $cls)
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
      (function(){
        async function loadMarketStatus(){
          const badge = document.getElementById('market-status-global');
          if (!badge) return;
          try{
            const url = "{{ route('api.market.status') }}";
            const resp = await fetch(url, { headers: { 'Accept':'application/json' } });
            const data = await resp.json().catch(()=>null);
            if(!resp.ok || !data) throw new Error('fail');
            const st = String(data.status||'').toLowerCase();
            const label = String(data.label||'Mercado');
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
            const nextStr = data.next_change_at ? ` • Próx: ${fmtBR(data.next_change_at)}` : '';
            let cls = 'bg-secondary';
            if (st === 'open') cls = 'bg-success';
            else if (st === 'pre') cls = 'bg-warning text-dark';
            else if (st === 'after') cls = 'bg-info text-dark';
            else if (st === 'closed') cls = 'bg-secondary';
            badge.className = 'badge rounded-pill ' + cls;
            badge.textContent = `Mercado: ${label}` + nextStr;
            if (data.reason){ badge.title = `${label} — ${data.reason}`; }
          }catch(_e){
            const badge = document.getElementById('market-status-global');
            if (badge){ badge.className='badge rounded-pill bg-secondary'; badge.textContent='Mercado: indisponível'; }
          }
        }
        try{ loadMarketStatus(); setInterval(loadMarketStatus, 60000); }catch(_e){}
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
</body>

</html>
<script>
    function scrollToBottom() {
      window.scrollTo(0, document.body.scrollHeight);
    }
    </script>
