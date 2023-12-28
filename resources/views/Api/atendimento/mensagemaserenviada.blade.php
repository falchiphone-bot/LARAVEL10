<form action="{{ route('whatsapp.enviarMensagemRespostaAtendimento', ['id' => $id, 'entry_id' => $NomeAtendido->entry_id] ) }}" method="POST"

    enctype="multipart/form-data">
    @csrf
    <div class="card" style="background-color: #ffffcc; padding: 20px;">
        <div class="form-group">
            <label for="mensagem">Mensagem a ser enviada</label>
            <textarea id="mensagem" name="mensagem" rows="4" cols="50" class="form-control" onfocus="stopPageRefresh();"
                onblur="allowPageRefresh();"></textarea>
        </div>

        <!-- Adicione um campo oculto para enviar recipient_id -->
        <input type="hidden" name="entry_id" value="{{ $NomeAtendido->entry_id ?? null }}">
        <input type="hidden" name="recipient_id" value="{{ $NomeAtendido->recipient_id ?? null }}">
        <input type="hidden" name="contactName" value="{{ $NomeAtendido->contactName ?? null }}">
        <input type="hidden" name="status_mensagem_enviada"
            value="{{ $NomeAtendido->status_mensagem_enviada ?? null }}">

        <div class="form-group">
            <label for="arquivo">Selecionar um arquivo:</label>
            <input type="file" id="arquivo" name="arquivo" class="form-control-file">
        </div>
        <button type="submit" class="btn btn-success">Enviar a
            mensagem</button>
    </div>
</form>
