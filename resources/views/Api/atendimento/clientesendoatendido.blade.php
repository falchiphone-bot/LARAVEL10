@if ($NomeAtendido->user_atendimento != null || $NomeAtendido->user_atendimento != trim(Auth::user()->email))
Se usuário atendimento é nulo ou usuário é diferente do usuário logado <br
    @if($NomeAtendido->user_atendimento)
        <span style="color: green; margin: 0; padding: 0;">Cliente sendo atendido por: </span>
        <span style="color: blue; margin: 0; padding: 0;">{{ $NomeAtendido->user_atendimento }}</span>
    @endif
@endif

<form action="{{ route('whatsapp.refreshpagina', ['id' => $id, 'entry_id' => $NomeAtendido->entry_id ]) }}" method="POST">
    @csrf
    <div class="card" style="background-color: #ffffcc; padding: 1px; margin-top: 1px;">
        <input type="hidden" name="recipient_id" value="{{ $NomeAtendido->recipient_id ?? null }}">
        <input type="hidden" name="contactName" value="{{ $NomeAtendido->contactName ?? null }}">
        <input type="hidden" name="status_mensagem_enviada" value="{{ $NomeAtendido->status_mensagem_enviada ?? null }}">

        @if ($NomeAtendido->pagina_refresh == null || $NomeAtendido->pagina_refresh == false)
            <button type="submit" class="btn btn-success">Ativar recarregamento da página</button>
        @else
            <button type="submit" class="btn btn-danger">Desativar recarregamento da página</button>
        @endif
    </div>
</form>
<form action="{{ route('whatsapp.carregamentomultimidia', ['id' => $id, 'entry_id' => $NomeAtendido->entry_id ]) }}" method="POST">
    @csrf
    <div class="card" style="background-color: #ffffcc; padding: 1px; margin-top: 1px;">
        <input type="hidden" name="recipient_id" value="{{ $NomeAtendido->recipient_id ?? null }}">
        <input type="hidden" name="contactName" value="{{ $NomeAtendido->contactName ?? null }}">
        <input type="hidden" name="status_mensagem_enviada" value="{{ $NomeAtendido->status_mensagem_enviada ?? null }}">

        @if ($NomeAtendido->carregamento_multimidia == null || $NomeAtendido->carregamento_multimidia == false)
            <button type="submit" class="btn btn-success">Ativar recarregamento multimídia</button>
        @else
            <button type="submit" class="btn btn-danger">Desativar recarregamento multimídia</button>
        @endif
    </div>
</form>

<form action="{{ route('whatsapp.PesquisaMensagens', $id) }}" method="GET">
    @csrf
    <div class="card-body" style="background-color: #b3ffb3;">
        <div class="card" style="background-color: #ffffcc; padding: 1px; margin-top: 1x;">
            <input type="hidden" name="recipient_id" value="{{ $NomeAtendido->recipient_id ?? null }}">
            <input type="hidden" name="contactName" value="{{ $NomeAtendido->contactName ?? null }}">
            <input type="hidden" name="entry_id" value="{{ $NomeAtendido->entry_id ?? null }}">
            <input type="hidden" name="status_mensagem_enviada" value="{{ $NomeAtendido->status_mensagem_enviada ?? null }}">

            <div class="col-8">
                <label for="pesquisar_mensagem">Texto para efetuar pesquisa</label>
                <input class="form-control @error('pesquisar_mensagem') is-invalid @else is-valid @enderror"
                    name="pesquisar_mensagem" type="text" id="pesquisar_mensagem" value="{{ $textopesquisar }}">
            </div>
            <div class="col-4">
                <label for="quantidademensagem">Quantidade de mensagens a apresentar</label>
                <input required class="form-control @error('quantidademensagem') is-invalid @else is-valid @enderror"
                    name="quantidademensagem" type="number" id="quantidademensagem" value="100">
            </div>
            <button type="submit" class="btn btn-primary">Pesquisar o texto acima em mensagens</button>
        </div>
    </div>
</form>
