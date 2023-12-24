     <form action="{{ route('whatsapp.ReabrirAposEncerramentoAtendimento', $id) }}" method="POST"
        enctype="multipart/form-data">
        @csrf
        <div class="card" style="background-color: #ffffcc; padding: 20px;">
            <input type="hidden" name="entry_id" value="{{ $NomeAtendido->entry_id  ?? null }}">
            <input type="hidden" name="recipient_id" value="{{ $NomeAtendido->recipient_id ?? null }}">
            <input type="hidden" name="contactName" value="{{ $NomeAtendido->contactName ?? null }}">
            <input type="hidden" name="status_mensagem_enviada"
                value="{{ $NomeAtendido->status_mensagem_enviada ?? null }}">


            <button type="submit" class="btn btn-primary">Abrir e ou reabrir o
                atendimento após encerramento</button>
        </div>
    </form>

