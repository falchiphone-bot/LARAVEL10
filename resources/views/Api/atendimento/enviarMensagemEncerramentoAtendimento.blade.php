
@if ($NomeAtendido->user_atendimento === trim(Auth::user()->email))
    {{-- @include('Api.atendimento.encerramentodoatendimento') --}}

    {{-- @include('Api.atendimento.mensagemaserenviada') --}}

@endif
