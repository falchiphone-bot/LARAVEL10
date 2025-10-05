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
    <a href="{{ route('holdings.import.form') }}" class="btn btn-sm btn-outline-dark" title="Importar ou colar CSV de holdings">Importar Holdings</a>
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
    <a href="{{ route('holdings.template.csv') }}" class="btn btn-sm btn-outline-secondary" title="Baixar template CSV genérico">Template CSV</a>
    @php
      $accountsList = collect($rows)->pluck('account')->unique()->filter();
    @endphp
    @if($accountsList->count())
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
    @endif
    <form action="{{ route('holdings.reimport') }}" method="POST" class="d-inline" onsubmit="return confirm('Limpar todas as posições (soft delete) e ir para importação?')">
      @csrf
      <button class="btn btn-sm btn-warning" type="submit" title="Soft delete de todas e redirecionar para importação">Reimportar (Limpar + Importar)</button>
    </form>
    @if($refresh && $updatedCodes)
      <span class="text-muted small">Atualizados: {{ implode(', ', $updatedCodes) }}</span>
    @endif
  </div>
  <div class="card shadow-sm @if(!empty($missingTable)) opacity-50 @endif">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>Posições</strong>
      <small class="text-muted">Total Investido: R$ {{ number_format($agg['total_invested'],2,',','.') }} | Valor Atual: @if(!is_null($agg['total_current'])) R$ {{ number_format($agg['total_current'],2,',','.') }} @else — @endif | P/L: @if(!is_null($agg['total_gain_loss_abs'])) R$ {{ number_format($agg['total_gain_loss_abs'],2,',','.') }} ({{ number_format($agg['total_gain_loss_pct'],2,',','.') }}%) @else — @endif</small>
    </div>
    <div class="table-responsive">
      <table class="table table-sm table-striped align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Código</th>
            <th>Conta</th>
            <th>Corretora</th>
            <th class="text-end">Qtd</th>
            <th class="text-end">Preço Médio</th>
            <th class="text-end">Investido (R$)</th>
            <th class="text-end">Cotação Atual</th>
            <th class="text-end">Valor Atual (R$)</th>
            <th class="text-end">P/L (R$)</th>
            <th class="text-end">P/L (%)</th>
            <th class="text-end" title="Variação mensal mais recente">Var. Mês (%)</th>
            <th class="text-center">Período</th>
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
              <td><strong>{{ $r['code'] }}</strong></td>
              <td>{{ $r['account'] ?: '—' }}</td>
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
