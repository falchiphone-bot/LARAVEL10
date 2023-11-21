
<div class="container">
    <h1 class="text-center bg-success text-white">
        {{ $NomeAtendido->contactName ?? null }}</h1>
        @if ($NomeAtendido->transferido_para)
              Transferido para: {{ $NomeAtendido->transferido_para }}
        @endif

</div>
