<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

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

<body>

    <main>
        <header>
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

                                <a href="#" onclick="scrollToBottom('left'); return false;" class="link-esquerda">Ir para a parte inferior</a>
                            </li>

                            </li>

                            <li>
                                <a href="/dashboard" data-bs-toggle="tooltip" data-bs-placement="top" . . .
                                    data-bs-custom-class="custom-tooltip"
                                    data-bs-title="Ir para o início do sistema com as opções disponíveis"
                                    class="nav-link text-white">
                                    <i class="fa-solid fa-house"></i>
                                    Início do sistema
                                </a>
                            </li>

                            <li>
                                <a href="/profile" data-bs-toggle="tooltip" data-bs-placement="top"
                                    data-bs-custom-class="custom-tooltip"
                                    data-bs-title="Email: {{ Auth::user()->email }} Clique para efetuar logout, alterar nome, senha, alterar email(atualizar o cadastro)."
                                    class="nav-link text-white">
                                    <i class="fa-solid fa-user"></i>

                                    Perfil do usuário: {{ Auth::user()->name }}

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
               <a href="#" onclick="window.history.back(); return false;"> <= Voltar para página anterior</a>
               <a href="#" onclick="window.history.forward(); return false;"> =>Avançar para página posterior</a>

            <p class="float-end mb-1">
                <a href="#">Ir para o topo da página.</a>
            </p>
            {{-- <p class="mb-1">Texto para editar</p>
        <p class="mb-0">New to Bootstrap? <a href="/">Visit the homepage</a> or read our <a href="/docs/5.3/getting-started/introduction/">getting started guide</a>.</p> --}}
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
