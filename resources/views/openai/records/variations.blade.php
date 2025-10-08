@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h2 class="mb-0">Variações de Ativos</h2>
    </div>
    <!-- Card de destaque (vermelho) com o botão de layout compacto -->
    <div class="card bg-danger text-white mb-3 shadow-sm">
        <div class="card-body py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="fw-bold">Opções de Exibição</span>
            <button type="button" id="btn-toggle-openai-variations-layout" class="btn btn-light btn-sm" title="Alterna exibição compacta (oculta formulários e cabeçalhos)">Modo Compacto</button>
        </div>
    </div>


    {{-- Formulário: Capital para calcular alocação sugerida com base nos itens exibidos --}}
    <form method="GET" class="row g-2 align-items-end mb-3">
        {{-- preservar parâmetros existentes da página --}}
        @foreach(request()->except(['capital']) as $k => $v)
            @if(is_array($v))
                @foreach($v as $vv)
                    <input type="hidden" name="{{ $k }}[]" value="{{ $vv }}">
                @endforeach
            @else
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endif
        @endforeach
        <div class="col-sm-4 col-md-3">
            <label class="form-label small mb-1">Capital (R$)</label>
            <input type="text" name="capital" value="{{ request('capital') }}" class="form-control form-control-sm" placeholder="ex: 150.000,00">
            <small class="text-muted">Use vírgula como decimal, ponto como milhar.</small>
        </div>
        <div class="col-sm-3 col-md-2 d-grid">
            <button class="btn btn-sm btn-primary">Calcular alocação</button>
        </div>
    </form>

    @php
        $capitalInput = request('capital');
        $capital = null;
        if ($capitalInput !== null && $capitalInput !== '') {
            $tmp = str_replace(['.', ' '], '', (string)$capitalInput);
            $tmp = str_replace(',', '.', $tmp);
            if (is_numeric($tmp)) { $capital = (float)$tmp; }
        }
        // Monta linhas com campos flexíveis
        $items = [];
        foreach (($variations ?? []) as $v) {
            $code = $v->asset_code ?? $v->code ?? optional($v->chat ?? null)->code ?? null;
            $title = $v->title ?? optional($v->chat ?? null)->title ?? '';
            $cur = $v->variation_current ?? $v->var_current ?? $v->current ?? $v->variation ?? null;
            $prev = $v->variation_previous ?? $v->var_previous ?? $v->previous ?? null;
            $diff = $v->difference ?? $v->diff ?? null;
            if ($diff === null && $cur !== null && $prev !== null) { $diff = (float)$cur - (float)$prev; }
            $items[] = [
                'code' => $code,
                'title' => $title,
                'cur' => $cur !== null ? (float)$cur : null,
                'prev' => $prev !== null ? (float)$prev : null,
                'diff' => $diff !== null ? (float)$diff : null,
            ];
        }
        // Seleciona acelerando (diff > 0). Se nenhum, usa top por cur.
        $accel = array_values(array_filter($items, fn($r) => isset($r['diff']) && $r['diff'] > 0));
        if (count($accel) === 0) {
            // fallback: ordenar por cur desc e pegar até 10
            $accel = $items;
            usort($accel, function($a,$b){ return ($b['cur'] ?? -INF) <=> ($a['cur'] ?? -INF); });
            $accel = array_slice($accel, 0, 10);
            // define score como cur (se diff ausente)
            foreach ($accel as &$r) { if (!isset($r['diff']) || $r['diff'] === null) { $r['diff'] = max(0.0, (float)($r['cur'] ?? 0)); } }
            unset($r);
        }
        // Soma dos scores (diff)
        $sum = 0.0;
        foreach ($accel as $r) { $sum += max(0.0, (float)($r['diff'] ?? 0)); }
        // Monta alocação se houver capital válido e soma>0
        $alloc = [];
        if ($capital !== null && $capital > 0 && $sum > 0) {
            foreach ($accel as $r) {
                $w = max(0.0, (float)($r['diff'] ?? 0)) / $sum; // peso 0..1
                $val = $w * $capital;
                $alloc[] = [
                    'code' => $r['code'] ?? '-',
                    'title' => $r['title'] ?? '',
                    'cur' => $r['cur'],
                    'prev' => $r['prev'],
                    'diff' => $r['diff'],
                    'weight' => $w,
                    'amount' => $val,
                ];
            }
        }
    @endphp

    @if($capital && $sum > 0)
        <div class="card mb-3 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Alocação sugerida</strong>
                <small class="text-muted">Base: itens exibidos nesta página • Peso ∝ Diferença (%) positiva</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:14%">Código</th>
                                <th>Conversa / Ativo</th>
                                <th class="text-end" style="width:12%">Variação Atual (%)</th>
                                <th class="text-end" style="width:12%">Anterior (%)</th>
                                <th class="text-end" style="width:12%">Diferença (pp)</th>
                                <th class="text-end" style="width:12%">Peso</th>
                                <th class="text-end" style="width:16%">Valor (R$)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alloc as $r)
                                <tr>
                                    <td><strong>{{ $r['code'] ?: '—' }}</strong></td>
                                    <td class="text-truncate" style="max-width: 420px">{{ $r['title'] ?: '—' }}</td>
                                    <td class="text-end">@if($r['cur']!==null) {{ number_format($r['cur'], 4, ',', '.') }} @else — @endif</td>
                                    <td class="text-end">@if($r['prev']!==null) {{ number_format($r['prev'], 4, ',', '.') }} @else — @endif</td>
                                    <td class="text-end">@if($r['diff']!==null) {{ number_format($r['diff'], 4, ',', '.') }} @else — @endif</td>
                                    <td class="text-end">{{ number_format($r['weight']*100, 2, ',', '.') }}%</td>
                                    <td class="text-end">{{ number_format($r['amount'], 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="6" class="text-end">Capital</th>
                                <th class="text-end">{{ number_format($capital, 2, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @elseif($capitalInput !== null)
        <div class="alert alert-warning">Não foi possível calcular a alocação. Verifique o capital informado e se há Diferença (%) positiva nos itens.</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Código do Ativo</th>
                <th>ID da Conversa</th>
                <th>Mês</th>
                <th>Ano</th>
                <th>Variação</th>
                <th>Data de Criação</th>
            </tr>
        </thead>
        <tbody>
            @forelse($variations as $variation)
                <tr>
                    <td>{{ $variation->id }}</td>
                    <td>{{ $variation->asset_code }}</td>
                    <td>{{ $variation->chat_id }}</td>
                    <td>{{ $variation->month }}</td>
                    <td>{{ $variation->year }}</td>
                    <td>{{ number_format($variation->variation, 4, ',', '.') }}</td>
                    <td>{{ $variation->created_at }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">Nenhuma variação encontrada.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('styles')
<style>
    body.openai-variations-compact header { display:none !important; }
    body.openai-variations-compact form.row.g-2.align-items-end.mb-3 { display:none !important; }
    body.openai-variations-compact .card.mb-3.shadow-sm,
    body.openai-variations-compact .card.bg-danger.text-white.mb-3.shadow-sm,
    body.openai-variations-compact .alert,
    /* Oculta o título (era mb-0, antes a regra apontava mb-3 e não surtia efeito) */
    body.openai-variations-compact h2.mb-0 { display:none !important; }
    body.openai-variations-compact #btn-toggle-openai-variations-layout { background:#212529; color:#fff; }
</style>
@endpush

@push('scripts')
<script>
(function(){
    const LS_KEY='openai_variations_layout_compact';
    const btnId='btn-toggle-openai-variations-layout';
    function apply(){
        const compact = localStorage.getItem(LS_KEY)==='1';
        document.body.classList.toggle('openai-variations-compact', compact);
        const btn = document.getElementById(btnId);
        if(btn){ btn.textContent = compact ? 'Modo Completo' : 'Modo Compacto'; }
    }
    document.addEventListener('DOMContentLoaded', function(){
        apply();
        document.getElementById(btnId)?.addEventListener('click', function(){
            const next = !(localStorage.getItem(LS_KEY)==='1');
            localStorage.setItem(LS_KEY, next ? '1':'0');
            apply();
        });
    });
})();
</script>
@endpush
