
@if ($NomeAtendido->user_atendimento != null && $NomeAtendido->user_atendimento != trim(Auth::user()->email))
        <span style="color: green;"> Cliente sendo atendido por: </span>
        <span style="color: blue;">{{ $NomeAtendido->user_atendimento }}</span>
    @endif
