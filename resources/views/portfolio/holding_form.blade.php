@extends('layouts.bootstrap5')
@section('content')
<div class="container" style="max-width:760px">
  <h1 class="h5 mb-3">@if($mode==='create') Nova Posição @else Editar Posição @endif</h1>
  <form method="post" action="@if($mode==='create'){{ route('holdings.store') }}@else{{ route('holdings.update',$holding) }}@endif" class="card shadow-sm">
    @csrf
    @if($mode==='edit') @method('PUT') @endif
    <div class="card-body">
      @if($errors->any())
        <div class="alert alert-danger py-2 small">
          <ul class="mb-0">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
          </ul>
        </div>
      @endif
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label small mb-1">Código</label>
          <input type="text" name="code" value="{{ old('code',$holding->code) }}" class="form-control form-control-sm" required maxlength="32" />
        </div>
        <div class="col-md-4">
          <label class="form-label small mb-1">Conta</label>
          <select name="account_id" class="form-select form-select-sm">
            <option value="">—</option>
            @foreach($accounts as $acc)
              <option value="{{ $acc->id }}" @selected(old('account_id',$holding->account_id)==$acc->id)>{{ $acc->account_name }} @if($acc->broker) ({{ $acc->broker }}) @endif</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label small mb-1">Moeda</label>
          <input type="text" name="currency" value="{{ old('currency',$holding->currency) }}" class="form-control form-control-sm" maxlength="8" placeholder="USD" />
        </div>
        <div class="col-md-4">
          <label class="form-label small mb-1">Quantidade</label>
          <input type="text" name="quantity" value="{{ old('quantity', number_format($holding->quantity ?? 0,4,',','.') ) }}" class="form-control form-control-sm" required />
        </div>
        <div class="col-md-4">
          <label class="form-label small mb-1">Preço Médio</label>
          <input type="text" name="avg_price" value="{{ old('avg_price', number_format($holding->avg_price ?? 0,4,',','.') ) }}" class="form-control form-control-sm" required />
        </div>
        <div class="col-md-4">
          <label class="form-label small mb-1">Investido (R$)</label>
          <input type="text" name="invested_value" value="{{ old('invested_value', $holding->invested_value ? number_format($holding->invested_value,2,',','.') : '') }}" class="form-control form-control-sm" placeholder="(auto = qtd * preço médio)" />
        </div>
        <div class="col-md-4">
          <label class="form-label small mb-1">Cotação Atual</label>
          <input type="text" name="current_price" value="{{ old('current_price', $holding->current_price ? number_format($holding->current_price,4,',','.') : '') }}" class="form-control form-control-sm" />
        </div>
      </div>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
      <div class="d-flex gap-2">
        <a href="{{ route('openai.portfolio.index') }}" class="btn btn-sm btn-secondary">Voltar</a>
        @if($mode==='edit')
          <button type="submit" class="btn btn-sm btn-primary">Salvar</button>
        @else
          <button type="submit" class="btn btn-sm btn-success">Criar</button>
        @endif
      </div>
      @if($mode==='edit')
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="if(confirm('Remover esta posição?')){ document.getElementById('del-form').submit(); }">Excluir</button>
      @endif
    </div>
  </form>
  @if($mode==='edit')
    <form id="del-form" method="post" action="{{ route('holdings.destroy',$holding) }}" class="d-none">
      @csrf @method('DELETE')
    </form>
  @endif
</div>
@endsection
