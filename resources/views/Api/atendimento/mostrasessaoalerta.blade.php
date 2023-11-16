@if (session('MensagemNaoPreenchida'))
    <div class="alert alert-danger">
        {{ session('MensagemNaoPreenchida') }}
    </div>
@elseif (session('error'))
    <div class="alert alert-danger">
        {{ session('errordesta') }}
    </div>
@elseif (session('usuarioatendente'))
    <div class="alert alert-danger">
        {{-- {{ session('usuarioatendente') }} --}}
    </div>
@endif
