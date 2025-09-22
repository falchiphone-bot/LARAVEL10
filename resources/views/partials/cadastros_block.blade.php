@can('CADASTROS - LISTAR')
<div class="card mb-3" style="background-color: hsla(234, 92%, 47%, 0.04);">
    <div class="card-header d-flex align-items-center" style="font-weight:600;">
        <i class="fa-solid fa-database me-2"></i> Cadastros
    </div>
    <div class="card-body py-3">
        <div class="d-flex flex-wrap gap-2">
            @can('REPRESENTANTES - LISTAR')
                <a class="btn btn-primary position-relative" href="/Representantes" data-bs-toggle="tooltip" data-bs-title="Total de representantes cadastrados">
                    Representantes <span class="badge bg-light text-dark ms-1" id="count-representantes">…</span>
                </a>
            @endcan
            @can('PREPARADORES - LISTAR')
                <a class="btn btn-primary position-relative" href="/Preparadores" data-bs-toggle="tooltip" data-bs-title="Total de preparadores cadastrados">
                    Preparadores <span class="badge bg-light text-dark ms-1" id="count-preparadores">…</span>
                </a>
            @endcan
            @can('FUNCAOPROFISSIONAL - LISTAR')
                <a class="btn btn-primary position-relative" href="/FuncaoProfissional" data-bs-toggle="tooltip" data-bs-title="Total de funções profissionais">
                    Função profissional <span class="badge bg-light text-dark ms-1" id="count-funcao">…</span>
                </a>
            @endcan
            @can('CATEGORIAS - LISTAR')
                <a class="btn btn-primary position-relative" href="/Categorias" data-bs-toggle="tooltip" data-bs-title="Total de categorias">
                    Categorias <span class="badge bg-light text-dark ms-1" id="count-categorias">…</span>
                </a>
            @endcan
            @can('POSICOES - LISTAR')
                <a class="btn btn-primary position-relative" href="/Posicoes" data-bs-toggle="tooltip" data-bs-title="Total de posições">
                    Posições <span class="badge bg-light text-dark ms-1" id="count-posicoes">…</span>
                </a>
            @endcan
            @can('TIPOARQUIVO - LISTAR')
                <a class="btn btn-primary position-relative" href="/TipoArquivo" data-bs-toggle="tooltip" data-bs-title="Total de tipos de arquivo">
                    Tipo de Arquivo <span class="badge bg-light text-dark ms-1" id="count-tipoarquivo">…</span>
                </a>
            @endcan
            @can('TIPOESPORTE - LISTAR')
                <a class="btn btn-primary position-relative" href="/TipoEsporte" data-bs-toggle="tooltip" data-bs-title="Total de tipos de esporte">
                    Tipo de Esporte <span class="badge bg-light text-dark ms-1" id="count-tipoesporte">…</span>
                </a>
            @endcan
        </div>
    </div>
</div>
@endcan
