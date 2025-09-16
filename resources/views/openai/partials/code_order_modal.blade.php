@props(['order'])

<div class="modal fade" id="editOrderModal_{{ $order->id }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Editar Ordem</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <form method="POST" action="{{ route('openai.records.codeOrder.update', $order->id) }}">
        @csrf
        @method('PATCH')
        <div class="modal-body vstack gap-2">
          <div>
            <label class="form-label small mb-1">Código</label>
            @php $chatHasCode = $order->chat && $order->chat->code; @endphp
            <input type="text" name="code" class="form-control" value="{{ $chatHasCode ? $order->chat->code : $order->code }}" maxlength="50" {{ $chatHasCode ? 'readonly' : '' }}>
            @if($chatHasCode)
              <div class="form-text">Vinculado ao código da conversa.</div>
            @endif
          </div>
          <div>
            <label class="form-label small mb-1">Tipo</label>
            <select name="type" class="form-select" required>
              <option value="compra" {{ $order->type==='compra'?'selected':'' }}>Compra</option>
              <option value="venda" {{ $order->type==='venda'?'selected':'' }}>Venda</option>
            </select>
          </div>
          <div>
            <label class="form-label small mb-1">Quantidade</label>
            <input type="text" name="quantity" class="form-control" inputmode="decimal" value="{{ rtrim(rtrim(number_format((float)$order->quantity, 6, ',', '.'), '0'), ',') }}" required>
          </div>
          <div>
            <label class="form-label small mb-1">Valor</label>
            <input type="text" name="value" class="form-control mask-money-br" inputmode="decimal" value="{{ !is_null($order->value) ? number_format((float)$order->value, 2, ',', '.') : '' }}">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
      </form>
    </div>
  </div>
</div>
