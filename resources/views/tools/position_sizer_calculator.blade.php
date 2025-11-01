@extends('layouts.bootstrap5')

@section('content')
<div class="container py-4">
  <h1 class="h4 mb-3">Position_Sizer – Calculadora</h1>

  <style>
    /* Alinha a segunda coluna (valores) à direita para leitura numérica */
    .psizer-table tbody tr > td:nth-child(2) { text-align: right; }
    /* Pílula de destaque para percentuais */
    .pct-pill { color:#0d47a1; border:2px solid #0d47a1; background-color:#fff; font-size:1.05rem; padding:.35rem .6rem; }
    /* Fundo azul claro somente para o card do formulário inicial */
    .psizer-form-card { background-color: #e7f1ff; }
    .psizer-form-card .card-body { background-color: #e7f1ff; }
  </style>

  <div class="card shadow-sm mb-4 psizer-form-card">
    <div class="card-body">
      <form method="get" action="{{ route('openai.tools.position-sizer.calculator') }}" class="row g-3">
        <div class="col-12 col-md-4">
          <label class="form-label">Equity da conta ($)</label>
          <input type="number" step="0.01" min="0" class="form-control" name="equity" value="{{ old('equity', $input['equity']) }}">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Risco por trade (fração)</label>
          <div class="input-group">
            <input type="number" step="0.0001" min="0" class="form-control" name="riskPct" value="{{ old('riskPct', $input['riskPct']) }}">
            <span class="input-group-text" title="Ex.: 0.01 = 1%">ex.: 0.01 = 1%</span>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Preço de entrada</label>
          <input type="number" step="0.01" class="form-control" name="entry" value="{{ old('entry', $input['entry']) }}">
        </div>

        <div class="col-12 col-md-4">
          <label class="form-label">Preço de stop</label>
          <input type="number" step="0.01" class="form-control" name="stop" value="{{ old('stop', $input['stop']) }}">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Slippage por ação ($)</label>
          <input type="number" step="0.01" min="0" class="form-control" name="slippage" value="{{ old('slippage', $input['slippage']) }}">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Taxas por ação ($)</label>
          <input type="number" step="0.01" min="0" class="form-control" name="feeShare" value="{{ old('feeShare', $input['feeShare']) }}">
        </div>

        <div class="col-12 col-md-4">
          <label class="form-label">Custos fixos por trade ($)</label>
          <input type="number" step="0.01" min="0" class="form-control" name="fixed" value="{{ old('fixed', $input['fixed']) }}">
        </div>
        <div class="col-12 col-md-8 d-flex align-items-end gap-2">
          <button type="submit" class="btn btn-primary">Calcular</button>
          <a href="{{ route('openai.tools.position-sizer.calculator') }}" class="btn btn-outline-secondary">Restaurar padrões</a>
          <a href="{{ route('openai.tools.position-sizer') }}" class="btn btn-outline-success" title="Baixar planilha XLSX">
            <i class="fa-solid fa-file-excel me-1"></i> Baixar XLSX
          </a>
          <a href="{{ route('openai.tools.position-sizer.preview') }}" class="btn btn-outline-info" title="Ver planilha na web (HTML)">
            <i class="fa-solid fa-eye me-1"></i> Ver na web
          </a>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-header fw-semibold">Resultados</div>
    <div class="card-body">
      @php
        $fmt = fn($v, $dec=2) => is_null($v) ? '—' : number_format((float)$v, $dec, ',', '.');
      @endphp
      <div class="table-responsive">
        <table class="table table-sm align-middle psizer-table">
          <tbody>
          <tr>
            <th style="width: 40%">Risco por ação ($)</th>
            <td>$ {{ $fmt($result['rPerShare']) }}</td>
          </tr>
          <tr>
            <th>Risco permitido ($)</th>
            <td>$ {{ $fmt($result['riskAllowed']) }}</td>
          </tr>
          <tr>
            <th>Risco permitido (aj. custos fixos) ($)</th>
            <td>$ {{ $fmt($result['riskAdj']) }}</td>
          </tr>
          <tr>
            <th>Tamanho da posição (ações)</th>
            <td>{{ number_format((int)$result['size'], 0, ',', '.') }}</td>
          </tr>
          <tr>
            <th>Exposição nominal ($)</th>
            <td>$ {{ $fmt($result['notional']) }}</td>
          </tr>
          <tr>
            <th>Direção</th>
            <td>{{ $result['dir'] }}</td>
          </tr>
          <tr>
            <th>Preço alvo 1R</th>
            <td>$ {{ $fmt($result['t1']) }}</td>
          </tr>
          <tr>
            <th>Preço alvo 2R</th>
            <td>$ {{ $fmt($result['t2']) }}</td>
          </tr>
          @php
            $delta12 = (isset($result['t2'], $result['t1'])) ? ((float)$result['t2'] - (float)$result['t1']) : null;
            $pct12 = (!is_null($delta12) && (float)($result['t1'] ?? 0) != 0.0)
              ? ($delta12 / (float)$result['t1'])
              : null;
          @endphp
          <tr class="table-info">
            <th>Variação entre 2R e 1R (valor)</th>
            <td>
              @if(!is_null($delta12))
                $ {{ $fmt($delta12) }}
              @else
                —
              @endif
            </td>
          </tr>
          <tr class="table-warning">
            <th>Variação entre 2R e 1R (%)</th>
            <td>
              @if(!is_null($pct12))
                <span class="badge rounded-pill fw-bold pct-pill">{{ number_format($pct12 * 100, 2, ',', '.') }}%</span>
              @else
                —
              @endif
            </td>
          </tr>
          <tr>
            <th>Perda máx. se stopar ($)</th>
            <td>$ {{ $fmt($result['maxLoss']) }}</td>
          </tr>
          <tr>
            <th>Preço de break-even (incl. custos fixos)</th>
            <td>$ {{ $fmt($result['breakeven']) }}</td>
          </tr>
          @php
            $be = $result['breakeven'] ?? null;
            $t1v = $result['t1'] ?? null;
            $t2v = $result['t2'] ?? null;
            $beVs1Val = (!is_null($be) && !is_null($t1v)) ? ((float)$t1v - (float)$be) : null;
            $beVs1Pct = (!is_null($beVs1Val) && (float)($be ?? 0) != 0.0)
              ? ($beVs1Val / (float)$be)
              : null;
            $beVs2Val = (!is_null($be) && !is_null($t2v)) ? ((float)$t2v - (float)$be) : null;
            $beVs2Pct = (!is_null($beVs2Val) && (float)($be ?? 0) != 0.0)
              ? ($beVs2Val / (float)$be)
              : null;
          @endphp
          <tr>
            <td colspan="2"><div class="small text-muted fw-semibold">Break‑even vs Alvos</div></td>
          </tr>
          <tr class="table-info">
            <th>Variação BE vs 1R (valor)</th>
            <td>
              @if(!is_null($beVs1Val))
                $ {{ $fmt($beVs1Val) }}
              @else
                —
              @endif
            </td>
          </tr>
          <tr class="table-warning">
            <th>Variação BE vs 1R (%)</th>
            <td>
              @if(!is_null($beVs1Pct))
                <span class="badge rounded-pill fw-bold pct-pill">{{ number_format($beVs1Pct * 100, 2, ',', '.') }}%</span>
              @else
                —
              @endif
            </td>
          </tr>
          <tr class="table-info">
            <th>Variação BE vs 2R (valor)</th>
            <td>
              @if(!is_null($beVs2Val))
                $ {{ $fmt($beVs2Val) }}
              @else
                —
              @endif
            </td>
          </tr>
          <tr class="table-warning">
            <th>Variação BE vs 2R (%)</th>
            <td>
              @if(!is_null($beVs2Pct))
                <span class="badge rounded-pill fw-bold pct-pill">{{ number_format($beVs2Pct * 100, 2, ',', '.') }}%</span>
              @else
                —
              @endif
            </td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
