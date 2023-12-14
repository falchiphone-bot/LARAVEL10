
@if ($NomeAtendido->user_atendimento != null || $NomeAtendido->user_atendimento != trim(Auth::user()->email))

        @if($NomeAtendido->user_atendimento)
            <span style="color: green;"> Cliente sendo atendido por: </span>
            <span style="color: blue;">{{ $NomeAtendido->user_atendimento }}</span>
        @endif
 @endif

 <form action="{{ route('whatsapp.refreshpagina', $id) }}" method="POST"">
        @csrf
        <div class="card" style="background-color: #ffffcc; padding: 20px;">
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

 <form action="{{ route('whatsapp.PesquisaMensagens', $id) }}" method="GET"">
    @csrf
    <div class="card-body" style="background-color: #b3ffb3;">
    <div class="card" style="background-color: #ffffcc; padding: 20px;">
        <input type="hidden" name="recipient_id" value="{{ $NomeAtendido->recipient_id ?? null }}">
        <input type="hidden" name="contactName" value="{{ $NomeAtendido->contactName ?? null }}">
        <input type="hidden" name="entry_id" value="{{ $NomeAtendido->entry_id ?? null }}">
        <input type="hidden" name="status_mensagem_enviada" value="{{ $NomeAtendido->status_mensagem_enviada ?? null }}">

        <div class="col-8">
            {{-- <label for="quantidade_nao_lida">Quantidade de mensagens a apresentar</label>
            <input class="form-control @error('quantidade_mensagem') is-invalid @else is-valid @enderror" name="quantidade_mensagem"
                type="number" id="quantidade_mensagem" value="{{ $model->quantidade_nao_lida ?? null }}"> --}}

            <label for="pesquisar_mensagem">Texto para efetuar pesquisa</label>
            <input class="form-control @error('pesquisar_mensagem') is-invalid @else is-valid @enderror" name="pesquisar_mensagem"
                type="texg" id="pesquisar_mensagem" value="{{ $textopesquisar }}">
        </div>
        <div class="col-4">
            <label for="quantidademensagem">Quantidade de mensagens a apresentar</label>
            <input required class="form-control @error('quantidademensagem') is-invalid @else is-valid @enderror" name="quantidademensagem"
                type="int" id="quantidademensagem" value="100">
        </div>
           <button type="submit" class="btn btn-primary">Pesquisar o texto acima em mensagens</button>
    </div>
</div>
</form>
