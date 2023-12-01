
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
