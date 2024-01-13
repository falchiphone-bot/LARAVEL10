
<div class="container">

             <div class="badge bg-secondary text-wrap" style="width: 100%;font-size: 24px;lign='Center'">

                <a href="{{ route('whatsapp.enviarFlowMenuCadastroBasico', ['recipient_id' => $NomeAtendido->recipient_id,
                    'entry_id' => $NomeAtendido->entry_id]) }}" class="btn btn-primary" tabindex="-1"
                    role="button" aria-disabled="true">Enviar flow menu do cadastro básico</a>


                <a href="{{ route('whatsapp.enviarFlowCadastro', ['recipient_id' => $NomeAtendido->recipient_id,
                                'entry_id' => $NomeAtendido->entry_id]) }}" class="btn btn-success" tabindex="-1"
                role="button" aria-disabled="true">Enviar flow para cadastrar formandos/atletas</a>


                <a href="{{ route('whatsapp.enviarFlowAlterarNomeCompleto', ['recipient_id' => $NomeAtendido->recipient_id,
                    'entry_id' => $NomeAtendido->entry_id]) }}" class="btn btn-warning" tabindex="-1"
                 role="button" aria-disabled="true">Enviar flow para alterar o NOME COMPLETO</a>


                <a href="{{ route('whatsapp.enviarFlowAlterarCPF', ['recipient_id' => $NomeAtendido->recipient_id,
                                'entry_id' => $NomeAtendido->entry_id]) }}" class="btn btn-danger" tabindex="-1"
                role="button" aria-disabled="true">Enviar flow para alterar CPF</a>


                <a href="{{ route('whatsapp.enviarFlowAlterarRG', ['recipient_id' => $NomeAtendido->recipient_id,
                                'entry_id' => $NomeAtendido->entry_id]) }}" class="btn btn-secondary" tabindex="-1"
                role="button" aria-disabled="true">Enviar flow para alterar DOCUMENTO/RG</a>

                <a href="{{ route('whatsapp.enviarFlowAlterarCidadeUf', ['recipient_id' => $NomeAtendido->recipient_id,
                    'entry_id' => $NomeAtendido->entry_id]) }}" class="btn btn-primary" tabindex="-1"
                     role="button" aria-disabled="true">Enviar flow para alterar CIDADE/UF</a>

                     <a href="{{ route('whatsapp.enviarFlowAlterarNascimento', ['recipient_id' => $NomeAtendido->recipient_id,
                        'entry_id' => $NomeAtendido->entry_id]) }}" class="btn btn-danger" tabindex="-1"
                     role="button" aria-disabled="true">Enviar flow para alterar NASCIMENTO</a>

                    <a href="{{ route('whatsapp.enviarFlowAlterarNomeMae', ['recipient_id' => $NomeAtendido->recipient_id,
                        'entry_id' => $NomeAtendido->entry_id]) }}" class="btn btn-success" tabindex="-1"
                    role="button" aria-disabled="true">Enviar flow para alterar NOME DA MÃE</a>

                    <a href="{{ route('whatsapp.enviarFlowAlterarNomePai', ['recipient_id' => $NomeAtendido->recipient_id,
                        'entry_id' => $NomeAtendido->entry_id]) }}" class="btn btn-warning" tabindex="-1"
                    role="button" aria-disabled="true">Enviar flow para alterar NOME DO PAI</a>


                    <a href="{{ route('whatsapp.EnviaMensagemDadosCadastroBasico', ['recipient_id' => $NomeAtendido->recipient_id,
                        'entry_id' => $NomeAtendido->entry_id]) }}" class="btn btn-warning" tabindex="-1"
                    role="button" aria-disabled="true">Enviar os dados do cadastro básico</a>


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
