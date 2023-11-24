@if ($NomeAtendido->transferido_para === Auth::user()->email
  && $NomeAtendido->user_atendimento !== Auth::user()->email)

    <form action="{{ route('whatsapp.enviarMensagemInicioAtendimento', $id) }}" method="POST"
        enctype="multipart/form-data">
        @csrf
        <div class="card" style="background-color: #ffffcc; padding: 20px;">
            <input type="hidden" name="recipient_id" value="{{ $NomeAtendido->recipient_id ?? null }}">
            <input type="hidden" name="contactName" value="{{ $NomeAtendido->contactName ?? null }}">
            <input type="hidden" name="status_mensagem_enviada"
                value="{{ $NomeAtendido->status_mensagem_enviada ?? null }}">


            <button type="submit" class="btn btn-primary">Iniciar o
                atendimento por transferência</button>


        </div>
    </form>
@elseif ($NomeAtendido->user_atendimento == null)
    <form action="{{ route('whatsapp.enviarMensagemInicioAtendimento', $id) }}" method="POST"
        enctype="multipart/form-data">
        @csrf
        <div class="card" style="background-color: #ffffcc; padding: 20px;">
            <input type="hidden" name="recipient_id" value="{{ $NomeAtendido->recipient_id ?? null }}">
            <input type="hidden" name="contactName" value="{{ $NomeAtendido->contactName ?? null }}">
            <input type="hidden" name="status_mensagem_enviada"
                value="{{ $NomeAtendido->status_mensagem_enviada ?? null }}">


            <button type="submit" class="btn btn-primary">Iniciar o
                atendimento</button>


        </div>
    </form>
@endif
