
<div class="container">
    <h1 class="text-center bg-success text-white">
        {{ $NomeAtendido->contactName ?? null }}</h1>

        @if($QuantidadeCanalAtendimento == 1) 
        Canal de entrada:
         <span class="red-strong">{{ $NomeAtendido->TelefoneWhatsApp->usuario }}</span>
             {{ '('. $NomeAtendido->TelefoneWhatsApp->telefone . ')'}}
             <hr>
         @endif

        @if ($NomeAtendido->transferido_para)
              Transferido para: {{ $NomeAtendido->transferido_para }}
        @endif

</div>
