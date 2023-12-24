
<div class="container">
        
             <div class="badge bg-primary text-wrap" style="width: 100%;font-size: 24px;lign=˜Center˜">
                <a href="{{ route('whatsapp.enviarFlowAlterarCPF', ['recipient_id' => $NomeAtendido->recipient_id, 
                                'entry_id' => $NomeAtendido->entry_id]) }}" class="btn btn-danger" tabindex="-1"
                role="button" aria-disabled="true">Enviar flow para alterar CPF</a>
             </div>


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
