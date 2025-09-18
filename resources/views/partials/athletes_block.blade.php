@php(
    $athleteCounts = Cache::remember('athletes_counts', 60, function() {
        return [
            'formandos' => class_exists(\App\Models\FormandoBase::class) ? \App\Models\FormandoBase::count() : null,
            'flow' => class_exists(\App\Models\FormandoBaseWhatsapp::class) ? \App\Models\FormandoBaseWhatsapp::count() : null,
            'percentuais' => class_exists(\App\Models\TanabiAthletePercentage::class) ? \App\Models\TanabiAthletePercentage::count() : null,
        ];
    })
)
@canany(['FORMANDOBASE - LISTAR','FORMANDOBASEWHATSAPP - LISTAR','TANABI ATLETAS PERCENTUAIS - LISTAR'])
<div class="card mb-2" style="background-color: rgba(0,123,255,.06);">
    <div class="card-header d-flex align-items-center" style="font-weight:600;">
        <i class="fa-solid fa-futbol me-2"></i> Atletas / Formação / Percentuais
    </div>
    <div class="card-body py-2">
        <div class="d-flex flex-wrap gap-2">
            @can('FORMANDOBASE - LISTAR')
                <a class="btn btn-outline-primary btn-sm position-relative" href="/FormandoBase" data-bs-toggle="tooltip" data-bs-title="Total de atletas cadastrados manualmente">
                    Atletas (cadastro)
                    @isset($athleteCounts['formandos'])
                        <span class="badge bg-light text-dark ms-1">{{ $athleteCounts['formandos'] }}</span>
                    @endisset
                </a>
            @endcan
            @can('FORMANDOBASEWHATSAPP - LISTAR')
                <a class="btn btn-outline-primary btn-sm position-relative" href="/FormandoBaseWhatsapp" data-bs-toggle="tooltip" data-bs-title="Total de atletas vindos do fluxo WhatsApp">
                    Atletas - via Flow WhatsApp
                    @isset($athleteCounts['flow'])
                        <span class="badge bg-light text-dark ms-1">{{ $athleteCounts['flow'] }}</span>
                    @endisset
                </a>
            @endcan
            @can('TANABI ATLETAS PERCENTUAIS - LISTAR')
                <a class="btn btn-outline-primary btn-sm position-relative" href="{{ route('tanabi.athletes.percentages.index') }}" data-bs-toggle="tooltip" data-bs-title="Total de registros de percentuais econômicos de atletas">
                    Atletas - Percentuais Econômicos
                    @isset($athleteCounts['percentuais'])
                        <span class="badge bg-light text-dark ms-1">{{ $athleteCounts['percentuais'] }}</span>
                    @endisset
                </a>
            @endcan
        </div>
    </div>
</div>
@endcanany
