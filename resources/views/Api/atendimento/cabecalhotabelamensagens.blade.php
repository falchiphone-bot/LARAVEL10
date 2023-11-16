<div class="card-body" style="max-width: 1024px; max-height: 500px; overflow: hidden;">
    {{-- @if (session('usuarioatendente') !== null) --}}
    <div class="container">
        <h1 class="text-center bg-success text-white">
            {{ $NomeAtendido->contactName ?? null }}</h1>
    </div>

    @if ($NomeAtendido->user_atendimento != null && $NomeAtendido->user_atendimento != trim(Auth::user()->email))
        <span style="color: green;"> Cliente sendo atendido por: </span>
        <span style="color: blue;">{{ $NomeAtendido->user_atendimento }}</span>
    @endif

    @if ($NomeAtendido->user_atendimento === trim(Auth::user()->email))
        <form action="{{ route('whatsapp.enviarMensagemEncerramentoAtendimento', $id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <div class="card" style="background-color: #ffffcc; padding: 20px;">
                <input type="hidden" name="recipient_id" value="{{ $NomeAtendido->recipient_id ?? null }}">
                <input type="hidden" name="contactName" value="{{ $NomeAtendido->contactName ?? null }}">
                <input type="hidden" name="status_mensagem_enviada"
                    value="{{ $NomeAtendido->status_mensagem_enviada ?? null }}">

                <button type="submit" class="btn btn-danger">Encerramento do
                    atendimento</button>
            </div>
        </form>

        <form action="{{ route('whatsapp.enviarMensagemRespostaAtendimento', $id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <div class="card" style="background-color: #ffffcc; padding: 20px;">
                <div class="form-group">
                    <label for="mensagem">Mensagem a ser enviada</label>
                    <textarea id="mensagem" name="mensagem" rows="4" cols="50" class="form-control" onfocus="stopPageRefresh();"
                        onblur="allowPageRefresh();"></textarea>
                </div>

                <!-- Adicione um campo oculto para enviar recipient_id -->
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
    @endif

    @if ($NomeAtendido->quantidade_nao_lida > 0)
        @can('WHATSAPP - ATENDIMENTO - INICIAR ATENDIMENTO')
            @include('Api.atendimento.enviarinicioatendimento')
        @endcan
    @endif

    @if ($NomeAtendido->quantidade_nao_lida == 0)
        @can('WHATSAPP - ATENDIMENTO - INICIAR ATENDIMENTO COM MENSAGEM NAO LIDA')
            @include('Api.atendimento.enviarinicioatendimento')
        @endcan
    @endif
</div>
