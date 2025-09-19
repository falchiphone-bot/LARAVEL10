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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-papNV2f7SUl+Yw9vFQ3Y2AuykUytJzZJr+gYw4E3aVqwnC5Cq2tv/x2aWg1o2UDNKXPYQj3gVWaV+Pk7uw6K4w==" crossorigin="anonymous" referrerpolicy="no-referrer" />

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
              <li class="ms-2 d-none d-md-flex align-items-center">
                <span id="market-status-global" class="badge rounded-pill bg-secondary" title="Status do mercado (NYSE)">Mercado: carregando…</span>
              </li>

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

                            <li>
                                <a href="/profile" data-bs-toggle="tooltip" data-bs-placement="top"
                                    data-bs-custom-class="custom-tooltip"
                                    data-bs-title="Email: {{ optional(Auth::user())->email ?? 'Não autenticado' }} Clique para efetuar logout, alterar nome, senha, alterar email(atualizar o cadastro)."
                                    class="nav-link text-white">
                                    <i class="fa-solid fa-user"></i>

                                    Perfil do usuário: {{ optional(Auth::user())->name ?? 'Visitante' }}

                                </a>
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
