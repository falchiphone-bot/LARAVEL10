<?php
namespace App\Services;
use App\Models\webhookAtendimentoEncerrado;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\webhook;
use App\Models\WebhookContact;
use App\Models\WebhookConfig;
use App\Models\WebhookTemplate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

class WebhookServicoInteligencia
{

    public static function selecaotexto($entry)
    {
        Log::info('Em selecao texto pelo procedimento de inteligencia artificial');
        $entry_id = $entry['id'] ?? null;
        $body  =  $entry[0]['changes'][0]['value']['messages'][0]['body'] ?? null;
        $messagesFrom = $entry['changes'][0]['value']['messages'][0]['from'] ?? null;
        $body = $entry['changes'][0]['value']['messages'][0]['text']['body'] ?? null;
        $recipient_id  =  $messagesFrom;

        Log::info('CANAL: '. $entry_id);
        Log::info('TELEFONE: '. $recipient_id);

                 if($body == 'OPÇÃO' || $body == 'opção' || $body == 'Opção' || $body == 'Opções'
                 || $body == 'OPCAO' || $body == 'opcao' || $body == 'Opcao' || $body == 'cadastro'
                 || $body == 'CADASTRO' || $body == 'Cadastro' || $body == 'Cadastros' || $body == 'cadastros')
                    {
                        Log::info('Pesquisa:'.$body);
                        WebhookContactsEnviarFlow::
                        EnviaMensagemFlowMenuCadastroBasico($recipient_id, $entry_id);
                    }else
                    if($body =='AVALIACAO' ||
                       $body =='avaliacao' ||
                       $body =='Avaliacao' ||
                       $body =='AVALIAÇÃO' ||
                       $body =='avaliação' ||
                       $body =='Avaliação' ||
                       $body == 'AVALIAÇÕES' ||
                       $body == 'avaliações' ||
                       $body == 'Avaliações' ||
                       $body == 'AVALIACOES' ||
                       $body == 'avaliacoes' ||
                       $body == 'Avaliacoes' || $body == 'AVALIAÇÃO' ||
                      $body == 'Avaliação' ||
                      $body == 'Avaliação')
                       {
                           Log::info('Pesquisa:'.$body);
                           WebhookContactsEnviarFlow::
                           enviarFlowAvaliacao29012024_01032024($recipient_id, $entry_id);
                       }else
                       if($body == 'saf' ||
                        $body == 'SAF' ||
                        $body == 'Saf' ||
                        $body == 'TANABI SAF' ||
                        $body == 'Tanabi Saf' ||
                        $body == 'Tanabi saf' ||
                        $body == 'tanabi saf')
                        {
                                Log::info('Pesquisa:'.$body);
                                WebhookMensagensAprovadas::
                                pagina_tanabi_saf_facebook($recipient_id, $entry_id);

                        }

    }


}

