@extends('layouts.bootstrap5')
@section('content')
<div class="container-fluid">
  <h1 class="h4 mb-3">Carteira Atual</h1>
  @if(!empty($missingTable))
    <div class="alert alert-warning d-flex justify-content-between align-items-center">
      <div>
        Tabela <code>user_holdings</code> não encontrada. Rode as migrations para habilitar a carteira.
      </div>
      <div class="small text-muted">artisan migrate</div>
    </div>
  @endif
  <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
    <a href="{{ request()->fullUrlWithQuery(['refresh'=>1]) }}" class="btn btn-sm btn-outline-primary" title="Atualizar cotações (limite de 300 posições por requisição)">Atualizar Cotações</a>
    <a href="{{ route('openai.variations.index') }}" class="btn btn-sm btn-outline-secondary" title="Ir para Variações Mensais">Variações Mensais</a>
    @php
      $__selParams = [];
      if(empty($missingTable) && !empty($rows)){
        $__count=0;
        foreach($rows as $__r){
          if(!empty($__r['code'])){
            $__selParams[] = 'selected_codes[]='.urlencode($__r['code']);
            $__count++; if($__count>=200) break; // evita URL gigante
          }
        }
      }
      $__variationsSelectedUrl = route('openai.variations.index') . (count($__selParams)?('?'.implode('&', $__selParams)):'');
    @endphp
    @if(!empty($__selParams))
      <a href="{{ $__variationsSelectedUrl }}" class="btn btn-sm btn-outline-warning" title="Abrir Variações com todos os códigos da carteira já selecionados (máx 200)">Variações (Códigos da Carteira)</a>
    @endif
    <a href="{{ route('holdings.create') }}" class="btn btn-sm btn-success" title="Adicionar nova posição">Nova Posição</a>
    @can('HOLDINGS - IMPORTAR')
      <a href="{{ route('holdings.screen.quick.form') }}" class="btn btn-sm btn-outline-primary" title="Importar/colar holdings via tela da Avenue (Screen)">
        Importar Holdings (Avenue)
      </a>
    @endcan
    @can('CASH EVENTS - LISTAR')
      <a href="{{ route('cash.events.index') }}#gsc.tab=0" class="btn btn-sm btn-outline-secondary" title="Ver eventos de caixa (dividendos, impostos, depósitos, retiradas)">
        Ver Eventos de Caixa
      </a>
    @endcan
  {{-- Rota deprecated removida: holdings.screen.quick.form --}}
  {{-- <a href="{{ route('holdings.screen.quick.form') }}" class="btn btn-sm btn-outline-primary" title="Colar rapidamente bloco de tela Avenue e atualizar">Atualizar (Avenue Screen)</a> --}}
    {{-- Botão Excluir Todas temporariamente desativado --}}
    {{----
    <form action="{{ route('holdings.bulkDestroy') }}" method="POST" class="d-inline" onsubmit="return confirm('Apagar TODAS as posições? Esta ação não pode ser desfeita.')">
      @csrf
      @method('DELETE')
      <input type="hidden" name="confirm" value="yes" />
      <button class="btn btn-sm btn-outline-danger" type="submit" title="Excluir todas as posições do usuário atual">Excluir Todas</button>
    </form>
    ----}}
    <a href="{{ route('holdings.export.csv') }}" class="btn btn-sm btn-outline-info" title="Exportar holdings atuais em CSV">Exportar CSV</a>
    <a href="{{ route('holdings.export.xlsx') }}" class="btn btn-sm btn-outline-info" title="Exportar holdings atuais em XLSX">Exportar XLSX</a>
  {{-- Rota deprecated removida: holdings.template.csv --}}
  {{-- <a href="{{ route('holdings.template.csv') }}" class="btn btn-sm btn-outline-secondary" title="Baixar template CSV genérico">Template CSV</a> --}}
    @php
      $accountsList = collect($rows)->pluck('account')->unique()->filter();
    @endphp
    {{-- Bloco deprecated: templates por conta removidos --}}
    {{-- @if($accountsList->count())
      <div class="btn-group btn-group-sm">
        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Template por Conta</button>
        <ul class="dropdown-menu">
          @foreach($accountsList as $accName)
            @php $accId = collect($rows)->firstWhere('account', $accName)['id'] ?? null; @endphp
            @if($accId)
              <li><a class="dropdown-item" href="{{ route('holdings.template.account.csv', $accId) }}">{{ $accName }}</a></li>
            @endif
          @endforeach
        </ul>
      </div>
    @endif --}}
    {{-- Rota deprecated removida: holdings.reimport --}}
    {{-- <form action="{{ route('holdings.reimport') }}" method="POST" class="d-inline" onsubmit="return confirm('Limpar todas as posições (soft delete) e ir para importação?')">
      @csrf
      <button class="btn btn-sm btn-warning" type="submit" title="Soft delete de todas e redirecionar para importação">Reimportar (Limpar + Importar)</button>
    </form> --}}
    @if($refresh && $updatedCodes)
      <span class="text-muted small">Atualizados: {{ implode(', ', $updatedCodes) }}</span>
    @endif
    <form method="get" action="{{ route('openai.portfolio.index') }}" class="d-flex flex-wrap gap-2 align-items-end mt-2 mt-md-0">
      <div>
        <label class="form-label small mb-1">Código</label>
        <input type="text" name="code" value="{{ $filter_code ?? '' }}" class="form-control form-control-sm" placeholder="Ex: AAPL" maxlength="15" />
      </div>
      <div>
        <label class="form-label small mb-1">Conta</label>
        <select name="account_id" class="form-select form-select-sm" style="min-width:160px">
          <option value="">— Todas —</option>
          @foreach($filter_accounts as $fa)
            <option value="{{ $fa->id }}" @selected($filter_account_id === $fa->id)>{{ $fa->account_name }} @if($fa->broker) ({{ $fa->broker }}) @endif</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="form-label small mb-1">Conta (texto)</label>
        <input type="text" name="account" value="{{ $filter_account_name ?? '' }}" class="form-control form-control-sm" placeholder="Nome ou corretora" />
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-sm btn-outline-secondary" type="submit" title="Aplicar filtros"><i class="fa-solid fa-filter me-1"></i>Filtrar</button>
        @if(($filter_code ?? '') !== '' || $filter_account_id || ($filter_account_name ?? '') !== '')
          <a href="{{ route('openai.portfolio.index') }}" class="btn btn-sm btn-outline-dark" title="Limpar filtros"><i class="fa-solid fa-xmark me-1"></i>Limpar</a>
        @endif
      </div>
    </form>
  </div>
  <div class="card shadow-sm @if(!empty($missingTable)) opacity-50 @endif">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>Posições</strong>
      <small class="text-muted d-block">
        USD: Investido $US {{ number_format($agg['total_invested'],2,',','.') }} | Atual @if(!is_null($agg['total_current'])) $US {{ number_format($agg['total_current'],2,',','.') }} @else — @endif | P/L @if(!is_null($agg['total_gain_loss_abs'])) $US {{ number_format($agg['total_gain_loss_abs'],2,',','.') }} ({{ number_format($agg['total_gain_loss_pct'],2,',','.') }}%) @else — @endif
      </small>
      @if(!is_null($usd_to_brl_rate) && !is_null($agg['total_invested_brl']))
      <small class="text-muted d-block">
        BRL (@ {{ number_format($usd_to_brl_rate,4,',','.') }}): Investido R$ {{ number_format($agg['total_invested_brl'],2,',','.') }} | Atual @if(!is_null($agg['total_current_brl'])) R$ {{ number_format($agg['total_current_brl'],2,',','.') }} @else — @endif | P/L @if(!is_null($agg['total_gain_loss_abs_brl'])) R$ {{ number_format($agg['total_gain_loss_abs_brl'],2,',','.') }} @else — @endif
      </small>
      @endif
    </div>
    <div class="table-responsive">
      <table class="table table-sm table-striped align-middle mb-0">
        <thead class="table-light">
          <tr>
            @php
              $baseQuery = array_filter([
                'code' => $filter_code ?? null,
                'account_id' => $filter_account_id ?? null,
              ]);
              function sortLink($col,$label,$currentSort,$currentDir,$base){
                $nextDir = ($currentSort === $col && $currentDir === 'asc') ? 'desc' : 'asc';
                $qs = http_build_query(array_merge($base, ['sort'=>$col,'dir'=>$nextDir]));
                $icon = '';
                if($currentSort === $col){
                  $icon = $currentDir === 'asc' ? ' <i class="fa-solid fa-caret-up small"></i>' : ' <i class="fa-solid fa-caret-down small"></i>';
                }
                return '<a href="?'.$qs.'" class="text-decoration-none">'.$label.$icon.'</a>';
              }
            @endphp
            <th>{!! sortLink('code','Código',$sort??'', $dir??'asc',$baseQuery) !!}</th>
            <th>{!! sortLink('account','Conta',$sort??'', $dir??'asc',$baseQuery) !!}</th>
            <th>Corretora</th>
            <th class="text-end">{!! sortLink('quantity','Qtd',$sort??'', $dir??'asc',$baseQuery) !!}</th>
            <th class="text-end">{!! sortLink('avg_price','Preço Médio',$sort??'', $dir??'asc',$baseQuery) !!}</th>
            <th class="text-end">{!! sortLink('invested_value','Investido ($US)',$sort??'', $dir??'asc',$baseQuery) !!}</th>
            <th class="text-end">{!! sortLink('current_price','Cotação Atual',$sort??'', $dir??'asc',$baseQuery) !!}</th>
            <th class="text-end">{!! sortLink('current_value','Valor Atual ($US)',$sort??'', $dir??'asc',$baseQuery) !!}</th>
            <th class="text-end">{!! sortLink('gain_loss_abs','P/L ($US)',$sort??'', $dir??'asc',$baseQuery) !!}</th>
            <th class="text-end">{!! sortLink('gain_loss_pct','P/L (%)',$sort??'', $dir??'asc',$baseQuery) !!}</th>
            <th class="text-end" title="Variação mensal mais recente">{!! sortLink('variation_monthly','Var. Mês (%)',$sort??'', $dir??'asc',$baseQuery) !!}</th>
            <th class="text-center">Período</th>
            <th class="text-end" title="Cobertura aproximada dos eventos de caixa (compras/vendas) em relação à posição. Heurística baseada em soma de valores e preço médio.">Cobertura Caixa</th>
            <th style="width:90px"></th>
          </tr>
        </thead>
        <tbody>
          @if(!empty($missingTable))
            <tr><td colspan="13" class="text-center text-muted">Aguardando criação da tabela de holdings.</td></tr>
          @else
          @forelse($rows as $r)
            @php
              $clsPl = is_null($r['gain_loss_abs']) ? 'text-muted' : ($r['gain_loss_abs'] > 0 ? 'text-success' : ($r['gain_loss_abs'] < 0 ? 'text-danger' : 'text-secondary'));
              $clsPlPct = is_null($r['gain_loss_pct']) ? 'text-muted' : ($r['gain_loss_pct'] > 0 ? 'text-success' : ($r['gain_loss_pct'] < 0 ? 'text-danger' : 'text-secondary'));
              $clsVar = is_null($r['variation_monthly']) ? 'text-muted' : ($r['variation_monthly'] > 0 ? 'text-success' : ($r['variation_monthly'] < 0 ? 'text-danger' : 'text-secondary'));
              $varLink = $r['code'] ? route('openai.variations.index', ['code'=>$r['code']]) : null;
            @endphp
            <tr>
              <td class="d-flex align-items-center gap-1">
                <a href="{{ request()->fullUrlWithQuery(['code'=>$r['code'],'page'=>null]) }}" class="text-decoration-none" title="Filtrar carteira por este código"><strong>{{ $r['code'] }}</strong></a>
                @if($varLink)
                  @php
                    $varTitle = 'Ver variações deste código em nova guia';
                    if(!empty($r['variation_period'])){ $varTitle .= ' (último período '.$r['variation_period'].')'; }
                    $varBtnClass = is_null($r['variation_monthly'])
                        ? 'btn-outline-secondary'
                        : ($r['variation_monthly'] > 0
                            ? 'btn-outline-success'
                            : ($r['variation_monthly'] < 0 ? 'btn-outline-danger' : 'btn-outline-secondary'));
                    $varBadgeClass = is_null($r['variation_monthly'])
                        ? 'bg-secondary'
                        : ($r['variation_monthly'] > 0
                            ? 'bg-success'
                            : ($r['variation_monthly'] < 0 ? 'bg-danger' : 'bg-secondary'));
                  @endphp
                  <a href="{{ $varLink }}#gsc.tab=0" target="_blank" rel="noopener" class="btn btn-xs {{ $varBtnClass }} py-0 px-1" style="font-size:.7rem" title="{{ $varTitle }}">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                  </a>
                  @if(!is_null($r['variation_monthly']))
                    <span class="badge {{ $varBadgeClass }}" style="font-size:.55rem" title="Variação mensal recente">
                      {{ ($r['variation_monthly'] > 0 ? '+' : '') . number_format($r['variation_monthly'], 2, ',', '.') }}%
                    </span>
                  @endif
                @endif
              </td>
              <td>
                @if($r['account'])
                  <a href="{{ request()->fullUrlWithQuery(['account'=>$r['account'],'account_id'=>null]) }}" class="text-decoration-none" title="Filtrar por conta">{{ $r['account'] }}</a>
                @else
                  —
                @endif
              </td>
              <td>{{ $r['broker'] ?: '—' }}</td>
              <td class="text-end">{{ number_format($r['quantity'], 4, ',', '.') }}</td>
              <td class="text-end">{{ number_format($r['avg_price'], 4, ',', '.') }}</td>
              <td class="text-end">{{ number_format($r['invested_value'], 2, ',', '.') }}</td>
              <td class="text-end">@if(!is_null($r['current_price'])) {{ number_format($r['current_price'], 4, ',', '.') }} @else — @endif</td>
              <td class="text-end">@if(!is_null($r['current_value'])) {{ number_format($r['current_value'], 2, ',', '.') }} @else — @endif</td>
              <td class="text-end {{ $clsPl }}">@if(!is_null($r['gain_loss_abs'])) {{ number_format($r['gain_loss_abs'], 2, ',', '.') }} @else — @endif</td>
              <td class="text-end {{ $clsPlPct }}">@if(!is_null($r['gain_loss_pct'])) {{ number_format($r['gain_loss_pct'], 2, ',', '.') }} @else — @endif</td>
              <td class="text-end {{ $clsVar }}">@if(!is_null($r['variation_monthly'])) {{ number_format($r['variation_monthly'], 4, ',', '.') }} @else — @endif</td>
              <td class="text-center">@if($r['variation_period']) <a href="{{ $varLink }}" class="text-decoration-none">{{ $r['variation_period'] }}</a> @else — @endif</td>
              <td class="text-end">
                @php
                  $ccp = $r['cash_cover_pct'];
                @endphp
                @if(!is_null($ccp))
                  @php
                    $badgeClass = $ccp >= 99.5 ? 'bg-success' : ($ccp >= 80 ? 'bg-primary' : ($ccp >= 50 ? 'bg-warning text-dark' : 'bg-danger'));
                  @endphp
                  <span class="badge {{ $badgeClass }}" style="font-size:.65rem" title="Aprox. adquirida: {{ number_format($r['cash_cover_approx_acquired_qty'] ?? 0,4,',','.') }} | Eventos trades: {{ $r['cash_cover_trade_events'] }}">{{ number_format($ccp,1,',','.') }}%</span>
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
              <td class="text-end d-flex flex-wrap gap-1 justify-content-end">
                <a href="{{ $varLink }}" class="btn btn-xs btn-outline-secondary" title="Ver histórico de variações">Histórico</a>
                {{-- Edição opcional (mantida se quiser ajustar manualmente) --}}
                {{--<a href="{{ route('holdings.edit', $r['id']) }}" class="btn btn-xs btn-outline-primary" title="Editar posição">Editar</a>--}}
                {{-- Botão excluir individual desativado temporariamente
                <form action="{{ route('holdings.destroy', $r['id']) }}" method="POST" onsubmit="return confirm('Excluir posição {{ $r['code'] }}?')">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-xs btn-outline-danger" title="Excluir esta posição" type="submit">Excluir</button>
                </form>
                --}}
              </td>
            </tr>
          @empty
            <tr><td colspan="13" class="text-center text-muted">Nenhuma posição cadastrada.</td></tr>
          @endforelse
          @endif
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
