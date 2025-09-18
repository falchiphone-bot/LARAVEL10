@php(
    $cadastrosCounts = Cache::remember('cadastros_counts', 60, function() {
        return [
            'representantes' => class_exists(\App\Models\Representantes::class) ? \App\Models\Representantes::count() : null,
            'preparadores' => class_exists(\App\Models\Preparadores::class) ? \App\Models\Preparadores::count() : null,
            'funcao' => class_exists(\App\Models\FuncaoProfissional::class) ? \App\Models\FuncaoProfissional::count() : null,
            'categorias' => class_exists(\App\Models\Categorias::class) ? \App\Models\Categorias::count() : null,
            'posicoes' => class_exists(\App\Models\Posicoes::class) ? \App\Models\Posicoes::count() : null,
            'tipoarquivo' => class_exists(\App\Models\TipoArquivo::class) ? \App\Models\TipoArquivo::count() : null,
            'tipoesporte' => class_exists(\App\Models\TipoEsporte::class) ? \App\Models\TipoEsporte::count() : null,
        ];
    })
)
@can('CADASTROS - LISTAR')
<div class="card mb-3" style="background-color: hsla(234, 92%, 47%, 0.04);">
    <div class="card-header d-flex align-items-center" style="font-weight:600;">
        <i class="fa-solid fa-database me-2"></i> Cadastros
    </div>
    <div class="card-body py-3">
        <div class="d-flex flex-wrap gap-2">
            @can('REPRESENTANTES - LISTAR')
                <a class="btn btn-primary position-relative" href="/Representantes" data-bs-toggle="tooltip" data-bs-title="Total de representantes cadastrados">
                    Representantes
                    @if(!is_null($cadastrosCounts['representantes']))
                        <span class="badge bg-light text-dark ms-1">{{ $cadastrosCounts['representantes'] }}</span>
                    @endif
                </a>
            @endcan
            @can('PREPARADORES - LISTAR')
                <a class="btn btn-primary position-relative" href="/Preparadores" data-bs-toggle="tooltip" data-bs-title="Total de preparadores cadastrados">
                    Preparadores
                    @if(!is_null($cadastrosCounts['preparadores']))
                        <span class="badge bg-light text-dark ms-1">{{ $cadastrosCounts['preparadores'] }}</span>
                    @endif
                </a>
            @endcan
            @can('FUNCAOPROFISSIONAL - LISTAR')
                <a class="btn btn-primary position-relative" href="/FuncaoProfissional" data-bs-toggle="tooltip" data-bs-title="Total de funções profissionais">
                    Função profissional
                    @if(!is_null($cadastrosCounts['funcao']))
                        <span class="badge bg-light text-dark ms-1">{{ $cadastrosCounts['funcao'] }}</span>
                    @endif
                </a>
            @endcan
            @can('CATEGORIAS - LISTAR')
                <a class="btn btn-primary position-relative" href="/Categorias" data-bs-toggle="tooltip" data-bs-title="Total de categorias">
                    Categorias
                    @if(!is_null($cadastrosCounts['categorias']))
                        <span class="badge bg-light text-dark ms-1">{{ $cadastrosCounts['categorias'] }}</span>
                    @endif
                </a>
            @endcan
            @can('POSICOES - LISTAR')
                <a class="btn btn-primary position-relative" href="/Posicoes" data-bs-toggle="tooltip" data-bs-title="Total de posições">
                    Posições
                    @if(!is_null($cadastrosCounts['posicoes']))
                        <span class="badge bg-light text-dark ms-1">{{ $cadastrosCounts['posicoes'] }}</span>
                    @endif
                </a>
            @endcan
            @can('TIPOARQUIVO - LISTAR')
                <a class="btn btn-primary position-relative" href="/TipoArquivo" data-bs-toggle="tooltip" data-bs-title="Total de tipos de arquivo">
                    Tipo de Arquivo
                    @if(!is_null($cadastrosCounts['tipoarquivo']))
                        <span class="badge bg-light text-dark ms-1">{{ $cadastrosCounts['tipoarquivo'] }}</span>
                    @endif
                </a>
            @endcan
            @can('TIPOESPORTE - LISTAR')
                <a class="btn btn-primary position-relative" href="/TipoEsporte" data-bs-toggle="tooltip" data-bs-title="Total de tipos de esporte">
                    Tipo de Esporte
                    @if(!is_null($cadastrosCounts['tipoesporte']))
                        <span class="badge bg-light text-dark ms-1">{{ $cadastrosCounts['tipoesporte'] }}</span>
                    @endif
                </a>
            @endcan
        </div>
    </div>
</div>
@endcan
